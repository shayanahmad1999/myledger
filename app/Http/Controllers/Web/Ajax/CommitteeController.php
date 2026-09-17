<?php

namespace App\Http\Controllers\Web\Ajax;

use App\Http\Controllers\Controller;
use App\Models\Committee;
use App\Models\CommitteeMember;
use App\Models\CommitteeRound;
use App\Services\CommitteeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommitteeController extends Controller
{
    public function index(Request $request, CommitteeService $service): JsonResponse
    {
        $committees = Committee::forUser($request->user()->id)
            ->with(['currency', 'myPerson', 'members', 'rounds'])
            ->withCount(['members', 'rounds'])
            ->latest()
            ->get()
            ->map(function ($c) {
                $completedRounds = $c->rounds->where('payout_status', 'paid')->count();
                $c->completed_rounds_count = $completedRounds;
                $c->progress_percent = $c->total_members > 0 ? round(($completedRounds / $c->total_members) * 100) : 0;
                return $c;
            });

        return response()->json([
            'committees' => $committees,
            'stats' => $service->getSummaryStats($request->user()),
        ]);
    }

    public function store(Request $request, CommitteeService $service): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:191',
            'currency_id' => 'nullable|exists:currencies,id',
            'contribution_amount' => 'required|numeric|min:1',
            'frequency' => 'required|in:monthly,weekly,biweekly',
            'start_date' => 'required|date',
            'my_role' => 'required|in:manager,member',
            'my_person_id' => 'nullable|exists:people,id',
            'notes' => 'nullable|string|max:1000',
            'members' => 'required|array|min:2',
            'members.*.name' => 'required|string|max:191',
            'members.*.person_id' => 'nullable|exists:people,id',
            'members.*.payout_round_no' => 'nullable|integer|min:1',
            'members.*.notes' => 'nullable|string|max:500',
        ]);

        $committee = $service->createCommittee($request->user(), $validated);

        return response()->json($committee, 201);
    }

    public function show(Request $request, Committee $committee): JsonResponse
    {
        $this->assertOwned($request, $committee);

        $committee->load([
            'currency',
            'myPerson',
            'members.person',
            'rounds.winnerMember',
            'rounds.payoutAccount',
            'rounds.payments.member',
            'rounds.payments.account',
        ]);

        $completedRounds = $committee->rounds->where('payout_status', 'paid')->count();
        $committee->completed_rounds_count = $completedRounds;
        $committee->progress_percent = $committee->total_members > 0 ? round(($completedRounds / $committee->total_members) * 100) : 0;

        return response()->json($committee);
    }

    public function recordPayment(Request $request, CommitteeRound $round, CommitteeMember $member, CommitteeService $service): JsonResponse
    {
        $this->assertOwned($request, $round->committee);

        $validated = $request->validate([
            'status' => 'required|in:pending,paid',
            'paid_at' => 'nullable|date',
            'account_id' => 'nullable|exists:ledger_accounts,id',
            'notes' => 'nullable|string|max:500',
        ]);

        $payment = $service->recordPayment($request->user(), $round, $member, $validated);

        return response()->json($payment);
    }

    public function disbursePayout(Request $request, CommitteeRound $round, CommitteeService $service): JsonResponse
    {
        $this->assertOwned($request, $round->committee);

        $validated = $request->validate([
            'winner_member_id' => 'required|exists:committee_members,id',
            'payout_date' => 'required|date',
            'account_id' => 'nullable|exists:ledger_accounts,id',
            'notes' => 'nullable|string|max:500',
        ]);

        $updatedRound = $service->disbursePayout($request->user(), $round, $validated);

        return response()->json($updatedRound);
    }

    public function destroy(Request $request, Committee $committee): JsonResponse
    {
        $this->assertOwned($request, $committee);
        $committee->delete();

        return response()->json(['message' => 'Committee deleted successfully']);
    }

    private function assertOwned(Request $request, Committee $committee): void
    {
        abort_unless($committee->user_id === $request->user()->id, 403);
    }
}
