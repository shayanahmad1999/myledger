<?php

namespace App\Services;

use App\Enums\TransactionType;
use App\Models\Committee;
use App\Models\CommitteeMember;
use App\Models\CommitteePayment;
use App\Models\CommitteeRound;
use App\Models\Currency;
use App\Models\LedgerAccount;
use App\Models\Person;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CommitteeService
{
    public function __construct(
        private readonly LedgerService $ledger,
        private readonly FinanceSetupService $setup,
    ) {}

    public function createCommittee(User $user, array $data): Committee
    {
        return DB::transaction(function () use ($user, $data) {
            $currencyId = $data['currency_id'] ?? Currency::where('code', config('finance.base_currency'))->value('id');
            $membersData = $data['members'] ?? [];
            $totalMembers = count($membersData);
            if ($totalMembers < 2) {
                throw ValidationException::withMessages(['members' => 'A committee must have at least 2 members.']);
            }

            $contributionAmount = (float) $data['contribution_amount'];
            $totalPoolAmount = $contributionAmount * $totalMembers;
            $startDate = Carbon::parse($data['start_date']);

            $committee = Committee::create([
                'user_id' => $user->id,
                'currency_id' => $currencyId,
                'name' => $data['name'],
                'contribution_amount' => $contributionAmount,
                'total_members' => $totalMembers,
                'total_pool_amount' => $totalPoolAmount,
                'frequency' => $data['frequency'] ?? 'monthly',
                'start_date' => $startDate->toDateString(),
                'status' => 'active',
                'my_role' => $data['my_role'] ?? 'manager',
                'my_person_id' => $data['my_person_id'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            // Create Member Slots
            $createdMembers = [];
            foreach ($membersData as $index => $m) {
                $slotNumber = $index + 1;
                $personId = !empty($m['person_id']) ? (int) $m['person_id'] : null;
                $memberName = $m['name'] ?? ('Member #' . $slotNumber);

                if ($personId) {
                    $person = Person::where('user_id', $user->id)->find($personId);
                    if ($person) {
                        $memberName = $person->name;
                    }
                }

                $payoutRoundNo = !empty($m['payout_round_no']) ? (int) $m['payout_round_no'] : $slotNumber;

                $member = CommitteeMember::create([
                    'committee_id' => $committee->id,
                    'person_id' => $personId,
                    'name' => $memberName,
                    'slot_number' => $slotNumber,
                    'payout_round_no' => $payoutRoundNo,
                    'notes' => $m['notes'] ?? null,
                ]);
                $createdMembers[$payoutRoundNo] = $member;
            }

            // Create Rounds Schedule
            for ($r = 1; $r <= $totalMembers; $r++) {
                $dueDate = clone $startDate;
                if ($committee->frequency === 'weekly') {
                    $dueDate->addWeeks($r - 1);
                } elseif ($committee->frequency === 'biweekly') {
                    $dueDate->addWeeks(($r - 1) * 2);
                } else {  // monthly
                    $dueDate->addMonths($r - 1);
                }

                $winnerMember = $createdMembers[$r] ?? null;

                $round = CommitteeRound::create([
                    'committee_id' => $committee->id,
                    'round_number' => $r,
                    'due_date' => $dueDate->toDateString(),
                    'winner_member_id' => $winnerMember?->id,
                    'total_expected' => $totalPoolAmount,
                    'total_collected' => 0,
                    'payout_amount' => $totalPoolAmount,
                    'payout_status' => 'pending',
                ]);

                // Create pending payment records for each member for this round
                foreach ($committee->members as $member) {
                    CommitteePayment::create([
                        'committee_round_id' => $round->id,
                        'committee_member_id' => $member->id,
                        'amount' => $contributionAmount,
                        'status' => 'pending',
                    ]);
                }
            }

            return $committee->fresh(['members.person', 'rounds.winnerMember', 'currency']);
        });
    }

    public function recordPayment(User $user, CommitteeRound $round, CommitteeMember $member, array $data): CommitteePayment
    {
        abort_unless($round->committee->user_id === $user->id, 403);
        abort_unless($member->committee_id === $round->committee_id, 422, 'Member does not belong to this committee.');

        return DB::transaction(function () use ($user, $round, $member, $data) {
            $payment = CommitteePayment::where('committee_round_id', $round->id)
                ->where('committee_member_id', $member->id)
                ->firstOrFail();

            $status = $data['status'] ?? 'paid';
            $paidAt = $status === 'paid' ? ($data['paid_at'] ?? now()->toDateString()) : null;
            $accountId = !empty($data['account_id']) ? (int) $data['account_id'] : null;

            $txId = $payment->financial_transaction_id;

            if ($status === 'paid' && $accountId && !$txId) {
                $account = LedgerAccount::findOrFail($accountId);
                abort_unless($account->user_id === $user->id, 403);

                // Create financial transaction
                // If member is user themselves -> Expense or Contribution
                // If manager receiving from external person -> Money into account
                $txType = TransactionType::CommitteeContribution;
                $personId = $member->person_id;

                $tx = $this->ledger->post(
                    $user,
                    $txType,
                    $paidAt,
                    (float) $payment->amount,
                    [
                        ['account' => $account, 'debit' => $payment->amount],
                        ['account' => $this->setup->systemAccount($user, 'opening_balance_equity'), 'credit' => $payment->amount],
                    ],
                    [
                        'source_account_id' => null,
                        'destination_account_id' => $account->id,
                        'person_id' => $personId,
                        'description' => "Committee contribution: {$round->committee->name} (Round {$round->round_number} - {$member->name})",
                    ]
                );

                $txId = $tx->id;
            }

            $payment->update([
                'status' => $status,
                'paid_at' => $paidAt,
                'account_id' => $accountId,
                'financial_transaction_id' => $txId,
                'notes' => $data['notes'] ?? $payment->notes,
            ]);

            // Recalculate round total collected
            $totalCollected = CommitteePayment::where('committee_round_id', $round->id)
                ->where('status', 'paid')
                ->sum('amount');

            $round->update(['total_collected' => $totalCollected]);

            return $payment->fresh(['member', 'account']);
        });
    }

    public function disbursePayout(User $user, CommitteeRound $round, array $data): CommitteeRound
    {
        abort_unless($round->committee->user_id === $user->id, 403);

        return DB::transaction(function () use ($user, $round, $data) {
            $payoutDate = $data['payout_date'] ?? now()->toDateString();
            $accountId = !empty($data['account_id']) ? (int) $data['account_id'] : null;
            $winnerMemberId = !empty($data['winner_member_id']) ? (int) $data['winner_member_id'] : $round->winner_member_id;

            $txId = $round->payout_transaction_id;

            if ($accountId && !$txId) {
                $account = LedgerAccount::findOrFail($accountId);
                abort_unless($account->user_id === $user->id, 403);

                $winner = CommitteeMember::find($winnerMemberId);
                $winnerName = $winner ? $winner->name : 'Member';
                $committee = $round->committee;

                // Role-based payout calculation:
                // If Manager/Organizer: Manager manages the whole pool and disburses the entire pot amount.
                // If Member: Member only pays their own contribution amount towards the payout round.
                $actualDeductionAmount = ($committee->my_role === 'manager')
                    ? (float) $round->payout_amount
                    : (float) $committee->contribution_amount;

                $tx = $this->ledger->post(
                    $user,
                    TransactionType::CommitteePayout,
                    $payoutDate,
                    $actualDeductionAmount,
                    [
                        ['account' => $this->setup->systemAccount($user, 'opening_balance_equity'), 'debit' => $actualDeductionAmount],
                        ['account' => $account, 'credit' => $actualDeductionAmount],
                    ],
                    [
                        'source_account_id' => $account->id,
                        'person_id' => $winner?->person_id,
                        'description' => "Committee payout disbursed: {$committee->name} (Round {$round->round_number} - Winner: {$winnerName})",
                    ]
                );

                $txId = $tx->id;
            }

            $round->update([
                'winner_member_id' => $winnerMemberId,
                'payout_status' => 'paid',
                'payout_date' => $payoutDate,
                'payout_account_id' => $accountId,
                'payout_transaction_id' => $txId,
                'notes' => $data['notes'] ?? $round->notes,
            ]);

            // Check if all rounds are completed
            $totalRounds = $round->committee->total_members;
            $paidRounds = CommitteeRound::where('committee_id', $round->committee_id)->where('payout_status', 'paid')->count();
            if ($paidRounds >= $totalRounds) {
                $round->committee->update(['status' => 'completed']);
            }

            return $round->fresh(['winnerMember', 'payoutAccount', 'payments.member']);
        });
    }

    public function getSummaryStats(User $user): array
    {
        $committees = Committee::forUser($user->id)->with('currency')->get();
        $activeCount = $committees->where('status', 'active')->count();

        $totalMonthlyContribution = $committees->where('status', 'active')->sum('contribution_amount');
        $totalPoolValue = $committees->sum('total_pool_amount');

        $activeIds = $committees->where('status', 'active')->pluck('id');
        $pendingPaymentsCount = CommitteePayment::whereHas('round', fn($q) => $q->whereIn('committee_id', $activeIds))
            ->where('status', 'pending')
            ->count();

        return [
            'total_committees' => $committees->count(),
            'active_committees' => $activeCount,
            'total_monthly_contribution' => $totalMonthlyContribution,
            'currency' => $committees->first()->currency ?? '',
            'total_pool_value' => $totalPoolValue,
            'pending_payments_count' => $pendingPaymentsCount,
        ];
    }
}
