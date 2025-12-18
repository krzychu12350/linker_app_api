<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class SocialRegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Allow the request since this is a public registration
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'access_token' => 'required|string',  // The social login token from the provider
            'provider' => 'required|string|in:google,facebook,twitter',  // The provider name (you can extend this list based on your requirements)
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes(): array
    {
        return [
            'access_token' => 'social login token',
            'provider' => 'social provider',
        ];
    }
}
