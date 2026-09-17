<?php

namespace App\Services;

use App\Enums\LedgerAccountKind;
use App\Enums\LedgerAccountType;
use App\Enums\LoanDirection;
use App\Enums\TransactionType;
use App\Models\AuditLog;
use App\Models\Category;
use App\Models\Currency;
use App\Models\FinancialTransaction;
use App\Models\LedgerAccount;
use App\Models\Loan;
use App\Models\LoanPayment;
use App\Models\Person;
use App\Models\SavingsAllocation;
use App\Models\SavingsGoal;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LedgerService
{
    public function __construct(
        private readonly FinanceSetupService $setup,
        private readonly BudgetAlertService $budgetAlerts,
    ) {}

    public function openingBalance(User $user, LedgerAccount $account, float $amount, string $date): FinancialTransaction
    {
        $this->assertOwned($user, $account);
        $equity = $this->setup->systemAccount($user, 'opening_balance_equity');
        $amount = abs($amount);
        if ($amount <= 0) throw ValidationException::withMessages(['opening_balance' => 'Opening balance must be greater than zero.']);

        $normalDebit = in_array($account->kind, [LedgerAccountKind::Asset, LedgerAccountKind::Expense], true);
        $lines = $normalDebit
            ? [['account' => $account, 'debit' => $amount], ['account' => $equity, 'credit' => $amount]]
            : [['account' => $equity, 'debit' => $amount], ['account' => $account, 'credit' => $amount]];

        return $this->post($user, TransactionType::OpeningBalance, $date, $amount, $lines, [
            'description' => "Opening balance - {$account->name}",
            'destination_account_id' => $normalDebit ? $account->id : null,
            'source_account_id' => $normalDebit ? null : $account->id,
        ]);
    }

    public function expense(User $user, array $data): FinancialTransaction
    {
        $source = LedgerAccount::findOrFail($data['source_account_id']);
        $category = Category::with('ledgerAccount')->findOrFail($data['category_id']);
        $this->assertOwned($user, $source, $category);
        abort_unless($category->type === 'expense', 422, 'Selected category is not an expense category.');

        $transaction = $this->post($user, TransactionType::Expense, $data['transaction_date'], (float) $data['amount'], [
            ['account' => $category->ledgerAccount, 'debit' => $data['amount']],
            ['account' => $source, 'credit' => $data['amount']],
        ], array_merge($data, ['source_account_id' => $source->id, 'category_id' => $category->id]));

        $this->budgetAlerts->check($user, $category->id, $data['transaction_date']);

        return $transaction;
    }

    public function splitExpense(User $user, array $data): FinancialTransaction
    {
        $source = LedgerAccount::findOrFail($data['source_account_id']);
        $this->assertOwned($user, $source);
        $splits = $data['splits'] ?? [];
        abort_if(count($splits) < 2, 422, 'A split transaction must have at least 2 expense splits.');

        $lines = [];
        $totalAmount = 0.0;

        foreach ($splits as $split) {
            $category = Category::with('ledgerAccount')->findOrFail($split['category_id']);
            $this->assertOwned($user, $category);
            abort_unless($category->type === 'expense', 422, 'All split categories must be expense categories.');
            $amount = (float) $split['amount'];
            abort_if($amount <= 0, 422, 'Each split amount must be greater than zero.');

            $totalAmount += $amount;
            $lines[] = [
                'account' => $category->ledgerAccount,
                'debit' => $amount,
                'memo' => $split['description'] ?? null,
            ];
        }

        $lines[] = ['account' => $source, 'credit' => $totalAmount];

        return $this->post($user, TransactionType::Expense, $data['transaction_date'], $totalAmount, $lines, [
            'source_account_id' => $source->id,
            'description' => $data['description'] ?? 'Split Expense',
            'notes' => $data['notes'] ?? null,
        ]);
    }

    public function income(User $user, array $data): FinancialTransaction
    {
        $destination = LedgerAccount::findOrFail($data['destination_account_id']);
        $category = Category::with('ledgerAccount')->findOrFail($data['category_id']);
        $this->assertOwned($user, $destination, $category);
        abort_unless($category->type === 'income', 422, 'Selected category is not an income category.');

        return $this->post($user, TransactionType::Income, $data['transaction_date'], (float) $data['amount'], [
            ['account' => $destination, 'debit' => $data['amount']],
            ['account' => $category->ledgerAccount, 'credit' => $data['amount']],
        ], array_merge($data, ['destination_account_id' => $destination->id, 'category_id' => $category->id]));
    }

    public function transfer(User $user, array $data, TransactionType $type = TransactionType::Transfer): FinancialTransaction
    {
        $source = LedgerAccount::findOrFail($data['source_account_id']);
        $destination = LedgerAccount::findOrFail($data['destination_account_id']);
        $this->assertOwned($user, $source, $destination);
        abort_if($source->id === $destination->id, 422, 'Source and destination accounts must be different.');

        return $this->post($user, $type, $data['transaction_date'], (float) $data['amount'], [
            ['account' => $destination, 'debit' => $data['amount']],
            ['account' => $source, 'credit' => $data['amount']],
        ], array_merge($data, ['source_account_id'=>$source->id,'destination_account_id'=>$destination->id]));
    }

    public function createLoan(User $user, array $data): Loan
    {
        return DB::transaction(function () use ($user, $data) {
            $person = Person::findOrFail($data['person_id']);
            $moneyAccount = LedgerAccount::findOrFail($data['account_id']);
            $this->assertOwned($user, $person, $moneyAccount);
            $direction = LoanDirection::from($data['direction']);
            $control = $this->setup->systemAccount($user, $direction === LoanDirection::Given ? LedgerAccountType::LoanReceivable->value : LedgerAccountType::LoanPayable->value);
            $currencyId = $moneyAccount->currency_id;
            $principal = (float) $data['principal'];

            $loan = Loan::create([
                'user_id'=>$user->id,'person_id'=>$person->id,'currency_id'=>$currencyId,'ledger_account_id'=>$control->id,
                'direction'=>$direction,'title'=>$data['title'] ?? null,'principal'=>$principal,'outstanding_principal'=>$principal,
                'interest_rate'=>$data['interest_rate'] ?? 0,'interest_type'=>$data['interest_type'] ?? 'none',
                'start_date'=>$data['start_date'],'due_date'=>$data['due_date'] ?? null,'notes'=>$data['notes'] ?? null,
            ]);

            $type = $direction === LoanDirection::Given ? TransactionType::LoanGiven : TransactionType::LoanTaken;
            $lines = $direction === LoanDirection::Given
                ? [['account'=>$control,'debit'=>$principal,'loan_id'=>$loan->id], ['account'=>$moneyAccount,'credit'=>$principal]]
                : [['account'=>$moneyAccount,'debit'=>$principal], ['account'=>$control,'credit'=>$principal,'loan_id'=>$loan->id]];

            $this->post($user, $type, $data['start_date'], $principal, $lines, [
                'source_account_id'=>$direction === LoanDirection::Given ? $moneyAccount->id : null,
                'destination_account_id'=>$direction === LoanDirection::Taken ? $moneyAccount->id : null,
                'person_id'=>$person->id,'loan_id'=>$loan->id,
                'description'=>$data['description'] ?? (($direction === LoanDirection::Given ? 'Loan given to ' : 'Loan taken from ').$person->name),
                'notes'=>$data['notes'] ?? null,
            ]);

            return $loan->fresh(['person','currency']);
        });
    }

    public function repayLoan(User $user, Loan $loan, array $data): FinancialTransaction
    {
        $this->assertOwned($user, $loan);
        return DB::transaction(function () use ($user, $loan, $data) {
            $principal = (float) $data['principal_amount'];
            $interest = (float) ($data['interest_amount'] ?? 0);
            abort_if($principal <= 0 || $principal > (float) $loan->outstanding_principal + 0.0001, 422, 'Invalid principal repayment amount.');
            abort_if($interest < 0, 422, 'Interest amount cannot be negative.');
            $total = $principal + $interest;
            $account = LedgerAccount::findOrFail($data['account_id']);
            $this->assertOwned($user, $account);
            $control = $loan->ledgerAccount;

            if ($loan->direction === LoanDirection::Given) {
                $interestAccount = $this->setup->systemAccount($user, 'interest_income');
                $lines = [['account'=>$account,'debit'=>$total], ['account'=>$control,'credit'=>$principal,'loan_id'=>$loan->id]];
                if ($interest > 0) $lines[] = ['account'=>$interestAccount,'credit'=>$interest];
                $type = TransactionType::LoanRepaymentReceived;
                $meta = ['destination_account_id'=>$account->id];
            } else {
                $interestAccount = $this->setup->systemAccount($user, 'interest_expense');
                $lines = [['account'=>$control,'debit'=>$principal,'loan_id'=>$loan->id]];
                if ($interest > 0) $lines[] = ['account'=>$interestAccount,'debit'=>$interest];
                $lines[] = ['account'=>$account,'credit'=>$total];
                $type = TransactionType::LoanRepaymentPaid;
                $meta = ['source_account_id'=>$account->id];
            }

            $tx = $this->post($user, $type, $data['paid_at'], $total, $lines, array_merge($meta, [
                'person_id'=>$loan->person_id,'loan_id'=>$loan->id,'description'=>$data['description'] ?? "Loan repayment - {$loan->person->name}",
            ]));

            LoanPayment::create(['loan_id'=>$loan->id,'financial_transaction_id'=>$tx->id,'principal_amount'=>$principal,'interest_amount'=>$interest,'paid_at'=>$data['paid_at']]);
            $remaining = max(0, (float)$loan->outstanding_principal - $principal);
            $loan->update(['outstanding_principal'=>$remaining,'status'=>$remaining <= 0.0001 ? 'paid' : 'active']);
            return $tx;
        });
    }

    public function contributeToGoal(User $user, SavingsGoal $goal, array $data): FinancialTransaction
    {
        $this->assertOwned($user, $goal);
        $data['destination_account_id'] = $goal->account_id;
        $tx = DB::transaction(function () use ($user, $goal, $data) {
            $tx = $this->transfer($user, $data, TransactionType::SavingsContribution);
            $tx->update(['savings_goal_id'=>$goal->id]);
            SavingsAllocation::create(['savings_goal_id'=>$goal->id,'financial_transaction_id'=>$tx->id,'direction'=>'contribution','amount'=>$data['amount'],'allocated_at'=>$data['transaction_date']]);
            $goal->increment('allocated_amount', (float)$data['amount']);
            $goal->refresh();
            if ((float)$goal->allocated_amount >= (float)$goal->target_amount) $goal->update(['is_completed'=>true]);
            return $tx;
        });
        return $tx;
    }

    public function withdrawFromGoal(User $user, SavingsGoal $goal, array $data): FinancialTransaction
    {
        $this->assertOwned($user, $goal);
        abort_if((float)$data['amount'] > (float)$goal->allocated_amount + 0.0001, 422, 'Withdrawal exceeds the amount allocated to this goal.');
        $data['source_account_id'] = $goal->account_id;
        return DB::transaction(function () use ($user, $goal, $data) {
            $tx = $this->transfer($user, $data, TransactionType::SavingsWithdrawal);
            $tx->update(['savings_goal_id'=>$goal->id]);
            SavingsAllocation::create(['savings_goal_id'=>$goal->id,'financial_transaction_id'=>$tx->id,'direction'=>'withdrawal','amount'=>$data['amount'],'allocated_at'=>$data['transaction_date']]);
            $goal->decrement('allocated_amount', (float)$data['amount']);
            $goal->refresh();
            if ((float)$goal->allocated_amount < (float)$goal->target_amount) $goal->update(['is_completed'=>false]);
            return $tx;
        });
    }

    public function reverse(User $user, FinancialTransaction $original, ?string $date = null): FinancialTransaction
    {
        $this->assertOwned($user, $original);
        abort_unless($original->status === 'posted', 422, 'Only posted transactions can be reversed.');
        abort_if($original->loan_id || $original->savings_goal_id, 422, 'Loan and savings transactions must be corrected through their dedicated workflow so balances remain consistent.');

        $reversal = DB::transaction(function () use ($user, $original, $date) {
            $original->load('entries.account');
            $lines = $original->entries->map(fn ($entry) => [
                'account' => $entry->account,
                'debit' => (float) $entry->credit,
                'credit' => (float) $entry->debit,
                'loan_id' => $entry->loan_id,
            ])->all();
            $reversal = $this->post(
                $user,
                TransactionType::Reversal,
                $date ?? now()->toDateString(),
                (float) $original->amount,
                $lines,
                [
                    'reversal_of_id' => $original->id,
                    'description' => 'Reversal of '.$original->reference_no,
                    'metadata' => ['original_type' => $original->type->value],
                ],
            );
            $original->update(['status' => 'reversed']);

            return $reversal;
        });

        if ($original->type === TransactionType::Expense && $original->category_id) {
            $this->budgetAlerts->check($user, (int) $original->category_id, $reversal->transaction_date->toDateString());
        }

        return $reversal;
    }

    public function post(User $user, TransactionType $type, string $date, float $amount, array $lines, array $meta = []): FinancialTransaction
    {
        abort_if($amount <= 0, 422, 'Transaction amount must be greater than zero.');
        $debit = 0.0; $credit = 0.0;
        foreach ($lines as $line) {
            $this->assertOwned($user, $line['account']);
            $d = (float)($line['debit'] ?? 0); $c = (float)($line['credit'] ?? 0);
            abort_if($d < 0 || $c < 0 || ($d > 0 && $c > 0) || ($d <= 0 && $c <= 0), 422, 'Each ledger line must contain either a positive debit or a positive credit.');
            $debit += $d; $credit += $c;
        }
        abort_if(abs($debit - $credit) > 0.0001, 422, 'Transaction is not balanced.');

        $currencyIds = array_values(array_unique(array_map(
            fn ($line) => (int) $line['account']->currency_id,
            $lines,
        )));
        abort_if(count($currencyIds) !== 1, 422, 'All accounts in a transaction must use the same currency. Currency conversion requires an explicit exchange workflow.');

        return DB::transaction(function () use ($user, $type, $date, $amount, $lines, $meta) {
            $currencyId = $meta['currency_id'] ?? ($lines[0]['account']->currency_id ?? Currency::where('code', config('finance.base_currency'))->value('id'));
            $reference = $meta['reference_no'] ?? $this->reference($type);
            $tx = FinancialTransaction::create([
                'user_id'=>$user->id,'currency_id'=>$currencyId,'type'=>$type,'reference_no'=>$reference,
                'transaction_date'=>$date,'amount'=>$amount,'description'=>$meta['description'] ?? null,'notes'=>$meta['notes'] ?? null,
                'source_account_id'=>$meta['source_account_id'] ?? null,'destination_account_id'=>$meta['destination_account_id'] ?? null,
                'category_id'=>$meta['category_id'] ?? null,'person_id'=>$meta['person_id'] ?? null,'loan_id'=>$meta['loan_id'] ?? null,
                'savings_goal_id'=>$meta['savings_goal_id'] ?? null,'recurring_transaction_id'=>$meta['recurring_transaction_id'] ?? null,
                'reversal_of_id'=>$meta['reversal_of_id'] ?? null,'metadata'=>$meta['metadata'] ?? null,'status'=>'posted',
            ]);
            foreach ($lines as $line) {
                $tx->entries()->create([
                    'ledger_account_id'=>$line['account']->id,'loan_id'=>$line['loan_id'] ?? null,
                    'debit'=>$line['debit'] ?? 0,'credit'=>$line['credit'] ?? 0,'memo'=>$line['memo'] ?? null,
                ]);
            }
            AuditLog::create([
                'user_id'=>$user->id,'auditable_type'=>FinancialTransaction::class,'auditable_id'=>$tx->id,'action'=>'created',
                'new_values'=>['type'=>$type->value,'reference_no'=>$reference,'amount'=>$amount,'date'=>$date],
                'ip_address'=>request()?->ip(),'user_agent'=>request()?->userAgent(),
            ]);
            return $tx->load(['entries.account','category','person','sourceAccount','destinationAccount']);
        });
    }

    private function reference(TransactionType $type): string
    {
        return strtoupper(substr($type->value, 0, 4)).'-'.now()->format('Ymd').'-'.Str::upper(Str::random(6));
    }

    private function assertOwned(User $user, object ...$models): void
    {
        foreach ($models as $model) {
            if (property_exists($model, 'user_id') || isset($model->user_id)) {
                abort_unless((int)$model->user_id === (int)$user->id, 403, 'You do not have access to this record.');
            }
        }
    }
}
