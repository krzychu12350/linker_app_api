<?php

namespace App\Http\Resources;

use App\Strategies\PhotoStorageStrategy\PhotoStorageStrategy;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use function PHPUnit\Framework\isEmpty;

class MatchedSwipeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Get the second user from the conversation
        $secondUser = $this->resource->conversation->users->first();

        // If secondUser is null, return a default value or empty string for the photo URL
        if (!$secondUser) {
            $photoUrl = ""; // Default value if secondUser is null
        } else {
            // If secondUser exists, get the photos
            $secondUserPhotos = $secondUser->photos;

            // Build the photo URL (using Cloudinary or empty string if no photos exist)
            $photoUrl = $secondUserPhotos->isEmpty()
                ? ""
                : $secondUserPhotos->first()->url;
        }

        // Get the conversation
        $conversation = $this->resource->conversation;

        return [
            'id' => $this->id,
            'user_id' => $secondUser->id ?? null,  // Make sure to handle null user
            'conversation_id' => $conversation->id,
            'last_message' => $conversation->messages()->latest()?->first()?->body,
            'first_name' => $secondUser->first_name ?? '',
            'last_name' => $secondUser->last_name ?? '',
            'age' => $secondUser->age ?? null,
            'major_photo' => $photoUrl,  // Cloudinary URL or empty string
        ];
    }
}
