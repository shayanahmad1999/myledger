<?php

namespace App\Http\Controllers\Web\Ajax;

use App\Enums\RecurringMode;
use App\Enums\TransactionType;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\LedgerAccount;
use App\Models\Person;
use App\Models\RecurringTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class RecurringTransactionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json(
            RecurringTransaction::forUser($request->user()->id)
                ->with('currency')
                ->orderBy('next_run_at')
                ->get()
        );
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validateData($request);
        $this->validateReferences($request, $data);

        $settings = $request->user()->settings;
        abort_unless($settings, 422, 'User finance settings are missing.');

        return response()->json(
            RecurringTransaction::create(array_merge($data, [
                'user_id' => $request->user()->id,
                'currency_id' => $settings->base_currency_id,
            ])),
            201,
        );
    }

    public function update(Request $request, RecurringTransaction $recurring): JsonResponse
    {
        $this->owned($request, $recurring);
        $data = $this->validateData($request, true);
        $merged = array_merge($recurring->toArray(), $data);
        $this->validateReferences($request, $merged);
        $recurring->update($data);

        return response()->json($recurring->fresh());
    }

    public function destroy(Request $request, RecurringTransaction $recurring): JsonResponse
    {
        $this->owned($request, $recurring);
        $recurring->update(['is_active' => false]);

        return response()->json(['message' => 'Recurring transaction disabled.']);
    }

    private function validateData(Request $request, bool $partial = false): array
    {
        $presence = $partial ? 'sometimes' : 'required';

        return $request->validate([
            'title' => "$presence|string|max:140",
            'type' => [$presence, Rule::in([
                TransactionType::Expense->value,
                TransactionType::Income->value,
                TransactionType::Transfer->value,
            ])],
            'amount' => "$presence|numeric|min:0.01",
            'source_account_id' => 'nullable|integer|exists:ledger_accounts,id',
            'destination_account_id' => 'nullable|integer|exists:ledger_accounts,id',
            'category_id' => 'nullable|integer|exists:categories,id',
            'person_id' => 'nullable|integer|exists:people,id',
            'frequency' => [$presence, Rule::in(['daily', 'weekly', 'monthly', 'yearly'])],
            'interval' => 'sometimes|integer|min:1|max:365',
            'next_run_at' => "$presence|date",
            'mode' => [$presence, Rule::enum(RecurringMode::class)],
            'is_active' => 'sometimes|boolean',
            'payload' => 'nullable|array',
        ]);
    }

    private function validateReferences(Request $request, array $data): void
    {
        $userId = $request->user()->id;
        $type = $data['type'] instanceof TransactionType
            ? $data['type']->value
            : (string) ($data['type'] ?? '');

        foreach (['source_account_id', 'destination_account_id'] as $key) {
            if (!empty($data[$key])) {
                $account = LedgerAccount::findOrFail($data[$key]);
                abort_unless((int) $account->user_id === (int) $userId, 403, 'Account does not belong to you.');
            }
        }

        $category = null;
        if (!empty($data['category_id'])) {
            $category = Category::findOrFail($data['category_id']);
            abort_unless((int) $category->user_id === (int) $userId, 403, 'Category does not belong to you.');
        }

        if (!empty($data['person_id'])) {
            $person = Person::findOrFail($data['person_id']);
            abort_unless((int) $person->user_id === (int) $userId, 403, 'Person does not belong to you.');
        }

        $errors = [];
        if ($type === TransactionType::Expense->value) {
            if (empty($data['source_account_id']))
                $errors['source_account_id'][] = 'A source account is required for a recurring expense.';
            if (empty($data['category_id']))
                $errors['category_id'][] = 'An expense category is required.';
            if ($category && $category->type !== 'expense')
                $errors['category_id'][] = 'The selected category must be an expense category.';
        } elseif ($type === TransactionType::Income->value) {
            if (empty($data['destination_account_id']))
                $errors['destination_account_id'][] = 'A destination account is required for recurring income.';
            if (empty($data['category_id']))
                $errors['category_id'][] = 'An income category is required.';
            if ($category && $category->type !== 'income')
                $errors['category_id'][] = 'The selected category must be an income category.';
        } elseif ($type === TransactionType::Transfer->value) {
            if (empty($data['source_account_id']))
                $errors['source_account_id'][] = 'A source account is required for a recurring transfer.';
            if (empty($data['destination_account_id']))
                $errors['destination_account_id'][] = 'A destination account is required for a recurring transfer.';
            if (!empty($data['source_account_id']) && $data['source_account_id'] === $data['destination_account_id']) {
                $errors['destination_account_id'][] = 'Source and destination accounts must be different.';
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }

    private function owned(Request $request, RecurringTransaction $recurring): void
    {
        abort_unless((int) $recurring->user_id === (int) $request->user()->id, 403);
    }
}
