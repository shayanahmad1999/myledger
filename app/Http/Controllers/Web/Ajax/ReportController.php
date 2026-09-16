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

    private function range(Request $request): array
    {
        $data = $request->validate([
            'from' => 'nullable|date',
            'to' => 'nullable|date',
        ]);

        $from = isset($data['from'])
            ? Carbon::parse($data['from'])->toDateString()
            : now()->startOfMonth()->toDateString();
        $to = isset($data['to'])
            ? Carbon::parse($data['to'])->toDateString()
            : now()->endOfMonth()->toDateString();

        if ($to < $from) {
            throw ValidationException::withMessages([
                'to' => ['The end date must be on or after the start date.'],
            ]);
        }

        return [$from, $to];
    }
}
