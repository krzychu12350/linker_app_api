<?php

namespace App\Enums;

namespace App\Enums;

use ArchTech\Enums\Values;

enum NotificationType: string
{
    use Values;

    case GROUP = 'group';
    case MATCH = 'match';
}
