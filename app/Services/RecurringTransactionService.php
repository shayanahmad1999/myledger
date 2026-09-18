<?php

namespace App\Services;

use App\Enums\RecurringMode;
use App\Enums\TransactionType;
use App\Models\RecurringTransaction;
use App\Models\User;
use App\Notifications\FinanceReminderNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RecurringTransactionService
{
    public function __construct(
        private readonly LedgerService $ledger
    ) {}

    public function processDue(): int
    {
        $count = 0;
        RecurringTransaction::where('is_active', true)->where('next_run_at', '<=', now())->orderBy('id')->chunkById(100, function ($items) use (&$count) {
            foreach ($items as $item) {
                $this->process($item);
                $count++;
            }
        });
        return $count;
    }

    public function process(RecurringTransaction $item): void
    {
        DB::transaction(function () use ($item) {
            $item->refresh();
            if (!$item->is_active || $item->next_run_at->isFuture())
                return;
            $user = User::findOrFail($item->user_id);
            if ($item->mode === RecurringMode::Remind) {
                $user->notify(new FinanceReminderNotification($item->title, "{$item->title} is due today.", ['recurring_transaction_id' => $item->id]));
            } else {
                $data = ['amount' => (float) $item->amount, 'transaction_date' => now()->toDateString(), 'description' => $item->title, 'recurring_transaction_id' => $item->id];
                if ($item->type === TransactionType::Expense) {
                    $data['source_account_id'] = $item->source_account_id;
                    $data['category_id'] = $item->category_id;
                    $this->ledger->expense($user, $data);
                } elseif ($item->type === TransactionType::Income) {
                    $data['destination_account_id'] = $item->destination_account_id;
                    $data['category_id'] = $item->category_id;
                    $this->ledger->income($user, $data);
                } elseif ($item->type === TransactionType::Transfer) {
                    $data['source_account_id'] = $item->source_account_id;
                    $data['destination_account_id'] = $item->destination_account_id;
                    $this->ledger->transfer($user, $data);
                }
            }
            $item->update(['next_run_at' => $this->nextRun($item)]);
        });
    }

    private function nextRun(RecurringTransaction $item): Carbon
    {
        $date = $item->next_run_at->copy();
        $n = max(1, $item->interval);
        return match ($item->frequency) {
            'daily' => $date->addDays($n),
            'weekly' => $date->addWeeks($n),
            'monthly' => $date->addMonthsNoOverflow($n),
            'yearly' => $date->addYearsNoOverflow($n),
            default => $date->addMonth()
        };
    }
}
