<?php

namespace App\Enums;

use ArchTech\Enums\Values;

enum BanType: int
{
    use Values;

    case TEMPORARY = 1;
    case PERMANENT = 2;

    case NON_BANNED = 3;

}
