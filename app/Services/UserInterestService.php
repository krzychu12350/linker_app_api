<?php

namespace App\Services;

use App\Models\Detail;
use App\Models\User;

class UserInterestService
{
    private function fetchDetails(): \Illuminate\Support\Collection
    {
        // Fetch all the details (groups, subgroups, and options) with their children in a single query
        return Detail::with('children.children') // Eager load subgroups and options
        ->whereNull('parent_id') // Only get top-level groups (no parent)
        ->get();
    }

    private function mapDetails($details, array $selectedDetailsIds = [], bool $includeSelection = true): array
    {
        $selectedDetailsSet = collect($selectedDetailsIds); // Create a set for faster lookup

        return $details->map(function ($group) use ($selectedDetailsSet, $includeSelection) {
            // Fetch the subgroups (child groups) for this group
            $subGroups = $group->children->map(function ($subGroup) use ($group, $selectedDetailsSet, $includeSelection) {
                // Check if this subgroup has its own subgroups (options)
                $subGroupOptions = $subGroup->children->map(function ($option) use ($selectedDetailsSet, $includeSelection) {
                    $optionData = [
                        'id' => $option->id, // Option ID
                        'name' => $option->name, // Option name
                    ];
                    if ($includeSelection) {
                        $optionData['is_selected'] = $selectedDetailsSet->contains($option->id); // Use contains for faster lookup
                    }
                    return $optionData;
                });

                // If there are no subgroups (options) for this subgroup, return main group options
                if ($subGroupOptions->isEmpty()) {
                    $subGroupOptions = collect([[
                        'id' => $group->id, // Return the main group ID as an option
                        'name' => $group->name, // Main group name as option
                        'is_selected' => $includeSelection ? $selectedDetailsSet->contains($group->id) : null, // Add is_selected if required
                    ]]);
                }

                return [
                    'id' => $subGroup->id, // Add subgroup ID
                    'name' => $subGroup->name,
                    'options' => $subGroupOptions->toArray(),
                ];
            });

            // For groups like "Gender", only return options without subgroups
            if ($group->children->isNotEmpty() && $group->children->first()->children->isEmpty()) {
                // Return the group with its options (without subgroups)
                $options = $group->children->map(function ($option) use ($selectedDetailsSet, $includeSelection) {
                    $optionData = [
                        'id' => $option->id, // Option ID
                        'name' => $option->name, // Option name
                    ];
                    if ($includeSelection) {
                        $optionData['is_selected'] = $selectedDetailsSet->contains($option->id); // Check if selected
                    }
                    return $optionData;
                });

                return [
                    'id' => $group->id, // Add group ID
                    'group' => $group->name,
                    'options' => $options->toArray(), // Return as an array of options
                ];
            }

            // If there are subgroups, return the full structure with subgroups
            return [
                'id' => $group->id, // Add group ID
                'group' => $group->name,
                'subGroups' => $subGroups->isEmpty() ? null : $subGroups->toArray(), // Return subgroups if they exist
            ];
        })->toArray();
    }

    private function mapSelectedDetails($details, array $selectedDetailsIds = []): array
    {
        $selectedDetailsSet = collect($selectedDetailsIds); // Create a set for faster lookup

        return $details->map(function ($group) use ($selectedDetailsSet) {
            // Filter options in the group for those where is_selected is true
            $selectedOptions = $group->children->flatMap(function ($subGroup) use ($selectedDetailsSet) {
                // Get selected options from subgroups or directly from the group
                return $subGroup->children->filter(function ($option) use ($selectedDetailsSet) {
                    return $selectedDetailsSet->contains($option->id); // Only keep selected options
                })->map(function ($option) {
                    return [
                        'id' => $option->id,
                        'name' => $option->name,
                    ];
                });
            });

            // If no selected options exist, skip this group
            if ($selectedOptions->isEmpty()) {
                return null;
            }

            return [
                'group' => $group->name,
                'selected_options' => $selectedOptions->toArray(),
            ];
        })->filter()->toArray(); // Filter out null groups
    }


    public function getAllUserDetails(): array
    {
        $details = $this->fetchDetails();
        return $this->mapDetails($details, [], false);
    }

    public function getAllUserDetailPreferencesWithSelection(User $user): array
    {
        $userDetailPreferences = $user
            ->detailPreferences()
            ->get();

//        if($userDetails->isEmpty()) {
//            return [];
//        }

        $selectedDetailsIds = $userDetailPreferences
            ->pluck('id')
            ->toArray(); // Use pluck() to fetch only IDs

        return $this->mapDetails($this->fetchDetails(), $selectedDetailsIds);
    }


    public function getAllUserDetailsWithSelection(User $user): array
    {
        $userDetails = $user
            ->details()
            ->get();

//        if($userDetails->isEmpty()) {
//            return [];
//        }

        $selectedDetailsIds = $userDetails
            ->pluck('id')
            ->toArray(); // Use pluck() to fetch only IDs

        return $this->mapDetails($this->fetchDetails(), $selectedDetailsIds);
    }


