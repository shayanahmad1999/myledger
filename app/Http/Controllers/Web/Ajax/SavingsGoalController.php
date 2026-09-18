<?php

namespace App\Http\Controllers\Web\Ajax;

use App\Http\Controllers\Controller;
use App\Models\LedgerAccount;
use App\Models\SavingsGoal;
use App\Services\LedgerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SavingsGoalController extends Controller
{
    public function index(Request $r): JsonResponse
    {
        return response()->json(SavingsGoal::forUser($r->user()->id)->with(['account', 'currency'])->orderBy('is_completed')->orderBy('target_date')->get()->append('progress_percent'));
    }

    public function store(Request $r): JsonResponse
    {
        $d = $r->validate(['account_id' => 'required|exists:ledger_accounts,id', 'title' => 'required|string|max:120', 'target_amount' => 'required|numeric|min:0.01', 'target_date' => 'nullable|date', 'icon' => 'nullable|string|max:50', 'color' => 'nullable|string|max:20']);
        $a = LedgerAccount::findOrFail($d['account_id']);
        abort_unless($a->user_id === $r->user()->id, 403);
        $g = SavingsGoal::create(array_merge($d, ['user_id' => $r->user()->id, 'currency_id' => $a->currency_id]));
        return response()->json($g->append('progress_percent'), 201);
    }

    public function contribute(Request $r, SavingsGoal $goal, LedgerService $ledger): JsonResponse
    {
        $this->owned($r, $goal);
        $d = $r->validate(['source_account_id' => 'required|exists:ledger_accounts,id|different:destination_account_id', 'amount' => 'required|numeric|min:0.01', 'transaction_date' => 'required|date', 'description' => 'nullable|string|max:255']);
        return response()->json($ledger->contributeToGoal($r->user(), $goal, $d), 201);
    }

    public function withdraw(Request $r, SavingsGoal $goal, LedgerService $ledger): JsonResponse
    {
        $this->owned($r, $goal);
        $d = $r->validate(['destination_account_id' => 'required|exists:ledger_accounts,id', 'amount' => 'required|numeric|min:0.01', 'transaction_date' => 'required|date', 'description' => 'nullable|string|max:255']);
        return response()->json($ledger->withdrawFromGoal($r->user(), $goal, $d), 201);
    }

    public function update(Request $r, SavingsGoal $goal): JsonResponse
    {
        $this->owned($r, $goal);
        $goal->update($r->validate(['title' => 'sometimes|required|string|max:120', 'target_amount' => 'sometimes|required|numeric|min:0.01', 'target_date' => 'nullable|date', 'icon' => 'nullable|string|max:50', 'color' => 'nullable|string|max:20']));
        return response()->json($goal->fresh()->append('progress_percent'));
    }

    private function owned(Request $r, SavingsGoal $m): void
    {
        abort_unless($m->user_id === $r->user()->id, 403);
    }
}
