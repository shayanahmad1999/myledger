<?php

namespace App\Http\Controllers\Web\Ajax;

use App\Http\Controllers\Controller;
use App\Models\FinancialTransaction;
use App\Models\Person;
use App\Models\Tag;
use App\Services\LedgerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = FinancialTransaction::forUser($request->user()->id)
            ->with(['category', 'person', 'sourceAccount', 'destinationAccount', 'tags', 'currency'])
            ->latest('transaction_date')
            ->latest('id');

        $query
            ->when($request->type, fn($q, $value) => $q->where('type', $value))
            ->when($request->account_id, function ($q, $value) {
                $q->where(fn($inner) => $inner->where('source_account_id', $value)->orWhere('destination_account_id', $value));
            })
            ->when($request->category_id, fn($q, $value) => $q->where('category_id', $value))
            ->when($request->person_id, fn($q, $value) => $q->where('person_id', $value))
            ->when($request->tag_id, fn($q, $value) => $q->whereHas('tags', fn($tags) => $tags->whereKey($value)))
            ->when($request->from, fn($q, $value) => $q->whereDate('transaction_date', '>=', $value))
            ->when($request->to, fn($q, $value) => $q->whereDate('transaction_date', '<=', $value))
            ->when($request->search, function ($q, $value) {
                $needle = '%' . mb_strtolower($value) . '%';
                $q->where(fn($inner) => $inner
                    ->whereRaw('LOWER(description) LIKE ?', [$needle])
                    ->orWhereRaw('LOWER(reference_no) LIKE ?', [$needle]));
            });

        return response()->json($query->paginate(min(100, max(10, (int) $request->input('per_page', 30)))));
    }

    public function show(Request $request, FinancialTransaction $transaction): JsonResponse
    {
        $this->owned($request, $transaction);

        return response()->json($transaction->load([
            'entries.account',
            'category',
            'currency',
            'person',
            'loan',
            'tags',
            'attachments',
            'sourceAccount',
            'destinationAccount',
        ]));
    }

    public function expense(Request $request, LedgerService $ledger): JsonResponse
    {
        $data = $this->base($request, [
            'source_account_id' => 'required|integer|exists:ledger_accounts,id',
            'category_id' => 'required|integer|exists:categories,id',
        ]);

        return $this->created($request, $ledger->expense($request->user(), $data), $data);
    }

    public function income(Request $request, LedgerService $ledger): JsonResponse
    {
        $data = $this->base($request, [
            'destination_account_id' => 'required|integer|exists:ledger_accounts,id',
            'category_id' => 'required|integer|exists:categories,id',
        ]);

        return $this->created($request, $ledger->income($request->user(), $data), $data);
    }

    public function transfer(Request $request, LedgerService $ledger): JsonResponse
    {
        $data = $this->base($request, [
            'source_account_id' => 'required|integer|exists:ledger_accounts,id',
            'destination_account_id' => 'required|integer|different:source_account_id|exists:ledger_accounts,id',
        ]);

        return $this->created($request, $ledger->transfer($request->user(), $data), $data);
    }

    public function split(Request $request, LedgerService $ledger): JsonResponse
    {
        $validated = $request->validate([
            'source_account_id' => 'required|integer|exists:ledger_accounts,id',
            'transaction_date' => 'required|date',
            'description' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:5000',
            'splits' => 'required|array|min:2',
            'splits.*.category_id' => 'required|integer|exists:categories,id',
            'splits.*.amount' => 'required|numeric|min:0.01',
            'splits.*.description' => 'nullable|string|max:255',
        ]);

        return response()->json($ledger->splitExpense($request->user(), $validated), 201);
    }

    public function reverse(Request $request, FinancialTransaction $transaction, LedgerService $ledger): JsonResponse
    {
        $this->owned($request, $transaction);
        $data = $request->validate(['date' => 'nullable|date']);

        return response()->json($ledger->reverse($request->user(), $transaction, $data['date'] ?? null), 201);
    }

    private function base(Request $request, array $extra): array
    {
        $data = $request->validate(array_merge([
            'amount' => 'required|numeric|min:0.01',
            'transaction_date' => 'required|date',
            'currency_id' => 'nullable|integer|exists:currencies,id',
            'description' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:5000',
            'reference_no' => 'nullable|string|max:60',
            'person_id' => 'nullable|integer|exists:people,id',
            'tag_ids' => 'nullable|array',
            'tag_ids.*' => 'integer|exists:tags,id',
        ], $extra));

        if (!empty($data['person_id'])) {
            abort_unless(
                Person::whereKey($data['person_id'])->where('user_id', $request->user()->id)->exists(),
                403,
                'Person does not belong to you.'
            );
        }

        if (!empty($data['tag_ids'])) {
            $ownedCount = Tag::where('user_id', $request->user()->id)->whereIn('id', $data['tag_ids'])->count();
            abort_unless($ownedCount === count(array_unique($data['tag_ids'])), 403, 'One or more tags do not belong to you.');
        }

        return $data;
    }

    private function created(Request $request, FinancialTransaction $transaction, array $data): JsonResponse
    {
        if (array_key_exists('tag_ids', $data)) {
            $transaction->tags()->sync($data['tag_ids'] ?? []);
        }

        return response()->json(
            $transaction->fresh(['entries.account', 'category', 'person', 'sourceAccount', 'destinationAccount', 'tags']),
            201,
        );
    }

    private function owned(Request $request, FinancialTransaction $transaction): void
    {
        abort_unless((int) $transaction->user_id === (int) $request->user()->id, 403);
    }
}
