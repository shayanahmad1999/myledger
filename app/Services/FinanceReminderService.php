<?php

namespace App\Services;

use App\Models\FinancialTransaction;
use App\Models\Loan;
use App\Models\SavingsGoal;
use App\Models\User;
use App\Notifications\FinanceReminderNotification;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class FinanceReminderService
{
    public function sendDueReminders(): int
    {
        $sent = 0;
        $timezone = config('finance.timezone', 'UTC');
        $now = Carbon::now($timezone);
        $today = $now->toDateString();
        $tomorrow = $now->copy()->addDay()->toDateString();
        $goalWindow = $now->copy()->addDays(7)->toDateString();
        $isDueReminderTime = $now->format('H:i') === config('finance.due_reminder_time', '08:00');

        User::with('settings')->chunkById(100, function ($users) use (
            &$sent,
            $now,
            $today,
            $tomorrow,
            $goalWindow,
            $isDueReminderTime,
        ) {
            foreach ($users as $user) {
                $todayNotifications = $user->notifications()
                    ->where('created_at', '>=', $now->copy()->startOfDay()->utc())
                    ->get();

                if ($isDueReminderTime) {
                    $sent += $this->sendLoanReminders($user, $todayNotifications, $today, $tomorrow);
                    $sent += $this->sendSavingsReminders($user, $todayNotifications, $today, $goalWindow);
                }

                $reminder = $user->settings?->daily_reminder_time;
                if (! $reminder || substr((string) $reminder, 0, 5) !== $now->format('H:i')) {
                    continue;
                }

                $hasActivity = FinancialTransaction::forUser($user->id)
                    ->whereDate('transaction_date', $today)
                    ->exists();

                if (! $hasActivity && ! $this->alreadySent($todayNotifications, 'daily_record', 0)) {
                    $user->notify(new FinanceReminderNotification(
                        'Record today’s money activity',
                        'No transactions have been recorded today. Add expenses, income, transfers, loans or savings while they are fresh.',
                        ['kind' => 'daily_record'],
                    ));
                    $sent++;
                }
            }
        });

        return $sent;
    }

    private function sendLoanReminders(User $user, Collection $todayNotifications, string $today, string $tomorrow): int
    {
        $sent = 0;
        $loans = Loan::forUser($user->id)
            ->with('person')
            ->where('status', 'active')
            ->whereNotNull('due_date')
            ->whereBetween('due_date', [$today, $tomorrow])
            ->get();

        foreach ($loans as $loan) {
            if ($this->alreadySent($todayNotifications, 'loan_due', $loan->id)) {
                continue;
            }

            $when = $loan->due_date->toDateString() === $today ? 'today' : 'tomorrow';
            $action = $loan->direction->value === 'given' ? 'receive' : 'pay';
            $user->notify(new FinanceReminderNotification(
                'Loan due '.$when,
                sprintf(
                    '%s: %s %s from/to %s.',
                    $loan->title ?: 'Loan',
                    ucfirst($action),
                    number_format((float) $loan->outstanding_principal, 2),
                    $loan->person->name,
                ),
                ['kind' => 'loan_due', 'loan_id' => $loan->id],
            ));
            $sent++;
        }

        return $sent;
    }

    private function sendSavingsReminders(User $user, Collection $todayNotifications, string $today, string $goalWindow): int
    {
        $sent = 0;
        $goals = SavingsGoal::forUser($user->id)
            ->where('is_completed', false)
            ->whereNotNull('target_date')
            ->whereBetween('target_date', [$today, $goalWindow])
            ->get();

        foreach ($goals as $goal) {
            if ($this->alreadySent($todayNotifications, 'savings_target', $goal->id)) {
                continue;
            }

            $remaining = max(0, (float) $goal->target_amount - (float) $goal->allocated_amount);
            $user->notify(new FinanceReminderNotification(
                'Savings goal reminder',
                sprintf(
                    '%s needs %s more by %s.',
                    $goal->title,
                    number_format($remaining, 2),
                    $goal->target_date->format('d M Y'),
                ),
                ['kind' => 'savings_target', 'savings_goal_id' => $goal->id],
            ));
            $sent++;
        }

        return $sent;
    }

    private function alreadySent(Collection $notifications, string $kind, int $id): bool
    {
        return $notifications->contains(function ($notification) use ($kind, $id) {
            $payload = $notification->data['payload'] ?? [];
            if (($payload['kind'] ?? null) !== $kind) {
                return false;
            }

            return match ($kind) {
                'loan_due' => (int) ($payload['loan_id'] ?? 0) === $id,
                'savings_target' => (int) ($payload['savings_goal_id'] ?? 0) === $id,
                default => true,
            };
        });
    }
}
