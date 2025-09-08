<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    /**
     * Get user's payments.
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        $payments = $user->payments()
            ->with(['subscription'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $payments
        ]);
    }

    /**
     * Get payment details.
     */
    public function show(Payment $payment): JsonResponse
    {
        $user = Auth::user();
        
        if ($payment->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Доступ запрещен'
            ], 403);
        }

        $payment->load(['subscription', 'user']);

        return response()->json([
            'success' => true,
            'data' => $payment
        ]);
    }

    /**
     * Process payment.
     */
    public function process(Request $request): JsonResponse
    {
        $request->validate([
            'payment_id' => 'required|string',
            'provider' => 'required|in:yandex_pay,sber_pay,apple_pay,google_pay,bonuses',
            'provider_data' => 'array',
        ]);

        $user = Auth::user();
        $paymentId = $request->payment_id;
        $provider = $request->provider;
        $providerData = $request->provider_data ?? [];

        $payment = Payment::where('payment_id', $paymentId)
            ->where('user_id', $user->id)
            ->first();

        if (!$payment) {
            return response()->json([
                'success' => false,
                'message' => 'Платеж не найден'
            ], 404);
        }

        if ($payment->isCompleted()) {
            return response()->json([
                'success' => false,
                'message' => 'Платеж уже обработан'
            ], 400);
        }

        if ($payment->isFailed()) {
            return response()->json([
                'success' => false,
                'message' => 'Платеж не удался'
            ], 400);
        }

        DB::beginTransaction();
        try {
            if ($provider === 'bonuses') {
                $this->processBonusPayment($payment, $user);
            } else {
                $this->processExternalPayment($payment, $provider, $providerData);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Платеж обработан успешно',
                'data' => $payment->fresh(['subscription'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Payment processing failed', [
                'payment_id' => $paymentId,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Ошибка при обработке платежа: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel payment.
     */
    public function cancel(Payment $payment, Request $request): JsonResponse
    {
        $user = Auth::user();
        
        if ($payment->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Доступ запрещен'
            ], 403);
        }

        if ($payment->isCompleted()) {
            return response()->json([
                'success' => false,
                'message' => 'Нельзя отменить завершенный платеж'
            ], 400);
        }

        $reason = $request->input('reason', 'Отменен пользователем');
        $payment->markAsCancelled($reason);

        return response()->json([
            'success' => true,
            'message' => 'Платеж отменен'
        ]);
    }

    /**
     * Request refund.
     */
    public function refund(Payment $payment, Request $request): JsonResponse
    {
        $user = Auth::user();
        
        if ($payment->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Доступ запрещен'
            ], 403);
        }

        if (!$payment->isCompleted()) {
            return response()->json([
                'success' => false,
                'message' => 'Можно вернуть только завершенные платежи'
            ], 400);
        }

        if ($payment->isRefunded()) {
            return response()->json([
                'success' => false,
                'message' => 'Платеж уже возвращен'
            ], 400);
        }

        // Check refund eligibility
        if (!$this->isRefundEligible($payment)) {
            return response()->json([
                'success' => false,
                'message' => 'Срок возврата истек'
            ], 400);
        }

        $reason = $request->input('reason', 'Запрошен пользователем');
        
        DB::beginTransaction();
        try {
            $payment->markAsRefunded($reason);
            
            // Cancel associated subscription if exists
            if ($payment->subscription) {
                $payment->subscription->cancel('Возврат платежа');
            }

            // Refund bonus balance if paid with bonuses
            if ($payment->bonus_amount && $payment->bonus_amount > 0) {
                $user->addBonusBalance($payment->bonus_amount);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Запрос на возврат отправлен'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при обработке возврата: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get payment methods.
     */
    public function methods(): JsonResponse
    {
        $methods = collect(config('subscription.payment_methods'))
            ->filter(fn($method) => $method['enabled'])
            ->map(fn($method, $key) => [
                'id' => $key,
                'name' => $method['name'],
                'icon' => $method['icon'],
            ])
            ->values();

        return response()->json([
            'success' => true,
            'data' => $methods
        ]);
    }

    /**
     * Process bonus payment.
     */
    private function processBonusPayment(Payment $payment, $user): void
    {
        if (!$user->hasEnoughBonusBalance($payment->bonus_amount)) {
            throw new \Exception('Недостаточно бонусов');
        }

        // Deduct bonus balance
        $user->deductBonusBalance($payment->bonus_amount);

        // Mark payment as completed
        $payment->markAsCompleted([
            'bonus_payment' => true,
            'bonus_balance_before' => $user->bonus_balance + $payment->bonus_amount,
            'bonus_balance_after' => $user->bonus_balance,
        ]);

        // Activate subscription if exists
        if ($payment->subscription) {
            $this->activateSubscription($payment->subscription);
        }
    }

    /**
     * Process external payment.
     */
    private function processExternalPayment(Payment $payment, string $provider, array $providerData): void
    {
        // Here you would integrate with actual payment providers
        // For now, we'll simulate successful payment
        
        $payment->markAsCompleted(array_merge($providerData, [
            'provider' => $provider,
            'processed_at' => now()->toISOString(),
        ]));

        // Activate subscription if exists
        if ($payment->subscription) {
            $this->activateSubscription($payment->subscription);
        }
    }

    /**
     * Activate subscription.
     */
    private function activateSubscription(Subscription $subscription): void
    {
        $subscription->update([
            'status' => 'active',
            'starts_at' => now(),
            'expires_at' => now()->addDays($subscription->metadata['duration_days'] ?? 30),
        ]);

        // Update user role if needed
        $user = $subscription->user;
        if ($subscription->type === 'premium' && !$user->isPremium()) {
            $user->update([
                'is_premium' => true,
                'premium_expires_at' => $subscription->expires_at,
            ]);
        }
    }

    /**
     * Check if payment is eligible for refund.
     */
    private function isRefundEligible(Payment $payment): bool
    {
        if (!config('subscription.refund.enabled')) {
            return false;
        }

        $maxRefundDays = config('subscription.refund.max_days', 14);
        $refundDeadline = $payment->paid_at->addDays($maxRefundDays);

        return now()->isBefore($refundDeadline);
    }
}
