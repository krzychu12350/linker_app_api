<?php

namespace App\Enums;

use ArchTech\Enums\Values;

enum NotificationStatus: string
{
    use Values;

    case READ = 'read';
    case UNREAD = 'unread';
}
