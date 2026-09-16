<?php

namespace App\Services;

use App\Enums\LedgerAccountType;
use App\Models\FinancialTransaction;
use App\Models\LedgerAccount;
use App\Models\SavingsGoal;
use App\Models\User;

class DashboardService
{
    public function __construct(private readonly ReportService $reports) {}

    public function build(User $user): array
    {
        $monthStart=now()->startOfMonth()->toDateString(); $monthEnd=now()->endOfMonth()->toDateString();
        $accounts=LedgerAccount::forUser($user->id)->moneyAccounts()->where('is_archived',false)->get()->map(fn($a)=>[
            'id'=>$a->id,'name'=>$a->name,'type'=>$a->type->value,'balance'=>round($a->balance(),2),'institution'=>$a->institution,'last_four'=>$a->last_four,'icon'=>$a->icon,'color'=>$a->color,
        ]);
        $savings=(float)$accounts->filter(fn($a)=>$a['type']===LedgerAccountType::Savings->value)->sum('balance');
        return [
            'net_worth'=>$this->reports->netWorth($user),
            'month'=>$this->reports->summary($user,$monthStart,$monthEnd),
            'accounts'=>$accounts->values(),
            'savings_total'=>$savings,
            'loans'=>$this->reports->loans($user),
            'budgets'=>$this->reports->budgets($user),
            'savings_goals'=>SavingsGoal::forUser($user->id)->with('account')->where('is_completed',false)->orderBy('target_date')->limit(5)->get()->append('progress_percent'),
            'recent_transactions'=>FinancialTransaction::forUser($user->id)->with(['category','person','sourceAccount','destinationAccount'])->latest('transaction_date')->latest('id')->limit(10)->get(),
        ];
    }
}
