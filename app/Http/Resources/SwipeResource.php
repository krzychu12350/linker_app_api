<?php

namespace App\Http\Resources;

use App\Http\Resources\Profile\ProfileResource;
use App\Http\Resources\User\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SwipeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $photoUrl = $this->resource->photos->isEmpty() ? "" : $this->resource->photos->first()->url;

        return [
                'user' => new SwipeUserResource($this->resource),
        ];
    }
}
