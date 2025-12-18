<?php
namespace App\Http\Requests\User\Detail;

use App\Models\Detail;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserDetailRequest extends FormRequest
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
            'details' => 'required|array', // Ensure details is an array
            'details.*.group_id' => 'required|exists:details,id', // Ensure group ID exists in details table
            'details.*.sub_group_id' => [
                'nullable',
                'exists:details,id', // Ensure sub_group ID exists if provided
                function ($attribute, $value, $fail) {
                    $index = $this->getIndex($attribute);
                    $groupId = $this->input("details.{$index}.group_id"); // Get the group_id for this specific detail

                    // If sub_group_id is provided, check that it belongs to the correct group
                    if ($value && $groupId) {
                        $subGroup = Detail::find($value);

                        if ($subGroup && $subGroup->parent_id !== $groupId) {
                            $fail("The selected sub-group ID does not belong to the selected group.");
                        }
                    }
                },
            ],
            'details.*.options' => 'required|array', // Ensure options is provided as an array
            'details.*.options.*' => 'exists:details,id', // Ensure each option ID exists in the details table
        ];
    }

    /**
     * Get the index of the array element in 'details'
     *
     * @param string $attribute
     * @return int
     */
    protected function getIndex(string $attribute): int
    {
        preg_match('/details\.(\d+)\./', $attribute, $matches);
        return (int) $matches[1];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'details.required' => 'The details field is required.',
            'details.array' => 'The details field must be an array.',
            'details.*.group_id.required' => 'Group ID is required.',
            'details.*.group_id.exists' => 'The selected group ID is invalid.',
            'details.*.sub_group_id.exists' => 'The selected sub-group ID is invalid.',
            'details.*.sub_group_id' => 'The sub-group ID must belong to the selected group.',
            'details.*.options.required' => 'The options field is required.',
            'details.*.options.array' => 'The options field must be an array.',
            'details.*.options.*.exists' => 'One or more selected options are invalid.',
        ];
    }
}
