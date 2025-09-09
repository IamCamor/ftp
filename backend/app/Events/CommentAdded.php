<?php

namespace App\Events;

use App\Models\CatchComment;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CommentAdded
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public CatchComment $comment;

    /**
     * Create a new event instance.
     */
    public function __construct(CatchComment $comment)
    {
        $this->comment = $comment;
    }
}
