<?php

namespace App\Http\Controllers\Web\Ajax;

use App\Http\Controllers\Controller;
use App\Models\FinancialTransaction;
use App\Models\LedgerAccount;
use App\Models\Loan;
use App\Models\SavingsGoal;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    public function transactionsCsv(Request $r):StreamedResponse
    {
        $q=FinancialTransaction::forUser($r->user()->id)->with(['category','person','sourceAccount','destinationAccount'])->orderBy('transaction_date');
        $q->when($r->from,fn($x,$v)=>$x->whereDate('transaction_date','>=',$v))->when($r->to,fn($x,$v)=>$x->whereDate('transaction_date','<=',$v));
        return response()->streamDownload(function()use($q){$h=fopen('php://output','w');fputcsv($h,['Date','Reference','Type','Amount','Category','Person','From','To','Description','Status']);$q->chunk(500,function($rows)use($h){foreach($rows as $t)fputcsv($h,[$t->transaction_date->toDateString(),$t->reference_no,$t->type->value,$t->amount,$t->category?->name,$t->person?->name,$t->sourceAccount?->name,$t->destinationAccount?->name,$t->description,$t->status]);});fclose($h);},'transactions.csv',['Content-Type'=>'text/csv']);
    }
    public function backup(Request $r)
    {
        $uid=$r->user()->id;
        return response()->json(['version'=>1,'exported_at'=>now()->toIso8601String(),'user'=>$r->user()->only(['name','email']),'settings'=>$r->user()->settings,'accounts'=>LedgerAccount::forUser($uid)->get(),'transactions'=>FinancialTransaction::forUser($uid)->with(['entries','tags'])->get(),'loans'=>Loan::forUser($uid)->with('payments')->get(),'savings_goals'=>SavingsGoal::forUser($uid)->with('allocations')->get()])->header('Content-Disposition', 'attachment; filename="myledger-backup-'.now()->format('Ymd-His').'.json"');
    }
}
