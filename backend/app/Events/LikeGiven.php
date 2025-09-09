<?php

namespace App\Events;

use App\Models\CatchLike;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LikeGiven
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public CatchLike $like;

    /**
     * Create a new event instance.
     */
    public function __construct(CatchLike $like)
    {
        $this->like = $like;
    }
}
