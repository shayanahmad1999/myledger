<?php

namespace App\Enums;

enum TransactionType: string
{
    case OpeningBalance = 'opening_balance';
    case Income = 'income';
    case Expense = 'expense';
    case Transfer = 'transfer';
    case LoanGiven = 'loan_given';
    case LoanTaken = 'loan_taken';
    case LoanRepaymentReceived = 'loan_repayment_received';
    case LoanRepaymentPaid = 'loan_repayment_paid';
    case SavingsContribution = 'savings_contribution';
    case SavingsWithdrawal = 'savings_withdrawal';
    case Adjustment = 'adjustment';
    case Refund = 'refund';
    case Investment = 'investment';
    case InvestmentWithdrawal = 'investment_withdrawal';
    case Reversal = 'reversal';
}
