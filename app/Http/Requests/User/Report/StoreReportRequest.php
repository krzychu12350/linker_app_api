<?php

namespace App\Http\Requests\User\Report;

use App\Enums\ReportType;
use Illuminate\Foundation\Http\FormRequest;

class StoreReportRequest extends FormRequest
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
    public function rules()
    {
        return [
            'description' => 'required|string|max:1000',
            'type' => 'required|in:' . implode(',', ReportType::values()),
            'reported_user_id' => 'required|exists:users,id',
            'files' => 'nullable|array', // The files field is optional
            'files.*' => 'file|mimes:jpg,jpeg,png,pdf,doc,docx|max:5120', // Max file size 5MB, can upload jpg, jpeg, png, pdf, doc, and docx
        ];
    }

}
