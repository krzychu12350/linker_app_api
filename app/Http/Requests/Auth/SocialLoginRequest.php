<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class SocialLoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        // Since the request is public for social login, you can just return true
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'access_token' => 'required|string', // Validate the presence of access token
            'provider'     => 'required|string|in:facebook,google,github', // Validate that the provider is one of the allowed providers
        ];
    }

    /**
     * Get custom messages for validation errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'access_token.required' => 'The access token is required for authentication.',
            'provider.required' => 'The provider is required for social login.',
            'provider.in' => 'The provider must be one of the following: facebook, google, github.',
        ];
    }
}