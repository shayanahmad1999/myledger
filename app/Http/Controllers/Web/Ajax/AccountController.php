<?php

namespace App\Http\Controllers\Web\Ajax;

use App\Enums\LedgerAccountKind;
use App\Enums\LedgerAccountType;
use App\Enums\TransactionType;
use App\Http\Controllers\Controller;
use App\Models\FinancialTransaction;
use App\Models\LedgerAccount;
use App\Models\User;
use App\Services\LedgerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AccountController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $items = LedgerAccount::forUser($request->user()->id)
            ->with('currency')
            ->moneyAccounts()
            ->when(!$request->boolean('include_archived'), fn($q) => $q->where('is_archived', false))
            ->get()
            ->map(fn($a) => array_merge($a->toArray(), ['balance' => round($a->balance(), 2)]));
        return response()->json($items);
    }

    public function store(Request $request, LedgerService $ledger): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:120',
            'type' => ['required', Rule::enum(LedgerAccountType::class)],
            'currency_id' => 'nullable|exists:currencies,id',
            'institution' => 'nullable|string|max:120',
            'last_four' => 'nullable|digits:4',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:20',
            'include_in_net_worth' => 'sometimes|boolean',
            'opening_balance' => 'nullable|numeric|min:0',
            'opening_balance_date' => 'required_with:opening_balance|date',
        ]);
        abort_unless(LedgerAccountType::from($data['type'])->isUserMoneyAccount(), 422, 'Invalid user account type.');
        $account = DB::transaction(function () use ($request, $data, $ledger) {
            $opening = (float) ($data['opening_balance'] ?? 0);
            unset($data['opening_balance'], $data['opening_balance_date']);
            $baseCurrencyId = $request->user()->settings?->base_currency_id;
            $data['currency_id'] = $data['currency_id'] ?? $baseCurrencyId;
            abort_unless($data['currency_id'], 422, 'Please select a currency or configure your Base Currency in Settings.');
            $account = LedgerAccount::create(array_merge($data, ['user_id' => $request->user()->id, 'kind' => LedgerAccountKind::Asset, 'is_system' => false]));
            if ($opening > 0)
                $ledger->openingBalance($request->user(), $account, $opening, $request->input('opening_balance_date', now()->toDateString()));
            return $account;
        });
        return response()->json(array_merge($account->toArray(), ['balance' => round($account->balance(), 2)]), 201);
    }

    public function show(Request $request, LedgerAccount $account): JsonResponse
    {
        $this->owned($request, $account);
        return response()->json(array_merge($account->toArray(), ['balance' => round($account->balance(), 2)]));
    }

    public function update(Request $request, LedgerAccount $account): JsonResponse
    {
        $this->owned($request, $account);
        abort_if($account->is_system, 422, 'System accounts cannot be edited here.');
        $data = $request->validate([
            'name' => 'sometimes|required|string|max:120',
            'type' => ['sometimes', 'required', Rule::enum(LedgerAccountType::class)],
            'currency_id' => 'sometimes|required|exists:currencies,id',
            'institution' => 'nullable|string|max:120',
            'last_four' => 'nullable|digits:4',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:20',
            'include_in_net_worth' => 'sometimes|boolean',
            'is_archived' => 'sometimes|boolean',
            'opening_balance' => 'nullable|numeric|min:0',
            'opening_balance_date' => 'required_with:opening_balance|date',
        ]);
        if (isset($data['type'])) {
            abort_unless(LedgerAccountType::from($data['type'])->isUserMoneyAccount(), 422, 'Invalid user account type.');
        }

        $openingBalance = $data['opening_balance'] ?? null;
        $openingBalanceDate = $data['opening_balance_date'] ?? null;
        unset($data['opening_balance'], $data['opening_balance_date']);

        $account->update($data);

        if ($openingBalance !== null) {
            $this->updateOpeningBalanceTransaction($request->user(), $account, (float) $openingBalance, $openingBalanceDate);
        }

        return response()->json(array_merge($account->fresh(['currency'])->toArray(), ['balance' => round($account->balance(), 2)]));
    }

    private function updateOpeningBalanceTransaction(User $user, LedgerAccount $account, float $amount, string $date): void
    {
        $existing = FinancialTransaction::forUser($user->id)
            ->where('type', TransactionType::OpeningBalance->value)
            ->where(function ($q) use ($account) {
                $q->where('source_account_id', $account->id)
                    ->orWhere('destination_account_id', $account->id);
            })
            ->first();

        if ($amount <= 0) {
            if ($existing) {
                $existing->entries()->delete();
                $existing->delete();
            }
            return;
        }

        $equity = $this->getSystemAccount($user, 'opening_balance_equity');
        $normalDebit = in_array($account->kind, [LedgerAccountKind::Asset, LedgerAccountKind::Expense], true);
        $lines = $normalDebit
            ? [['account' => $account, 'debit' => $amount], ['account' => $equity, 'credit' => $amount]]
            : [['account' => $equity, 'debit' => $amount], ['account' => $account, 'credit' => $amount]];

        $this->postTransaction($user, TransactionType::OpeningBalance, $date, $amount, $lines, [
            'description' => "Opening balance - {$account->name}",
            'destination_account_id' => $normalDebit ? $account->id : null,
            'source_account_id' => $normalDebit ? null : $account->id,
        ], $existing);
    }

    private function getSystemAccount(User $user, string $type): LedgerAccount
    {
        return LedgerAccount::forUser($user->id)
            ->where('type', $type)
            ->where('is_system', true)
            ->firstOrFail();
    }

    private function postTransaction(User $user, TransactionType $type, string $date, float $amount, array $lines, array $meta = [], ?FinancialTransaction $existing = null): FinancialTransaction
    {
        $reference = $meta['reference_no'] ?? $this->generateReference($type);

        if ($existing) {
            $existing->entries()->delete();
            $existing->update([
                'currency_id' => $lines[0]['account']->currency_id,
                'transaction_date' => $date,
                'amount' => $amount,
                'description' => $meta['description'] ?? null,
                'source_account_id' => $meta['source_account_id'] ?? null,
                'destination_account_id' => $meta['destination_account_id'] ?? null,
                'notes' => $meta['notes'] ?? null,
                'status' => 'posted',
                'reference_no' => $reference,
            ]);
            $tx = $existing;
        } else {
            $tx = FinancialTransaction::create([
                'user_id' => $user->id,
                'currency_id' => $lines[0]['account']->currency_id,
                'type' => $type,
                'reference_no' => $reference,
                'transaction_date' => $date,
                'amount' => $amount,
                'description' => $meta['description'] ?? null,
                'source_account_id' => $meta['source_account_id'] ?? null,
                'destination_account_id' => $meta['destination_account_id'] ?? null,
                'notes' => $meta['notes'] ?? null,
                'status' => 'posted',
            ]);
        }

        foreach ($lines as $line) {
            $tx->entries()->create([
                'ledger_account_id' => $line['account']->id,
                'debit' => $line['debit'] ?? 0,
                'credit' => $line['credit'] ?? 0,
            ]);
        }

        return $tx;
    }

    private function generateReference(TransactionType $type): string
    {
        return strtoupper(substr($type->value, 0, 4)) . '-' . now()->format('Ymd') . '-' . Str::upper(Str::random(6));
    }

    public function destroy(Request $request, LedgerAccount $account): JsonResponse
    {
        $this->owned($request, $account);
        abort_if($account->is_system, 422, 'System accounts cannot be archived.');
        $account->update(['is_archived' => true]);
        return response()->json(['message' => 'Account archived.']);
    }

    private function owned(Request $request, LedgerAccount $account): void
    {
        abort_unless($account->user_id === $request->user()->id, 403);
    }
}
