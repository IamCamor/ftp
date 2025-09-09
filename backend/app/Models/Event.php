<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'lat',
        'lng',
        'location_name',
        'start_at',
        'end_at',
        'max_participants',
        'status',
        'organizer_id',
        'group_id',
        'cover_url',
        // New fields for events features
        'type',
        'organizer',
        'contact_email',
        'contact_phone',
        'website',
        'address',
        'city',
        'region',
        'country',
        'latitude',
        'longitude',
        'radius_km',
        'registration_start',
        'registration_end',
        'event_start',
        'event_end',
        'is_all_day',
        'current_participants',
        'entry_fee',
        'currency',
        'requires_registration',
        'is_public',
        'moderation_status',
        'moderation_result',
        'moderated_at',
        'moderated_by',
        'cover_image',
        'gallery',
        'documents',
        'rules',
        'prizes',
        'schedule',
        'views_count',
        'subscribers_count',
        'shares_count',
        'rating',
        'reviews_count',
        'notifications_enabled',
        'reminders_enabled',
        'allow_comments',
        'allow_sharing',
        'tags',
        'categories',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'lat' => 'decimal:7',
        'lng' => 'decimal:7',
        // New casts for events features
        'registration_start' => 'datetime',
        'registration_end' => 'datetime',
        'event_start' => 'datetime',
        'event_end' => 'datetime',
        'is_all_day' => 'boolean',
        'requires_registration' => 'boolean',
        'is_public' => 'boolean',
        'moderated_at' => 'datetime',
        'moderation_result' => 'array',
        'gallery' => 'array',
        'documents' => 'array',
        'tags' => 'array',
        'categories' => 'array',
        'notifications_enabled' => 'boolean',
        'reminders_enabled' => 'boolean',
        'allow_comments' => 'boolean',
        'allow_sharing' => 'boolean',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'entry_fee' => 'decimal:2',
    ];

    public function organizer()
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function participants()
    {
        return $this->belongsToMany(User::class, 'event_participants')
                    ->withPivot('status', 'notes')
                    ->withTimestamps();
    }

    /**
     * Get the subscriptions for the event
     */
    public function subscriptions()
    {
        return $this->hasMany(EventSubscription::class);
    }

    /**
     * Get the news for the event
     */
    public function news()
    {
        return $this->hasMany(EventNews::class);
    }

    /**
     * Get the moderator that moderated the event
     */
    public function moderator()
    {
        return $this->belongsTo(User::class, 'moderated_by');
    }

    public function chat()
    {
        return $this->hasOne(Chat::class);
    }

    /**
     * Scope for published events
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    /**
     * Scope for upcoming events
     */
    public function scopeUpcoming($query)
    {
        return $query->where('event_start', '>', now());
    }

    /**
     * Scope for events by type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope for events in radius
     */
    public function scopeInRadius($query, $latitude, $longitude, $radiusKm = 50)
    {
        return $query->whereRaw(
            "ST_Distance_Sphere(POINT(longitude, latitude), POINT(?, ?)) <= ?",
            [$longitude, $latitude, $radiusKm * 1000]
        );
    }

    /**
     * Scope for events by city
     */
    public function scopeByCity($query, $city)
    {
        return $query->where('city', $city);
    }

    /**
     * Scope for events by region
     */
    public function scopeByRegion($query, $region)
    {
        return $query->where('region', $region);
    }

    /**
     * Scope for approved events
     */
    public function scopeApproved($query)
    {
        return $query->where('moderation_status', 'approved');
    }

    /**
     * Get type description
     */
    public function getTypeDescriptionAttribute(): string
    {
        return match ($this->type) {
            'exhibition' => 'Выставка',
            'competition' => 'Соревнование',
            'workshop' => 'Мастер-класс',
            'meeting' => 'Встреча',
            default => 'Мероприятие'
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
            'cancelled' => 'Отменено',
            'completed' => 'Завершено',
            default => 'Неизвестно'
        };
    }

    /**
     * Check if event is upcoming
     */
    public function isUpcoming(): bool
    {
        return $this->event_start > now();
    }

    /**
     * Check if event is ongoing
     */
    public function isOngoing(): bool
    {
        return $this->event_start <= now() && $this->event_end >= now();
    }

    /**
     * Check if event is completed
     */
    public function isCompleted(): bool
    {
        return $this->event_end < now();
    }

    /**
     * Check if registration is open
     */
    public function isRegistrationOpen(): bool
    {
        if (!$this->requires_registration) {
            return false;
        }

        $now = now();
        return (!$this->registration_start || $this->registration_start <= $now) &&
               (!$this->registration_end || $this->registration_end >= $now);
    }

    /**
     * Check if event has available spots
     */
    public function hasAvailableSpots(): bool
    {
        if (!$this->max_participants) {
            return true;
        }

        return $this->current_participants < $this->max_participants;
    }

    /**
     * Get available spots count
     */
    public function getAvailableSpotsAttribute(): int
    {
        if (!$this->max_participants) {
            return -1; // Unlimited
        }

        return max(0, $this->max_participants - $this->current_participants);
    }

    /**
     * Increment views count
     */
    public function incrementViews(): void
    {
        $this->increment('views_count');
    }

    /**
     * Increment subscribers count
     */
    public function incrementSubscribers(): void
    {
        $this->increment('subscribers_count');
    }

    /**
     * Decrement subscribers count
     */
    public function decrementSubscribers(): void
    {
        $this->decrement('subscribers_count');
    }

    /**
     * Increment shares count
     */
    public function incrementShares(): void
    {
        $this->increment('shares_count');
    }

    /**
     * Get distance from point
     */
    public function getDistanceFrom($latitude, $longitude): float
    {
        if (!$this->latitude || !$this->longitude) {
            return 0;
        }

        $earthRadius = 6371; // km

        $latFrom = deg2rad((float)$latitude);
        $lonFrom = deg2rad((float)$longitude);
        $latTo = deg2rad((float)$this->latitude);
        $lonTo = deg2rad((float)$this->longitude);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
             cos($latFrom) * cos($latTo) *
             sin($lonDelta / 2) * sin($lonDelta / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    public function liveSessions()
    {
        return $this->hasMany(LiveSession::class);
    }

    public function getParticipantsCountAttribute()
    {
        return $this->participants()->where('status', 'confirmed')->count();
    }

    public function isParticipant($userId)
    {
        return $this->participants()->where('user_id', $userId)->exists();
    }

    public function isOrganizer($userId)
    {
        return $this->organizer_id === $userId;
    }
}

