<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CatchRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'point_id',
        'fish_type',
        'weight',
        'length',
        'bait',
        'weather',
        'temperature',
        'description',
        'photos',
        'videos',
        'main_photo',
        'main_video',
        'media_count',
        'caught_at',
        'is_public',
        'moderation_status',
        'moderation_result',
        'moderated_at',
        'moderated_by',
        'likes_count',
        'comments_count',
        'is_blocked',
        'blocked_at',
        'block_reason',
        'blocked_by',
        'is_edited_by_admin',
        'edited_by_admin_at',
        'edited_by_admin_id',
        'fish_species_id',
        'fishing_method_id',
        'fishing_knot_id',
        'boat_id',
        'engine_id',
        'location_id',
        'tackle_used',
    ];

    protected $casts = [
        'caught_at' => 'datetime',
        'blocked_at' => 'datetime',
        'moderated_at' => 'datetime',
        'moderation_result' => 'array',
        'edited_by_admin_at' => 'datetime',
        'length' => 'decimal:2',
        'weight' => 'decimal:2',
        'is_public' => 'boolean',
        'is_blocked' => 'boolean',
        'is_edited_by_admin' => 'boolean',
        'photos' => 'array',
        'videos' => 'array',
        'tackle_used' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function point()
    {
        return $this->belongsTo(Point::class);
    }

    public function fishingSession()
    {
        return $this->belongsTo(FishingSession::class);
    }

    public function biometricData()
    {
        return $this->hasMany(BiometricData::class);
    }

    public function likes()
    {
        return $this->hasMany(CatchLike::class, 'catch_id');
    }

    public function comments()
    {
        return $this->hasMany(CatchComment::class, 'catch_id');
    }

    public function ratings()
    {
        return $this->morphMany(Rating::class, 'entity', 'entity_type', 'entity_id');
    }

    public function getLikesCountAttribute()
    {
        return $this->likes()->count();
    }

    public function getCommentsCountAttribute()
    {
        return $this->comments()->where('is_approved', true)->count();
    }

    public function getAverageRatingAttribute()
    {
        return $this->ratings()->avg('value');
    }

    /**
     * Get the admin who blocked this catch.
     */
    public function blockedBy()
    {
        return $this->belongsTo(User::class, 'blocked_by');
    }

    /**
     * Get the admin who edited this catch.
     */
    public function editedByAdmin()
    {
        return $this->belongsTo(User::class, 'edited_by_admin_id');
    }

    /**
     * Get reports for this catch.
     */
    public function reports()
    {
        return $this->morphMany(Report::class, 'reportable');
    }

    /**
     * Get the fish species for this catch.
     */
    public function fishSpecies()
    {
        return $this->belongsTo(FishSpecies::class);
    }

    /**
     * Get the fishing method used for this catch.
     */
    public function fishingMethod()
    {
        return $this->belongsTo(FishingMethod::class);
    }

    /**
     * Get the fishing knot used for this catch.
     */
    public function fishingKnot()
    {
        return $this->belongsTo(FishingKnot::class);
    }

    /**
     * Get the boat used for this catch.
     */
    public function boat()
    {
        return $this->belongsTo(Boat::class);
    }

    /**
     * Get the engine used for this catch.
     */
    public function engine()
    {
        return $this->belongsTo(BoatEngine::class);
    }

    /**
     * Get the fishing location for this catch.
     */
    public function fishingLocation()
    {
        return $this->belongsTo(FishingLocation::class, 'location_id');
    }

    /**
     * Get the tackle used for this catch.
     */
    public function tackle()
    {
        if (!$this->tackle_used) {
            return collect();
        }
        
        return FishingTackle::whereIn('id', $this->tackle_used)->get();
    }

    /**
     * Check if catch is blocked.
     */
    public function isBlocked(): bool
    {
        return $this->is_blocked;
    }

    /**
     * Check if catch was edited by admin.
     */
    public function wasEditedByAdmin(): bool
    {
        return $this->is_edited_by_admin;
    }

    /**
     * Scope for active (non-blocked) catches.
     */
    public function scopeActive($query)
    {
        return $query->where('is_blocked', false);
    }

    /**
     * Scope for blocked catches.
     */
    public function scopeBlocked($query)
    {
        return $query->where('is_blocked', true);
    }

    /**
     * Scope for admin-edited catches.
     */
    public function scopeEditedByAdmin($query)
    {
        return $query->where('is_edited_by_admin', true);
    }

    /**
     * Get media limits for user role.
     */
    public function getMediaLimits(): array
    {
        $userRole = $this->user->role;
        return config("media.limits.{$userRole}", config('media.limits.user'));
    }

    /**
     * Check if user can add more photos.
     */
    public function canAddPhotos(int $count = 1): bool
    {
        $limits = $this->getMediaLimits();
        $currentPhotos = count($this->photos ?? []);
        return ($currentPhotos + $count) <= $limits['max_photos'];
    }

    /**
     * Check if user can add more videos.
     */
    public function canAddVideos(int $count = 1): bool
    {
        $limits = $this->getMediaLimits();
        if (!$limits['video_enabled']) {
            return false;
        }
        $currentVideos = count($this->videos ?? []);
        return ($currentVideos + $count) <= $limits['max_videos'];
    }

    /**
     * Check if user can add more media total.
     */
    public function canAddMedia(int $count = 1): bool
    {
        $limits = $this->getMediaLimits();
        return ($this->media_count + $count) <= $limits['max_media_total'];
    }

    /**
     * Add photos to catch.
     */
    public function addPhotos(array $photoUrls): bool
    {
        if (!$this->canAddPhotos(count($photoUrls))) {
            return false;
        }

        $currentPhotos = $this->photos ?? [];
        $newPhotos = array_merge($currentPhotos, $photoUrls);
        
        $this->update([
            'photos' => $newPhotos,
            'main_photo' => $this->main_photo ?: $photoUrls[0] ?? null,
            'media_count' => count($newPhotos) + count($this->videos ?? []),
        ]);

        return true;
    }

    /**
     * Add videos to catch.
     */
    public function addVideos(array $videoUrls): bool
    {
        if (!$this->canAddVideos(count($videoUrls))) {
            return false;
        }

        $currentVideos = $this->videos ?? [];
        $newVideos = array_merge($currentVideos, $videoUrls);
        
        $this->update([
            'videos' => $newVideos,
            'main_video' => $this->main_video ?: $videoUrls[0] ?? null,
            'media_count' => count($this->photos ?? []) + count($newVideos),
        ]);

        return true;
    }

    /**
     * Get main media for preview.
     */
    public function getMainMedia(): ?string
    {
        return $this->main_video ?: $this->main_photo;
    }

    /**
     * Check if catch has videos.
     */
    public function hasVideos(): bool
    {
        return !empty($this->videos);
    }

    /**
     * Check if catch has photos.
     */
    public function hasPhotos(): bool
    {
        return !empty($this->photos);
    }

    /**
     * Get all media URLs.
     */
    public function getAllMedia(): array
    {
        $media = [];
        
        if ($this->photos) {
            foreach ($this->photos as $photo) {
                $media[] = ['type' => 'photo', 'url' => $photo];
            }
        }
        
        if ($this->videos) {
            foreach ($this->videos as $video) {
                $media[] = ['type' => 'video', 'url' => $video];
            }
        }
        
        return $media;
    }
}

