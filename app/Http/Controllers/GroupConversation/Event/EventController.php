<?php

namespace App\Http\Controllers\GroupConversation\Event;

use App\Enums\NotificationType;
use App\Helpers\Pusher;
use App\Http\Controllers\Controller;
use App\Http\Requests\GroupConversation\Event\StoreEventRequest;
use App\Http\Requests\GroupConversation\Event\UpdateEventRequest;
use App\Models\Conversation;
use App\Models\Event;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class EventController extends Controller
{
    public function __construct(private readonly NotificationService $notificationService)
    {
    }

    /**
     * Get a list of events for a particular conversation.
     *
     * @param \App\Models\Conversation $group
     * @return JsonResponse
     */
    public function index(Conversation $group)
    {
        // Get all events related to the specific conversation
        $events = $group->events; // This loads all the events with their related polls and users

        // Add the grouped votes for each event
        $events = $events->map(function ($event) {
            // Fetch the grouped votes for the event using the votes() method in the Event model
            $event->votes = $event->votes();
            $event->user = $event->user->get();
            return $event;
        });

        // Return the events along with the grouped votes (if needed, you can customize the resource here)
        return response()->json($events);
    }

    // Get event with poll results
    public function show(Conversation $group, Event $event)
    {
        $event->load('polls.user');

        return response()->json($event);
    }

    // Create a new event
    public function store(StoreEventRequest $request, Conversation $group)
    {
        $event = $group->events()->create($request->validated());

        //dd($group->users()->get());
        $groupMembers = $group->users()->get();
        foreach ($groupMembers as $groupMember) {

            $this->notificationService->addNotification(
                $groupMember,
                'User ' . $event->user->first_name . ' ' . $event->user->last_name . ' added event: ' . $event->title
            );
        }


        return response()->json(['message' => 'Event created successfully', 'event' => $event], 201);
    }

    /**
     * Update an existing event.
     */
    public function update(UpdateEventRequest $request, Conversation $group, Event $event): JsonResponse
    {
        $user = Auth::user();

        // Ensure the user is authorized to update the event
        if ($event->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Update the event with validated data
        $event->update($request->validated());

        // Send a notification about the update
        $this->notificationService->addNotification(
            $user,
            'Event "' . $event->title . '" has been updated.'
        );

        return response()->json(['message' => 'Event updated successfully', 'event' => $event]);
    }


    /**
     * Delete an event and its associated votes.
     *
     * @param \App\Models\Conversation $group
     * @param \App\Models\Event $event
     * @return JsonResponse
     */
    public function destroy(Conversation $group, Event $event): JsonResponse
    {
        $user = Auth::user();
        // Delete associated votes first
        $event->polls()->delete();

        // Delete the event itself
        $event->delete();

        // Send a notification or response after deletion (if needed)
        $this->notificationService->addNotification(
            $user, // Assuming you want to notify the group user about the event deletion
            'Event "' . $event->title . '" has been deleted.'
        );

        return response()->json([], 204);
    }
}
