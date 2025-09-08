<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Follow extends Model
{
    use HasFactory;

    protected $fillable = [
        'follower_id',
        'following_id',
    ];

    /**
     * Get the user who is following.
     */
    public function follower(): BelongsTo
    {
        return $this->belongsTo(User::class, 'follower_id');
    }

    /**
     * Get the user being followed.
     */
    public function following(): BelongsTo
    {
        return $this->belongsTo(User::class, 'following_id');
    }

    /**
     * Check if a user is following another user.
     */
    public static function isFollowing(int $followerId, int $followingId): bool
    {
        return self::where('follower_id', $followerId)
                  ->where('following_id', $followingId)
                  ->exists();
    }

    /**
     * Follow a user.
     */
    public static function followUser(int $followerId, int $followingId): bool
    {
        if ($followerId === $followingId) {
            return false; // Cannot follow yourself
        }

        if (self::isFollowing($followerId, $followingId)) {
            return false; // Already following
        }

        $follow = self::create([
            'follower_id' => $followerId,
            'following_id' => $followingId,
        ]);

        if ($follow) {
            // Update counters
            User::where('id', $followerId)->increment('following_count');
            User::where('id', $followingId)->increment('followers_count');
        }

        return (bool) $follow;
    }

    /**
     * Unfollow a user.
     */
    public static function unfollowUser(int $followerId, int $followingId): bool
    {
        $follow = self::where('follower_id', $followerId)
                     ->where('following_id', $followingId)
                     ->first();

        if (!$follow) {
            return false; // Not following
        }

        $deleted = $follow->delete();

        if ($deleted) {
            // Update counters
            User::where('id', $followerId)->decrement('following_count');
            User::where('id', $followingId)->decrement('followers_count');
        }

        return $deleted;
    }

    /**
     * Toggle follow status.
     */
    public static function toggleFollow(int $followerId, int $followingId): array
    {
        if (self::isFollowing($followerId, $followingId)) {
            $success = self::unfollowUser($followerId, $followingId);
            return ['following' => false, 'success' => $success];
        } else {
            $success = self::followUser($followerId, $followingId);
            return ['following' => true, 'success' => $success];
        }
    }
}
