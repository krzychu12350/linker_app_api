<?php

namespace App\Enums;

use ArchTech\Enums\Values;

enum ConversationType: string
{
    use Values;

    case USER = 'user';  // One-on-one conversation
    case GROUP = 'group'; // Group conversation
}

