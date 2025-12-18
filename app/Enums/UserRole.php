<?php

namespace App\Enums;

use ArchTech\Enums\Values;

enum UserRole: string
{
    use Values;

    case USER = 'user';
    case MODERATOR = 'moderator';
    case ADMIN = 'admin';
}
