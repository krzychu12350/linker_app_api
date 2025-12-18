<?php

namespace App\Http\Controllers\User\Ban;

use App\Enums\BanType;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\Ban\BanUserRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;

class BanController extends Controller
{
    public function banUser(BanUserRequest $request, User $user)
    {
        $validated = $request->validated();
        // Check if the user is already banned
        if ($user->is_banned) {
            return response()->json([
                'message' => 'The user is already banned and cannot be banned again.'
            ], 400); // 400 Bad Request
        }

        if ($validated['ban_type'] === BanType::TEMPORARY->value) {
            $user->update([
                'is_banned' => true,
                'banned_until' => $validated['duration']
            ]);
        } else {
            // Permanent ban
            $user->update([
                'is_banned' => true,
                'banned_until' => null
            ]);
        }

        return response()->json([
            'message' => 'User banned successfully.',
            'user' => $user
        ]);
    }


    // Unban user
    public function unbanUser(User $user): JsonResponse
    {
        // Unban the user
        $user->update([
            'is_banned' => false,
            'ban_until' => null
        ]);

        return response()->json([
            'message' => 'User unbanned successfully.',
        ]);
    }
}
