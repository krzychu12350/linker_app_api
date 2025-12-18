<?php

namespace App\Http\Resources;

use App\Enums\MessageType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConversationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Get the photo URL of the matched user
        $photoUrl = $this->resource['matched_user_photo_url'];

        // Map messages to include author details
        $messagesWithAuthors = $this->resource["messages"]->map(function ($message) {
            $messageFiles = $message->files;

            $messageArray = [
                'id' => $message->id,
                'body' => $message->body,
                'type' => $messageFiles->isEmpty() ? MessageType::TEXT : MessageType::FILE,
                'read_at' => $message->read_at,
                'author' => [
                    'id' => $message->sender->id,
                    'first_name' => $message->sender->first_name,
                ],
            ];

            // Conditionally add 'files' only if it's not empty
            if (!$messageFiles->isEmpty()) {
                $messageArray['files'] = FileResource::collection($messageFiles);
            }

            return $messageArray;
        });


        return [
            'id' => $this->resource["conversation"]->id,
            'receiver_user' => [
                'id' => $this->resource['matched_user']->id,
                'first_name' => $this->resource['matched_user']->first_name,
                'last_name' => $this->resource['matched_user']->last_name,
                'photo' => $photoUrl,
                'last_message' => $this->resource["messages"]?->last()->body ?? "",
            ],
            'messages' => $messagesWithAuthors, // Include messages with author details
        ];
    }
}
