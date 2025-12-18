<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Detail;

class StoreUserPreferencesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'preferences' => 'required|array', // Ensure preferences is an array
            'preferences.*.group_id' => 'required|exists:details,id', // Ensure group ID exists
            'preferences.*.sub_group_id' => [
                'nullable',
                'exists:details,id',
                function ($attribute, $value, $fail) {
                    $index = $this->getIndex($attribute);
                    $groupId = $this->input("preferences.{$index}.group_id");

                    if ($value && $groupId) {
                        $subGroup = Detail::find($value);

                        if ($subGroup && $subGroup->parent_id !== $groupId) {
                            $fail("The selected sub-group ID does not belong to the selected group.");
                        }
                    }
                },
            ],
            'preferences.*.options' => 'required|array', // Ensure options are provided
            'preferences.*.options.*' => 'exists:details,id', // Ensure each option ID is valid
            'age_range_start' => 'nullable|integer|min:1',
            'age_range_end' => 'nullable|integer|min:1|gte:age_range_start',
        ];
    }

    /**
     * Get the index of the array element in 'preferences'
     *
     * @param string $attribute
     * @return int
     */
    protected function getIndex(string $attribute): int
    {
        preg_match('/preferences\.(\d+)\./', $attribute, $matches);
        return (int) $matches[1];
    }

    /**
     * Custom error messages.
     */
    public function messages(): array
    {
        return [
            'preferences.required' => 'You must select at least one preference.',
            'preferences.array' => 'Preferences must be an array.',
            'preferences.*.group_id.required' => 'Group ID is required.',
            'preferences.*.group_id.exists' => 'The selected group ID is invalid.',
            'preferences.*.sub_group_id.exists' => 'The selected sub-group ID is invalid.',
            'preferences.*.sub_group_id' => 'The sub-group ID must belong to the selected group.',
            'preferences.*.options.required' => 'Options field is required.',
            'preferences.*.options.array' => 'Options must be an array.',
            'preferences.*.options.*.exists' => 'One or more selected options are invalid.',
            'age_range_end.gte' => 'Age range end must be greater than or equal to the start.',
        ];
    }
}
