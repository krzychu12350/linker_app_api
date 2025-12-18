<?php

namespace App\Enums;

namespace App\Enums;

use ArchTech\Enums\Values;

enum SwipeType: string
{
    use Values;

    case LEFT = 'left';
    case RIGHT = 'right';
    case UP = 'up';
}

