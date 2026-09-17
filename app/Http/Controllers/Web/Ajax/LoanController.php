<?php

namespace App\Http\Controllers\Web\Ajax;

use App\Enums\LoanDirection;
use App\Enums\TransactionType;
use App\Http\Controllers\Controller;
use App\Models\FinancialTransaction;
use App\Models\Loan;
use App\Models\Person;
use App\Models\TransactionEntry;
use App\Services\LedgerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class LoanController extends Controller
{
    public function index(Request $r): JsonResponse
    {
        return response()->json(Loan::forUser($r->user()->id)->with(['person', 'currency', 'payments', 'transactions' => fn($q) => $q->latest('transaction_date')])->when($r->direction, fn($q, $v) => $q->where('direction', $v))->when($r->status, fn($q, $v) => $q->where('status', $v))->latest()->get());
    }

    public function store(Request $r, LedgerService $ledger): JsonResponse
    {
        $d = $r->validate(['person_id' => 'required|exists:people,id', 'account_id' => 'required|exists:ledger_accounts,id', 'direction' => ['required', Rule::enum(LoanDirection::class)], 'title' => 'nullable|string|max:140', 'principal' => 'required|numeric|min:0.01', 'interest_rate' => 'nullable|numeric|min:0', 'interest_type' => 'nullable|in:none,simple,fixed', 'start_date' => 'required|date', 'due_date' => 'nullable|date|after_or_equal:start_date', 'description' => 'nullable|string|max:255', 'notes' => 'nullable|string|max:5000']);

        return response()->json($ledger->createLoan($r->user(), $d), 201);
    }

    public function show(Request $r, Loan $loan): JsonResponse
    {
        $this->owned($r, $loan);

        return response()->json($loan->load(['person', 'currency', 'payments', 'transactions' => fn($q) => $q->latest('transaction_date')]));
    }

    public function update(Request $r, Loan $loan): JsonResponse
    {
        $this->owned($r, $loan);
        $d = $r->validate([
            'person_id' => 'sometimes|required|exists:people,id',
            'direction' => ['sometimes', 'required', Rule::enum(LoanDirection::class)],
            'title' => 'nullable|string|max:140',
            'principal' => 'sometimes|required|numeric|min:0.01',
            'interest_rate' => 'nullable|numeric|min:0',
            'interest_type' => 'nullable|in:none,simple,fixed',
            'start_date' => 'sometimes|required|date',
            'due_date' => 'nullable|date',
            'notes' => 'nullable|string|max:5000',
            'status' => 'sometimes|required|in:active,paid,cancelled',
        ]);

        DB::transaction(function () use ($loan, $d) {
            $oldPrincipal = (float) $loan->principal;
            $oldOutstanding = (float) $loan->outstanding_principal;
            $paidSoFar = $oldPrincipal - $oldOutstanding;

            if (isset($d['principal'])) {
                $newPrincipal = (float) $d['principal'];
                $d['outstanding_principal'] = max(0, $newPrincipal - $paidSoFar);
                if ($d['outstanding_principal'] <= 0.0001) {
                    $d['status'] = 'paid';
                }
            }

            $personId = $d['person_id'] ?? $loan->person_id;
            $person = Person::find($personId);
            $personName = $person ? $person->name : '';

            $newDirection = isset($d['direction']) ? LoanDirection::from($d['direction']) : $loan->direction;
            $newDirectionStr = $newDirection === LoanDirection::Given ? 'given' : 'taken';

            $loan->update($d);

            // 1. Sync original initial Loan FinancialTransaction & TransactionEntries
            $origTx = FinancialTransaction::where('loan_id', $loan->id)
                ->whereIn('type', [TransactionType::LoanGiven, TransactionType::LoanTaken])
                ->first();

            if ($origTx) {
                $txType = $newDirection === LoanDirection::Given ? TransactionType::LoanGiven : TransactionType::LoanTaken;
                $txData = ['type' => $txType];

                if (isset($d['person_id'])) {
                    $txData['person_id'] = $d['person_id'];
                }
                if (isset($d['start_date'])) {
                    $txData['transaction_date'] = $d['start_date'];
                }
                if (isset($d['principal'])) {
                    $txData['amount'] = $d['principal'];
                }
                if (isset($d['notes'])) {
                    $txData['notes'] = $d['notes'];
                }
                $txData['description'] = ($newDirection === LoanDirection::Given ? 'Loan given to ' : 'Loan taken from ') . $personName;

                $origTx->update($txData);

                if (isset($d['principal'])) {
                    TransactionEntry::where('financial_transaction_id', $origTx->id)->each(function ($entry) use ($d) {
                        if ((float) $entry->debit > 0) {
                            $entry->update(['debit' => $d['principal']]);
                        }
                        if ((float) $entry->credit > 0) {
                            $entry->update(['credit' => $d['principal']]);
                        }
                    });
                }
            }

            // 2. Sync all associated repayment FinancialTransactions & LoanPayments
            $repaymentTxs = FinancialTransaction::where('loan_id', $loan->id)
                ->whereIn('type', [TransactionType::LoanRepaymentReceived, TransactionType::LoanRepaymentPaid])
                ->get();

            $repaymentType = $newDirection === LoanDirection::Given
                ? TransactionType::LoanRepaymentReceived
                : TransactionType::LoanRepaymentPaid;

            foreach ($repaymentTxs as $repaymentTx) {
                $repaymentTx->update([
                    'person_id' => $personId,
                    'type' => $repaymentType,
                    'description' => "Loan repayment - {$personName}",
                ]);
            }
        });

        return response()->json($loan->fresh(['person', 'currency', 'payments', 'transactions']));
    }

    public function repay(Request $r, Loan $loan, LedgerService $ledger): JsonResponse
    {
        $this->owned($r, $loan);
        $d = $r->validate(['account_id' => 'required|exists:ledger_accounts,id', 'principal_amount' => 'required|numeric|min:0.01', 'interest_amount' => 'nullable|numeric|min:0', 'paid_at' => 'required|date', 'description' => 'nullable|string|max:255']);

        return response()->json($ledger->repayLoan($r->user(), $loan, $d), 201);
    }

    private function owned(Request $r, Loan $m): void
    {
        abort_unless($m->user_id === $r->user()->id, 403);
    }
}
