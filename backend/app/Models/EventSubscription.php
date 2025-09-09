<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventSubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'event_id',
        'status',
        'notifications_enabled',
        'reminders_enabled',
        'news_enabled',
        'reminder_hours_before',
        'email_notifications',
        'push_notifications',
        'sms_notifications',
        'notes',
        'is_attending',
        'attending_confirmed_at',
        'subscribed_at',
        'last_notification_at',
    ];

    protected $casts = [
        'notifications_enabled' => 'boolean',
        'reminders_enabled' => 'boolean',
        'news_enabled' => 'boolean',
        'email_notifications' => 'boolean',
        'push_notifications' => 'boolean',
        'sms_notifications' => 'boolean',
        'is_attending' => 'boolean',
        'attending_confirmed_at' => 'datetime',
        'subscribed_at' => 'datetime',
        'last_notification_at' => 'datetime',
    ];

    /**
     * Get the user that owns the subscription
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the event that owns the subscription
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Scope for active subscriptions
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'subscribed');
    }

    /**
     * Scope for hidden subscriptions
     */
    public function scopeHidden($query)
    {
        return $query->where('status', 'hidden');
    }

    /**
     * Scope for attending users
     */
    public function scopeAttending($query)
    {
        return $query->where('is_attending', true);
    }

    /**
     * Scope for notifications enabled
     */
    public function scopeWithNotifications($query)
    {
        return $query->where('notifications_enabled', true);
    }

    /**
     * Scope for reminders enabled
     */
    public function scopeWithReminders($query)
    {
        return $query->where('reminders_enabled', true);
    }

    /**
     * Scope for news enabled
     */
    public function scopeWithNews($query)
    {
        return $query->where('news_enabled', true);
    }

    /**
     * Get subscription status description
     */
    public function getStatusDescriptionAttribute(): string
    {
        return match ($this->status) {
            'subscribed' => 'Подписан',
            'unsubscribed' => 'Отписан',
            'hidden' => 'Скрыт',
            default => 'Неизвестно'
        };
    }

    /**
     * Check if user can receive notifications
     */
    public function canReceiveNotifications(): bool
    {
        return $this->status === 'subscribed' && $this->notifications_enabled;
    }

    /**
     * Check if user can receive reminders
     */
    public function canReceiveReminders(): bool
    {
        return $this->status === 'subscribed' && $this->reminders_enabled;
    }

    /**
     * Check if user can receive news
     */
    public function canReceiveNews(): bool
    {
        return $this->status === 'subscribed' && $this->news_enabled;
    }

    /**
     * Get notification channels
     */
    public function getNotificationChannels(): array
    {
        $channels = [];
        
        if ($this->push_notifications) {
            $channels[] = 'push';
        }
        
        if ($this->email_notifications) {
            $channels[] = 'email';
        }
        
        if ($this->sms_notifications) {
            $channels[] = 'sms';
        }
        
        return $channels;
    }

    /**
     * Update last notification time
     */
    public function updateLastNotificationTime(): void
    {
        $this->update(['last_notification_at' => now()]);
    }

    /**
     * Confirm attendance
     */
    public function confirmAttendance(): void
    {
        $this->update([
            'is_attending' => true,
            'attending_confirmed_at' => now(),
        ]);
    }

    /**
     * Cancel attendance
     */
    public function cancelAttendance(): void
    {
        $this->update([
            'is_attending' => false,
            'attending_confirmed_at' => null,
        ]);
    }

    /**
     * Hide subscription (user won't receive notifications but event remains in catalog)
     */
    public function hide(): void
    {
        $this->update(['status' => 'hidden']);
    }

    /**
     * Unhide subscription
     */
    public function unhide(): void
    {
        $this->update(['status' => 'subscribed']);
    }

    /**
     * Unsubscribe completely
     */
    public function unsubscribe(): void
    {
        $this->update(['status' => 'unsubscribed']);
    }

    /**
     * Resubscribe
     */
    public function resubscribe(): void
    {
        $this->update([
            'status' => 'subscribed',
            'subscribed_at' => now(),
        ]);
    }
}