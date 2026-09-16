<?php

namespace App\Http\Controllers\Web\Ajax;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TagController extends Controller
{
    public function index(Request $r):JsonResponse{return response()->json(Tag::where('user_id',$r->user()->id)->orderBy('name')->get());}
    public function store(Request $r):JsonResponse{$d=$r->validate(['name'=>'required|string|max:80','color'=>'nullable|string|max:20']);return response()->json(Tag::create(array_merge($d,['user_id'=>$r->user()->id])),201);}
    public function update(Request $r,Tag $tag):JsonResponse{$this->owned($r,$tag);$tag->update($r->validate(['name'=>'sometimes|required|string|max:80','color'=>'nullable|string|max:20']));return response()->json($tag);}
    public function destroy(Request $r,Tag $tag):JsonResponse{$this->owned($r,$tag);$tag->delete();return response()->json(['message'=>'Tag deleted.']);}
    private function owned(Request $r,Tag $m):void{abort_unless($m->user_id===$r->user()->id,403);}
}
