<?php

namespace App\Http\Controllers\GroupConversation;

use App\Enums\ConversationType;
use App\Http\Controllers\Controller;
use App\Http\Requests\GroupConversation\GroupConversationStoreRequest;
use App\Http\Requests\GroupConversation\UpdateConversationAdminRequest;
use App\Http\Requests\GroupConversation\UpdateConversationNameRequest;
use App\Http\Resources\ConversationResource;
use App\Models\Conversation;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class GroupConversationController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => Conversation::where('type', ConversationType::GROUP)->get([
                'id',
                'name'
            ]),
        ]);
    }

    public function show(Conversation $group): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => $group->load('users')->only([
                'id',
                'name',
                'users'
            ]),
        ]);
    }

    /**
     * Store a newly created conversation and assign users to it.
     */
    public function store(GroupConversationStoreRequest $request): JsonResponse
    {
        // Get the validated data
        $data = $request->validated();

        // Create the conversation
        $conversation = Conversation::create([
            'type' => ConversationType::GROUP,
            'name' => $data['name'] ?: null,
        ]);

        // Attach users to the conversation and assign 'is_admin' for the owner
        $userIds = $data['user_ids'];
        $adminId = $data['admin_id'];  // Get the owner ID from the request

        // Add the owner with 'is_admin' => true
        $conversation->users()->attach($adminId, ['is_admin' => true]);

        // Attach other users without admin privileges
        $otherUserIds = array_diff($userIds, [$adminId]);  // Exclude owner from other users
        $conversation->users()->attach($otherUserIds, ['is_admin' => false]);

        // Return the created conversation as a response
        return response()->json([
            'status' => 'success',
            'message' => 'Conversation created successfully.',
            'data' => $conversation->load('users'), // Load the users relationship
        ], 201);
    }


    /**
     * Remove a group conversation and detach all connected users and messages.
     */
    public function destroy(Conversation $group): JsonResponse
    {
        //  dd($group->toArray());
        // Ensure the conversation is a group conversation
        if ($group->type !== ConversationType::GROUP) {
            return response()->json([
                'status' => 'error',
                'message' => 'Only group conversations can be deleted.',
            ], 400);
        }

        // Delete all related messages and their files
        $group->messages->each(function ($message) {

            // Delete associated files if any
            $message->delete();
        });

        // Detach all users from the conversation
        $group->users()->detach();

        // Delete the conversation
        $group->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Group conversation and related messages deleted successfully.',
        ], 200);
    }

    /**
     * Update the name of an existing conversation.
     */
    public function updateName(UpdateConversationNameRequest $request, $conversationId): JsonResponse
    {
        // Get the validated data
        $validated = $request->validated();

        // Find the conversation
        $conversation = Conversation::find($conversationId);

        // Check if the conversation exists
        if (!$conversation) {
            return response()->json([
                'status' => 'error',
                'message' => 'Conversation not found.',
            ], 404);
        }

        // Update the conversation name
        $conversation->name = $validated['name'];
        $conversation->save();

        // Return the updated conversation as a response
        return response()->json([
            'status' => 'success',
            'message' => 'Conversation name updated successfully.',
            'data' => $conversation,
        ], 200);
    }

    /**
     * Update the admin of a group conversation.
     */
    public function updateAdmin(UpdateConversationAdminRequest $request, $conversationId): JsonResponse
    {
        // Validate the incoming request
        $validated = $request->validated();

        // Find the conversation
        $conversation = Conversation::find($conversationId);

        // Check if the conversation exists and is of type GROUP
        if (!$conversation || $conversation->type !== ConversationType::GROUP) {
            return response()->json([
                'status' => 'error',
                'message' => 'Group conversation not found.',
            ], 404);
        }

        // Check if the new admin is already a member of the group
        $newAdminId = $validated['user_id'];
        if (!$conversation->users()->where('user_id', $newAdminId)->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'New admin must be a member of the group.',
            ], 400);
        }

        // Get the current admin
        $currentAdmin = $conversation->users()->wherePivot('is_admin', true)->first();

        if (!$currentAdmin) {
            return response()->json([
                'status' => 'error',
                'message' => 'There is no admin in the group to update.',
            ], 400);
        }

        // Remove the admin status from the current admin
        $conversation->users()->updateExistingPivot($currentAdmin->id, ['is_admin' => false]);

        // Assign the new admin
        $conversation->users()->updateExistingPivot($newAdminId, ['is_admin' => true]);

        // Return a success response
        return response()->json([
            'status' => 'success',
            'message' => 'Group admin updated successfully.',
            'data' => $conversation->load('users'), // Return the updated conversation with users
        ], 200);
    }

}


