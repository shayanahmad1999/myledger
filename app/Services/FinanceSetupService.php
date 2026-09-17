<?php

namespace App\Services;

use App\Enums\LedgerAccountKind;
use App\Enums\LedgerAccountType;
use App\Models\Category;
use App\Models\Currency;
use App\Models\LedgerAccount;
use App\Models\User;
use App\Models\UserSetting;

class FinanceSetupService
{
    public function bootstrapUser(User $user, ?int $currencyId = null): void
    {
        $currency = $currencyId ? Currency::find($currencyId) : null;
        if (!$currency) {
            $currency = Currency::firstOrCreate(
                ['code' => config('finance.base_currency', 'PKR')],
                ['name' => 'Pakistani Rupee', 'symbol' => 'Rs', 'decimal_places' => 2, 'is_active' => true]
            );
        }

        UserSetting::firstOrCreate(
            ['user_id' => $user->id],
            ['base_currency_id' => $currency->id]
        );

        foreach ($this->systemDefinitions() as $key => $def) {
            LedgerAccount::firstOrCreate(
                ['user_id' => $user->id, 'type' => $def['type'], 'name' => $def['name'], 'is_system' => true],
                [
                    'currency_id' => $currency->id,
                    'kind' => $def['kind'],
                    'include_in_net_worth' => $def['include_in_net_worth'],
                ]
            );
        }

        LedgerAccount::firstOrCreate(
            ['user_id' => $user->id, 'type' => LedgerAccountType::Cash->value, 'name' => 'Cash in Hand', 'is_system' => false],
            ['currency_id' => $currency->id, 'kind' => LedgerAccountKind::Asset->value, 'include_in_net_worth' => true, 'icon' => 'wallet']
        );

        foreach ($this->defaultCategories() as $category) {
            $ledger = LedgerAccount::firstOrCreate(
                ['user_id' => $user->id, 'type' => LedgerAccountType::Category->value, 'name' => $category['name'], 'is_system' => true],
                ['currency_id' => $currency->id, 'kind' => $category['type'] === 'expense' ? LedgerAccountKind::Expense->value : LedgerAccountKind::Income->value, 'include_in_net_worth' => false, 'icon' => $category['icon']]
            );
            Category::firstOrCreate(
                ['user_id' => $user->id, 'type' => $category['type'], 'name' => $category['name']],
                ['ledger_account_id' => $ledger->id, 'icon' => $category['icon'], 'is_active' => true]
            );
        }
    }

    public function systemAccount(User|int $user, string $type): LedgerAccount
    {
        $userId = $user instanceof User ? $user->id : $user;
        $account = LedgerAccount::where('user_id', $userId)->where('type', $type)->where('is_system', true)->first();
        abort_unless($account, 422, "Required system account [$type] is missing.");
        return $account;
    }

    private function defaultCategories(): array
    {
        return [
            ['type' => 'income', 'name' => 'Salary', 'icon' => 'payments'],
            ['type' => 'income', 'name' => 'Freelancing', 'icon' => 'work'],
            ['type' => 'income', 'name' => 'Business Income', 'icon' => 'business_center'],
            ['type' => 'income', 'name' => 'Other Income', 'icon' => 'add_circle'],
            ['type' => 'expense', 'name' => 'Food & Groceries', 'icon' => 'restaurant'],
            ['type' => 'expense', 'name' => 'Transport & Fuel', 'icon' => 'directions_car'],
            ['type' => 'expense', 'name' => 'Home & Utilities', 'icon' => 'home'],
            ['type' => 'expense', 'name' => 'Health', 'icon' => 'medical_services'],
            ['type' => 'expense', 'name' => 'Education', 'icon' => 'school'],
            ['type' => 'expense', 'name' => 'Shopping', 'icon' => 'shopping_bag'],
            ['type' => 'expense', 'name' => 'Entertainment', 'icon' => 'movie'],
            ['type' => 'expense', 'name' => 'Other Expense', 'icon' => 'more_horiz'],
        ];
    }

    private function systemDefinitions(): array
    {
        return [
            'opening' => [
                'type' => LedgerAccountType::OpeningBalanceEquity->value,
                'name' => config('finance.system_accounts.opening_balance_equity'),
                'kind' => LedgerAccountKind::Equity->value,
                'include_in_net_worth' => false,
            ],

            'receivable' => [
                'type' => LedgerAccountType::LoanReceivable->value,
                'name' => config('finance.system_accounts.loan_receivable'),
                'kind' => LedgerAccountKind::Asset->value,
                'include_in_net_worth' => true,
            ],

            'payable' => [
                'type' => LedgerAccountType::LoanPayable->value,
                'name' => config('finance.system_accounts.loan_payable'),
                'kind' => LedgerAccountKind::Liability->value,
                'include_in_net_worth' => true,
            ],

            'interest_income' => [
                'type' => LedgerAccountType::InterestIncome->value,
                'name' => config('finance.system_accounts.interest_income'),
                'kind' => LedgerAccountKind::Income->value,
                'include_in_net_worth' => false,
            ],

            'interest_expense' => [
                'type' => LedgerAccountType::InterestExpense->value,
                'name' => config('finance.system_accounts.interest_expense'),
                'kind' => LedgerAccountKind::Expense->value,
                'include_in_net_worth' => false,
            ],

            'adjustment' => [
                'type' => LedgerAccountType::AdjustmentEquity->value,
                'name' => config('finance.system_accounts.adjustment_equity'),
                'kind' => LedgerAccountKind::Equity->value,
                'include_in_net_worth' => false,
            ],
        ];
    }
}
