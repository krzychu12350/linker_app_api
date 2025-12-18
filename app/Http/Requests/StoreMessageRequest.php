<?php

namespace App\Http\Requests;

use App\Models\Conversation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreMessageRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Ensure the user is authorized to send messages (you can add logic here if needed)
    }

    public function rules()
    {
        $conversationId = $this->route('conversationId'); // Get the conversation ID from route

        return [
            // `body` is required only if `file` is not provided
            'body' => 'required_without:file|string',
            //|mimes:jpg,jpeg,png,gif,mp3,mp4,pdf|max:10240
            // `file` validation (optional, customize as needed)
            'file' => 'nullable|file', // Max size: 10MB

            'sender_id' => [
                'required',
                'exists:users,id', // sender_id must exist in the users table
                function ($attribute, $value, $fail) use ($conversationId) {
                    if ($value != Auth::id()) {
                        $fail('The sender must be the authenticated user.');
                    }
                },
            ],
            'receiver_id' => [
                'required',
                'exists:users,id', // receiver_id must exist in the users table
                function ($attribute, $value, $fail) use ($conversationId) {
                    // Check if the receiver_id is part of the conversation
                    $conversation = Conversation::find($conversationId);
                    if ($conversation && !$conversation->users()->where('id', $value)->exists()) {
                        $fail('The receiver must be a participant in this conversation.');
                    }
                },
            ],
        ];
    }

    public function messages()
    {
        return [
            'body.required_without' => 'The message body is required if no file is attached.',
            'file.mimes' => 'The file must be of type: jpg, jpeg, png, gif, mp3, mp4, pdf.',
            'file.max' => 'The file may not be greater than 10MB.',
        ];
    }

}
