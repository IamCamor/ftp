<?php

namespace App\Events;

use App\Models\Point;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PointCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Point $point;

    /**
     * Create a new event instance.
     */
    public function __construct(Point $point)
    {
        $this->point = $point;
    }
}
