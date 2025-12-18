<?php

namespace App\Http\Controllers\User\Notification;

use App\Enums\NotificationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\Notification\UpdateNotificationRequest;
use App\Http\Resources\User\Notification\NotificationResource;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class NotificationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, User $user)
    {
        $user = Auth::user();
        $notifications = $user->notifications()->latest('id')->get();

        $notifications->transform(function ($notification) {
            $notification->time_ago = Carbon::parse($notification->created_at)->diffForHumans();
            return $notification;
        });

        return NotificationResource::collection($notifications);
    }

    /**
     * Update the specified resource in storage.
     * Mark the notification as read.
     */
    public function update(UpdateNotificationRequest $request, User $user, Notification $notification)
    {
        $notification->update($request->validated());

        return response()->json([
            'message' => 'Notification marked as read successfully.',
            'notification' => new NotificationResource($notification),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     * Mark the notification as read (or delete it, based on your needs).
     */
    public function destroy(User $user, Notification $notification)
    {
        $notification->delete();

        return response()->json([], 204);
    }
}
