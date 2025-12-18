<?php

namespace App\Http\Requests\GroupConversation;

use Illuminate\Foundation\Http\FormRequest;

class GroupConversationStoreRequest extends FormRequest
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
            //'type' => 'required|in:user,group', // Ensure type is valid (user or group)
            'name' => 'nullable|string|max:255', // Optional for group conversations
            'admin_id' =>  'exists:users,id',
            'user_ids' => 'required|array|min:1', // Ensure at least one user ID is provided
            'user_ids.*' => 'exists:users,id', // Ensure each user ID exists in the users table
        ];
    }


    /**
     * Customize the error messages for validation.
     */
    public function messages(): array
    {
        return [
            //'type.required' => 'The type of conversation is required.',
            'type.in' => 'The conversation type must be either user or group.',
            'user_ids.required' => 'At least one user ID must be provided.',
            'user_ids.*.exists' => 'One or more of the provided user IDs do not exist.',
        ];
    }
}
