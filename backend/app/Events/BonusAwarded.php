<?php

namespace App\Events;

use App\Models\BonusTransaction;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BonusAwarded
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public BonusTransaction $transaction;

    /**
     * Create a new event instance.
     */
    public function __construct(BonusTransaction $transaction)
    {
        $this->transaction = $transaction;
    }
}
