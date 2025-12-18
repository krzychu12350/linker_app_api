<?php

namespace App\Http\Controllers\Admin;

use App\Enums\BanType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\User\BanUserRequest;
use App\Http\Requests\Admin\User\StoreModeratorRequest;
use App\Http\Requests\Admin\User\UpdateModeratorRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class AdminUserController extends Controller
{
    /**
     * Display a listing of the moderators.
     */
    public function index(Request $request)
    {
        $moderators = User::role('moderator')->latest('id')->paginate($request->per_page ?? 10);
        return response()->json($moderators);
    }

    /**
     * Store a newly created moderator.
     */
    public function store(StoreModeratorRequest $request)
    {
        $validated = $request->validated();

        $validated['password'] = Hash::make($validated['password']);

        $moderator = User::create($validated);
        $moderator->assignRole('moderator');

        return response()->json($moderator, 201);
    }

    /**
     * Display the specified moderator.
     */
    public function show(User $user)
    {
        if (!$user->hasRole('moderator')) {
            return response()->json(['message' => 'User is not a moderator.'], 404);
        }

        return response()->json($user);
    }

    /**
     * Update the specified moderator.
     */
    public function update(UpdateModeratorRequest $request, User $user)
    {
        if (!$user->hasRole('moderator')) {
            return response()->json(['message' => 'User is not a moderator.'], 404);
        }

        $validated = $request->validated();

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);

        return response()->json($user);
    }

    /**
     * Remove the specified moderator.
     */
    public function destroy(User $user)
    {
        if (!$user->hasRole('moderator')) {
            return response()->json(['message' => 'User is not a moderator.'], 404);
        }
        
        $user->delete();

        return response()->json(null, 204);
    }

    /**
     * Ban a moderator.
     */
    public function ban(BanUserRequest $request, User $user)
    {
        $authUser = Auth::user();

        if (!$authUser->hasRole('admin')) {
            return response()->json(['message' => 'User is not a admin.'], 403);
        }

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
                'banned_until' => $validated['banned_until']
            ]);
        } else {
            // Permanent ban
            $user->update([
                'is_banned' => true,
                'banned_until' => null
            ]);
        }


        return response()->json(['message' => 'Moderator banned successfully.']);
    }

    /**
     * Unban a moderator.
     */
    public function unban(User $user)
    {
        $authUser = Auth::user();

        if (!$authUser->hasRole('admin')) {
            return response()->json(['message' => 'User is not a admin.'], 403);
        }

        $user->is_banned = false;
        $user->banned_until = null;
        $user->save();

        return response()->json(['message' => 'Moderator unbanned successfully.']);
    }
}
