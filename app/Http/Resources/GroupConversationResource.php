<?php

namespace App\Http\Resources;

use App\Enums\ConversationType;
use App\Http\Resources\GroupConversation\Users\UserGroupResource;
use App\Http\Resources\User\UserResource;
use App\Http\Resources\GroupConversation\Event\EventResource;

// You may need to create this
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GroupConversationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
//        dd($this->type === ConversationType::GROUP);
        return [
            'id' => $this->id,
            'name' => $this->name,
            'last_message' => new MessageResource($this->messages->last()),
            'users' => UserGroupResource::collection($this->users),
            'events' => $this->events->map(function ($event) {
                // Map events to include their grouped votes
                return [
                    'id' => $event->id,
                    'title' => $event->title,
                    'description' => $event->description,
                    'time' => $event->time,
                    'votes' => $event->votes(), // Assuming the `votes()` method groups the votes
                ];
            }),
        ];
    }
}
