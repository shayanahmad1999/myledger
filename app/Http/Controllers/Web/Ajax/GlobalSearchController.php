<?php

namespace App\Http\Controllers\Web\Ajax;

use App\Http\Controllers\Controller;
use App\Models\Committee;
use App\Models\FinancialTransaction;
use App\Models\LedgerAccount;
use App\Models\Loan;
use App\Models\Person;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GlobalSearchController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $q = trim($request->query('q', ''));
        if (mb_strlen($q) < 2) {
            return response()->json(['results' => []]);
        }

        $needle = '%' . mb_strtolower($q) . '%';
        $user = $request->user();

        // Transactions
        $transactions = FinancialTransaction::forUser($user->id)
            ->where(fn($query) => $query->whereRaw('LOWER(description) LIKE ?', [$needle])->orWhereRaw('LOWER(reference_no) LIKE ?', [$needle]))
            ->latest('transaction_date')
            ->limit(5)
            ->get()
            ->map(fn($t) => [
                'type' => 'Transaction',
                'title' => $t->description ?: $t->reference_no,
                'subtitle' => "{$t->transaction_date->toDateString()} · Amount: {$t->amount}",
                'url' => route('transactions') . '?search=' . urlencode($t->reference_no),
                'icon' => 'bi-arrow-left-right',
            ]);

        // Accounts
        $accounts = LedgerAccount::forUser($user->id)
            ->whereRaw('LOWER(name) LIKE ?', [$needle])
            ->limit(5)
            ->get()
            ->map(fn($a) => [
                'type' => 'Account',
                'title' => $a->name,
                'subtitle' => ucfirst($a->type->value) . ' · ' . $a->institution,
                'url' => route('accounts'),
                'icon' => 'bi-bank',
            ]);

        // Committees
        $committees = Committee::forUser($user->id)
            ->whereRaw('LOWER(name) LIKE ?', [$needle])
            ->limit(5)
            ->get()
            ->map(fn($c) => [
                'type' => 'Committee',
                'title' => $c->name,
                'subtitle' => "{$c->total_members} Members · Pot: {$c->total_pool_amount}",
                'url' => route('committees'),
                'icon' => 'bi-diagram-3-fill',
            ]);

        // People
        $people = Person::where('user_id', $user->id)
            ->where(fn($query) => $query->whereRaw('LOWER(name) LIKE ?', [$needle])->orWhereRaw('LOWER(email) LIKE ?', [$needle]))
            ->limit(5)
            ->get()
            ->map(fn($p) => [
                'type' => 'Person',
                'title' => $p->name,
                'subtitle' => $p->email ?: $p->phone ?: 'Contact',
                'url' => route('people'),
                'icon' => 'bi-person',
            ]);

        // Loans
        $loans = Loan::forUser($user->id)
            ->with('person')
            ->where(fn($query) => $query->whereRaw('LOWER(title) LIKE ?', [$needle])->orWhereHas('person', fn($pq) => $pq->whereRaw('LOWER(name) LIKE ?', [$needle])))
            ->limit(5)
            ->get()
            ->map(fn($l) => [
                'type' => 'Loan',
                'title' => $l->title ?: ('Loan with ' . $l->person->name),
                'subtitle' => ucfirst($l->direction->value) . " · Outstanding: {$l->outstanding_principal}",
                'url' => route('loans'),
                'icon' => 'bi-cash-stack',
            ]);

        $results = array_merge(
            $transactions->toArray(),
            $accounts->toArray(),
            $committees->toArray(),
            $people->toArray(),
            $loans->toArray()
        );

        return response()->json(['results' => $results]);
    }
}
