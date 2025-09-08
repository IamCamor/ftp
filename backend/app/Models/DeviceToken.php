<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeviceToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'token',
        'platform',
        'device_id',
        'device_model',
        'app_version',
        'capabilities',
        'is_active',
        'last_used_at',
    ];

    protected $casts = [
        'capabilities' => 'array',
        'is_active' => 'boolean',
        'last_used_at' => 'datetime',
    ];

    /**
     * Get the user that owns the device token.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for active tokens.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for specific platform.
     */
    public function scopePlatform($query, string $platform)
    {
        return $query->where('platform', $platform);
    }

    /**
     * Scope for web tokens.
     */
    public function scopeWeb($query)
    {
        return $query->where('platform', 'web');
    }

    /**
     * Scope for iOS tokens.
     */
    public function scopeIos($query)
    {
        return $query->where('platform', 'ios');
    }

    /**
     * Scope for Android tokens.
     */
    public function scopeAndroid($query)
    {
        return $query->where('platform', 'android');
    }

    /**
     * Get the platform display name.
     */
    public function getPlatformDisplayNameAttribute(): string
    {
        return match($this->platform) {
            'web' => 'Веб',
            'ios' => 'iOS',
            'android' => 'Android',
            default => 'Неизвестно'
        };
    }

    /**
     * Check if token is valid.
     */
    public function isValid(): bool
    {
        return $this->is_active && 
               $this->last_used_at && 
               $this->last_used_at->isAfter(now()->subDays(30));
    }

    /**
     * Update last used timestamp.
     */
    public function updateLastUsed(): bool
    {
        return $this->update(['last_used_at' => now()]);
    }

    /**
     * Deactivate token.
     */
    public function deactivate(): bool
    {
        return $this->update(['is_active' => false]);
    }
}
