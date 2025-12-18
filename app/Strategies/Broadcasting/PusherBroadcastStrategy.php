<?php

namespace App\Strategies\Broadcasting;

use App\Enums\ConversationType;
use App\Enums\MessageType;
use App\Http\Resources\FileResource;
use App\Models\Message;
use GuzzleHttp\Client;
use Pusher\Pusher;

class PusherBroadcastStrategy implements MessageBroadcastStrategy
{
    protected Pusher $pusher;

    public function __construct()
    {
        $this->pusher = new Pusher(
            config('broadcasting.connections.pusher.key'),
            config('broadcasting.connections.pusher.secret'),
            config('broadcasting.connections.pusher.app_id'),
            [
                'cluster' => config('broadcasting.connections.pusher.options.cluster'),
                'useTLS'  => true,
            ],
            new Client()
        );
    }

    public function broadcast(Message $message): void
    {
        $conversation = $message->conversation;
        $files = $message->files;

        $data = [
            'message' => [
                'id'          => $message->id,
                'body'        => $message->body,
                'type'        => $files->isEmpty() ? MessageType::TEXT : MessageType::FILE,
                'read_at'     => $message->read_at,
                'sender_id'   => $message->sender_id,
                'receiver_id' => $conversation->type === ConversationType::GROUP
                    ? $conversation->id
                    : $message->receiver_id,
                'author' => [
                    'id'         => $message->sender->id,
                    'first_name' => $message->sender->first_name,
                    'photo'      => $message->sender->photo,
                ],
                'files' => FileResource::collection($files),
            ],
        ];

        $this->pusher->trigger(
            'conversation.' . $conversation->id,
            'message.sent',
            $data
        );
    }
}
