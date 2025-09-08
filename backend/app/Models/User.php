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
        'is_premium',
        'premium_expires_at',
        'crown_icon_url',
        'bonus_balance',
        'last_bonus_earned_at',
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
        'premium_expires_at' => 'datetime',
        'last_bonus_earned_at' => 'datetime',
        'is_premium' => 'boolean',
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

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
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
     * Check if user is Pro.
     */
    public function isPro(): bool
    {
        return $this->role === 'pro' || $this->hasActiveSubscription('pro');
    }

    /**
     * Check if user is Premium.
     */
    public function isPremium(): bool
    {
        return $this->role === 'premium' || $this->is_premium || $this->hasActiveSubscription('premium');
    }

    /**
     * Check if user has active subscription of specific type.
     */
    public function hasActiveSubscription(string $type): bool
    {
        return $this->subscriptions()
            ->where('type', $type)
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->exists();
    }

    /**
     * Get active subscription of specific type.
     */
    public function getActiveSubscription(string $type): ?Subscription
    {
        return $this->subscriptions()
            ->where('type', $type)
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->first();
    }

    /**
     * Get all active subscriptions.
     */
    public function getActiveSubscriptions()
    {
        return $this->subscriptions()
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->get();
    }

    /**
     * Add bonus balance.
     */
    public function addBonusBalance(int $amount): bool
    {
        return $this->increment('bonus_balance', $amount);
    }

    /**
     * Deduct bonus balance.
     */
    public function deductBonusBalance(int $amount): bool
    {
        if ($this->bonus_balance < $amount) {
            return false;
        }

        return $this->decrement('bonus_balance', $amount);
    }

    /**
     * Check if user has enough bonus balance.
     */
    public function hasEnoughBonusBalance(int $amount): bool
    {
        return $this->bonus_balance >= $amount;
    }

    /**
     * Get crown icon URL for premium users.
     */
    public function getCrownIconUrl(): ?string
    {
        if ($this->isPremium()) {
            return $this->crown_icon_url ?? config('subscription.premium_crown_icon_url');
        }

        return null;
    }

    /**
     * Scope for Pro users.
     */
    public function scopePro($query)
    {
        return $query->where('role', 'pro')
            ->orWhereHas('subscriptions', function ($q) {
                $q->where('type', 'pro')
                  ->where('status', 'active')
                  ->where('expires_at', '>', now());
            });
    }

    /**
     * Scope for Premium users.
     */
    public function scopePremium($query)
    {
        return $query->where('role', 'premium')
            ->orWhere('is_premium', true)
            ->orWhereHas('subscriptions', function ($q) {
                $q->where('type', 'premium')
                  ->where('status', 'active')
                  ->where('expires_at', '>', now());
            });
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

