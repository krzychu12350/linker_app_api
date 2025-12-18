<?php

namespace App\Http\Controllers\User\Profile;

use App\Enums\FileType;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\Profile\UserProfileUpdateRequest;
use App\Http\Resources\User\Profile\UserProfileResource;
use App\Models\User;
use Illuminate\Http\Request;

class UserProfileController extends Controller
{
    public function show(Request $request, User $user)
    {
        return new UserProfileResource($user);
    }

    public function update(UserProfileUpdateRequest $request, User $user)
    {
        $user->update($request->validated());

        return new UserProfileResource($user);
    }
}
