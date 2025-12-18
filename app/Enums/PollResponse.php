<?php

namespace App\Enums;

use ArchTech\Enums\Values;

enum PollResponse: int
{
    use Values;

    case MAYBE = 0;               // User is unsure
    case GOING = 1;               // User intends to go
    case FOR_SURE = 2;            // User is definitely going
    case CANNOT_ATTEND = 3;        // User cannot attend ("nie dam rady")
}