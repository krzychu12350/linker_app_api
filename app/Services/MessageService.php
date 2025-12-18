<?php

namespace App\Services;

use App\Helpers\FileTypeHelper;
use App\Models\Conversation;
use App\Models\File;
use App\Models\Message;
use App\Strategies\Broadcasting\MessageBroadcastStrategy;
use Cloudinary\Api\Exception\ApiError;
use Illuminate\Http\Request;

class MessageService
{
    public function __construct(
        private readonly MessageBroadcastStrategy $broadcastStrategy
    ) {}

    /**
     * @throws ApiError
     */
    public function store(Request $request, Conversation $conversation): Message
    {
        $data = $request->validated();

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id'       => $data['sender_id'],
            'receiver_id'     => $data['receiver_id'] ?? null,
            'body'            => $data['body'] ?? null,
        ]);

        if ($request->hasFile('file')) {
            $this->attachFile($message, $request->file('file'));
        }

        $this->broadcastStrategy->broadcast($message);

        return $message;
    }

    private function attachFile(Message $message, $file): void
    {
        $fileType = FileTypeHelper::getFileType($file);

        $uploaded = cloudinary()->upload($file->getRealPath(), [
            'resource_type' => 'auto',
            'folder'        => 'messages',
        ]);

        $fileRecord = File::create([
            'url'  => $uploaded->getSecurePath(),
            'type' => $fileType,
        ]);

        $message->files()->attach($fileRecord);
    }
}