    public function getUserSelectedOptionForEachGroup(User $user): array
    {
        $data = $this->getAllUserDetailsWithSelection($user);

        $selectedOptions = [];

        foreach ($data as $group) {
            $filteredGroup = $group;
            $filteredGroup['options'] = []; // Initialize options
            $filteredGroup['subGroups'] = []; // Initialize subGroups, if needed

            // Process subGroups if they exist
            if (isset($group['subGroups']) && !empty($group['subGroups'])) {
                foreach ($group['subGroups'] as $subGroup) {
                    $filteredSubGroup = $subGroup;

                    // Filter and map selected options
                    $filteredSubGroup['options'] = array_values(array_map(function ($option) {
                        unset($option['is_selected']); // Remove "is_selected"
                        return $option;
                    }, array_filter($subGroup['options'], function ($option) {
                        return $option['is_selected'] === true;
                    })));

                    // Add the subGroup only if it has selected options
                    if (!empty($filteredSubGroup['options'])) {
                        $filteredGroup['subGroups'][] = $filteredSubGroup;
                    }
                }
            }

            // Process options if they exist
            if (isset($group['options'])) {
                $filteredGroup['options'] = array_values(array_map(function ($option) {
                    unset($option['is_selected']); // Remove "is_selected"
                    return $option;
                }, array_filter($group['options'], function ($option) {
                    return $option['is_selected'] === true;
                })));
            }

            // Remove `options` key if group has subGroups
            if (!empty($filteredGroup['subGroups'])) {
                unset($filteredGroup['options']);
            }

            // Remove the subGroups key if empty
            if (empty($filteredGroup['subGroups'])) {
                unset($filteredGroup['subGroups']);
            }

            // Add the group only if it has non-empty options or subGroups
            if (!empty($filteredGroup['options']) || isset($filteredGroup['subGroups'])) {
                $selectedOptions[] = $filteredGroup;
            }
        }

        return $selectedOptions;
    }

    public function getFlattenedOptionsForGroupsWithSubGroups(User $user): array
    {
        $data = $this->getAllUserDetailsWithSelection($user);

        $flattenedOptions = [];

        foreach ($data as $group) {
            $flattenedGroup = [
                'id' => $group['id'],
                'group' => $group['group'],
                'options' => []
            ];

            if (isset($group['subGroups']) && !empty($group['subGroups'])) {
                foreach ($group['subGroups'] as $subGroup) {
                    foreach ($subGroup['options'] as $option) {
                        if (!empty($option['is_selected'])) {
                            $flattenedGroup['options'][] = [
                                'id' => $option['id'],
                                'name' => $option['name']
                            ];
                        }
                    }
                }
            } else if (isset($group['options'])) {
                foreach ($group['options'] as $option) {
                    if (!empty($option['is_selected'])) {
                        $flattenedGroup['options'][] = [
                            'id' => $option['id'],
                            'name' => $option['name']
                        ];
                    }
                }
            }

            if (!empty($flattenedGroup['options'])) {
                $flattenedOptions[] = $flattenedGroup;
            }
        }

        return $flattenedOptions;
    }

    /**
     * Update the user details with the provided group and sub-group IDs.
     *
     * @param array $validatedData
     * @param int $userId
     * @return bool
     */
    public function updateUserDetails(array $validatedData, int $userId): bool
    {
        // Find the user
        $user = User::find($userId);
        if (!$user) {
            return false;
        }

        $detailsToUpdate = [];

        // Collect all the sub-group details first for better performance
        $subGroups = Detail::whereIn('id', array_column($validatedData['details'], 'sub_group_id'))
            ->get()->keyBy('id');

        $groupDetails = Detail::whereIn('id', array_column($validatedData['details'], 'group_id'))
            ->get()->keyBy('id');

        // Loop through the provided details
        foreach ($validatedData['details'] as $detailData) {
            $subGroupDetail = $detailData['sub_group_id'] ?? null;

            // Check for valid sub-group detail if exists
            if ($subGroupDetail && isset($subGroups[$subGroupDetail])) {
                $groupDetail = $groupDetails[$detailData['group_id']] ?? null;

                if ($groupDetail && $subGroups[$subGroupDetail]->parent_id == $groupDetail->id) {
                    $detailsToUpdate[] = $detailData['options'];
                }
            } else {
                $detailsToUpdate[] = $detailData['options'];
            }
        }

        // Flatten and merge the options
        $mergedDetailIds = collect($detailsToUpdate)->flatten()->toArray();

        // Sync the user with the selected details
        $user->details()->sync($mergedDetailIds);

        return true;
    }

    /**
     * Update the user detail preferences with the provided group and sub-group IDs.
     *
     * @param array $validatedData
     * @param int $userId
     * @return bool
     */
    public function updateUserDetailPreferences(array $validatedData, int $userId): bool
    {
        // Find the user
        $user = User::find($userId);
        if (!$user) {
            return false;
        }

        $preferencesToUpdate = [];

        // Collect all the sub-group details first for better performance
        $subGroups = Detail::whereIn('id', array_column($validatedData['preferences'], 'sub_group_id'))
            ->get()->keyBy('id');

        $groupDetails = Detail::whereIn('id', array_column($validatedData['preferences'], 'group_id'))
            ->get()->keyBy('id');

        // Loop through the provided preferences
        foreach ($validatedData['preferences'] as $preferenceData) {
            $subGroupDetail = $preferenceData['sub_group_id'] ?? null;

            // Check for valid sub-group detail if exists
            if ($subGroupDetail && isset($subGroups[$subGroupDetail])) {
                $groupDetail = $groupDetails[$preferenceData['group_id']] ?? null;

                if ($groupDetail && $subGroups[$subGroupDetail]->parent_id == $groupDetail->id) {
                    $preferencesToUpdate[] = $preferenceData['options'];
                }
            } else {
                $preferencesToUpdate[] = $preferenceData['options'];
            }
        }

        // Flatten and merge the options
        $mergedPreferenceIds = collect($preferencesToUpdate)->flatten()->toArray();

        // Sync the user with the selected preferences
        $user->detailPreferences()->sync($mergedPreferenceIds);

        return true;
    }

}
