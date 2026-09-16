<?php

namespace App\Services;

use App\Models\Budget;
use App\Models\TransactionEntry;
use App\Models\User;
use App\Notifications\FinanceReminderNotification;

class BudgetAlertService
{
    public function check(User $user, int $categoryId, string $date): void
    {
        $budgets = Budget::forUser($user->id)
            ->with('category')
            ->where('category_id', $categoryId)
            ->where('is_active', true)
            ->whereDate('period_start', '<=', $date)
            ->whereDate('period_end', '>=', $date)
            ->get();

        foreach ($budgets as $budget) {
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
            if ($amount <= 0) {
                continue;
            }

            $percent = ($spent / $amount) * 100;
            $level = $percent >= 100 ? 2 : ($percent >= $budget->alert_percent ? 1 : 0);
            $previous = (int) $budget->last_alert_level;

            // A reversal or correction can move spending back below a threshold.
            // Reset silently so a later genuine crossing can alert again.
            if ($level < $previous) {
                $budget->update(['last_alert_level' => $level]);
                continue;
            }

            if ($level === 0 || $level <= $previous) {
                continue;
            }

            $category = $budget->category->name ?? 'category';
            $message = $level === 2
                ? sprintf('You have exceeded your %s budget (%.0f%% used).', $category, $percent)
                : sprintf('You have used %.0f%% of your %s budget.', $percent, $category);

            $user->notify(new FinanceReminderNotification(
                $level === 2 ? 'Budget exceeded' : 'Budget alert',
                $message,
                ['budget_id' => $budget->id, 'percent' => round($percent, 2)],
            ));

            $budget->update(['last_alert_level' => $level]);
        }
    }
}
