<?php

namespace App\Http\Controllers\Web\Ajax;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CurrencyController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Currency::where('is_active', true)->orderBy('code')->get());
    }

    public function update(Request $request, Currency $currency): JsonResponse
    {
        $validated = $request->validate([
            'exchange_rate' => 'required|numeric|min:0.000001',
            'is_active' => 'nullable|boolean',
        ]);

        $currency->update($validated);

        return response()->json($currency);
    }
}
