<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Point extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'latitude',
        'longitude',
        'type',
        'is_public',
        'rating',
        'visits_count',
        'is_blocked',
        'blocked_at',
        'block_reason',
        'blocked_by',
        'is_edited_by_admin',
        'edited_by_admin_at',
        'edited_by_admin_id',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'rating' => 'decimal:2',
        'is_public' => 'boolean',
        'is_blocked' => 'boolean',
        'is_edited_by_admin' => 'boolean',
        'blocked_at' => 'datetime',
        'edited_by_admin_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function media()
    {
        return $this->hasMany(PointMedia::class);
    }

    public function ratings()
    {
        return $this->morphMany(Rating::class, 'entity', 'entity_type', 'entity_id');
    }

    public function getAverageRatingAttribute()
    {
        return $this->ratings()->avg('value');
    }

    /**
     * Get catch records for this point.
     */
    public function catchRecords()
    {
        return $this->hasMany(CatchRecord::class);
    }

    /**
     * Get the admin who blocked this point.
     */
    public function blockedBy()
    {
        return $this->belongsTo(User::class, 'blocked_by');
    }

    /**
     * Get the admin who edited this point.
     */
    public function editedByAdmin()
    {
        return $this->belongsTo(User::class, 'edited_by_admin_id');
    }

    /**
     * Get reports for this point.
     */
    public function reports()
    {
        return $this->morphMany(Report::class, 'reportable');
    }

    /**
     * Check if point is blocked.
     */
    public function isBlocked(): bool
    {
        return $this->is_blocked;
    }

    /**
     * Check if point was edited by admin.
     */
    public function wasEditedByAdmin(): bool
    {
        return $this->is_edited_by_admin;
    }

    /**
     * Scope for active (non-blocked) points.
     */
    public function scopeActive($query)
    {
        return $query->where('is_blocked', false);
    }

    /**
     * Scope for blocked points.
     */
    public function scopeBlocked($query)
    {
        return $query->where('is_blocked', true);
    }

    /**
     * Scope for admin-edited points.
     */
    public function scopeEditedByAdmin($query)
    {
        return $query->where('is_edited_by_admin', true);
    }
}

