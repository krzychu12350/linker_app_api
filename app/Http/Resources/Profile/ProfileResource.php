<?php

namespace App\Http\Resources\Profile;

use App\Http\Resources\PhotoResource;
use App\Http\Resources\User\Profile\UserProfileResource;
use App\Services\UserInterestService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        return [
            'user' => [
                'first_name' =>  $this->resource->first_name,
                'age' =>  $this->resource->age,
            ],
            'photos' => PhotoResource::collection($this->resource->photos),
        ];
    }
}
