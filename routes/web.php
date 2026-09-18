<?php

use App\Http\Controllers\Web\Ajax\AccountController;
use App\Http\Controllers\Web\Ajax\AttachmentController;
use App\Http\Controllers\Web\Ajax\AuditController;
use App\Http\Controllers\Web\Ajax\BudgetController;
use App\Http\Controllers\Web\Ajax\CalendarController;
use App\Http\Controllers\Web\Ajax\CategoryController;
use App\Http\Controllers\Web\Ajax\CommitteeController;
use App\Http\Controllers\Web\Ajax\CurrencyController;
use App\Http\Controllers\Web\Ajax\DashboardController;
use App\Http\Controllers\Web\Ajax\ExportController;
use App\Http\Controllers\Web\Ajax\GlobalSearchController;
use App\Http\Controllers\Web\Ajax\LoanController;
use App\Http\Controllers\Web\Ajax\NotificationController;
use App\Http\Controllers\Web\Ajax\PersonController;
use App\Http\Controllers\Web\Ajax\RecurringTransactionController;
use App\Http\Controllers\Web\Ajax\ReportController;
use App\Http\Controllers\Web\Ajax\SavingsGoalController;
use App\Http\Controllers\Web\Ajax\SettingsController;
use App\Http\Controllers\Web\Ajax\TagController;
use App\Http\Controllers\Web\Ajax\TransactionController;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\PageController;
use App\Http\Controllers\Web\ProfileController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'loginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
    Route::get('/register', [AuthController::class, 'registerForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/', fn() => redirect()->route('dashboard'));
    Route::get('/dashboard', [PageController::class, 'dashboard'])->name('dashboard');
    Route::get('/transactions', [PageController::class, 'transactions'])->name('transactions');
    Route::get('/accounts', [PageController::class, 'accounts'])->name('accounts');
    Route::get('/people', [PageController::class, 'people'])->name('people');
    Route::get('/loans', [PageController::class, 'loans'])->name('loans');
    Route::get('/savings', [PageController::class, 'savings'])->name('savings');
    Route::get('/committees', [PageController::class, 'committees'])->name('committees');
    Route::get('/calendar', [PageController::class, 'calendar'])->name('calendar');
    Route::get('/budgets', [PageController::class, 'budgets'])->name('budgets');
    Route::get('/recurring', [PageController::class, 'recurring'])->name('recurring');
    Route::get('/categories', [PageController::class, 'categories'])->name('categories');
    Route::get('/reports', [PageController::class, 'reports'])->name('reports');
    Route::get('/notifications', [PageController::class, 'notifications'])->name('notifications');
    Route::get('/settings', [PageController::class, 'settings'])->name('settings');

    Route::prefix('ajax')->name('ajax.')->group(function () {
        Route::get('/dashboard', DashboardController::class)->name('dashboard');

        Route::resource('accounts', AccountController::class)->except(['create', 'edit']);
        Route::resource('categories', CategoryController::class)->except(['create', 'edit', 'show']);
        Route::resource('people', PersonController::class)->except(['create', 'edit', 'show']);
        Route::resource('budgets', BudgetController::class)->except(['create', 'edit', 'show']);
        Route::resource('tags', TagController::class)->except(['create', 'edit', 'show']);
        Route::resource('recurring-transactions', RecurringTransactionController::class)
            ->except(['create', 'edit', 'show'])
            ->parameters(['recurring-transactions' => 'recurring']);

        Route::get('/transactions', [TransactionController::class, 'index']);
        Route::get('/transactions/{transaction}', [TransactionController::class, 'show']);
        Route::post('/transactions/expense', [TransactionController::class, 'expense']);
        Route::post('/transactions/income', [TransactionController::class, 'income']);
        Route::post('/transactions/transfer', [TransactionController::class, 'transfer']);
        Route::post('/transactions/{transaction}/reverse', [TransactionController::class, 'reverse']);

        Route::get('/loans', [LoanController::class, 'index']);
        Route::post('/loans', [LoanController::class, 'store']);
        Route::get('/loans/{loan}', [LoanController::class, 'show']);
        Route::patch('/loans/{loan}', [LoanController::class, 'update']);
        Route::post('/loans/{loan}/repay', [LoanController::class, 'repay']);

        Route::get('/savings-goals', [SavingsGoalController::class, 'index']);
        Route::post('/savings-goals', [SavingsGoalController::class, 'store']);
        Route::patch('/savings-goals/{goal}', [SavingsGoalController::class, 'update']);
        Route::post('/savings-goals/{goal}/contribute', [SavingsGoalController::class, 'contribute']);
        Route::post('/savings-goals/{goal}/withdraw', [SavingsGoalController::class, 'withdraw']);

        Route::get('/committees', [CommitteeController::class, 'index']);
        Route::post('/committees', [CommitteeController::class, 'store']);
        Route::get('/committees/{committee}', [CommitteeController::class, 'show']);
        Route::delete('/committees/{committee}', [CommitteeController::class, 'destroy']);
        Route::post('/committees/rounds/{round}/members/{member}/payment', [CommitteeController::class, 'recordPayment']);
        Route::post('/committees/rounds/{round}/payout', [CommitteeController::class, 'disbursePayout']);

        Route::post('/transactions/split', [TransactionController::class, 'split']);

        Route::get('/calendar-events', CalendarController::class);
        Route::get('/search', GlobalSearchController::class);
        Route::get('/currencies', [CurrencyController::class, 'index']);
        Route::post('/currencies', [CurrencyController::class, 'store']);
        Route::patch('/currencies/{currency}', [CurrencyController::class, 'update']);
        Route::get('/currencies/{currency}/history', [CurrencyController::class, 'history']);
        Route::post('/currencies/{currency}/history', [CurrencyController::class, 'storeHistory']);
        Route::delete('/currencies/{currency}/history/{history}', [CurrencyController::class, 'destroyHistory']);

        Route::prefix('reports')->group(function () {
            Route::get('/summary', [ReportController::class, 'summary']);
            Route::get('/expense-by-category', [ReportController::class, 'categories']);
            Route::get('/monthly-trend', [ReportController::class, 'trend']);
            Route::get('/net-worth', [ReportController::class, 'netWorth']);
            Route::get('/cash-flow', [ReportController::class, 'cashFlow']);
            Route::get('/loans', [ReportController::class, 'loans']);
            Route::get('/committees', [ReportController::class, 'committees']);
            Route::get('/trial-balance', [ReportController::class, 'trialBalance']);
            Route::get('/general-ledger', [ReportController::class, 'generalLedger']);
            Route::get('/smart-insights', [ReportController::class, 'smartInsights']);
            Route::get('/balance-sheet', [ReportController::class, 'balanceSheet']);
            Route::get('/profit-and-loss', [ReportController::class, 'profitAndLoss']);
            Route::get('/party-ledger', [ReportController::class, 'partyLedger']);
            Route::get('/comparison', [ReportController::class, 'comparison']);
            Route::get('/burn-rate', [ReportController::class, 'burnRate']);
        });

        Route::get('/audit-log', [AuditController::class, 'index']);

        Route::get('/settings', [SettingsController::class, 'show']);
        Route::patch('/settings', [SettingsController::class, 'update']);
        Route::patch('/profile', [ProfileController::class, 'update']);
        Route::put('/profile/password', [ProfileController::class, 'password']);

        Route::post('/attachments', [AttachmentController::class, 'store']);
        Route::get('/attachments/{attachment}/download', [AttachmentController::class, 'download']);
        Route::delete('/attachments/{attachment}', [AttachmentController::class, 'destroy']);

        Route::get('/notifications', [NotificationController::class, 'index']);
        Route::post('/notifications/read-all', [NotificationController::class, 'readAll']);
        Route::post('/notifications/{id}/read', [NotificationController::class, 'read']);
    });

    Route::get('/exports/transactions.csv', [ExportController::class, 'transactionsCsv'])->name('exports.transactions');
    Route::get('/exports/backup.json', [ExportController::class, 'backup'])->name('exports.backup');
});
