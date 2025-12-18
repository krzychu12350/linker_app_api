<?php

namespace Database\Seeders;

use App\Enums\DetailGroup;
use App\Models\Detail;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define the group and their subgroups with options
        $groups = [
            // Groups with subgroups and options (INTERESTS case)
            DetailGroup::INTERESTS->value => [
                'fashion' => ['Casual', 'Formal', 'Streetwear', 'Vintage'],
                'music' => ['Rock', 'Pop', 'Jazz', 'Classical', 'Hip-hop'],
                'culture' => ['Art', 'Theatre', 'Literature', 'Film', 'Travel'],
            ],

//            DetailGroup::INTERESTS->value => [
//             'Casual', 'Formal', 'Streetwear', 'Vintage',
//             'Rock', 'Pop', 'Jazz', 'Classical', 'Hip-hop',
//             'Art', 'Theatre', 'Literature', 'Film', 'Travel',
//            ],

            // Groups without subgroups, just direct options
            DetailGroup::CHILDREN->value => ['Yes', 'No'],
            DetailGroup::RELATIONSHIP->value => ['Single', 'In a Relationship', 'Married', 'Divorced'],
            DetailGroup::STAR_SIGN->value => ['Aries', 'Taurus', 'Gemini', 'Cancer', 'Leo', 'Virgo', 'Libra', 'Scorpio', 'Sagittarius', 'Capricorn', 'Aquarius', 'Pisces'],
            DetailGroup::GENDER->value => ['Male', 'Female', 'Non-binary'],
            DetailGroup::PERSONALITY_TYPE->value => ['INTJ', 'INFJ', 'ENTP', 'ENFP', 'ISTJ', 'ISFJ', 'ESTJ', 'ESFJ'],
            DetailGroup::SMOKING->value => ['I smoke', 'I do not smoke', 'I used to smoke', 'I want to quit'],
            DetailGroup::PETS->value => ['I have', "I'm going to have", "I don't have", 'I had'],
//            DetailGroup::RELIGION->value => [
//                'Christianity' => ['Catholic', 'Protestant', 'Orthodox'],
//                'Islam' => ['Sunni', 'Shia'],
//                'Hinduism' => ['Vaishnavism', 'Shaivism'],
//                'Buddhism' => ['Theravada', 'Mahayana'],
//            ],
            DetailGroup::RELIGION->value => ['Catholic', 'Protestant', 'Orthodox','Sunni', 'Shia', 'Vaishnavism', 'Shaivism','Theravada', 'Mahayana'],
            DetailGroup::EDUCATION_LEVEL->value => ['High School', 'Associate Degree', 'Bachelor’s Degree', 'Master’s Degree', 'Doctorate'],
        ];

        // Loop through each group and insert into the `details` table
        foreach ($groups as $group => $subGroups) {
            // Create group entry
            $groupDetail = Detail::create([
                'name' => ucfirst($group),
                'group' => $group,
            ]);

            // Check if this group has subgroups (i.e., is it an array of subgroups)
            if (is_array($subGroups)) {
                // Loop through subgroups and create options for each subgroup
                foreach ($subGroups as $subGroupName => $options) {
                    // If it's not a subgroup (i.e., just an array of options directly under the group)
                    if (is_array($options)) {
                        // Create subgroup entry
                        $subGroupDetail = Detail::create([
                            'name' => ucfirst($subGroupName),
                            'group' => $group,
                            'parent_id' => $groupDetail->id, // Associate with parent group
                        ]);

                        // Create options for each subgroup
                        foreach ($options as $option) {
                            Detail::create([
                                'name' => $option,
                                'group' => $group,
                                'sub_group' => $subGroupName,
                                'parent_id' => $subGroupDetail->id, // Associate with the subgroup
                            ]);
                        }
                    } else {
                        // If no subgroups (just a direct group), create options for each option
                        Detail::create([
                            'name' => $options,
                            'group' => $group,
                            'parent_id' => $groupDetail->id, // Associate directly with the group
                        ]);
                    }
                }
            }
        }

        // Add more groups as necessary, following the same pattern
    }
}
