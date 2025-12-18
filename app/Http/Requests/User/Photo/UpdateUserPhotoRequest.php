<?php

namespace App\Http\Requests\User\Photo;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserPhotoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // You can add logic here to ensure the user is authorized to update the photos
        // For now, let's assume all authenticated users can update their photos
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'photos' => 'required|array', // Ensure photos is an array
            'photos.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048', // Validate each photo
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages()
    {
        return [
            'photos.required' => 'You must upload at least one photo.',
            'photos.array' => 'Photos must be an array of files.',
            'photos.*.image' => 'Each file must be an image (jpeg, png, jpg, gif).',
            'photos.*.mimes' => 'Each photo must be of type jpeg, png, jpg, or gif.',
            'photos.*.max' => 'Each photo must not exceed 2MB in size.',
        ];
    }
}
