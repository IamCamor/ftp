<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'subscription_id',
        'payment_id',
        'provider',
        'status',
        'type',
        'amount',
        'currency',
        'bonus_amount',
        'description',
        'provider_data',
        'metadata',
        'paid_at',
        'expires_at',
    ];

    protected $casts = [
        'provider_data' => 'array',
        'metadata' => 'array',
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * Get the user that owns the payment.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the subscription that owns the payment.
     */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    /**
     * Check if payment is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if payment is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if payment is failed.
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Check if payment is cancelled.
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * Check if payment is refunded.
     */
    public function isRefunded(): bool
    {
        return $this->status === 'refunded';
    }

    /**
     * Mark payment as completed.
     */
    public function markAsCompleted(array $providerData = []): bool
    {
        return $this->update([
            'status' => 'completed',
            'paid_at' => now(),
            'provider_data' => array_merge($this->provider_data ?? [], $providerData),
        ]);
    }

    /**
     * Mark payment as failed.
     */
    public function markAsFailed(string $reason = null): bool
    {
        return $this->update([
            'status' => 'failed',
            'metadata' => array_merge($this->metadata ?? [], ['failure_reason' => $reason]),
        ]);
    }

    /**
     * Mark payment as cancelled.
     */
    public function markAsCancelled(string $reason = null): bool
    {
        return $this->update([
            'status' => 'cancelled',
            'metadata' => array_merge($this->metadata ?? [], ['cancellation_reason' => $reason]),
        ]);
    }

    /**
     * Mark payment as refunded.
     */
    public function markAsRefunded(string $reason = null): bool
    {
        return $this->update([
            'status' => 'refunded',
            'metadata' => array_merge($this->metadata ?? [], ['refund_reason' => $reason]),
        ]);
    }

    /**
     * Scope for completed payments.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for pending payments.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for failed payments.
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope for subscription payments.
     */
    public function scopeSubscription($query)
    {
        return $query->whereIn('type', ['subscription_pro', 'subscription_premium']);
    }

    /**
     * Scope for Pro subscription payments.
     */
    public function scopeProSubscription($query)
    {
        return $query->where('type', 'subscription_pro');
    }

    /**
     * Scope for Premium subscription payments.
     */
    public function scopePremiumSubscription($query)
    {
        return $query->where('type', 'subscription_premium');
    }

    /**
     * Get provider display name.
     */
    public function getProviderDisplayNameAttribute(): string
    {
        return match($this->provider) {
            'yandex_pay' => 'Яндекс.Платежи',
            'sber_pay' => 'Сбербанк',
            'apple_pay' => 'Apple Pay',
            'google_pay' => 'Google Pay',
            'bonuses' => 'Бонусы',
            default => 'Неизвестно'
        };
    }

    /**
     * Get status display name.
     */
    public function getStatusDisplayNameAttribute(): string
    {
        return match($this->status) {
            'pending' => 'Ожидает оплаты',
            'processing' => 'Обрабатывается',
            'completed' => 'Оплачен',
            'failed' => 'Ошибка оплаты',
            'cancelled' => 'Отменен',
            'refunded' => 'Возвращен',
            default => 'Неизвестно'
        };
    }

    /**
     * Get type display name.
     */
    public function getTypeDisplayNameAttribute(): string
    {
        return match($this->type) {
            'subscription_pro' => 'Подписка Pro',
            'subscription_premium' => 'Подписка Premium',
            'bonus_purchase' => 'Покупка бонусов',
            default => 'Неизвестно'
        };
    }

    /**
     * Get formatted amount.
     */
    public function getFormattedAmountAttribute(): string
    {
        if ($this->bonus_amount) {
            return number_format($this->bonus_amount) . ' бонусов';
        }

        return number_format($this->amount, 2) . ' ' . $this->currency;
    }
}
