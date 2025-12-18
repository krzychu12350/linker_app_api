<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use App\Models\Detail; // Assuming you have a Detail model

class StoreUserPreferencesRequest2 extends FormRequest
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
            'details' => 'required|array',
            'details.*' => 'exists:details,id',
            'age_range_start' => 'nullable|integer|min:1',
            'age_range_end' => 'nullable|integer|min:1|gte:age_range_start',
            'height' => 'nullable|integer|min:1',
        ];
    }

    /**
     * Custom error messages.
     */
    public function messages()
    {
        return [
            'details.required' => 'You must select at least one preference.',
            'details.*.exists' => 'Selected preference must be a valid detail.',
            'age_range_end.gte' => 'Age range end must be greater than or equal to the start.',
        ];
    }

    /**
     * Add custom validation after default validation.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            if ($this->has('details')) {
                $details = $this->input('details');

                // Find details that are parents of other details
                $invalidDetails = Detail::whereIn('id', $details)
                    ->whereHas('children') // Assuming "children" is the relationship for child details
                    ->get();

                if ($invalidDetails->isNotEmpty()) {
                    $validator->errors()->add('details', 'Selected details cannot be parents of other details.');
                }
            }
        });
    }
}
