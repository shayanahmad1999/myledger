<?php

namespace App\Http\Controllers\Web\Ajax;

use App\Enums\LedgerAccountKind;
use App\Enums\LedgerAccountType;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\LedgerAccount;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index(Request $request): JsonResponse { return response()->json(Category::forUser($request->user()->id)->with('children')->when($request->type,fn($q,$v)=>$q->where('type',$v))->where('is_active',true)->orderBy('name')->get()); }
    public function store(Request $request): JsonResponse
    {
        $data=$request->validate(['name'=>'required|string|max:100','type'=>['required',Rule::in(['income','expense'])],'parent_id'=>'nullable|integer|exists:categories,id','icon'=>'nullable|string|max:50','color'=>'nullable|string|max:20']);
        if(!empty($data['parent_id'])){ $p=Category::findOrFail($data['parent_id']); abort_unless($p->user_id===$request->user()->id && $p->type===$data['type'],422,'Parent category must belong to you and have the same type.'); }
        $settings=$request->user()->settings; abort_unless($settings,422,'User finance settings are missing.');
        $category=DB::transaction(function()use($request,$data,$settings){
            $ledger=LedgerAccount::create(['user_id'=>$request->user()->id,'currency_id'=>$settings->base_currency_id,'kind'=>$data['type']==='expense'?LedgerAccountKind::Expense:LedgerAccountKind::Income,'type'=>LedgerAccountType::Category,'name'=>$data['name'],'is_system'=>true,'include_in_net_worth'=>false,'icon'=>$data['icon']??null,'color'=>$data['color']??null]);
            return Category::create(array_merge($data,['user_id'=>$request->user()->id,'ledger_account_id'=>$ledger->id]));
        });
        return response()->json($category,201);
    }
    public function update(Request $request, Category $category): JsonResponse
    {
        $this->owned($request,$category); $data=$request->validate(['name'=>'sometimes|required|string|max:100','icon'=>'nullable|string|max:50','color'=>'nullable|string|max:20','is_active'=>'sometimes|boolean']);
        DB::transaction(function()use($category,$data){$category->update($data); if(isset($data['name']))$category->ledgerAccount()->update(['name'=>$data['name']]);}); return response()->json($category->fresh());
    }
    public function destroy(Request $request, Category $category): JsonResponse { $this->owned($request,$category); $category->update(['is_active'=>false]); return response()->json(['message'=>'Category archived.']); }
    private function owned(Request $r, Category $m):void{abort_unless($m->user_id===$r->user()->id,403);}
}
