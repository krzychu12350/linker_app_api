<?php

namespace App\Http\Resources\GroupConversation\Event\Vote;

use App\Enums\PollResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventVoteResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        // The resource is already the grouped votes passed from the controller
        return $this->resource->map(function ($votesForResponse, $response) {
            return [
                'response'      => $response,
                'response_name' => PollResponse::tryFrom($response)->name, // Adjust if necessary
                'count'         => $votesForResponse['count'],
                'users'         => $votesForResponse['users'],
            ];
        });
    }
}
