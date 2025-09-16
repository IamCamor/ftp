<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CatchReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'catch_id',
        'user_id',
        'category',
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
     * Get the catch that was reported.
     */
    public function catch()
    {
        return $this->belongsTo(CatchRecord::class, 'catch_id');
    }

    /**
     * Get the user who made the report.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the admin who reviewed the report.
     */
    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
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
     * Get category label in Russian.
     */
    public function getCategoryLabelAttribute(): string
    {
        return match($this->category) {
            'spam' => 'Спам',
            'advertisement' => 'Реклама',
            'fraud' => 'Обман',
            default => $this->category,
        };
    }

    /**
     * Get status label in Russian.
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending' => 'Ожидает рассмотрения',
            'reviewed' => 'Рассмотрено',
            'resolved' => 'Решено',
            'dismissed' => 'Отклонено',
            default => $this->status,
        };
    }
}
