<?php

namespace App\Services;

use App\Helpers\Pusher;
use App\Models\User;

readonly class MatcherNotifier
{

    public function __construct(private Pusher $pusher)
    {
    }

    public function notify(User $authUser, User $swipedUser)
    {

        $this->pusher->triggerAsync(
            'matches.user.' . $authUser->id,
            'matches.user',
            $swipedUser->toArray()
        );

        $this->pusher->triggerAsync(
            'matches.user.' . $swipedUser->id,
            'matches.user',
            $authUser->toArray()
        );
    }
}