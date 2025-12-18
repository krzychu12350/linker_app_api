<?php

namespace App\Events;

use App\Enums\MessageType;
use App\Http\Resources\FileResource;
use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Message $message;

    /**
     * Create a new event instance.
     *
     * @param Message $message
     * @return void
     */
    public function __construct(Message $message)
    {
        $this->message = $message;
        //dd($this->message->conversation()->first()?->id);
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('conversation.' . $this->message->conversation()->first()?->id),
//            new PrivateChannel(
//                'conversation.' . $this->message->conversation()->first()?->id
//            ),
        ];
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith(): array
    {
        $messageFiles = $this->message->files;

        return [
            'message' => [
                'id' => $this->message->id,
                'body' => $this->message->body,
                'type' => $messageFiles->isEmpty() ? MessageType::TEXT : MessageType::FILE,
                'read_at' => $this->message->read_at,
                'sender_id' => $this->message->sender->id,
                'receiver_id' => $this->message->receiver->id,
                'author' => [
                    'id' => $this->message->sender->id,
                    'first_name' => $this->message->sender->first_name,
                ],
                'files' => FileResource::collection($this->message->files)
            ]
        ];
    }

    /**
     * Get the event name that should be broadcast.
     *
     * @return string
     */
    public function broadcastAs(): string
    {
        return 'message.sent'; // Customize the event name as needed
    }
}
