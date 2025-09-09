<?php

namespace App\Listeners;

use App\Events\UserRegistered;
use App\Events\CatchRecorded;
use App\Events\PaymentCompleted;
use App\Events\PointCreated;
use App\Services\TelegramService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendTelegramNotification implements ShouldQueue
{
    use InteractsWithQueue;

    private TelegramService $telegramService;

    /**
     * Create the event listener.
     */
    public function __construct(TelegramService $telegramService)
    {
        $this->telegramService = $telegramService;
    }

    /**
     * Handle user registration event.
     */
    public function handleUserRegistered(UserRegistered $event): void
    {
        try {
            $user = $event->user;
            
            $data = [
                '{name}' => $user->name,
                '{username}' => $user->username,
                '{email}' => $user->email,
                '{role}' => $user->role,
            ];

            $this->telegramService->sendUserRegistrationNotification($data);
        } catch (\Exception $e) {
            Log::error('Failed to send user registration notification', [
                'error' => $e->getMessage(),
                'user_id' => $event->user->id
            ]);
        }
    }

    /**
     * Handle catch recorded event.
     */
    public function handleCatchRecorded(CatchRecorded $event): void
    {
        try {
            $catch = $event->catch;
            $user = $catch->user;
            
            $data = [
                '{user_name}' => $user->name,
                '{username}' => $user->username,
                '{fish_type}' => $catch->fish_type ?? 'Unknown',
                '{weight}' => $catch->weight,
                '{length}' => $catch->length,
                '{location}' => $catch->point ? $catch->point->title : 'Unknown location',
            ];

            $this->telegramService->sendCatchNotification($data);
        } catch (\Exception $e) {
            Log::error('Failed to send catch notification', [
                'error' => $e->getMessage(),
                'catch_id' => $event->catch->id
            ]);
        }
    }

    /**
     * Handle payment completed event.
     */
    public function handlePaymentCompleted(PaymentCompleted $event): void
    {
        try {
            $payment = $event->payment;
            $user = $payment->user;
            
            $data = [
                '{user_name}' => $user->name,
                '{username}' => $user->username,
                '{amount}' => $payment->amount,
                '{currency}' => $payment->currency,
                '{payment_type}' => $payment->type,
                '{status}' => $payment->status,
            ];

            $this->telegramService->sendPaymentNotification($data);
        } catch (\Exception $e) {
            Log::error('Failed to send payment notification', [
                'error' => $e->getMessage(),
                'payment_id' => $event->payment->id
            ]);
        }
    }

    /**
     * Handle point created event.
     */
    public function handlePointCreated(PointCreated $event): void
    {
        try {
            $point = $event->point;
            $user = $point->user;
            
            $data = [
                '{user_name}' => $user->name,
                '{username}' => $user->username,
                '{title}' => $point->title,
                '{location}' => "{$point->lat}, {$point->lng}",
                '{privacy}' => $point->privacy,
            ];

            $this->telegramService->sendPointNotification($data);
        } catch (\Exception $e) {
            Log::error('Failed to send point notification', [
                'error' => $e->getMessage(),
                'point_id' => $event->point->id
            ]);
        }
    }
}
