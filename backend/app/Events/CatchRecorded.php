<?php

namespace App\Events;

use App\Models\CatchRecord;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CatchRecorded
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public CatchRecord $catch;

    /**
     * Create a new event instance.
     */
    public function __construct(CatchRecord $catch)
    {
        $this->catch = $catch;
    }
}
