<?php

namespace App\Http\Controllers\User\Detail;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\Detail\UpdateUserDetailRequest;
use App\Http\Resources\User\Detail\UserDetailResource;
use App\Models\Detail;
use App\Models\User;
use App\Services\UserInterestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class UserDetailController extends Controller
{

    public function __construct(private readonly UserInterestService $userInterestService)
    {
    }


    /**
     * Get all user details options (with or without subgroups).
     *
     * @param int $id
     */
    public function index(int $id)
    {
        // Find the user by ID
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }


        return response()->json([
            'status' => 'success',
            'data' => $this->userInterestService->getAllUserDetailsWithSelection($user),
        ], 200);
    }


    /**
     * Update user details with the provided group and subgroup IDs.
     *
     * @param UpdateUserDetailRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function store(UpdateUserDetailRequest $request, int $id): JsonResponse
    {

        //  dd($request->validated());

        // Validate and update user details via the service
        $updated = $this->userInterestService->updateUserDetails($request->validated(), $id);

        if ($updated) {
            return response()->json([
                'message' => 'User details updated successfully',
            ], 200);
        }

        return response()->json([
            'message' => 'Failed to update user details',
        ], 500);
    }


    /**
     * Update user details with the provided group and subgroup IDs.
     *
     * @param UpdateUserDetailRequest $request
     * @param int $userId
     * @return JsonResponse
     */
    public function update(UpdateUserDetailRequest $request, int $id): JsonResponse
    {
        // Validate and update user details via the service
        $updated = $this->userInterestService->updateUserDetails($request->validated(), $id);

        if ($updated) {
            return response()->json([
                'message' => 'User details updated successfully',
            ], 200);
        }

        return response()->json([
            'message' => 'Failed to update user details',
        ], 500);
    }
}
