<?php

namespace App\Services;

use App\Models\CommitteeRound;
use App\Models\FinancialTransaction;
use App\Models\Loan;
use App\Models\RecurringTransaction;
use App\Models\User;
use Carbon\Carbon;

class CalendarService
{
    public function getEvents(User $user, ?string $start = null, ?string $end = null): array
    {
        $startDate = $start ? Carbon::parse($start)->startOfDay() : now()->startOfMonth()->startOfDay();
        $endDate = $end ? Carbon::parse($end)->endOfDay() : now()->endOfMonth()->endOfDay();

        $events = [];

        // 1. Transactions
        $transactions = FinancialTransaction::forUser($user->id)
            ->with(['category', 'sourceAccount', 'destinationAccount', 'person', 'currency'])
            ->whereBetween('transaction_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->whereIn('status', ['posted'])
            ->get();

        foreach ($transactions as $tx) {
            $type = $tx->type->value;
            $badgeColor = in_array($type, ['income', 'loan_repayment_received', 'savings_withdrawal'], true) ? 'success' : (in_array($type, ['expense', 'loan_given', 'savings_contribution'], true) ? 'danger' : 'info');

            $events[] = [
                'id' => 'tx-' . $tx->id,
                'title' => $tx->description ?: ($tx->category?->name ?? ucfirst($type)),
                'date' => $tx->transaction_date->toDateString(),
                'amount' => (float) $tx->amount,
                'type' => 'transaction',
                'sub_type' => $type,
                'color' => $badgeColor,
                'details' => [
                    'reference' => $tx->reference_no,
                    'category' => $tx->category?->name,
                    'currency' => $tx->currency?->symbol,
                    'source' => $tx->sourceAccount?->name,
                    'destination' => $tx->destinationAccount?->name,
                    'person' => $tx->person?->name,
                ],
            ];
        }

        // 2. Loans Due
        $loans = Loan::forUser($user->id)
            ->with('person')
            ->where('status', 'active')
            ->whereNotNull('due_date')
            ->whereBetween('due_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->get();

        foreach ($loans as $loan) {
            $events[] = [
                'id' => 'loan-' . $loan->id,
                'title' => 'Loan Due: ' . ($loan->title ?: $loan->person->name),
                'date' => $loan->due_date->toDateString(),
                'amount' => (float) $loan->outstanding_principal,
                'type' => 'loan',
                'sub_type' => $loan->direction->value,
                'color' => 'warning',
                'details' => [
                    'person' => $loan->person->name,
                    'direction' => ucfirst($loan->direction->value),
                    'principal' => (float) $loan->principal,
                    'outstanding' => (float) $loan->outstanding_principal,
                ],
            ];
        }

        // 3. Committee Rounds Due
        $rounds = CommitteeRound::whereHas('committee', fn ($q) => $q->where('user_id', $user->id)->where('status', 'active'))
            ->with(['committee', 'winnerMember'])
            ->whereBetween('due_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->get();

        foreach ($rounds as $round) {
            $events[] = [
                'id' => 'round-' . $round->id,
                'title' => "Committee Round #{$round->round_number}: {$round->committee->name}",
                'date' => $round->due_date->toDateString(),
                'amount' => (float) $round->payout_amount,
                'type' => 'committee',
                'sub_type' => $round->payout_status,
                'color' => $round->payout_status === 'paid' ? 'success' : 'primary',
                'details' => [
                    'committee' => $round->committee->name,
                    'winner' => $round->winnerMember?->name ?? 'Unassigned',
                    'contribution' => (float) $round->committee->contribution_amount,
                    'payout_status' => ucfirst($round->payout_status),
                ],
            ];
        }

        // 4. Recurring Transactions Next Runs
        $recurrings = RecurringTransaction::forUser($user->id)
            ->where('is_active', true)
            ->whereBetween('next_run_at', [$startDate->toDateString(), $endDate->toDateString()])
            ->get();

        foreach ($recurrings as $rec) {
            $events[] = [
                'id' => 'recurring-' . $rec->id,
                'title' => 'Scheduled: ' . ($rec->description ?: $rec->type->value),
                'date' => $rec->next_run_at->toDateString(),
                'amount' => (float) $rec->amount,
                'type' => 'recurring',
                'sub_type' => $rec->frequency,
                'color' => 'secondary',
                'details' => [
                    'frequency' => ucfirst($rec->frequency),
                    'description' => $rec->description,
                ],
            ];
        }

        usort($events, fn ($a, $b) => strcmp($a['date'], $b['date']));

        return $events;
    }
}
