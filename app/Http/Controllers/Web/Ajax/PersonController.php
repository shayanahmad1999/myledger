<?php

namespace App\Http\Controllers\Web\Ajax;

use App\Http\Controllers\Controller;
use App\Models\Person;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PersonController extends Controller
{
    public function index(Request $r):JsonResponse{return response()->json(Person::forUser($r->user()->id)->withCount('loans')->orderBy('name')->get());}
    public function store(Request $r):JsonResponse{$d=$r->validate(['name'=>'required|string|max:120','phone'=>'nullable|string|max:40','email'=>'nullable|email|max:150','notes'=>'nullable|string|max:2000']);return response()->json(Person::create(array_merge($d,['user_id'=>$r->user()->id])),201);}
    public function update(Request $r,Person $person):JsonResponse{$this->owned($r,$person);$person->update($r->validate(['name'=>'sometimes|required|string|max:120','phone'=>'nullable|string|max:40','email'=>'nullable|email|max:150','notes'=>'nullable|string|max:2000']));return response()->json($person);}
    public function destroy(Request $r,Person $person):JsonResponse{$this->owned($r,$person);abort_if($person->loans()->where('status','active')->exists(),422,'A person with an active loan cannot be deleted.');$person->delete();return response()->json(['message'=>'Person deleted.']);}
    private function owned(Request $r,Person $m):void{abort_unless($m->user_id===$r->user()->id,403);}
}
