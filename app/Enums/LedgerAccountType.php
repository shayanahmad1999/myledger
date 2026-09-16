<?php

namespace App\Enums;

enum LedgerAccountType: string
{
    case Cash = 'cash';
    case Bank = 'bank';
    case MobileWallet = 'mobile_wallet';
    case Savings = 'savings';
    case Investment = 'investment';
    case LoanReceivable = 'loan_receivable';
    case LoanPayable = 'loan_payable';
    case Category = 'category';
    case System = 'system';
    case Equity = 'equity';

        // System ledger accounts
    case OpeningBalanceEquity = 'opening_balance_equity';
    case InterestIncome = 'interest_income';
    case InterestExpense = 'interest_expense';
    case AdjustmentEquity = 'adjustment_equity';

    public function isUserMoneyAccount(): bool
    {
        return in_array($this, [
            self::Cash,
            self::Bank,
            self::MobileWallet,
            self::Savings,
            self::Investment,
        ], true);
    }
}
