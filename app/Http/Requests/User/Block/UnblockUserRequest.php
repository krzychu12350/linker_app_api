<?php

namespace App\Http\Requests\User\Block;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\Block;

class UnblockUserRequest extends FormRequest
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
           // 'blocked_id' => 'required|exists:users,id',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            dd($this->qu);
            $blockedUserId = $this->input('blocked_id');
            $blockerId = Auth::id();

            if (!Block::where('blocker_id', $blockerId)
                ->where('blocked_id', $blockedUserId)
                ->exists()) {
                $validator->errors()->add('blocked_id', 'User is not blocked.');
            }
        });
    }
}
