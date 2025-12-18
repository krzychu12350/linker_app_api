<?php

namespace App\Http\Requests\User\Ban;

use App\Enums\BanType;
use Illuminate\Foundation\Http\FormRequest;

class BanUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        //return auth()->user()->hasRole('admin');
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
            'ban_type' => 'required|in:' . implode(',', BanType::values()), // Ensure ban_type is valid
            'duration' => 'required_if:ban_type,temporary|date|after:now', // Ensure it's a valid future date
        ];
    }

    public function messages(): array
    {
        return [
            'ban_type.in' => 'The selected ban type is invalid.',
            'duration.required_if' => 'Duration is required if the ban type is temporary.',
            'duration.date' => 'The duration must be a valid date.',
            'duration.after' => 'The duration must be a future date and time.',
        ];
    }
}
