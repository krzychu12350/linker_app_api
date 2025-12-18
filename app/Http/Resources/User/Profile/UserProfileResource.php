<?php

namespace App\Http\Resources\User\Profile;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserProfileResource extends JsonResource
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
            'last_name' => $this->last_name,
            'email' => $this->email,
            'city' => $this->city,
            'profession' => $this->profession,
            'bio' => $this->bio,
            'weight' => $this->weight,
            'height' => $this->height,
            'age' => $this->age,
        ];
    }
}
