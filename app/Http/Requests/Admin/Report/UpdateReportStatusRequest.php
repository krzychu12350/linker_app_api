<?php

namespace App\Http\Requests\Admin\Report;

use App\Enums\BanType;
use App\Enums\ReportStatus;
use Illuminate\Foundation\Http\FormRequest;

class UpdateReportStatusRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'status' => 'required|in:' . implode(',', ReportStatus::values()),
            'ban_type' => 'required_if:status,1|in:' . implode(',', BanType::values()),
            'banned_until' => [
                'nullable',                     // Allows empty value or null
                'required_if:ban_type,1',        // Required if `ban_type` is 1
                'date_format:Y-m-d H:i:s',       // Validates the date format "YYYY-MM-DD HH:MM:SS"
                'after:today',                   // Ensures the date is after today (if provided)
            ],
        ];
    }

    /**
     * Custom error messages (optional).
     */
    public function messages(): array
    {
        return [
            'status.required' => 'The status field is required.',
            'status.in' => 'The selected status is invalid.',
            'ban_type.required_if' => 'The ban type is required when the report is accepted.',
            'ban_type.in' => 'The ban type must be either temporary or permanent.',
            'banned_until.required_if' => 'The banned until date is required for temporary bans.',
            'banned_until.date' => 'The banned until field must be a valid date.',
            'banned_until.after' => 'The banned until date must be a future date.',
        ];
    }
}
