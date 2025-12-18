<?php

namespace App\Enums;

use ArchTech\Enums\Values;

enum ReportType: string
{
    use Values;

    case ILLEGAL_CONTENT = 'illegal_content';
    case SPAM = 'spam';
    case ABUSE = 'abuse';
    case OTHER = 'other';
}
