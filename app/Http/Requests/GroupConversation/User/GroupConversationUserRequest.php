<?php

namespace App\Http\Requests\GroupConversation\User;

use Illuminate\Foundation\Http\FormRequest;

class GroupConversationUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Add authorization logic if needed
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id', // Ensure each user ID exists in the users table
        ];
    }

    /**
     * Customize the error messages for validation.
     */
    public function messages(): array
    {
        return [
            'user_ids.required' => 'The user IDs field is required.',
            'user_ids.array' => 'The user IDs must be an array.',
            'user_ids.*.exists' => 'One or more user IDs do not exist.',
        ];
    }
}
