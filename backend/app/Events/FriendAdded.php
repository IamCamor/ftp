<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FriendAdded
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public User $user;
    public User $friend;

    /**
     * Create a new event instance.
     */
    public function __construct(User $user, User $friend)
    {
        $this->user = $user;
        $this->friend = $friend;
    }
}
