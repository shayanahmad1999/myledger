<?php

namespace App\Enums;

enum LoanDirection: string
{
    case Given = 'given';
    case Taken = 'taken';
}
