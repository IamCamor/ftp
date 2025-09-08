<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'cover_url',
        'privacy',
        'owner_id',
        'members_count',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'group_members')
                    ->withPivot('role', 'is_active')
                    ->withTimestamps();
    }

    public function events()
    {
        return $this->hasMany(Event::class);
    }

    public function chats()
    {
        return $this->hasMany(Chat::class);
    }

    public function isMember($userId)
    {
        return $this->members()->where('user_id', $userId)->where('is_active', true)->exists();
    }

    public function isAdmin($userId)
    {
        return $this->members()
                    ->where('user_id', $userId)
                    ->where('is_active', true)
                    ->whereIn('role', ['admin', 'moderator'])
                    ->exists();
    }
}

