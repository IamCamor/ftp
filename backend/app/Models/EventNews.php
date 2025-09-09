<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class EventNews extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'user_id',
        'title',
        'content',
        'excerpt',
        'cover_image',
        'gallery',
        'attachments',
        'type',
        'priority',
        'status',
        'moderation_status',
        'moderation_result',
        'moderated_at',
        'moderated_by',
        'published_at',
        'scheduled_at',
        'is_pinned',
        'views_count',
        'likes_count',
        'shares_count',
        'comments_count',
        'allow_comments',
        'allow_sharing',
        'send_notifications',
        'tags',
    ];

    protected $casts = [
        'gallery' => 'array',
        'attachments' => 'array',
        'moderation_result' => 'array',
        'moderated_at' => 'datetime',
        'published_at' => 'datetime',
        'scheduled_at' => 'datetime',
        'is_pinned' => 'boolean',
        'allow_comments' => 'boolean',
        'allow_sharing' => 'boolean',
        'send_notifications' => 'boolean',
        'tags' => 'array',
    ];

    /**
     * Get the event that owns the news
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Get the user that owns the news
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the moderator that moderated the news
     */
    public function moderator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'moderated_by');
    }

    // Comments and likes relationships will be implemented later
    // when the corresponding models are created

    /**
     * Scope for published news
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
                    ->where('published_at', '<=', now());
    }

    /**
     * Scope for scheduled news
     */
    public function scopeScheduled($query)
    {
        return $query->where('status', 'published')
                    ->where('scheduled_at', '>', now());
    }

    /**
     * Scope for pinned news
     */
    public function scopePinned($query)
    {
        return $query->where('is_pinned', true);
    }

    /**
     * Scope for approved news
     */
    public function scopeApproved($query)
    {
        return $query->where('moderation_status', 'approved');
    }

    /**
     * Scope for pending moderation
     */
    public function scopePendingModeration($query)
    {
        return $query->where('moderation_status', 'pending');
    }

    /**
     * Scope by type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope by priority
     */
    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Get type description
     */
    public function getTypeDescriptionAttribute(): string
    {
        return match ($this->type) {
            'announcement' => 'Объявление',
            'update' => 'Обновление',
            'reminder' => 'Напоминание',
            'result' => 'Результат',
            'photo_report' => 'Фотоотчет',
            'other' => 'Другое',
            default => 'Неизвестно'
        };
    }

    /**
     * Get priority description
     */
    public function getPriorityDescriptionAttribute(): string
    {
        return match ($this->priority) {
            'low' => 'Низкий',
            'normal' => 'Обычный',
            'high' => 'Высокий',
            'urgent' => 'Срочный',
            default => 'Неизвестно'
        };
    }

    /**
     * Get status description
     */
    public function getStatusDescriptionAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'Черновик',
            'published' => 'Опубликовано',
            'archived' => 'Архивировано',
            default => 'Неизвестно'
        };
    }

    /**
     * Get moderation status description
     */
    public function getModerationStatusDescriptionAttribute(): string
    {
        return match ($this->moderation_status) {
            'pending' => 'Ожидает модерации',
            'approved' => 'Одобрено',
            'rejected' => 'Отклонено',
            'pending_review' => 'Требует проверки',
            default => 'Неизвестно'
        };
    }

    /**
     * Check if news is published
     */
    public function isPublished(): bool
    {
        return $this->status === 'published' && 
               $this->published_at && 
               $this->published_at <= now();
    }

    /**
     * Check if news is scheduled
     */
    public function isScheduled(): bool
    {
        return $this->status === 'published' && 
               $this->scheduled_at && 
               $this->scheduled_at > now();
    }

    /**
     * Check if news is approved
     */
    public function isApproved(): bool
    {
        return $this->moderation_status === 'approved';
    }

    /**
     * Check if news needs moderation
     */
    public function needsModeration(): bool
    {
        return $this->moderation_status === 'pending';
    }

    /**
     * Publish news
     */
    public function publish(): void
    {
        $this->update([
            'status' => 'published',
            'published_at' => now(),
        ]);
    }

    /**
     * Schedule news
     */
    public function schedule(\DateTime $scheduledAt): void
    {
        $this->update([
            'status' => 'published',
            'scheduled_at' => $scheduledAt,
        ]);
    }

    /**
     * Pin news
     */
    public function pin(): void
    {
        $this->update(['is_pinned' => true]);
    }

    /**
     * Unpin news
     */
    public function unpin(): void
    {
        $this->update(['is_pinned' => false]);
    }

    /**
     * Archive news
     */
    public function archive(): void
    {
        $this->update(['status' => 'archived']);
    }

    /**
     * Increment views count
     */
    public function incrementViews(): void
    {
        $this->increment('views_count');
    }

    /**
     * Increment likes count
     */
    public function incrementLikes(): void
    {
        $this->increment('likes_count');
    }

    /**
     * Decrement likes count
     */
    public function decrementLikes(): void
    {
        $this->decrement('likes_count');
    }

    /**
     * Increment shares count
     */
    public function incrementShares(): void
    {
        $this->increment('shares_count');
    }

    /**
     * Increment comments count
     */
    public function incrementComments(): void
    {
        $this->increment('comments_count');
    }

    /**
     * Decrement comments count
     */
    public function decrementComments(): void
    {
        $this->decrement('comments_count');
    }

    /**
     * Get excerpt or generate from content
     */
    public function getExcerptOrGenerated(): string
    {
        if ($this->excerpt) {
            return $this->excerpt;
        }

        return Str::limit(strip_tags($this->content), 200);
    }

    /**
     * Get reading time estimate
     */
    public function getReadingTimeAttribute(): int
    {
        $wordCount = str_word_count(strip_tags($this->content));
        return max(1, round($wordCount / 200)); // Assuming 200 words per minute
    }
}