<?php

namespace App\Http\Controllers\Web\Ajax;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ReportController extends Controller
{
    public function summary(Request $request, ReportService $reports): JsonResponse
    {
        [$from, $to] = $this->range($request);

        return response()->json($reports->summary($request->user(), $from, $to));
    }

    public function categories(Request $request, ReportService $reports): JsonResponse
    {
        [$from, $to] = $this->range($request);

        return response()->json($reports->expenseByCategory($request->user(), $from, $to));
    }

    public function trend(Request $request, ReportService $reports): JsonResponse
    {
        return response()->json(
            $reports->monthlyTrend($request->user(), min(36, max(1, (int) $request->input('months', 12))))
        );
    }

    public function netWorth(Request $request, ReportService $reports): JsonResponse
    {
        $data = $request->validate(['as_of' => 'nullable|date']);

        return response()->json($reports->netWorth($request->user(), $data['as_of'] ?? null));
    }

    public function cashFlow(Request $request, ReportService $reports): JsonResponse
    {
        [$from, $to] = $this->range($request);

        return response()->json($reports->cashFlow($request->user(), $from, $to));
    }

    public function loans(Request $request, ReportService $reports): JsonResponse
    {
        return response()->json($reports->loans($request->user()));
    }

    public function committees(Request $request, ReportService $reports): JsonResponse
    {
        return response()->json($reports->committees($request->user()));
    }

    public function trialBalance(Request $request, ReportService $reports): JsonResponse
    {
        $asOf = $request->query('as_of');

        return response()->json($reports->trialBalance($request->user(), $asOf));
    }

    public function generalLedger(Request $request, ReportService $reports): JsonResponse
    {
        $accountId = (int) $request->validate(['account_id' => 'required|integer|exists:ledger_accounts,id'])['account_id'];
        [$from, $to] = $this->range($request);

        return response()->json($reports->generalLedger($request->user(), $accountId, $from, $to));
    }

    public function smartInsights(Request $request, ReportService $reports): JsonResponse
    {
        return response()->json($reports->smartInsights($request->user()));
    }

    public function balanceSheet(Request $request, ReportService $reports): JsonResponse
    {
        $asOf = $request->query('as_of');

        return response()->json($reports->balanceSheet($request->user(), $asOf));
    }

    public function profitAndLoss(Request $request, ReportService $reports): JsonResponse
    {
        [$from, $to] = $this->range($request);

        return response()->json($reports->profitAndLoss($request->user(), $from, $to));
    }

    public function partyLedger(Request $request, ReportService $reports): JsonResponse
    {
        $personId = (int) $request->validate(['person_id' => 'required|integer|exists:people,id'])['person_id'];
        [$from, $to] = $this->range($request);

        return response()->json($reports->partyLedger($request->user(), $personId, $from, $to));
    }

    public function comparison(Request $request, ReportService $reports): JsonResponse
    {
        $year = (int) $request->input('year', now()->year);

        return response()->json($reports->momYoyComparison($request->user(), $year));
    }

    public function burnRate(Request $request, ReportService $reports): JsonResponse
    {
        [$from, $to] = $this->range($request);

        return response()->json($reports->dailyBurnRate($request->user(), $from, $to));
    }

    private function range(Request $request): array
    {
        $data = $request->validate([
            'from' => 'nullable|date',
            'to' => 'nullable|date',
        ]);

        // Default "from" to earliest transaction date for the user, or start of year if none
        if (!empty($data['from'])) {
            $from = Carbon::parse($data['from'])->toDateString();
        } else {
            $earliest = \App\Models\Transaction::where('user_id', $request->user()->id)
                ->orderBy('date')
                ->value('date');
            $from = $earliest ? Carbon::parse($earliest)->toDateString() : now()->startOfYear()->toDateString();
        }

        // Default "to" to today if not provided
        $to = !empty($data['to']) ? Carbon::parse($data['to'])->toDateString() : now()->toDateString();

        if ($to < $from) {
            throw ValidationException::withMessages([
                'to' => ['The end date must be on or after the start date.'],
            ]);
        }

        return [$from, $to];
    }
}
