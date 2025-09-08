<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LiveSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'lat',
        'lng',
        'stream_url',
        'status',
        'user_id',
        'event_id',
        'viewers_count',
        'started_at',
        'ended_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'lat' => 'decimal:7',
        'lng' => 'decimal:7',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function viewers()
    {
        return $this->belongsToMany(User::class, 'live_viewers')
                    ->withPivot('joined_at', 'left_at', 'watch_duration')
                    ->withTimestamps();
    }

    public function getCurrentViewersCountAttribute()
    {
        return $this->viewers()->whereNull('left_at')->count();
    }

    public function getTotalWatchTimeAttribute()
    {
        return $this->viewers()->sum('watch_duration');
    }
}

