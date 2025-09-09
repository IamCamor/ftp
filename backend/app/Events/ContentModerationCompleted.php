<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ContentModerationCompleted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $contentType;
    public string $contentId;
    public array $moderationResult;
    public ?int $userId;

    /**
     * Create a new event instance.
     */
    public function __construct(
        string $contentType,
        string $contentId,
        array $moderationResult,
        ?int $userId = null
    ) {
        $this->contentType = $contentType;
        $this->contentId = $contentId;
        $this->moderationResult = $moderationResult;
        $this->userId = $userId;
    }
}
