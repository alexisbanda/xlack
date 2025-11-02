<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewMessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Message $message) {}

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        // Determinar el tipo de canal según messageable_type
        $channelPrefix = str_contains($this->message->messageable_type, 'Channel') ? 'channel' : 'dm';
        
        return [new PrivateChannel($channelPrefix . '.' . $this->message->messageable_id)];
    }

    public function broadcastAs(): string
    {
        return 'NewMessageSent';
    }

    public function broadcastWith(): array
    {
        $msg = $this->message->loadMissing('user:id,name');
        return [
            'message' => [
                'id' => $msg->id,
                'user_id' => $msg->user_id,
                'body' => $msg->body,
                'messageable_type' => $msg->messageable_type,
                'messageable_id' => $msg->messageable_id,
                'created_at' => optional($msg->created_at)->toISOString(),
                'user' => [
                    'id' => $msg->user->id,
                    'name' => $msg->user->name,
                ],
            ],
        ];
    }
}
