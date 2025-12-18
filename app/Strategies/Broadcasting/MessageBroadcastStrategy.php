<?php


namespace App\Strategies\Broadcasting;

use App\Models\Message;

interface MessageBroadcastStrategy
{
    public function broadcast(Message $message): void;
}
