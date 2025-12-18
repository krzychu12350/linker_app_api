<?php

namespace App\Http\Requests\User\Block;

use App\Models\Block;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class BlockUserRequest extends FormRequest
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
            'blocked_id' => 'required|exists:users,id',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $blockedUserId = $this->input('blocked_id');
            $blockerId = Auth::id();

            if ($blockerId == $blockedUserId) {
                $validator->errors()->add('blocked_id', 'Cannot block yourself');
            }

            if (Block::where('blocker_id', $blockedUserId)
                ->where('blocked_id', $blockerId)
                ->exists()) {
                $validator->errors()->add('blocked_id', 'Cannot block a user who has blocked you');
            }

            if (Block::where('blocker_id', $blockerId)
                ->where('blocked_id', $blockedUserId)
                ->exists()) {
                $validator->errors()->add('blocked_id', 'User is already blocked');
            }
        });
    }
}
