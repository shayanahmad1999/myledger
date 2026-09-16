<?php

namespace Tests\Feature;

use App\Enums\LedgerAccountKind;
use App\Enums\LedgerAccountType;
use App\Models\Category;
use App\Models\LedgerAccount;
use App\Models\Person;
use App\Models\User;
use App\Services\FinanceSetupService;
use App\Services\LedgerService;
use App\Services\ReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LedgerServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_income_expense_and_transfer_keep_balanced_accounting(): void
    {
        $user=User::factory()->create(); app(FinanceSetupService::class)->bootstrapUser($user); $user->refresh();
        $currency=$user->settings->base_currency_id;
        $bank=LedgerAccount::create(['user_id'=>$user->id,'currency_id'=>$currency,'kind'=>LedgerAccountKind::Asset,'type'=>LedgerAccountType::Bank,'name'=>'Test Bank']);
        $savings=LedgerAccount::create(['user_id'=>$user->id,'currency_id'=>$currency,'kind'=>LedgerAccountKind::Asset,'type'=>LedgerAccountType::Savings,'name'=>'Emergency Savings']);
        $income=Category::forUser($user->id)->where('type','income')->firstOrFail();
        $expense=Category::forUser($user->id)->where('type','expense')->firstOrFail();
        $ledger=app(LedgerService::class);

        $ledger->income($user,['destination_account_id'=>$bank->id,'category_id'=>$income->id,'amount'=>100000,'transaction_date'=>'2026-09-01']);
        $ledger->expense($user,['source_account_id'=>$bank->id,'category_id'=>$expense->id,'amount'=>10000,'transaction_date'=>'2026-09-02']);
        $ledger->transfer($user,['source_account_id'=>$bank->id,'destination_account_id'=>$savings->id,'amount'=>20000,'transaction_date'=>'2026-09-03']);

        $this->assertEquals(70000.0,$bank->fresh()->balance());
        $this->assertEquals(20000.0,$savings->fresh()->balance());
        $summary=app(ReportService::class)->summary($user,'2026-09-01','2026-09-30');
        $this->assertEquals(100000.0,$summary['income']);
        $this->assertEquals(10000.0,$summary['expense']);
    }

    public function test_loan_given_is_an_asset_and_repayment_reduces_receivable(): void
    {
        $user=User::factory()->create(); app(FinanceSetupService::class)->bootstrapUser($user); $user->refresh();
        $bank=LedgerAccount::create(['user_id'=>$user->id,'currency_id'=>$user->settings->base_currency_id,'kind'=>LedgerAccountKind::Asset,'type'=>LedgerAccountType::Bank,'name'=>'Bank']);
        app(LedgerService::class)->openingBalance($user,$bank,100000,'2026-09-01');
        $person=Person::create(['user_id'=>$user->id,'name'=>'Ali']);
        $loan=app(LedgerService::class)->createLoan($user,['person_id'=>$person->id,'account_id'=>$bank->id,'direction'=>'given','principal'=>50000,'start_date'=>'2026-09-02']);

        $net=app(ReportService::class)->netWorth($user,'2026-09-02');
        $this->assertEquals(100000.0,$net['net_worth']);
        $this->assertEquals(50000.0,$bank->fresh()->balance());

        app(LedgerService::class)->repayLoan($user,$loan,['account_id'=>$bank->id,'principal_amount'=>20000,'interest_amount'=>0,'paid_at'=>'2026-09-10']);
        $this->assertEquals(30000.0,(float)$loan->fresh()->outstanding_principal);
        $this->assertEquals(70000.0,$bank->fresh()->balance());
    }

    public function test_reversal_nets_the_immutable_ledger_to_zero(): void
    {
        $user = User::factory()->create();
        app(FinanceSetupService::class)->bootstrapUser($user);
        $user->refresh();

        $bank = LedgerAccount::create([
            'user_id' => $user->id,
            'currency_id' => $user->settings->base_currency_id,
            'kind' => LedgerAccountKind::Asset,
            'type' => LedgerAccountType::Bank,
            'name' => 'Reversal Bank',
        ]);
        $income = Category::forUser($user->id)->where('type', 'income')->firstOrFail();
        $ledger = app(LedgerService::class);

        $transaction = $ledger->income($user, [
            'destination_account_id' => $bank->id,
            'category_id' => $income->id,
            'amount' => 25000,
            'transaction_date' => '2026-09-01',
        ]);
        $ledger->reverse($user, $transaction, '2026-09-02');

        $this->assertEquals(0.0, $bank->fresh()->balance());
        $summary = app(ReportService::class)->summary($user, '2026-09-01', '2026-09-30');
        $this->assertEquals(0.0, $summary['income']);
    }

    public function test_loan_interest_is_included_in_income_report_without_counting_principal_as_income(): void
    {
        $user = User::factory()->create();
        app(FinanceSetupService::class)->bootstrapUser($user);
        $user->refresh();

        $bank = LedgerAccount::create([
            'user_id' => $user->id,
            'currency_id' => $user->settings->base_currency_id,
            'kind' => LedgerAccountKind::Asset,
            'type' => LedgerAccountType::Bank,
            'name' => 'Interest Bank',
        ]);
        app(LedgerService::class)->openingBalance($user, $bank, 100000, '2026-09-01');
        $person = Person::create(['user_id' => $user->id, 'name' => 'Ahmed']);
        $loan = app(LedgerService::class)->createLoan($user, [
            'person_id' => $person->id,
            'account_id' => $bank->id,
            'direction' => 'given',
            'principal' => 50000,
            'start_date' => '2026-09-02',
        ]);

        app(LedgerService::class)->repayLoan($user, $loan, [
            'account_id' => $bank->id,
            'principal_amount' => 10000,
            'interest_amount' => 1500,
            'paid_at' => '2026-09-10',
        ]);

        $summary = app(ReportService::class)->summary($user, '2026-09-01', '2026-09-30');
        $this->assertEquals(1500.0, $summary['income']);
        $this->assertEquals(0.0, $summary['expense']);
    }

}
