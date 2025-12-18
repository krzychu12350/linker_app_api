<?php

namespace App\Http\Requests\Swipe;

use App\Enums\SwipeType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class StoreSwipeRequest extends FormRequest
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
            'user_id' => [
                'required',
                'exists:users,id',
                Rule::in([auth()->id()]), // Ensure user_id matches the authenticated user's ID
            ],
            'swiped_user_id' => 'required|exists:users,id|different:user_id',
            'type' => [
                'required',
                new Enum(SwipeType::class)
            ]
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.in' => 'The user_id must match the authenticated user.', // Custom message for Rule::in
            'user_id.required' => 'The user_id field is required.',
            'user_id.exists' => 'The selected user_id is invalid.',

            'swiped_user_id.required' => 'The swiped user ID is required.',
            'swiped_user_id.exists' => 'The selected swiped user ID does not exist in our records.',
            'swiped_user_id.different' => 'You cannot swipe on yourself.',

            'type.required' => 'The swipe type is required.',
            'type.enum' => 'The selected swipe type is invalid.',
        ];
    }

}
