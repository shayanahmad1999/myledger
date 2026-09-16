<?php

namespace App\Services;

use App\Enums\LedgerAccountKind;
use App\Enums\TransactionType;
use App\Models\Budget;
use App\Models\FinancialTransaction;
use App\Models\LedgerAccount;
use App\Models\Loan;
use App\Models\TransactionEntry;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ReportService
{
    public function summary(User $user, string $from, string $to): array
    {
        $base = TransactionEntry::query()
            ->join('financial_transactions', 'financial_transactions.id', '=', 'transaction_entries.financial_transaction_id')
            ->join('ledger_accounts', 'ledger_accounts.id', '=', 'transaction_entries.ledger_account_id')
            ->where('financial_transactions.user_id', $user->id)
            ->where('ledger_accounts.user_id', $user->id)
            ->whereIn('financial_transactions.status', ['posted', 'reversed'])
            ->whereBetween('financial_transactions.transaction_date', [$from, $to]);

        $income = (float) (clone $base)
            ->where('ledger_accounts.kind', LedgerAccountKind::Income->value)
            ->selectRaw('COALESCE(SUM(transaction_entries.credit - transaction_entries.debit), 0) AS total')
            ->value('total');

        $expense = (float) (clone $base)
            ->where('ledger_accounts.kind', LedgerAccountKind::Expense->value)
            ->selectRaw('COALESCE(SUM(transaction_entries.debit - transaction_entries.credit), 0) AS total')
            ->value('total');

        $income = round($income, 2);
        $expense = round($expense, 2);
        $surplus = round($income - $expense, 2);

        return [
            'from' => $from,
            'to' => $to,
            'income' => $income,
            'expense' => $expense,
            'net_cash_surplus' => $surplus,
            'savings_rate' => $income > 0 ? round(($surplus / $income) * 100, 2) : 0,
        ];
    }

    public function netWorth(User $user, ?string $asOf = null): array
    {
        $accounts = LedgerAccount::forUser($user->id)
            ->where('include_in_net_worth', true)
            ->get();

        $assets = [];
        $liabilities = [];

        foreach ($accounts as $account) {
            $balance = $account->balance($asOf);
            if ($account->kind === LedgerAccountKind::Asset) {
                $assets[] = [
                    'id' => $account->id,
                    'name' => $account->name,
                    'type' => $account->type->value,
                    'balance' => round($balance, 2),
                ];
            }
            if ($account->kind === LedgerAccountKind::Liability) {
                $liabilities[] = [
                    'id' => $account->id,
                    'name' => $account->name,
                    'type' => $account->type->value,
                    'balance' => round($balance, 2),
                ];
            }
        }

        $assetTotal = array_sum(array_column($assets, 'balance'));
        $liabilityTotal = array_sum(array_column($liabilities, 'balance'));

        return [
            'as_of' => $asOf ?? now()->toDateString(),
            'assets' => $assets,
            'liabilities' => $liabilities,
            'asset_total' => round($assetTotal, 2),
            'liability_total' => round($liabilityTotal, 2),
            'net_worth' => round($assetTotal - $liabilityTotal, 2),
        ];
    }

    public function expenseByCategory(User $user, string $from, string $to): array
    {
        return TransactionEntry::query()
            ->join('financial_transactions', 'financial_transactions.id', '=', 'transaction_entries.financial_transaction_id')
            ->join('ledger_accounts', 'ledger_accounts.id', '=', 'transaction_entries.ledger_account_id')
            ->leftJoin('categories', function ($join) use ($user) {
                $join->on('categories.ledger_account_id', '=', 'ledger_accounts.id')
                    ->where('categories.user_id', '=', $user->id);
            })
            ->where('financial_transactions.user_id', $user->id)
            ->where('ledger_accounts.user_id', $user->id)
            ->where('ledger_accounts.kind', LedgerAccountKind::Expense->value)
            ->whereIn('financial_transactions.status', ['posted', 'reversed'])
            ->whereBetween('financial_transactions.transaction_date', [$from, $to])
            ->groupBy('ledger_accounts.id', 'ledger_accounts.name', 'categories.id', 'categories.name', 'categories.color')
            ->select([
                'categories.id as category_id',
                DB::raw('COALESCE(categories.name, ledger_accounts.name) as name'),
                'categories.color',
                DB::raw('SUM(transaction_entries.debit - transaction_entries.credit) as total'),
            ])
            ->havingRaw('SUM(transaction_entries.debit - transaction_entries.credit) > 0')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row) => [
                'category_id' => $row->category_id,
                'name' => $row->name,
                'color' => $row->color,
                'total' => round((float) $row->total, 2),
            ])
            ->all();
    }

    public function monthlyTrend(User $user, int $months = 12): array
    {
        $start = now()->startOfMonth()->subMonths($months - 1);
        $output = [];

        for ($i = 0; $i < $months; $i++) {
            $month = $start->copy()->addMonths($i);
            $summary = $this->summary(
                $user,
                $month->copy()->startOfMonth()->toDateString(),
                $month->copy()->endOfMonth()->toDateString(),
            );
            $output[] = [
                'month' => $month->format('Y-m'),
                'income' => $summary['income'],
                'expense' => $summary['expense'],
                'net' => $summary['income'] - $summary['expense'],
            ];
        }

        return $output;
    }

    public function budgets(User $user, ?string $date = null): array
    {
        $date ??= now()->toDateString();

        return Budget::forUser($user->id)
            ->with('category')
            ->where('is_active', true)
            ->whereDate('period_start', '<=', $date)
            ->whereDate('period_end', '>=', $date)
            ->get()
            ->map(function ($budget) use ($user) {
                $spent = (float) TransactionEntry::query()
                    ->join('financial_transactions', 'financial_transactions.id', '=', 'transaction_entries.financial_transaction_id')
                    ->where('financial_transactions.user_id', $user->id)
                    ->where('transaction_entries.ledger_account_id', $budget->category->ledger_account_id)
                    ->whereIn('financial_transactions.status', ['posted', 'reversed'])
                    ->whereBetween('financial_transactions.transaction_date', [
                        $budget->period_start->toDateString(),
                        $budget->period_end->toDateString(),
                    ])
                    ->selectRaw('COALESCE(SUM(transaction_entries.debit - transaction_entries.credit), 0) AS total')
                    ->value('total');

                $amount = (float) $budget->amount;
                $percent = $amount > 0 ? round(($spent / $amount) * 100, 2) : 0;

                return [
                    'id' => $budget->id,
                    'category' => $budget->category->name,
                    'amount' => $amount,
                    'spent' => $spent,
                    'remaining' => max(0, $amount - $spent),
                    'percent' => $percent,
                    'alert_percent' => $budget->alert_percent,
                    'over_budget' => $spent > $amount,
                ];
            })
            ->all();
    }

    public function cashFlow(User $user, string $from, string $to): array
    {
        $base = FinancialTransaction::forUser($user->id)
            ->where('status', 'posted')
            ->whereBetween('transaction_date', [$from, $to]);

        $inflowTypes = [
            TransactionType::Income->value,
            TransactionType::LoanTaken->value,
            TransactionType::LoanRepaymentReceived->value,
        ];
        $outflowTypes = [
            TransactionType::Expense->value,
            TransactionType::LoanGiven->value,
            TransactionType::LoanRepaymentPaid->value,
        ];
        $internalTypes = [
            TransactionType::Transfer->value,
            TransactionType::SavingsContribution->value,
            TransactionType::SavingsWithdrawal->value,
        ];

        $inflow = (float) (clone $base)->whereIn('type', $inflowTypes)->sum('amount');
        $outflow = (float) (clone $base)->whereIn('type', $outflowTypes)->sum('amount');
        $internalTransfers = (float) (clone $base)->whereIn('type', $internalTypes)->sum('amount');

        return [
            'from' => $from,
            'to' => $to,
            'inflow' => round($inflow, 2),
            'outflow' => round($outflow, 2),
            'net' => round($inflow - $outflow, 2),
            'internal_transfers' => round($internalTransfers, 2),
        ];
    }

    public function loans(User $user): array
    {
        $items = Loan::forUser($user->id)
            ->with('person')
            ->orderByRaw("CASE WHEN status = 'active' THEN 0 ELSE 1 END")
            ->get();

        return [
            'given_total' => (float) $items
                ->filter(fn ($loan) => $loan->direction->value === 'given' && $loan->status === 'active')
                ->sum('outstanding_principal'),
            'taken_total' => (float) $items
                ->filter(fn ($loan) => $loan->direction->value === 'taken' && $loan->status === 'active')
                ->sum('outstanding_principal'),
            'items' => $items,
        ];
    }
}
