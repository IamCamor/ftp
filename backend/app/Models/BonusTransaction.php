<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BonusTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'action',
        'amount',
        'description',
        'metadata',
        'related_user_id',
        'related_catch_id',
        'related_point_id',
        'related_comment_id',
        'related_like_id',
    ];

    protected $casts = [
        'metadata' => 'array',
        'amount' => 'integer',
    ];

    // Transaction types
    const TYPE_EARNED = 'earned';
    const TYPE_SPENT = 'spent';
    const TYPE_REFUND = 'refund';

    // Action types
    const ACTION_FRIEND_ADDED = 'friend_added';
    const ACTION_CATCH_RECORDED = 'catch_recorded';
    const ACTION_POINT_CREATED = 'point_created';
    const ACTION_COMMENT_ADDED = 'comment_added';
    const ACTION_LIKE_GIVEN = 'like_given';
    const ACTION_SUBSCRIPTION_PURCHASED = 'subscription_purchased';

    // Bonus amounts
    const BONUS_FRIEND_ADDED = 200;
    const BONUS_CATCH_RECORDED = 50;
    const BONUS_POINT_CREATED = 100;
    const BONUS_COMMENT_ADDED = 10;
    const BONUS_LIKE_GIVEN = 5;

    /**
     * Get the user that owns the bonus transaction.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the related user (for friend actions).
     */
    public function relatedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'related_user_id');
    }

    /**
     * Get the related catch record.
     */
    public function relatedCatch(): BelongsTo
    {
        return $this->belongsTo(CatchRecord::class, 'related_catch_id');
    }

    /**
     * Get the related point.
     */
    public function relatedPoint(): BelongsTo
    {
        return $this->belongsTo(Point::class, 'related_point_id');
    }

    /**
     * Get the related comment.
     */
    public function relatedComment(): BelongsTo
    {
        return $this->belongsTo(CatchComment::class, 'related_comment_id');
    }

    /**
     * Get the related like.
     */
    public function relatedLike(): BelongsTo
    {
        return $this->belongsTo(CatchLike::class, 'related_like_id');
    }

    /**
     * Scope for earned bonuses.
     */
    public function scopeEarned($query)
    {
        return $query->where('type', self::TYPE_EARNED);
    }

    /**
     * Scope for spent bonuses.
     */
    public function scopeSpent($query)
    {
        return $query->where('type', self::TYPE_SPENT);
    }

    /**
     * Scope for specific action.
     */
    public function scopeForAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Get formatted amount with sign.
     */
    public function getFormattedAmountAttribute(): string
    {
        $sign = $this->amount > 0 ? '+' : '';
        return $sign . $this->amount;
    }

    /**
     * Get action description.
     */
    public function getActionDescriptionAttribute(): string
    {
        return match ($this->action) {
            self::ACTION_FRIEND_ADDED => 'Добавление друга',
            self::ACTION_CATCH_RECORDED => 'Запись улова',
            self::ACTION_POINT_CREATED => 'Создание точки',
            self::ACTION_COMMENT_ADDED => 'Добавление комментария',
            self::ACTION_LIKE_GIVEN => 'Поставлен лайк',
            self::ACTION_SUBSCRIPTION_PURCHASED => 'Покупка подписки',
            default => 'Неизвестное действие',
        };
    }

    /**
     * Get type description.
     */
    public function getTypeDescriptionAttribute(): string
    {
        return match ($this->type) {
            self::TYPE_EARNED => 'Заработано',
            self::TYPE_SPENT => 'Потрачено',
            self::TYPE_REFUND => 'Возврат',
            default => 'Неизвестно',
        };
    }
}