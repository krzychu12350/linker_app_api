<?php

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

//Broadcast::channel('conversation.{conversation_id}', function (User $user, $conversation_id) {
//    // Check if the user is part of the conversation
//    $conversation = Conversation::find($conversation_id);
//
//    // If the conversation exists and the user is a participant, authorize access
//    return $conversation && $conversation->users->contains($user);
//});

//Broadcast::channel('conversation.6', function () {
//    return true;
//});

Broadcast::channel('conversation.{conversation_id}', function ($user, $conversation_id) {
    // Check if the user is part of the conversation
//    $conversation = Conversation::find($conversation_id);
//    // If the conversation exists and the user is a participant, authorize access
//    return $conversation && $conversation->users->contains($user);
    return true;
});

Broadcast::channel('notifications.user.{user_id}', function ($user, $userId) {
    return true;
});

Broadcast::channel('matches.user.{user_id}', function ($user, $userId) {
    return true;
});

Broadcast::channel('notifications.user.{user_id}', function ($user, $userId) {
    return true;
});

