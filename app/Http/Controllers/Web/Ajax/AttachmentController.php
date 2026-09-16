<?php

namespace App\Http\Controllers\Web\Ajax;

use App\Http\Controllers\Controller;
use App\Models\Attachment;
use App\Models\FinancialTransaction;
use App\Models\Loan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttachmentController extends Controller
{
    public function store(Request $r):JsonResponse
    {
        $d=$r->validate(['file'=>'required|file|max:'.config('finance.attachment_max_kb',10240),'financial_transaction_id'=>'nullable|exists:financial_transactions,id','loan_id'=>'nullable|exists:loans,id']);
        if(!empty($d['financial_transaction_id']))abort_unless(FinancialTransaction::findOrFail($d['financial_transaction_id'])->user_id===$r->user()->id,403);
        if(!empty($d['loan_id']))abort_unless(Loan::findOrFail($d['loan_id'])->user_id===$r->user()->id,403);
        $file=$r->file('file');$disk=config('finance.attachment_disk','local');$path=$file->store('finance/'.$r->user()->id.'/attachments',$disk);
        $a=Attachment::create(['user_id'=>$r->user()->id,'financial_transaction_id'=>$d['financial_transaction_id']??null,'loan_id'=>$d['loan_id']??null,'disk'=>$disk,'path'=>$path,'original_name'=>$file->getClientOriginalName(),'mime_type'=>$file->getMimeType(),'size'=>$file->getSize()]);
        return response()->json($a,201);
    }
    public function download(Request $r,Attachment $attachment):StreamedResponse{$this->owned($r,$attachment);return Storage::disk($attachment->disk)->download($attachment->path,$attachment->original_name);}
    public function destroy(Request $r,Attachment $attachment):JsonResponse{$this->owned($r,$attachment);Storage::disk($attachment->disk)->delete($attachment->path);$attachment->delete();return response()->json(['message'=>'Attachment deleted.']);}
    private function owned(Request $r,Attachment $m):void{abort_unless($m->user_id===$r->user()->id,403);}
}
