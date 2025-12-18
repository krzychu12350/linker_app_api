<?php

namespace App\Http\Controllers\GroupConversation\User;

use App\Enums\ConversationType;
use App\Http\Controllers\Controller;
use App\Http\Requests\GroupConversation\User\GroupConversationUserRequest;
use App\Models\Conversation;
use App\Models\Event;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;

class GroupConversationUserController extends Controller
{
    public function __construct(private readonly NotificationService $notificationService)
    {
    }

    /**
     * Add multiple users to a specific group conversation.
     */
    public function store(GroupConversationUserRequest $request, Conversation $group): JsonResponse
    {
        // Get validated user IDs from the request
        $userIds = $request->validated()['user_ids'];

        // Ensure the conversation is a group conversation
        if ($group->type !== ConversationType::GROUP) {
            return response()->json([
                'status' => 'error',
                'message' => 'Only group conversations can have users added.',
            ], 400);
        }

        // Filter out users who are already in the conversation
        $existingUserIds = $group->users()->pluck('user_id')->toArray();
        $newUserIds = array_diff($userIds, $existingUserIds);

        if (empty($newUserIds)) {
            return response()->json([
                'status' => 'error',
                'message' => 'All specified users are already part of this conversation.',
            ], 409);
        }

        // Add the new users to the conversation
        $group->users()->attach($newUserIds);


        foreach ($newUserIds as $userId) {
            $user = User::find($userId);
            $this->notificationService->addNotification(
                $user,
                'User ' . $user->first_name . ' ' . $user->last_name . ' has been added to ' . $group->name . '.'
            );
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Users added to the conversation successfully.',
        ], 200);
    }


    /**
     * Remove multiple users from a specific group conversation and assign admin privilege to a random user if an admin is removed.
     */
    public function destroy(GroupConversationUserRequest $request, Conversation $group): JsonResponse
    {
        // Get validated user IDs from the request
        $userIds = $request->validated()['user_ids'];

        // Ensure the conversation is a group conversation
        if ($group->type !== ConversationType::GROUP) {
            return response()->json([
                'status' => 'error',
                'message' => 'Only group conversations can have users removed.',
            ], 400);
        }

        // Filter out users who are not in the conversation
        $existingUserIds = $group->users()->pluck('user_id')->toArray();
        $removableUserIds = array_intersect($userIds, $existingUserIds);

        if (empty($removableUserIds)) {
            return response()->json([
                'status' => 'error',
                'message' => 'None of the specified users are part of this conversation.',
            ], 404);
        }

        // Check if the admin is among the removable users
        $adminUserIds = $group->users()->where('is_admin', true)->pluck('user_id')->toArray();
        $adminRemoved = !empty(array_intersect($removableUserIds, $adminUserIds));

        // If an admin is removed, assign admin privilege to a random user
        if ($adminRemoved) {
            // Get remaining users in the group
            $remainingUsers = $group->users()->whereNotIn('user_id', $removableUserIds)->pluck('user_id')->toArray();

            // Ensure there are still users remaining in the group
            if (count($remainingUsers) > 0) {
                // Select a random user to be the new admin
                $randomUserId = $remainingUsers[array_rand($remainingUsers)];

                // Assign the admin privilege to the selected random user
                $group->users()->updateExistingPivot($randomUserId, ['is_admin' => true]);
            }
        }

        // Remove the users from the conversation
        $group->users()->detach($removableUserIds);

        foreach ($removableUserIds as $userId) {
            $user = User::find($userId);
            $this->notificationService->addNotification(
                $user,
                'User ' . $user->first_name . ' ' . $user->last_name . ' has been removed from ' . $group->name . '.'
            );
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Users removed from the conversation, and admin privilege assigned if necessary.',
        ], 200);
    }


}
