<?php

namespace App\Http\Resources\User\Notification;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' =>$this->id,
            'message' => $this->message,
            'status' => $this->status,
            'time_ago' => $this->time_ago
        ];
    }
}
