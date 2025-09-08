<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'status',
        'payment_method',
        'amount',
        'bonus_amount',
        'starts_at',
        'expires_at',
        'cancelled_at',
        'cancellation_reason',
        'metadata',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'metadata' => 'array',
        'amount' => 'decimal:2',
    ];

    /**
     * Get the user that owns the subscription.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the payments for the subscription.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Check if subscription is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active' && 
               $this->expires_at && 
               $this->expires_at->isFuture();
    }

    /**
     * Check if subscription is expired.
     */
    public function isExpired(): bool
    {
        return $this->status === 'expired' || 
               ($this->expires_at && $this->expires_at->isPast());
    }

    /**
     * Check if subscription is cancelled.
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * Get subscription duration in days.
     */
    public function getDurationInDays(): int
    {
        if (!$this->starts_at || !$this->expires_at) {
            return 0;
        }

        return $this->starts_at->diffInDays($this->expires_at);
    }

    /**
     * Get remaining days.
     */
    public function getRemainingDays(): int
    {
        if (!$this->expires_at || $this->expires_at->isPast()) {
            return 0;
        }

        return now()->diffInDays($this->expires_at);
    }

    /**
     * Cancel subscription.
     */
    public function cancel(string $reason = null): bool
    {
        return $this->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancellation_reason' => $reason,
        ]);
    }

    /**
     * Extend subscription.
     */
    public function extend(int $days): bool
    {
        $newExpiresAt = $this->expires_at ? 
            $this->expires_at->addDays($days) : 
            now()->addDays($days);

        return $this->update([
            'expires_at' => $newExpiresAt,
            'status' => 'active',
        ]);
    }

    /**
     * Scope for active subscriptions.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
                    ->where('expires_at', '>', now());
    }

    /**
     * Scope for expired subscriptions.
     */
    public function scopeExpired($query)
    {
        return $query->where(function ($q) {
            $q->where('status', 'expired')
              ->orWhere('expires_at', '<=', now());
        });
    }

    /**
     * Scope for cancelled subscriptions.
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    /**
     * Scope for Pro subscriptions.
     */
    public function scopePro($query)
    {
        return $query->where('type', 'pro');
    }

    /**
     * Scope for Premium subscriptions.
     */
    public function scopePremium($query)
    {
        return $query->where('type', 'premium');
    }

    /**
     * Get subscription type display name.
     */
    public function getTypeDisplayNameAttribute(): string
    {
        return match($this->type) {
            'pro' => 'Pro',
            'premium' => 'Premium',
            default => 'Unknown'
        };
    }

    /**
     * Get subscription status display name.
     */
    public function getStatusDisplayNameAttribute(): string
    {
        return match($this->status) {
            'active' => 'Активна',
            'expired' => 'Истекла',
            'cancelled' => 'Отменена',
            default => 'Неизвестно'
        };
    }
}
