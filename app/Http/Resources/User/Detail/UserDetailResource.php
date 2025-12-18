<?php

namespace App\Http\Resources\User\Detail;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // We need to process this single detail object and its potential children
        return $this->groupData($this->resource);
    }

    /**
     * Helper method to format group data from a single Detail object with potential children.
     *
     * @param $data
     * @return array
     */
    private function groupData($data)
    {
        // Initialize the group array
        $group = [
            'id' => $data->id,
            'group' => $data->group,
        ];

        // Check if the group has any subgroups (e.g. Fashion, Music, etc.)
        $subGroups = $data->children->map(function ($subGroup) {
            // Process each subgroup and get its children (options)
            $subGroupOptions = $subGroup->children->map(function ($option) {
                return [
                    'id' => $option->id,
                    'name' => $option->name,
                ];
            });

            // If the subgroup has no options, return the subgroup itself
            if ($subGroupOptions->isEmpty()) {
                $subGroupOptions = collect([[
                    'id' => $subGroup->id,
                    'name' => $subGroup->name,
                ]]);
            }

            return [
                'id' => $subGroup->id,
                'name' => $subGroup->name,
                'options' => $subGroupOptions,
            ];
        });

        // If there are no subgroups, process the options directly
        if ($subGroups->isEmpty() && $data->children->isNotEmpty()) {
            $options = $data->children->map(function ($option) {
                return [
                    'id' => $option->id,
                    'name' => $option->name,
                ];
            });

            $group['options'] = $options->toArray();
        } else {
            // If there are subgroups, include them in the response
            $group['subGroups'] = $subGroups->isEmpty() ? null : $subGroups;
        }

        return $group;
    }
}
