<?php

namespace App\Enums;

use ArchTech\Enums\Values;

enum ConversationMemberType: string
{
    use Values;

    case USER = 'user';  // One-on-one conversation
    case ADMIN = 'admin'; // Group conversation
}

