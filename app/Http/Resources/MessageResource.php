<?php

namespace App\Http\Resources;

use App\Enums\MessageType;
use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        $author = $this->sender;
        $authorPhotoUrl =  $author->photos->isEmpty() ? "" : $author->photos->first()->url;
        $messageFiles = $this->files;

        return [
            'body' => $this->body,
            'read_at' => $this->read_at,
            'is_read' => (bool) $this->read_at,  // Assuming is_read is determined based on read_at
            'type' => $messageFiles->isEmpty() ? MessageType::TEXT : MessageType::FILE,
            'author' => [
                'id' => $this->sender->id,
                'first_name' => $this->sender->first_name,
                'photo' => $authorPhotoUrl,
            ],
            'files' => FileResource::collection($this->files)
        ];
    }
}
