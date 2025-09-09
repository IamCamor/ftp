<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ContentModerationRequested
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $contentType;
    public string $contentId;
    public string $content;
    public string $format; // 'text' or 'image'
    public ?int $userId;

    /**
     * Create a new event instance.
     */
    public function __construct(
        string $contentType,
        string $contentId,
        string $content,
        string $format = 'text',
        ?int $userId = null
    ) {
        $this->contentType = $contentType;
        $this->contentId = $contentId;
        $this->content = $content;
        $this->format = $format;
        $this->userId = $userId;
    }
}
