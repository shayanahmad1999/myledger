<?php

namespace App\Http\Controllers\Web\Ajax;

use App\Enums\LoanDirection;
use App\Http\Controllers\Controller;
use App\Models\Loan;
use App\Services\LedgerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LoanController extends Controller
{
    public function index(Request $r):JsonResponse{return response()->json(Loan::forUser($r->user()->id)->with(['person','currency'])->when($r->direction,fn($q,$v)=>$q->where('direction',$v))->when($r->status,fn($q,$v)=>$q->where('status',$v))->latest()->get());}
    public function store(Request $r,LedgerService $ledger):JsonResponse{$d=$r->validate(['person_id'=>'required|exists:people,id','account_id'=>'required|exists:ledger_accounts,id','direction'=>['required',Rule::enum(LoanDirection::class)],'title'=>'nullable|string|max:140','principal'=>'required|numeric|min:0.01','interest_rate'=>'nullable|numeric|min:0','interest_type'=>'nullable|in:none,simple,fixed','start_date'=>'required|date','due_date'=>'nullable|date|after_or_equal:start_date','description'=>'nullable|string|max:255','notes'=>'nullable|string|max:5000']);return response()->json($ledger->createLoan($r->user(),$d),201);}
    public function show(Request $r,Loan $loan):JsonResponse{$this->owned($r,$loan);return response()->json($loan->load(['person','currency','payments','transactions'=>fn($q)=>$q->latest('transaction_date')]));}
    public function repay(Request $r,Loan $loan,LedgerService $ledger):JsonResponse{$this->owned($r,$loan);$d=$r->validate(['account_id'=>'required|exists:ledger_accounts,id','principal_amount'=>'required|numeric|min:0.01','interest_amount'=>'nullable|numeric|min:0','paid_at'=>'required|date','description'=>'nullable|string|max:255']);return response()->json($ledger->repayLoan($r->user(),$loan,$d),201);}
    private function owned(Request $r,Loan $m):void{abort_unless($m->user_id===$r->user()->id,403);}
}
