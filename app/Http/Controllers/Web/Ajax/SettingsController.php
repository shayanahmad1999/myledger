<?php

namespace App\Http\Controllers\Web\Ajax;

use App\Http\Controllers\Controller;
use App\Models\UserSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function show(Request $r): JsonResponse
    {
        return response()->json($r->user()->settings);
    }

    public function update(Request $r): JsonResponse
    {
        $d = $r->validate([
            'base_currency_id' => 'sometimes|nullable|exists:currencies,id',
            'theme' => 'sometimes|in:system,light,dark',
            'locale' => 'sometimes|string|max:10',
            'daily_reminder_time' => 'nullable|date_format:H:i',
            'preferences' => 'nullable|array'
        ]);

        if (isset($d['base_currency_id'])) {
            $currentBaseId = $r->user()->settings?->base_currency_id;
            if ($currentBaseId && (int) $d['base_currency_id'] !== (int) $currentBaseId) {
                $hasTransactions = \App\Models\FinancialTransaction::forUser($r->user()->id)->exists();
                abort_if($hasTransactions, 422, 'The software uses a single primary base currency for your ledger. You cannot change your base currency once transactions exist.');
            }
        }

        $r->user()->settings()->update($d);
        return response()->json($r->user()->settings->fresh());
    }
}
