<?php

namespace App\Services;

use App\Enums\LedgerAccountType;
use App\Models\Committee;
use App\Models\CommitteePayment;
use App\Models\FinancialTransaction;
use App\Models\LedgerAccount;
use App\Models\SavingsGoal;
use App\Models\User;

class DashboardService
{
    public function __construct(
        private readonly ReportService $reports
    ) {}

    public function build(User $user): array
    {
        $monthStart = now()->startOfMonth()->toDateString();
        $monthEnd = now()->endOfMonth()->toDateString();

        $accounts = LedgerAccount::forUser($user->id)->with('currency')->moneyAccounts()->where('is_archived', false)->get()->map(fn($a) => [
            'id' => $a->id,
            'name' => $a->name,
            'type' => $a->type->value,
            'balance' => round($a->balance(), 2),
            'institution' => $a->institution,
            'last_four' => $a->last_four,
            'icon' => $a->icon,
            'color' => $a->color,
            'currency' => [
                'symbol' => $a->currency->symbol,
            ],
        ]);

        $savingsTotal = (float) $accounts->filter(fn($a) => $a['type'] === LedgerAccountType::Savings->value)->sum('balance');
        $liquidCash = (float) $accounts->filter(fn($a) => in_array($a['type'], [LedgerAccountType::Cash->value, LedgerAccountType::Bank->value, LedgerAccountType::Savings->value], true))->sum('balance');

        $monthSummary = $this->reports->summary($user, $monthStart, $monthEnd);
        $avgMonthlyExpense = max(1, (float) $monthSummary['expense']);
        $emergencyMonths = round($liquidCash / $avgMonthlyExpense, 1);

        // Financial Health Score Calculation (0-100)
        $savingsRate = (float) $monthSummary['savings_rate'];
        $healthScore = min(100, max(0, round(($emergencyMonths >= 3 ? 40 : ($emergencyMonths / 3) * 40) + ($savingsRate >= 20 ? 40 : ($savingsRate / 20) * 40) + ($monthSummary['income'] > 0 ? 20 : 0))));

        // Upcoming Committee Turns
        $activeCommittees = Committee::forUser($user->id)
            ->with(['rounds.winnerMember', 'rounds.payments', 'currency'])
            ->where('status', 'active')
            ->get()
            ->map(function ($c) {
                $nextRound = $c->rounds->where('payout_status', 'pending')->first();
                $pendingPaymentsCount = $nextRound ? $nextRound->payments->where('status', 'pending')->count() : 0;

                return [
                    'id' => $c->id,
                    'name' => $c->name,
                    'contribution_amount' => (float) $c->contribution_amount,
                    'total_pool_amount' => (float) $c->total_pool_amount,
                    'frequency' => $c->frequency,
                    'next_round_number' => $nextRound?->round_number,
                    'next_due_date' => $nextRound?->due_date?->toDateString(),
                    'next_winner' => $nextRound?->winnerMember?->name ?? 'Unassigned',
                    'pending_payments_count' => $pendingPaymentsCount,
                    'progress_percent' => $c->total_members > 0 ? round(($c->rounds->where('payout_status', 'paid')->count() / $c->total_members) * 100) : 0,
                    'currency' => [
                        'symbol' => $c->currency->symbol,
                    ],
                ];
            });

        return [
            'net_worth' => $this->reports->netWorth($user),
            'month' => $monthSummary,
            'accounts' => $accounts->values(),
            'savings_total' => $savingsTotal,
            'loans' => $this->reports->loans($user),
            'budgets' => $this->reports->budgets($user),
            'savings_goals' => SavingsGoal::forUser($user->id)->with(['account', 'currency'])->where('is_completed', false)->orderBy('target_date')->limit(5)->get()->append('progress_percent'),
            'recent_transactions' => FinancialTransaction::forUser($user->id)->with(['category', 'person', 'sourceAccount', 'destinationAccount', 'currency'])->latest('transaction_date')->latest('id')->limit(10)->get(),
            'financial_health' => [
                'score' => $healthScore,
                'emergency_months' => $emergencyMonths,
                'liquid_cash' => $liquidCash,
            ],
            'upcoming_committees' => $activeCommittees,
        ];
    }
}
