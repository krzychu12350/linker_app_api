<?php

namespace App\Enums;

use ArchTech\Enums\Values;

enum ReportStatus: int
{
    use Values;

    case ACCEPTED = 1;
    case WAITING = 2;
    case REJECTED = 3;
}
