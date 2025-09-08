<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SubscriptionController extends Controller
{
    /**
     * Get user's subscriptions.
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        $subscriptions = $user->subscriptions()
            ->with(['payments'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $subscriptions
        ]);
    }

    /**
     * Get subscription plans and pricing.
     */
    public function plans(): JsonResponse
    {
        $plans = [
            'pro' => [
                'name' => 'Pro',
                'price_rub' => config('subscription.pro.price_rub'),
                'price_bonus' => config('subscription.pro.price_bonus'),
                'duration_days' => config('subscription.pro.duration_days'),
                'features' => config('subscription.pro.features'),
                'description' => 'Расширенные возможности для активных рыбаков',
            ],
            'premium' => [
                'name' => 'Premium',
                'price_rub' => config('subscription.premium.price_rub'),
                'price_bonus' => config('subscription.premium.price_bonus'),
                'duration_days' => config('subscription.premium.duration_days'),
                'features' => config('subscription.premium.features'),
                'crown_icon_url' => config('subscription.premium.crown_icon_url'),
                'description' => 'Максимальные возможности с короной и приоритетом',
            ],
        ];

        $paymentMethods = collect(config('subscription.payment_methods'))
            ->filter(fn($method) => $method['enabled'])
            ->map(fn($method, $key) => [
                'id' => $key,
                'name' => $method['name'],
                'icon' => $method['icon'],
            ])
            ->values();

        return response()->json([
            'success' => true,
            'data' => [
                'plans' => $plans,
                'payment_methods' => $paymentMethods,
                'user_bonus_balance' => Auth::user()->bonus_balance,
            ]
        ]);
    }

    /**
     * Create a new subscription.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'type' => 'required|in:pro,premium',
            'payment_method' => 'required|in:yandex_pay,sber_pay,apple_pay,google_pay,bonuses',
            'use_trial' => 'boolean',
        ]);

        $user = Auth::user();
        $type = $request->type;
        $paymentMethod = $request->payment_method;
        $useTrial = $request->boolean('use_trial', false);

        // Check if user already has active subscription of this type
        if ($user->hasActiveSubscription($type)) {
            return response()->json([
                'success' => false,
                'message' => 'У вас уже есть активная подписка этого типа'
            ], 400);
        }

        // Check trial eligibility
        if ($useTrial && !$this->canUseTrial($user, $type)) {
            return response()->json([
                'success' => false,
                'message' => 'Пробный период недоступен'
            ], 400);
        }

        DB::beginTransaction();
        try {
            // Create subscription
            $subscription = $this->createSubscription($user, $type, $paymentMethod, $useTrial);
            
            // Create payment
            $payment = $this->createPayment($user, $subscription, $paymentMethod, $useTrial);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Подписка создана успешно',
                'data' => [
                    'subscription' => $subscription->load('payments'),
                    'payment' => $payment,
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при создании подписки: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get subscription details.
     */
    public function show(Subscription $subscription): JsonResponse
    {
        $user = Auth::user();
        
        if ($subscription->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Доступ запрещен'
            ], 403);
        }

        $subscription->load(['payments', 'user']);

        return response()->json([
            'success' => true,
            'data' => $subscription
        ]);
    }

    /**
     * Cancel subscription.
     */
    public function cancel(Subscription $subscription, Request $request): JsonResponse
    {
        $user = Auth::user();
        
        if ($subscription->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Доступ запрещен'
            ], 403);
        }

        if (!$subscription->isActive()) {
            return response()->json([
                'success' => false,
                'message' => 'Подписка не активна'
            ], 400);
        }

        $reason = $request->input('reason', 'Отменена пользователем');
        $subscription->cancel($reason);

        return response()->json([
            'success' => true,
            'message' => 'Подписка отменена'
        ]);
    }

    /**
     * Extend subscription.
     */
    public function extend(Subscription $subscription, Request $request): JsonResponse
    {
        $user = Auth::user();
        
        if ($subscription->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Доступ запрещен'
            ], 403);
        }

        $request->validate([
            'days' => 'required|integer|min:1|max:365',
        ]);

        $days = $request->days;
        $subscription->extend($days);

        return response()->json([
            'success' => true,
            'message' => "Подписка продлена на {$days} дней"
        ]);
    }

    /**
     * Get user's subscription status.
     */
    public function status(): JsonResponse
    {
        $user = Auth::user();
        
        $activeSubscriptions = $user->getActiveSubscriptions();
        $isPro = $user->isPro();
        $isPremium = $user->isPremium();
        $crownIconUrl = $user->getCrownIconUrl();

        return response()->json([
            'success' => true,
            'data' => [
                'is_pro' => $isPro,
                'is_premium' => $isPremium,
                'crown_icon_url' => $crownIconUrl,
                'bonus_balance' => $user->bonus_balance,
                'active_subscriptions' => $activeSubscriptions,
                'role' => $user->role,
            ]
        ]);
    }

    /**
     * Create subscription.
     */
    private function createSubscription($user, string $type, string $paymentMethod, bool $useTrial): Subscription
    {
        $config = config("subscription.{$type}");
        $durationDays = $useTrial ? config('subscription.trial.duration_days') : $config['duration_days'];
        
        $startsAt = now();
        $expiresAt = $startsAt->copy()->addDays($durationDays);

        return Subscription::create([
            'user_id' => $user->id,
            'type' => $type,
            'status' => 'active',
            'payment_method' => $paymentMethod,
            'amount' => $useTrial ? 0 : $config['price_rub'],
            'bonus_amount' => $useTrial ? 0 : $config['price_bonus'],
            'starts_at' => $startsAt,
            'expires_at' => $expiresAt,
            'metadata' => [
                'is_trial' => $useTrial,
                'duration_days' => $durationDays,
            ],
        ]);
    }

    /**
     * Create payment.
     */
    private function createPayment($user, Subscription $subscription, string $paymentMethod, bool $useTrial): Payment
    {
        $config = config("subscription.{$subscription->type}");
        $amount = $useTrial ? 0 : $config['price_rub'];
        $bonusAmount = $useTrial ? 0 : $config['price_bonus'];

        // Generate unique payment ID
        $paymentId = 'pay_' . time() . '_' . $user->id . '_' . uniqid();

        return Payment::create([
            'user_id' => $user->id,
            'subscription_id' => $subscription->id,
            'payment_id' => $paymentId,
            'provider' => $paymentMethod,
            'status' => $useTrial ? 'completed' : 'pending',
            'type' => "subscription_{$subscription->type}",
            'amount' => $amount,
            'bonus_amount' => $bonusAmount,
            'description' => "Подписка {$subscription->type_display_name}" . ($useTrial ? ' (пробный период)' : ''),
            'paid_at' => $useTrial ? now() : null,
            'expires_at' => $useTrial ? null : now()->addHours(24),
        ]);
    }

    /**
     * Check if user can use trial.
     */
    private function canUseTrial($user, string $type): bool
    {
        if (!config('subscription.trial.enabled')) {
            return false;
        }

        if (!in_array($type, config('subscription.trial.types'))) {
            return false;
        }

        // Check if user already used trial for this subscription type
        return !$user->subscriptions()
            ->where('type', $type)
            ->whereJsonContains('metadata->is_trial', true)
            ->exists();
    }
}
