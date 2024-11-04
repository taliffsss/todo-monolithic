<?php

namespace App\Enum;

enum TaskStatus: string
{
    case ARCHIVED = 'archived';
    case RESTORE = 'restore';
}
