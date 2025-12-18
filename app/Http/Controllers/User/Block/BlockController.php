<?php

namespace App\Http\Controllers\User\Block;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\Block\BlockUserRequest;
use App\Models\Block;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BlockController extends Controller
{

    public function index(Request $request, User $user): JsonResponse
    {
        $authUser = Auth::user();

        $blockedUsers =  $authUser->blockedUsers()
            ->latest()
            ->paginate($request->per_page ?? 10);


        return response()->json($blockedUsers);
    }

    public function store(BlockUserRequest $request, User $user): JsonResponse
    {
        $blockedUserId = $request->input('blocked_id');
        $blockerId = Auth::id();

        Block::create([
            'blocker_id' => $blockerId,
            'blocked_id' => $blockedUserId,
        ]);

        return response()->json(['message' => 'User blocked successfully'], 201);
    }

    public function destroy(Request $request, User $user, Block $block): JsonResponse
    {
        $block->delete();

        return response()->json(['message' => 'User unblocked successfully'], 204);
    }
}
