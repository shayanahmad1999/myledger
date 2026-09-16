<?php

namespace App\Enums;

enum RecurringMode: string
{
    case AutoPost = 'auto_post';
    case Remind = 'remind';
}
