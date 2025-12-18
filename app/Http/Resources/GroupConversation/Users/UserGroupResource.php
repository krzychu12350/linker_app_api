<?php

namespace App\Http\Resources\GroupConversation\Users;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserGroupResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'photo' => $this->photos->first()->url ?? "",
            'is_admin' => $this->pivot?->is_admin
        ];
    }
}
