<?php

namespace App\Http\Controllers\Swipe;

use App\Enums\ConversationType;
use App\Enums\SwipeType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Swipe\StoreSwipeRequest;
use App\Http\Resources\SwipeResource;
use App\Http\Resources\MatchedSwipeResource;
use App\Models\Conversation;
use App\Models\ConversationUser;
use App\Models\Swipe;
use App\Models\SwipeMatch;
use App\Models\User;
use App\Services\MatcherNotifier;
use App\Services\SwipeFilterService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use App\Services\UserInterestService;
use Illuminate\Http\Request;

class SwipeController extends Controller
{
    public function __construct(
        private readonly UserInterestService $userInterestService,
        private readonly SwipeFilterService  $swipeFilterService,
        private readonly MatcherNotifier     $matcherNotifier,
    )
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();

        // Use the builder pattern to filter users
        $users = $this->swipeFilterService
            ->initialize($user)                // Initialize the query with the base conditions
//            ->filterByPreferenceData($user)     // Filter by preference data (age, height)
//            ->filterByDetailPreferences($user) // Filter by detail preferences
            ->excludeMatchedUsers($user)       // Exclude already matched users
            ->excludeAlreadySwipedUsers($user)
            ->excludeUsersWithRoles(['admin', 'moderator']) // Exclude users with 'admin' and 'moderator' roles
            ->excludeUsersWithoutDetails()
            ->get();                           // Get the final filtered users

        return SwipeResource::collection($users);








