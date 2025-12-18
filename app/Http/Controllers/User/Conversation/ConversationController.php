<?php

namespace App\Http\Controllers\User\Conversation;

use App\Enums\ConversationType;
use App\Http\Controllers\Controller;
use App\Http\Requests\GroupConversation\GroupConversationStoreRequest;
use App\Http\Resources\ConversationResource;
use App\Models\Conversation;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class ConversationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Get the authenticated user
        $user = auth()->user();

        // Retrieve all conversations that the user is part of, including messages and users
        $conversations = $user->conversations()
            ->with(['messages', 'users', 'users.photos']) // Load related messages, users, and photos
            ->get();

        // If no conversations exist for the user, return a message
        if ($conversations->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'No conversations found for the user.',
            ], 404);
        }

        // Format the response
        $conversationsData = $conversations->map(function ($conversation) use ($user) {
            // Get the matched user (who is not the authenticated user)
            $matchedUser = $conversation->users->where('id', '!=', $user->id)->first();

            // Get the first photo URL of the matched user (if available)
            $photoUrl = null;
            if ($matchedUser && $matchedUser->photos->isNotEmpty()) {
                // Assuming the first photo is the one you want
                $photoUrl = $matchedUser->photos->first()->url; // Adjust to the correct column name for the photo URL
            }

            return [
                'conversation' => $conversation,
                'messages' => $conversation->messages, // Include messages related to the conversation
                'matched_user' => $matchedUser, // The matched user details
                'matched_user_photo_url' => $photoUrl, // The first photo URL of the matched user
            ];
        });
        // return $conversationsData->toArray();
        return ConversationResource::collection($conversationsData->toArray());
    }

    /**
     * Display a specific conversation.
     *
     */
    public function show(User $user, Conversation $conversation)
    {
        // Get the authenticated user
        $user = auth()->user();

        // Check if the user is part of the conversation
        if (!$conversation->users->contains($user)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You are not authorized to view this conversation.',
            ], 403);
        }

        // Get the matched user (who is not the authenticated user)
        $matchedUser = $conversation->users->where('id', '!=', $user->id)->first();

        // Get the first photo URL of the matched user (if available)
        $photoUrl = null;
        if ($matchedUser && $matchedUser->photos->isNotEmpty()) {
            // Assuming the first photo is the one you want
            $photoUrl = $matchedUser->photos->first()->url; // Adjust to the correct column name for the photo URL
        }

        // Format the response as ConversationResource
        $conversationData = [
            'conversation' => $conversation,
            'messages' => $conversation->messages, // Include messages related to the conversation
            'matched_user' => $matchedUser, // The matched user details
            'matched_user_photo_url' => $photoUrl, // The first photo URL of the matched user
        ];

        // Return the resource for the specific conversation
        return new ConversationResource($conversationData);
    }
}
