<?php

namespace App\Http\Controllers\Web\Ajax;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\ExchangeRateHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CurrencyController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Currency::where('is_active', true)->orderBy('code')->get());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|size:3|unique:currencies,code',
            'name' => 'required|string|max:100',
            'symbol' => 'required|string|max:10',
            'exchange_rate' => 'required|numeric|min:0.000001',
        ]);

        $currency = Currency::create(array_merge($validated, ['is_active' => true]));

        return response()->json($currency, 201);
    }

    public function update(Request $request, Currency $currency): JsonResponse
    {
        $validated = $request->validate([
            'exchange_rate' => 'required|numeric|min:0.000001',
            'is_active' => 'nullable|boolean',
        ]);

        $currency->update($validated);

        // Also update/create today's historical rate
        $baseCurrencyId = $request->user()->settings?->base_currency_id;
        if ($baseCurrencyId && (int) $currency->id !== (int) $baseCurrencyId) {
            ExchangeRateHistory::setRate(
                $currency->id,
                $baseCurrencyId,
                now()->toDateString(),
                (float) $validated['exchange_rate']
            );
        }

        return response()->json($currency);
    }

    public function history(Request $request, Currency $currency): JsonResponse
    {
        $baseCurrencyId = $request->user()->settings?->base_currency_id;
        abort_unless($baseCurrencyId, 422, 'Base currency not configured.');

        $history = ExchangeRateHistory::where('currency_id', $currency->id)
            ->where('base_currency_id', $baseCurrencyId)
            ->orderByDesc('rate_date')
            ->get();

        return response()->json($history);
    }

    public function storeHistory(Request $request, Currency $currency): JsonResponse
    {
        $baseCurrencyId = $request->user()->settings?->base_currency_id;
        abort_unless($baseCurrencyId, 422, 'Base currency not configured.');

        $validated = $request->validate([
            'rate_date' => 'required|date',
            'rate' => 'required|numeric|min:0.000001',
        ]);

        $history = ExchangeRateHistory::setRate(
            $currency->id,
            $baseCurrencyId,
            $validated['rate_date'],
            (float) $validated['rate']
        );

        return response()->json($history, 201);
    }

    public function destroyHistory(Request $request, Currency $currency, ExchangeRateHistory $history): JsonResponse
    {
        $baseCurrencyId = $request->user()->settings?->base_currency_id;
        abort_unless($baseCurrencyId, 422, 'Base currency not configured.');
        abort_unless($history->currency_id === $currency->id && $history->base_currency_id === $baseCurrencyId, 404);

        $history->delete();
        return response()->json(['message' => 'Historical rate deleted.']);
    }
}