        //old
//        $user = auth()->user();
//        $userId = $user->id;
//
//        $users = User::with(['photos'])
//            ->where('id', '!=', $userId) // Exclude current user
//            ->whereNotIn('id', function ($query) use ($userId) {
//                $query->select('swipe_id_2')
//                    ->from('swipe_matches')
//                    ->where('swipe_id_1', $userId)
//                    ->union(
//                        SwipeMatch::select('swipe_id_1')
//                            ->where('swipe_id_2', $userId)
//                    );
//            })
//            ->get();
//
//        return SwipeResource::collection($users);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSwipeRequest $request)
    {
        // The validated data from the request
        $validated = $request->validated();

        // Get the authenticated user
        $user = auth()->user();

        // Ensure the swiped user exists
        $swipedUser = User::find($validated['swiped_user_id']);
        if (!$swipedUser) {
            return response()->json([
                'status' => 'error',
                'message' => 'The user you swiped on does not exist.'
            ], 404);
        }

        $existingMatch = SwipeMatch::where(function ($query) use ($validated) {
            $query->where('swipe_id_1', $validated['user_id'])
                ->where('swipe_id_2', $validated['swiped_user_id']);
        })->orWhere(function ($query) use ($validated) {
            $query->where('swipe_id_1', $validated['swiped_user_id'])
                ->where('swipe_id_2', $validated['user_id']);
        })->first();

        if ($existingMatch) {
            return response()->json([
                'status' => 'error',
                'message' => 'A match with this user already exists.'
            ], 400);
        }

        // Check if the swipe already exists
        $swipe = Swipe::firstOrCreate(
            [
                'user_id' => $validated['user_id'],
                'swiped_user_id' => $validated['swiped_user_id'],
            ],
            [
                'type' => $validated['type'],
            ]
        );

        // Check if the other user has swiped back
        $hasMatch = Swipe::where('user_id', $validated['swiped_user_id'])
            ->where('swiped_user_id', $validated['user_id'])
            ->whereIn('type', [SwipeType::RIGHT, SwipeType::UP]) // Check for mutual right or up swipes
            ->exists();

        if ($hasMatch) {
            // Ensure that we have a unique match
            $swipeMatch = SwipeMatch::firstOrCreate([
                'swipe_id_1' => $validated['user_id'],
                'swipe_id_2' => $validated['swiped_user_id'],
            ]);

            // Create a conversation between the two matched users
            $conversation = Conversation::create([
                'match_id' => $swipeMatch->id,
                'type' => ConversationType::USER, // Assuming a PRIVATE type exists in ConversationType
            ]);

            // Add the current user to the conversation
            ConversationUser::create([
                'conversation_id' => $conversation->id,
                'user_id' => $validated['user_id'],
                'is_admin' => false, // By default, the user is not an admin
            ]);

            // Add the swiped user to the conversation
            ConversationUser::create([
                'conversation_id' => $conversation->id,
                'user_id' => $validated['swiped_user_id'],
                'is_admin' => false, // By default, the user is not an admin
            ]);

            $this->matcherNotifier->notify(
                User::find($validated['user_id']),
                User::find($validated['swiped_user_id'])
            );

            // Return success response with matched users
            return response()->json([
                'status' => 'success',
                'message' => 'Match and conversation created',
                'data' => [
                    'current_user' => new SwipeResource(User::find($validated['user_id'])),
                    'matched_user' => new SwipeResource(User::find($validated['swiped_user_id'])),
                ]
            ], 201);
        }

        // If no match, just return a success message
        return response()->json([
            'status' => 'success',
            'message' => 'Swipe added successfully',
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        // dd($user->toArray());

        // dd( $this->userInterestService->getUserSelectedOptionForEachGroup($user));
        return response()->json([
            'status' => 'success',
            'data' => [
                'primary' => [
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'email' => $user->email,
                    'city' => $user->city,
                    'profession' => $user->profession,
                    'bio' => $user->bio,
                    'weight' => $user->weight,
                    'height' => $user->height,
                    'age' => $user->age,
                ],
                'details' => $this->userInterestService->getUserSelectedOptionForEachGroup($user),
            ]
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified swipe match from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        $swipeMatch = SwipeMatch::findOrFail($id);

        // Get the authenticated user
        $user = auth()->user();

        // Check if the user is involved in this swipe match
        if ($swipeMatch->swipe_id_1 == $user->id || $swipeMatch->swipe_id_2 == $user->id) {
            // Delete the swipe match
            $swipeMatch->delete();

            return response()->json(['message' => 'Swipe match deleted successfully.'], 204);
        }

        // If the user is not part of this swipe match
        return response()->json(['message' => 'You do not have permission to delete this swipe match.'], 403);
    }


    public function getMatchedSwipes(): AnonymousResourceCollection
    {
        // Get the authenticated user
        $user = auth()->user();

        // Retrieve all blocked user IDs
        $blockedUserIds = $user->blockedUsers()->pluck('blocked_id')->toArray();

        // Retrieve matched swipes and filter out blocked users
        $swipes = $user->swipeMatches()->reject(function ($swipe) use ($blockedUserIds) {
            return in_array($swipe->swipe_id_1, $blockedUserIds) || in_array($swipe->swipe_id_2, $blockedUserIds);
        });

        //  dd( $swipes);
        // dd($swipes->toArray());
//        $swipes = SwipeMatch::all();
//        //dd(  SwipeMatch::all()->toArray());
//        // Initialize an array to store matched users where swipe_id_2 matches
//        $matchedUsers = [];
//        // Loop through each swipeMatch
//        foreach ($swipes as $swipe) {
//            // Check if swipe_id_2 matches the authenticated user's ID
//            if ($swipe->swipe_id_1 == $user->id) {
//
//                // Find the user who swiped on the authenticated user (where swipe_id_1 matches)
//                $matchedUser = User::find($swipe->swipe_id_2);
//
//                // Add the matched user to the array
//                $matchedUsers[] = $matchedUser;
//            } elseif ($swipe->swipe_id_2 == $user->id) {
//
//                // Find the user who swiped on the authenticated user (where swipe_id_1 matches)
//                $matchedUser = User::find($swipe->swipe_id_1);
//
//                // Add the matched user to the array
//                $matchedUsers[] = $matchedUser;
//            }
//        }

        // Return matched users with the specified relationships
        return MatchedSwipeResource::collection($swipes);
    }
}
