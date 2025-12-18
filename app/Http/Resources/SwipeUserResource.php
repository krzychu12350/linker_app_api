<?php

namespace App\Http\Resources;

use App\Http\Resources\User\Profile\UserProfileResource;
use App\Services\UserInterestService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SwipeUserResource extends JsonResource
{


    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Resolve the service inside the method
        $userInterestService = app(UserInterestService::class);

        return [
            'information' => new UserProfileResource($this->resource),
            'photos' => $this->resource->photos->pluck('url'),
            'details' => $userInterestService->getFlattenedOptionsForGroupsWithSubGroups($this->resource)
        ];
    }
}
