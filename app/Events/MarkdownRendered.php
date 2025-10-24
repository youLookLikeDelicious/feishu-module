<?php

namespace Modules\Feishu\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * @method static void dispatch(mixed $content, string $summary = '', array $tags = [], string $docId, string $cover = '', array $series = [])
 * @see Modules\Feishu\Listeners\CommitToGithub
 */
class MarkdownRendered
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public $content, public $summary = '', public $tags = [], public string $docId, public string $cover = '', public $series = [])
    {
    }

    /**
     * Get the channels the event should be broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('channel-name'),
        ];
    }
}
