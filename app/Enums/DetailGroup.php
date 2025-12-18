<?php

namespace App\Enums;

use ArchTech\Enums\Values;

enum DetailGroup: string
{
    use Values;

    case CHILDREN = 'children';
    case RELATIONSHIP = 'relationship';
    case STAR_SIGN = 'star sign';
    case GENDER = 'gender';
    case PERSONALITY_TYPE = 'personality_type';
    case INTERESTS = 'interests';
    case SMOKING = 'smoking';
    case PETS = 'pets';
    case RELIGION = 'religion';
    case EDUCATION_LEVEL = 'education level';
}
