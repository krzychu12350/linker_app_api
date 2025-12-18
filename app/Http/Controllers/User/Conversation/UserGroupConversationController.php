<?php

namespace App\Http\Controllers\User\Conversation;

use App\Http\Controllers\Controller;
use App\Http\Resources\GroupConversationResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class UserGroupConversationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(User $user): JsonResponse
    {
        // Get the authenticated user
        $user = auth()->user();

        // Fetch group conversations with the last message and events with votes eagerly loaded
        $userConversations = $user->conversations()
            ->with(['users', 'events.polls.user']) // Eager load users, events, and polls with users
            ->where('type', 'group')
            ->get();

        // Include events with their grouped votes for each conversation
        $userConversations = $userConversations->map(function ($conversation) {
            // Include grouped votes in each event
            $conversation->events = $conversation->events->map(function ($event) {
                // Add the grouped votes to each event
                $event->grouped_votes = $event->votes();
                return $event;
            });
            return $conversation;
        });

        return response()->json([
            'status' => 'success',
            'data' => GroupConversationResource::collection($userConversations),
        ]);
    }
}
