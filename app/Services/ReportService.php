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
use Carbon\Carbon;
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
                $join
                    ->on('categories.ledger_account_id', '=', 'ledger_accounts.id')
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
            ->map(fn($row) => [
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
            ->with(['category', 'currency'])
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
                    'currency' => $budget->currency->symbol,
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
            ->with(['person', 'currency'])
            ->orderByRaw("CASE WHEN status = 'active' THEN 0 ELSE 1 END")
            ->get();

        return [
            'given_total' => (float) $items
                ->filter(fn($loan) => $loan->direction->value === 'given' && $loan->status === 'active')
                ->sum('outstanding_principal'),
            'taken_total' => (float) $items
                ->filter(fn($loan) => $loan->direction->value === 'taken' && $loan->status === 'active')
                ->sum('outstanding_principal'),
            'items' => $items,
            'currency' => [
                'symbol' => $items->first()?->currency->symbol,
            ],
        ];
    }

    public function committees(User $user): array
    {
        $items = \App\Models\Committee::forUser($user->id)
            ->with(['members', 'rounds'])
            ->latest()
            ->get();

        $activePoolTotal = (float) $items->where('status', 'active')->sum('total_pool_amount');
        $totalMonthlyContribution = (float) $items->where('status', 'active')->sum('contribution_amount');

        $committeeIds = $items->pluck('id');
        $totalCollected = (float) \App\Models\CommitteePayment::whereHas('round', fn($q) => $q->whereIn('committee_id', $committeeIds))
            ->where('status', 'paid')
            ->sum('amount');

        $totalDisbursed = (float) \App\Models\CommitteeRound::whereIn('committee_id', $committeeIds)
            ->where('payout_status', 'paid')
            ->sum('payout_amount');

        $formatted = $items->map(function ($c) {
            $completedRounds = $c->rounds->where('payout_status', 'paid')->count();
            $nextRound = $c->rounds->where('payout_status', 'pending')->first();
            return [
                'id' => $c->id,
                'name' => $c->name,
                'status' => $c->status,
                'frequency' => $c->frequency,
                'contribution_amount' => (float) $c->contribution_amount,
                'total_members' => $c->total_members,
                'total_pool_amount' => (float) $c->total_pool_amount,
                'completed_rounds' => $completedRounds,
                'progress_percent' => $c->total_members > 0 ? round(($completedRounds / $c->total_members) * 100) : 0,
                'next_due_date' => $nextRound?->due_date?->toDateString(),
            ];
        })->all();

        return [
            'active_count' => $items->where('status', 'active')->count(),
            'active_pool_total' => $activePoolTotal,
            'monthly_contribution_total' => $totalMonthlyContribution,
            'total_collected' => $totalCollected,
            'total_disbursed' => $totalDisbursed,
            'committees' => $formatted,
        ];
    }

    public function trialBalance(User $user, ?string $asOf = null): array
    {
        $asOfDate = $asOf ?? now()->toDateString();
        $accounts = LedgerAccount::forUser($user->id)->get();

        $rows = [];
        $totalDebit = 0.0;
        $totalCredit = 0.0;

        foreach ($accounts as $acc) {
            $entries = TransactionEntry::query()
                ->join('financial_transactions', 'financial_transactions.id', '=', 'transaction_entries.financial_transaction_id')
                ->where('financial_transactions.user_id', $user->id)
                ->where('transaction_entries.ledger_account_id', $acc->id)
                ->where('financial_transactions.status', 'posted')
                ->whereDate('financial_transactions.transaction_date', '<=', $asOfDate)
                ->selectRaw('COALESCE(SUM(debit), 0) AS total_debit, COALESCE(SUM(credit), 0) AS total_credit')
                ->first();

            $debit = (float) ($entries?->total_debit ?? 0);
            $credit = (float) ($entries?->total_credit ?? 0);

            if ($debit == 0 && $credit == 0)
                continue;

            $netBalance = $debit - $credit;

            $totalDebit += $debit;
            $totalCredit += $credit;

            $rows[] = [
                'id' => $acc->id,
                'name' => $acc->name,
                'kind' => $acc->kind->value,
                'type' => $acc->type->value,
                'debit' => round($debit, 2),
                'credit' => round($credit, 2),
                'net_balance' => round($netBalance, 2),
            ];
        }

        return [
            'as_of' => $asOfDate,
            'accounts' => $rows,
            'total_debit' => round($totalDebit, 2),
            'total_credit' => round($totalCredit, 2),
            'is_balanced' => abs($totalDebit - $totalCredit) < 0.001,
        ];
    }

    public function generalLedger(User $user, int $accountId, string $from, string $to): array
    {
        $account = LedgerAccount::forUser($user->id)->findOrFail($accountId);

        // Calculate opening balance prior to $from date
        $priorEntries = TransactionEntry::query()
            ->join('financial_transactions', 'financial_transactions.id', '=', 'transaction_entries.financial_transaction_id')
            ->where('financial_transactions.user_id', $user->id)
            ->where('transaction_entries.ledger_account_id', $account->id)
            ->where('financial_transactions.status', 'posted')
            ->whereDate('financial_transactions.transaction_date', '<', $from)
            ->selectRaw('COALESCE(SUM(debit), 0) AS d, COALESCE(SUM(credit), 0) AS c')
            ->first();

        $openingDebit = (float) ($priorEntries?->d ?? 0);
        $openingCredit = (float) ($priorEntries?->c ?? 0);
        $normalDebit = in_array($account->kind, [LedgerAccountKind::Asset, LedgerAccountKind::Expense], true);
        $openingBalance = $normalDebit ? ($openingDebit - $openingCredit) : ($openingCredit - $openingDebit);

        // Fetch period entries
        $entries = TransactionEntry::query()
            ->join('financial_transactions', 'financial_transactions.id', '=', 'transaction_entries.financial_transaction_id')
            ->where('financial_transactions.user_id', $user->id)
            ->where('transaction_entries.ledger_account_id', $account->id)
            ->where('financial_transactions.status', 'posted')
            ->whereBetween('financial_transactions.transaction_date', [$from, $to])
            ->orderBy('financial_transactions.transaction_date')
            ->orderBy('financial_transactions.id')
            ->select(
                'financial_transactions.id as tx_id',
                'financial_transactions.reference_no',
                'financial_transactions.transaction_date',
                'financial_transactions.type',
                'financial_transactions.description',
                'financial_transactions.currency_id',
                'financial_transactions.original_amount',
                'financial_transactions.original_currency_id',
                'financial_transactions.exchange_rate',
                'transaction_entries.debit',
                'transaction_entries.credit',
                'transaction_entries.memo'
            )
            ->get();

        $runningBalance = $openingBalance;
        $statementRows = [];

        foreach ($entries as $e) {
            $d = (float) $e->debit;
            $c = (float) $e->credit;
            $runningBalance += $normalDebit ? ($d - $c) : ($c - $d);

            $statementRows[] = [
                'tx_id' => $e->tx_id,
                'reference_no' => $e->reference_no,
                'date' => $e->transaction_date,
                'type' => $e->type,
                'description' => $e->description ?: $e->memo,
                'debit' => round($d, 2),
                'credit' => round($c, 2),
                'running_balance' => round($runningBalance, 2),
                'currency_id' => $e->currency_id,
                'original_amount' => $e->original_amount ? round((float) $e->original_amount, 2) : null,
                'original_currency_id' => $e->original_currency_id,
                'exchange_rate' => $e->exchange_rate ? round((float) $e->exchange_rate, 8) : null,
            ];
        }

        return [
            'account' => [
                'id' => $account->id,
                'name' => $account->name,
                'kind' => $account->kind->value,
                'type' => $account->type->value,
            ],
            'from' => $from,
            'to' => $to,
            'opening_balance' => round($openingBalance, 2),
            'closing_balance' => round($runningBalance, 2),
            'entries' => $statementRows,
        ];
    }

    public function smartInsights(User $user): array
    {
        $insights = [];

        // 1. Month Summary check
        $from = now()->startOfMonth()->toDateString();
        $to = now()->endOfMonth()->toDateString();
        $summary = $this->summary($user, $from, $to);

        if ($summary['income'] > 0 && $summary['savings_rate'] >= 20) {
            $insights[] = [
                'type' => 'success',
                'icon' => 'bi-award',
                'title' => 'Great Savings Pace!',
                'message' => "You have saved {$summary['savings_rate']}% of your income this month. Keep it up!",
            ];
        } elseif ($summary['expense'] > $summary['income'] && $summary['income'] > 0) {
            $insights[] = [
                'type' => 'warning',
                'icon' => 'bi-exclamation-triangle',
                'title' => 'Monthly Spending Alert',
                'message' => 'Your expenses this month exceed your income by ' . round($summary['expense'] - $summary['income'], 2) . '.',
            ];
        }

        // 2. Active Committees Alert
        $committeesStats = $this->committees($user);
        if ($committeesStats['active_count'] > 0) {
            $insights[] = [
                'type' => 'info',
                'icon' => 'bi-diagram-3',
                'title' => 'Active Committees Tracker',
                'message' => "You are managing/participating in {$committeesStats['active_count']} active committees with a monthly pool value of " . round($committeesStats['active_pool_total'], 2) . '.',
            ];
        }

        // 3. Loans Warning
        $loansInfo = $this->loans($user);
        if ($loansInfo['taken_total'] > 0) {
            $insights[] = [
                'type' => 'warning',
                'icon' => 'bi-cash-stack',
                'title' => 'Outstanding Liabilities',
                'message' => 'You have ' . round($loansInfo['taken_total'], 2) . ' in active loans payable to return.',
            ];
        }

        return $insights;
    }

    public function balanceSheet(User $user, ?string $asOf = null): array
    {
        $asOfDate = $asOf ?? now()->toDateString();
        $priorDate = \Carbon\Carbon::parse($asOfDate)->subMonth()->toDateString();

        $accounts = LedgerAccount::forUser($user->id)->get();

        $assets = [];
        $liabilities = [];
        $equityAccounts = [];

        foreach ($accounts as $acc) {
            $currentBal = $acc->balance($asOfDate);
            $priorBal = $acc->balance($priorDate);

            $item = [
                'id' => $acc->id,
                'name' => $acc->name,
                'type' => $acc->type->value,
                'current_balance' => round($currentBal, 2),
                'prior_balance' => round($priorBal, 2),
                'change' => round($currentBal - $priorBal, 2),
            ];

            if ($acc->kind === LedgerAccountKind::Asset) {
                $assets[] = $item;
            } elseif ($acc->kind === LedgerAccountKind::Liability) {
                $liabilities[] = $item;
            } elseif ($acc->kind === LedgerAccountKind::Equity) {
                $equityAccounts[] = $item;
            }
        }

        $retainedEarningsCurrent = (float) TransactionEntry::query()
            ->join('financial_transactions', 'financial_transactions.id', '=', 'transaction_entries.financial_transaction_id')
            ->join('ledger_accounts', 'ledger_accounts.id', '=', 'transaction_entries.ledger_account_id')
            ->where('financial_transactions.user_id', $user->id)
            ->where('financial_transactions.status', 'posted')
            ->whereDate('financial_transactions.transaction_date', '<=', $asOfDate)
            ->selectRaw("SUM(CASE WHEN ledger_accounts.kind = 'income' THEN (transaction_entries.credit - transaction_entries.debit) WHEN ledger_accounts.kind = 'expense' THEN (transaction_entries.credit - transaction_entries.debit) ELSE 0 END) as net")
            ->value('net') ?? 0;

        $retainedEarningsPrior = (float) TransactionEntry::query()
            ->join('financial_transactions', 'financial_transactions.id', '=', 'transaction_entries.financial_transaction_id')
            ->join('ledger_accounts', 'ledger_accounts.id', '=', 'transaction_entries.ledger_account_id')
            ->where('financial_transactions.user_id', $user->id)
            ->where('financial_transactions.status', 'posted')
            ->whereDate('financial_transactions.transaction_date', '<=', $priorDate)
            ->selectRaw("SUM(CASE WHEN ledger_accounts.kind = 'income' THEN (transaction_entries.credit - transaction_entries.debit) WHEN ledger_accounts.kind = 'expense' THEN (transaction_entries.credit - transaction_entries.debit) ELSE 0 END) as net")
            ->value('net') ?? 0;

        $totalAssets = array_sum(array_column($assets, 'current_balance'));
        $totalLiabilities = array_sum(array_column($liabilities, 'current_balance'));
        $totalEquityBase = array_sum(array_column($equityAccounts, 'current_balance'));
        $totalEquity = $totalEquityBase + $retainedEarningsCurrent;

        $priorAssets = array_sum(array_column($assets, 'prior_balance'));
        $priorLiabilities = array_sum(array_column($liabilities, 'prior_balance'));
        $priorEquityBase = array_sum(array_column($equityAccounts, 'prior_balance'));
        $priorEquity = $priorEquityBase + $retainedEarningsPrior;

        return [
            'as_of' => $asOfDate,
            'prior_as_of' => $priorDate,
            'assets' => $assets,
            'liabilities' => $liabilities,
            'equity_accounts' => $equityAccounts,
            'retained_earnings' => [
                'current' => round($retainedEarningsCurrent, 2),
                'prior' => round($retainedEarningsPrior, 2),
                'change' => round($retainedEarningsCurrent - $retainedEarningsPrior, 2),
            ],
            'totals' => [
                'assets' => round($totalAssets, 2),
                'liabilities' => round($totalLiabilities, 2),
                'equity' => round($totalEquity, 2),
                'liabilities_plus_equity' => round($totalLiabilities + $totalEquity, 2),
                'is_balanced' => abs($totalAssets - ($totalLiabilities + $totalEquity)) < 0.05,
                'prior_assets' => round($priorAssets, 2),
                'prior_liabilities' => round($priorLiabilities, 2),
                'prior_equity' => round($priorEquity, 2),
            ],
        ];
    }

    public function profitAndLoss(User $user, string $from, string $to): array
    {
        $startDate = \Carbon\Carbon::parse($from);
        $endDate = \Carbon\Carbon::parse($to);
        $days = $startDate->diffInDays($endDate) + 1;

        $priorTo = $startDate->copy()->subDay()->toDateString();
        $priorFrom = $startDate->copy()->subDays($days)->toDateString();

        $getEntries = function ($f, $t, $kind) use ($user) {
            return TransactionEntry::query()
                ->join('financial_transactions', 'financial_transactions.id', '=', 'transaction_entries.financial_transaction_id')
                ->join('ledger_accounts', 'ledger_accounts.id', '=', 'transaction_entries.ledger_account_id')
                ->leftJoin('categories', function ($join) use ($user) {
                    $join
                        ->on('categories.ledger_account_id', '=', 'ledger_accounts.id')
                        ->where('categories.user_id', '=', $user->id);
                })
                ->where('financial_transactions.user_id', $user->id)
                ->where('ledger_accounts.user_id', $user->id)
                ->where('ledger_accounts.kind', $kind)
                ->where('financial_transactions.status', 'posted')
                ->whereBetween('financial_transactions.transaction_date', [$f, $t])
                ->groupBy('ledger_accounts.id', 'ledger_accounts.name', 'categories.name')
                ->select([
                    'ledger_accounts.id',
                    DB::raw('COALESCE(categories.name, ledger_accounts.name) as name'),
                    DB::raw($kind === LedgerAccountKind::Income->value
                        ? 'SUM(transaction_entries.credit - transaction_entries.debit) as total'
                        : 'SUM(transaction_entries.debit - transaction_entries.credit) as total')
                ])
                ->get()
                ->keyBy('id');
        };

        $currentIncome = $getEntries($from, $to, LedgerAccountKind::Income->value);
        $priorIncome = $getEntries($priorFrom, $priorTo, LedgerAccountKind::Income->value);

        $currentExpense = $getEntries($from, $to, LedgerAccountKind::Expense->value);
        $priorExpense = $getEntries($priorFrom, $priorTo, LedgerAccountKind::Expense->value);

        $incomeRows = [];
        $allIncomeIds = $currentIncome->keys()->merge($priorIncome->keys())->unique();
        foreach ($allIncomeIds as $id) {
            $c = (float) ($currentIncome[$id]->total ?? 0);
            $p = (float) ($priorIncome[$id]->total ?? 0);
            $incomeRows[] = [
                'id' => $id,
                'name' => $currentIncome[$id]->name ?? $priorIncome[$id]->name ?? 'Income',
                'current' => round($c, 2),
                'prior' => round($p, 2),
                'change' => round($c - $p, 2),
                'change_percent' => $p > 0 ? round((($c - $p) / $p) * 100, 1) : 0,
            ];
        }

        $expenseRows = [];
        $allExpenseIds = $currentExpense->keys()->merge($priorExpense->keys())->unique();
        foreach ($allExpenseIds as $id) {
            $c = (float) ($currentExpense[$id]->total ?? 0);
            $p = (float) ($priorExpense[$id]->total ?? 0);
            $expenseRows[] = [
                'id' => $id,
                'name' => $currentExpense[$id]->name ?? $priorExpense[$id]->name ?? 'Expense',
                'current' => round($c, 2),
                'prior' => round($p, 2),
                'change' => round($c - $p, 2),
                'change_percent' => $p > 0 ? round((($c - $p) / $p) * 100, 1) : 0,
            ];
        }

        $totalCurrentIncome = array_sum(array_column($incomeRows, 'current'));
        $totalPriorIncome = array_sum(array_column($incomeRows, 'prior'));

        $totalCurrentExpense = array_sum(array_column($expenseRows, 'current'));
        $totalPriorExpense = array_sum(array_column($expenseRows, 'prior'));

        $netCurrentProfit = $totalCurrentIncome - $totalCurrentExpense;
        $netPriorProfit = $totalPriorIncome - $totalPriorExpense;

        return [
            'from' => $from,
            'to' => $to,
            'prior_from' => $priorFrom,
            'prior_to' => $priorTo,
            'income' => $incomeRows,
            'expense' => $expenseRows,
            'totals' => [
                'current_income' => round($totalCurrentIncome, 2),
                'prior_income' => round($totalPriorIncome, 2),
                'income_change' => round($totalCurrentIncome - $totalPriorIncome, 2),
                'current_expense' => round($totalCurrentExpense, 2),
                'prior_expense' => round($totalPriorExpense, 2),
                'expense_change' => round($totalCurrentExpense - $totalPriorExpense, 2),
                'current_net' => round($netCurrentProfit, 2),
                'prior_net' => round($netPriorProfit, 2),
                'net_change' => round($netCurrentProfit - $netPriorProfit, 2),
                'net_margin_percent' => $totalCurrentIncome > 0 ? round(($netCurrentProfit / $totalCurrentIncome) * 100, 1) : 0,
            ],
        ];
    }

    public function partyLedger(User $user, int $personId, string $from, string $to): array
    {
        $person = \App\Models\Person::forUser($user->id)->findOrFail($personId);

        $transactions = FinancialTransaction::forUser($user->id)
            ->where('person_id', $personId)
            ->where('status', 'posted')
            ->whereBetween('transaction_date', [$from, $to])
            ->orderBy('transaction_date')
            ->orderBy('id')
            ->select([
                'id', 'transaction_date', 'reference_no', 'type', 'description',
                'currency_id', 'original_amount', 'original_currency_id', 'exchange_rate', 'amount'
            ])
            ->get();

        $priorTxs = FinancialTransaction::forUser($user->id)
            ->where('person_id', $personId)
            ->where('status', 'posted')
            ->whereDate('transaction_date', '<', $from)
            ->select([
                'id', 'transaction_date', 'reference_no', 'type', 'description',
                'currency_id', 'original_amount', 'original_currency_id', 'exchange_rate', 'amount'
            ])
            ->get();

        $calcNet = function ($tx) {
            return match ($tx->type->value) {
                'loan_given', 'expense' => (float) $tx->amount,
                'loan_taken', 'loan_repayment_received', 'income' => -(float) $tx->amount,
                default => 0.0,
            };
        };

        $openingBalance = 0.0;
        foreach ($priorTxs as $ptx) {
            $openingBalance += $calcNet($ptx);
        }

        $runningBalance = $openingBalance;
        $items = [];
        $totalDebit = 0.0;
        $totalCredit = 0.0;

        foreach ($transactions as $tx) {
            $debit = in_array($tx->type->value, ['loan_given', 'expense']) ? (float) $tx->amount : 0.0;
            $credit = in_array($tx->type->value, ['loan_taken', 'loan_repayment_received', 'income']) ? (float) $tx->amount : 0.0;

            $totalDebit += $debit;
            $totalCredit += $credit;
            $runningBalance += ($debit - $credit);

            $items[] = [
                'id' => $tx->id,
                'date' => $tx->transaction_date,
                'reference_no' => $tx->reference_no,
                'type' => $tx->type->value,
                'description' => $tx->description,
                'debit' => round($debit, 2),
                'credit' => round($credit, 2),
                'running_balance' => round($runningBalance, 2),
                'currency_id' => $tx->currency_id,
                'original_amount' => $tx->original_amount ? round((float) $tx->original_amount, 2) : null,
                'original_currency_id' => $tx->original_currency_id,
                'exchange_rate' => $tx->exchange_rate ? round((float) $tx->exchange_rate, 8) : null,
            ];
        }

        return [
            'person' => [
                'id' => $person->id,
                'name' => $person->name,
                'phone' => $person->phone,
                'email' => $person->email,
            ],
            'from' => $from,
            'to' => $to,
            'opening_balance' => round($openingBalance, 2),
            'closing_balance' => round($runningBalance, 2),
            'total_debit' => round($totalDebit, 2),
            'total_credit' => round($totalCredit, 2),
            'entries' => $items,
        ];
    }

    public function momYoyComparison(User $user, int $year): array
    {
        $months = [];
        $yearIncome = 0.0;
        $yearExpense = 0.0;

        $priorYear = $year - 1;
        $priorYearIncome = 0.0;
        $priorYearExpense = 0.0;

        for ($m = 1; $m <= 12; $m++) {
            $start = sprintf('%04d-%02d-01', $year, $m);
            $end = \Carbon\Carbon::parse($start)->endOfMonth()->toDateString();

            $sum = $this->summary($user, $start, $end);
            $inc = $sum['income'];
            $exp = $sum['expense'];
            $net = $inc - $exp;

            $yearIncome += $inc;
            $yearExpense += $exp;

            $pStart = sprintf('%04d-%02d-01', $priorYear, $m);
            $pEnd = \Carbon\Carbon::parse($pStart)->endOfMonth()->toDateString();
            $pSum = $this->summary($user, $pStart, $pEnd);
            $pInc = $pSum['income'];
            $pExp = $pSum['expense'];

            $priorYearIncome += $pInc;
            $priorYearExpense += $pExp;

            $months[] = [
                'month_name' => \Carbon\Carbon::parse($start)->format('M'),
                'month_num' => $m,
                'income' => $inc,
                'expense' => $exp,
                'net' => round($net, 2),
                'savings_rate' => $inc > 0 ? round(($net / $inc) * 100, 1) : 0,
                'prior_income' => $pInc,
                'prior_expense' => $pExp,
                'income_growth' => $pInc > 0 ? round((($inc - $pInc) / $pInc) * 100, 1) : 0,
                'expense_growth' => $pExp > 0 ? round((($exp - $pExp) / $pExp) * 100, 1) : 0,
            ];
        }

        $yearNet = $yearIncome - $yearExpense;
        $priorYearNet = $priorYearIncome - $priorYearExpense;

        return [
            'year' => $year,
            'prior_year' => $priorYear,
            'months' => $months,
            'totals' => [
                'year_income' => round($yearIncome, 2),
                'prior_year_income' => round($priorYearIncome, 2),
                'income_growth_percent' => $priorYearIncome > 0 ? round((($yearIncome - $priorYearIncome) / $priorYearIncome) * 100, 1) : 0,
                'year_expense' => round($yearExpense, 2),
                'prior_year_expense' => round($priorYearExpense, 2),
                'expense_growth_percent' => $priorYearExpense > 0 ? round((($yearExpense - $priorYearExpense) / $priorYearExpense) * 100, 1) : 0,
                'year_net' => round($yearNet, 2),
                'prior_year_net' => round($priorYearNet, 2),
                'net_growth_percent' => $priorYearNet != 0 ? round((($yearNet - $priorYearNet) / abs($priorYearNet)) * 100, 1) : 0,
            ],
        ];
    }

    public function dailyBurnRate(User $user, string $from, string $to): array
    {
        $startDate = \Carbon\Carbon::parse($from);
        $endDate = \Carbon\Carbon::parse($to);
        $daysCount = max(1, $startDate->diffInDays($endDate) + 1);

        $totalExpense = (float) TransactionEntry::query()
            ->join('financial_transactions', 'financial_transactions.id', '=', 'transaction_entries.financial_transaction_id')
            ->join('ledger_accounts', 'ledger_accounts.id', '=', 'transaction_entries.ledger_account_id')
            ->where('financial_transactions.user_id', $user->id)
            ->where('ledger_accounts.user_id', $user->id)
            ->where('ledger_accounts.kind', LedgerAccountKind::Expense->value)
            ->where('financial_transactions.status', 'posted')
            ->whereBetween('financial_transactions.transaction_date', [$from, $to])
            ->selectRaw('COALESCE(SUM(transaction_entries.debit - transaction_entries.credit), 0) AS total')
            ->value('total');

        $dailyAverage = round($totalExpense / $daysCount, 2);

        $liquidBalance = (float) LedgerAccount::forUser($user->id)
            ->where('kind', LedgerAccountKind::Asset)
            ->whereIn('type', ['cash', 'bank', 'wallet'])
            ->get()
            ->sum(fn($acc) => $acc->balance());

        $estimatedRunwayDays = $dailyAverage > 0 ? round($liquidBalance / $dailyAverage) : 999;

        $peakDays = TransactionEntry::query()
            ->join('financial_transactions', 'financial_transactions.id', '=', 'transaction_entries.financial_transaction_id')
            ->join('ledger_accounts', 'ledger_accounts.id', '=', 'transaction_entries.ledger_account_id')
            ->where('financial_transactions.user_id', $user->id)
            ->where('ledger_accounts.kind', LedgerAccountKind::Expense->value)
            ->where('financial_transactions.status', 'posted')
            ->whereBetween('financial_transactions.transaction_date', [$from, $to])
            ->groupBy('financial_transactions.transaction_date')
            ->select([
                'financial_transactions.transaction_date as date',
                DB::raw('SUM(transaction_entries.debit - transaction_entries.credit) as daily_total'),
            ])
            ->orderByDesc('daily_total')
            ->limit(5)
            ->get()
            ->map(fn($r) => [
                'date' => $r->date,
                'total' => round((float) $r->daily_total, 2),
            ])
            ->all();

        $categories = $this->expenseByCategory($user, $from, $to);

        return [
            'from' => $from,
            'to' => $to,
            'days_count' => $daysCount,
            'total_expense' => round($totalExpense, 2),
            'daily_average_burn' => $dailyAverage,
            'liquid_assets' => round($liquidBalance, 2),
            'runway_days' => $estimatedRunwayDays,
            'peak_spending_days' => $peakDays,
            'category_breakdown' => array_map(function ($cat) use ($totalExpense) {
                $cat['percent_of_burn'] = $totalExpense > 0 ? round(($cat['total'] / $totalExpense) * 100, 1) : 0;
                return $cat;
            }, $categories),
        ];
    }
}
