<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PushNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'body',
        'type',
        'data',
        'image_url',
        'action_url',
        'action_text',
        'target_users',
        'status',
        'scheduled_at',
        'sent_at',
        'sent_count',
        'failed_count',
        'delivery_stats',
        'created_by',
    ];

    protected $casts = [
        'data' => 'array',
        'target_users' => 'array',
        'delivery_stats' => 'array',
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
    ];

    /**
     * Get the user who created this notification.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope for scheduled notifications.
     */
    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }

    /**
     * Scope for sent notifications.
     */
    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    /**
     * Scope for failed notifications.
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope for specific type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope for notifications ready to send.
     */
    public function scopeReadyToSend($query)
    {
        return $query->where('status', 'scheduled')
                    ->where('scheduled_at', '<=', now());
    }

    /**
     * Get the status display name.
     */
    public function getStatusDisplayNameAttribute(): string
    {
        return match($this->status) {
            'scheduled' => 'Запланировано',
            'sent' => 'Отправлено',
            'failed' => 'Ошибка',
            default => 'Неизвестно'
        };
    }

    /**
     * Get the type display name.
     */
    public function getTypeDisplayNameAttribute(): string
    {
        return match($this->type) {
            'general' => 'Общее',
            'catch' => 'Улов',
            'event' => 'Событие',
            'subscription' => 'Подписка',
            'system' => 'Системное',
            'promotion' => 'Акция',
            default => 'Неизвестно'
        };
    }

    /**
     * Check if notification is ready to send.
     */
    public function isReadyToSend(): bool
    {
        return $this->status === 'scheduled' && 
               $this->scheduled_at && 
               $this->scheduled_at->isPast();
    }

    /**
     * Mark notification as sent.
     */
    public function markAsSent(int $sentCount = 0, int $failedCount = 0): bool
    {
        return $this->update([
            'status' => 'sent',
            'sent_at' => now(),
            'sent_count' => $sentCount,
            'failed_count' => $failedCount,
        ]);
    }

    /**
     * Mark notification as failed.
     */
    public function markAsFailed(): bool
    {
        return $this->update([
            'status' => 'failed',
        ]);
    }

    /**
     * Get delivery success rate.
     */
    public function getSuccessRateAttribute(): float
    {
        $total = $this->sent_count + $this->failed_count;
        if ($total === 0) {
            return 0;
        }
        return ($this->sent_count / $total) * 100;
    }
}
