<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'reporter_id',
        'reportable_type',
        'reportable_id',
        'reason',
        'description',
        'status',
        'reviewed_by',
        'reviewed_at',
        'admin_notes',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    /**
     * Get the user who made the report.
     */
    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    /**
     * Get the admin who reviewed the report.
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Get the reportable entity (catch, point, user).
     */
    public function reportable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope for pending reports.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for reviewed reports.
     */
    public function scopeReviewed($query)
    {
        return $query->where('status', 'reviewed');
    }

    /**
     * Scope for resolved reports.
     */
    public function scopeResolved($query)
    {
        return $query->where('status', 'resolved');
    }

    /**
     * Get the reason label.
     */
    public function getReasonLabelAttribute(): string
    {
        $labels = [
            'spam' => 'Спам',
            'inappropriate_content' => 'Неподходящий контент',
            'fake_content' => 'Фальшивый контент',
            'harassment' => 'Преследование',
            'violence' => 'Насилие',
            'illegal_content' => 'Незаконный контент',
            'copyright_violation' => 'Нарушение авторских прав',
            'other' => 'Другое',
        ];

        return $labels[$this->reason] ?? $this->reason;
    }

    /**
     * Get the status label.
     */
    public function getStatusLabelAttribute(): string
    {
        $labels = [
            'pending' => 'Ожидает рассмотрения',
            'reviewed' => 'Рассмотрено',
            'resolved' => 'Решено',
            'dismissed' => 'Отклонено',
        ];

        return $labels[$this->status] ?? $this->status;
    }
}
