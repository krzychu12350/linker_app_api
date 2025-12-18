<?php

namespace App\Enums;

use ArchTech\Enums\Values;

enum MessageType: string
{
    use Values;

    case FILE = 'file';

    case TEXT = 'text';
}
