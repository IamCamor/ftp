<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'username',
        'photo_url',
        'phone',
        'role',
        'is_blocked',
        'blocked_at',
        'block_reason',
        'blocked_by',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'blocked_at' => 'datetime',
        'is_blocked' => 'boolean',
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function oauthIdentities()
    {
        return $this->hasMany(OAuthIdentity::class);
    }

    public function catchRecords()
    {
        return $this->hasMany(CatchRecord::class);
    }

    public function points()
    {
        return $this->hasMany(Point::class);
    }

    public function weatherFavs()
    {
        return $this->hasMany(WeatherFav::class);
    }

    public function bonuses()
    {
        return $this->hasMany(Bonus::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }

    public function catchLikes()
    {
        return $this->hasMany(CatchLike::class);
    }

    public function catchComments()
    {
        return $this->hasMany(CatchComment::class);
    }

    public function getTotalBonusesAttribute()
    {
        return $this->bonuses()->sum('amount');
    }

    public function getAverageRatingAttribute()
    {
        $ratings = $this->ratings()->where('entity_type', 'user')->avg('value');
        return round($ratings, 1);
    }

    /**
     * Get the admin who blocked this user.
     */
    public function blockedBy()
    {
        return $this->belongsTo(User::class, 'blocked_by');
    }

    /**
     * Get reports made by this user.
     */
    public function reports()
    {
        return $this->hasMany(Report::class, 'reporter_id');
    }

    /**
     * Get reports reviewed by this user (if admin).
     */
    public function reviewedReports()
    {
        return $this->hasMany(Report::class, 'reviewed_by');
    }

    /**
     * Check if user is admin.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is blocked.
     */
    public function isBlocked(): bool
    {
        return $this->is_blocked;
    }

    /**
     * Scope for active (non-blocked) users.
     */
    public function scopeActive($query)
    {
        return $query->where('is_blocked', false);
    }

    /**
     * Scope for blocked users.
     */
    public function scopeBlocked($query)
    {
        return $query->where('is_blocked', true);
    }
}

