<?php

namespace App\Http\Controllers\GroupConversation\Event\Vote;

use App\Enums\PollResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\GroupConversation\Event\StoreEventVoteRequest;
use App\Http\Resources\GroupConversation\Event\Vote\EventVoteResource;
use App\Models\Conversation;
use App\Models\Event;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Auth;

class EventVoteController extends Controller
{
    public function __construct(private readonly NotificationService $notificationService)
    {
    }


    // Get all votes for a specific event
    public function index(Conversation $group, Event $event)
    {
        // Get the event's grouped votes from the Event model
        $groupedVotes = $event->votes();

        // Return the formatted votes using EventVoteResource
        return new EventVoteResource($groupedVotes);
    }

    // Store or update a user's vote on an event
    public function store(StoreEventVoteRequest $request, Conversation $group, Event $event)
    {

        // Validate the response using the StoreEventVoteRequest validation rules
        $validatedData = $request->validated();

        $user = User::findOrFail($validatedData['user_id']);

        // Check if the user is part of the specific group conversation
        if (!$group->users->contains($user)) {
            return response()->json(['message' => 'User is not part of this group conversation.'], 403);
        }

        // Check if the user has already voted on this event
        $existingVote = $event->polls()->where('user_id', $user->id)->first();

        if ($existingVote) {
            // If the user has voted already, update their vote
            $existingVote->update([
                'response' => $validatedData['response'],
            ]);

            $this->broadcastNotifications($group, $event);

            return response()->json(['message' => 'Vote updated successfully', 'vote' => $existingVote], 200);
        } else {
            // Otherwise, create a new vote
            $vote = $event->polls()->create([
                'user_id' => $user->id,
                'response' => $validatedData['response'],
            ]);

            $this->broadcastNotifications($group, $event);

            return response()->json(['message' => 'Vote recorded successfully', 'vote' => $vote], 201);
        }
    }

    private function broadcastNotifications(Conversation $group, Event $event): void
    {
        $groupMembers = $group->users()->get();

        foreach ($groupMembers as $groupMember) {
            $this->notificationService->addNotification(
                $groupMember,
                'User ' . $event->user->first_name . ' ' . $event->user->last_name . ' has voted on ' . $event->title . ' event.'
            );
        }


    }

}
