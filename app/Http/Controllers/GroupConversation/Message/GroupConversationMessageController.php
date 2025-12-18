<?php

namespace App\Http\Controllers\GroupConversation\Message;

use App\Enums\ConversationType;
use App\Enums\MessageType;
use App\Helpers\FileTypeHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\GroupConversation\User\GroupConversationUserRequest;
use App\Http\Requests\StoreGroupMessageRequest;
use App\Http\Requests\StoreMessageRequest;
use App\Http\Resources\FileResource;
use App\Http\Resources\MessageResource;
use App\Models\Conversation;
use App\Models\File;
use App\Models\Message;
use Cloudinary\Api\Exception\ApiError;
use Illuminate\Http\JsonResponse;
use Pusher\PusherException;

class GroupConversationMessageController extends Controller
{
    public function index(int $conversationId)
    {
        // dd($userId, $conversationId);
        $conversation = Conversation::findOrFail($conversationId);
        $messages = $conversation->messages()->get();

        return MessageResource::collection($messages);
    }

    // Store a new message for a conversation

    /**
     * @throws PusherException
     * @throws ApiError
     */
    public function store(StoreGroupMessageRequest $request, int $group)
    {
        $validatedData = $request->validated();

        // Find the conversation or fail if it doesn't exist
        $conversation = Conversation::findOrFail($group);

        // Prepare the message data
        $messageData = [
            'conversation_id' => $conversation->id,
            'sender_id' => $validatedData['sender_id'],
            'receiver_id' => $validatedData['receiver_id'] ?? null,
        ];

        // If there is a body, add it to the message data
        if (isset($validatedData['body'])) {
            $messageData['body'] = $validatedData['body'];
        }

        // Create a new message and associate it with the conversation and authenticated user
        $message = Message::create($messageData);

        // Handle the file if it exists
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $fileType = FileTypeHelper::getFileType($file); // Use the helper to determine the file type

            // Store the file (assuming you use Cloudinary, adjust accordingly)
            //$filePath = $file->store('messages', 'public'); // Or upload to Cloudinary if needed

            $uploadedFile = cloudinary()->upload($file->getRealPath(), [
                'resource_type' => 'auto', // Cloudinary treats audio files as video resources
                'folder' => 'messages',
            ]);

            // Retrieve the file information (you can also save other details like public_id or URL)
            $fileRecord = File::create([
//                'url' => $uploadedFile->getPublicId(),
                'url' => $uploadedFile->getSecurePath(),
                'type' => $fileType, // Store the file type as a string from the enum
            ]);

            // Attach the file to the created message
            $message->files()->attach($fileRecord);
        }


        // Trigger the event to broadcast the message
        //  event(new MessageSent($message));

        // broadcast(new MessageSent($message));
        // event(new MessageSent($message));

        $custom_client = new \GuzzleHttp\Client();

        $options = [
            'cluster' => 'eu',
            'useTLS' => false
        ];
        $pusher = new \Pusher\Pusher(
            '26143f87a08bdfeab780',
            'bc9f0158cdd1ebd43f76',
            '1843953',
            $options,
            $custom_client
        );

        $messageFiles = $message->files;

        $data = [
            'message' => [
                'id' => $message->id,
                'body' => $message->body,
                'type' => $messageFiles->isEmpty() ? MessageType::TEXT : MessageType::FILE,
                'read_at' => $message->read_at,
                'sender_id' => $message->sender->id,
                'receiver_id' => $conversation->type === ConversationType::GROUP
                    ? $conversation->id
                    : $message->receiver->id,
                'author' => [
                    'id' => $message->sender->id,
                    'first_name' => $message->sender->first_name,
                    'photo' => $message->sender->photo,
                ],
                'files' => FileResource::collection($message->files)
            ]
        ];

        $promise = $pusher->triggerAsync(
            ['conversation.' . $message->conversation()->first()?->id],
            'message.sent',
            $data
        );

        $promise->then(function($result) {
            // do something with $result
            return $result;
        });

        $final_result = $promise->wait();

        // Return the newly created message as a resource
        return new MessageResource($message);
    }
}
