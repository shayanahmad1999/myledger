<?php

namespace App\Enums;

enum LedgerAccountKind: string
{
    case Asset = 'asset';
    case Liability = 'liability';
    case Income = 'income';
    case Expense = 'expense';
    case Equity = 'equity';
}
