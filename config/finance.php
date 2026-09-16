<?php

return [
    'base_currency' => env('FINANCE_BASE_CURRENCY', 'PKR'),
    'money_scale' => 2,
    'timezone' => env('FINANCE_TIMEZONE', 'Asia/Karachi'),
    'default_budget_alert_percent' => 80,
    'due_reminder_time' => env('FINANCE_DUE_REMINDER_TIME', '08:00'),
    'attachment_disk' => env('FINANCE_ATTACHMENT_DISK', 'local'),
    'attachment_max_kb' => 10240,
    'system_accounts' => [
        'opening_balance_equity' => 'Opening Balance Equity',
        'loan_receivable' => 'Loans Receivable',
        'loan_payable' => 'Loans Payable',
        'interest_income' => 'Interest Income',
        'interest_expense' => 'Interest Expense',
        'adjustment_equity' => 'Balance Adjustment',
    ],
];
