<?php

namespace App\Http\Controllers\Web\Ajax;

use App\Http\Controllers\Controller;
use App\Models\Budget;
use App\Models\Category;
use App\Services\ReportService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BudgetController extends Controller
{
    public function index(Request $request, ReportService $reports): JsonResponse
    {
        return response()->json($reports->budgets($request->user(), $request->date));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
            'amount' => 'required|numeric|min:0.01',
            'alert_percent' => 'nullable|integer|min:1|max:100',
        ]);

        $category = Category::findOrFail($data['category_id']);
        abort_unless(
            (int) $category->user_id === (int) $request->user()->id && $category->type === 'expense',
            422,
            'Budget category must be your expense category.',
        );

        $budget = Budget::create(array_merge($data, [
            'user_id' => $request->user()->id,
            'currency_id' => $category->ledgerAccount->currency_id,
            'alert_percent' => $data['alert_percent'] ?? config('finance.default_budget_alert_percent', 80),
        ]));

        return response()->json($budget, 201);
    }

    public function update(Request $request, Budget $budget): JsonResponse
    {
        $this->owned($request, $budget);
        $data = $request->validate([
            'amount' => 'sometimes|required|numeric|min:0.01',
            'alert_percent' => 'sometimes|integer|min:1|max:100',
            'period_end' => 'sometimes|date',
            'is_active' => 'sometimes|boolean',
        ]);

        if (isset($data['period_end']) && Carbon::parse($data['period_end'])->lt($budget->period_start)) {
            abort(422, 'Budget end date cannot be before the budget start date.');
        }

        if (array_key_exists('amount', $data) || array_key_exists('alert_percent', $data)) {
            $data['last_alert_level'] = 0;
        }

        $budget->update($data);

        return response()->json($budget->fresh());
    }

    public function destroy(Request $request, Budget $budget): JsonResponse
    {
        $this->owned($request, $budget);
        $budget->update(['is_active' => false]);

        return response()->json(['message' => 'Budget disabled.']);
    }

    private function owned(Request $request, Budget $budget): void
    {
        abort_unless((int) $budget->user_id === (int) $request->user()->id, 403);
    }
}
