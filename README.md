# MyLedger - Personal Finance Management System

A comprehensive personal finance application built with Laravel 11, featuring double-entry bookkeeping, multi-currency support, and modern UI.

## Features

### Core Finance
- **Double-Entry Bookkeeping** - Immutable ledger with balanced transactions
- **Multi-Currency Support** - Historical exchange rates with automatic base currency conversion
- **Accounts Management** - Bank, cash, mobile wallet, savings, investment accounts
- **Categories** - Hierarchical income/expense categories with budgets

### Transactions
- **Income / Expense / Transfer** - Full CRUD with account filtering by currency
- **Split Expenses** - Multiple categories in single transaction
- **Opening Balance** - Set initial account balances with automatic equity entry
- **Exchange Transactions** - Currency conversion between accounts with rate tracking
- **Reversals** - Immutable audit trail with reversal support

### Loan Management
- **Given/Taken Loans** - Track money lent and borrowed
- **Repayment Tracking** - Principal + interest with automatic outstanding calculation
- **Interest Types** - Simple, fixed, or none
- **Receipts** - Printable/shareable loan and repayment receipts

### Committees (ROSCA)
- **Rotating Savings Pools** - Member slots, turn schedules, contributions
- **Round Management** - Collection tracking, payout disbursement
- **Payment Recording** - Member contributions with ledger integration
- **Receipts** - Contribution and payout receipts

### Savings Goals
- **Goal Tracking** - Target amounts, deadlines, progress visualization
- **Move Funds** - Transfer between accounts and goals
- **Progress Reports** - Visual progress bars and statistics

### Reports
- **General Ledger** - Full transaction history with running balances
- **Party Ledger** - Per-person transaction history
- **Income/Expense Report** - Category breakdown with budgets
- **Cash Flow** - Inflow/outflow analysis
- **Net Worth** - Account balances over time
- **Multi-Currency View** - Dynamic currency selector with exchange rate conversion (persisted in localStorage)

### Settings
- **Currency Management** - Add/edit currencies with exchange rates
- **Exchange Rate History** - Historical rates per currency per date (modal UI)
- **Base Currency** - User preference with automatic defaulting
- **Categories & Budgets** - Full category management with monthly budgets

## Tech Stack

- **Backend**: Laravel 11, PHP 8.2+
- **Database**: MySQL/PostgreSQL (double-entry schema)
- **Frontend**: Bootstrap 5, Vanilla JS (MyLedger.js module)
- **Architecture**: Service layer (LedgerService), Enum-based types, Repository patterns

## Installation

```bash
git clone <repo>
cd myledger
composer install
npm install && npm run build
cp .env.example .env
# Configure DB, APP_URL, etc.
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

## Key Architecture

### LedgerService (`app/Services/LedgerService.php`)
Central transaction processor enforcing double-entry rules:
- `post()` - Core balanced transaction creation with multi-currency conversion
- `expense()` / `income()` / `transfer()` / `exchange()` / `openingBalance()` / `loanGiven()` / `loanRepayment()` / `reversal()`

### Exchange Rate System
- `exchange_rate_history` table - Historical rates (currency_id, rate_date, rate)
- `ExchangeRateHistory::getRate($from, $to, $date)` - Retrieves rate for date (falls back to current)
- `CurrencyController` - AJAX endpoints for history CRUD
- Automatic base currency conversion in `LedgerService::post()`

### Currency Handling
- Base currency from `users.settings.base_currency_id`
- Page-level `data-base-currency-id` attribute read by JS
- All currency dropdowns default to base currency
- Reports: dynamic selector with localStorage persistence (`reportCurrencyId`)

### Frontend (MyLedger.js)
- `M.request()` - Authenticated AJAX with CSRF
- `M.money(amount, symbol)` - Currency formatting
- `M.fillSelect()` - Dropdown population with label/placeholder/selected
- `M.renderCurrencyRateNotice()` - Live exchange rate display
- `M.getCurrencies()` - Cached currency list for rate notice

## Database Schema Highlights

- `ledger_accounts` - Chart of accounts (system + user, typed by kind/type)
- `financial_transactions` - Immutable transaction headers (type, amount, currency, exchange_rate, original_amount)
- `transaction_entries` - Double-entry lines (ledger_account_id, debit, credit)
- `exchange_rate_history` - Historical rates per currency per date
- `loans`, `loan_payments` - Loan tracking
- `committees`, `committee_members`, `committee_rounds`, `committee_payments` - ROSCA
- `savings_goals`, `savings_goal_moves` - Goal tracking
- `categories` - Hierarchical with budget_amount

## Testing

```bash
php artisan test
# 8 passing tests (LedgerService, Web Finance)
# 1 pre-existing unrelated failure (ExampleTest root route)
```

## License

MIT License