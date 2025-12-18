<?php

namespace App\Http\Controllers\Message;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMessageRequest;
use App\Http\Resources\MessageResource;
use App\Models\Conversation;
use App\Services\MessageService;
use Cloudinary\Api\Exception\ApiError;

class MessageController extends Controller
{
    public function __construct(
        private readonly MessageService $messageService
    ) {}

    public function index(int $userId, int $conversationId)
    {
        $conversation = Conversation::findOrFail($conversationId);

        return MessageResource::collection(
            $conversation->messages()->get()
        );
    }

    /**
     * Store a new message for a conversation
     *
     * @throws ApiError
     */
    public function store(
        StoreMessageRequest $request,
        int $userId,
        int $conversationId
    ) {
        $conversation = Conversation::findOrFail($conversationId);

        $message = $this->messageService->store(
            $request,
            $conversation
        );

        return new MessageResource($message);
    }
}
