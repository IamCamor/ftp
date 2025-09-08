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
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'lat' => 'decimal:7',
        'lng' => 'decimal:7',
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

    public function chat()
    {
        return $this->hasOne(Chat::class);
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

