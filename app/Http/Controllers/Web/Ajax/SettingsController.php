<?php

namespace App\Http\Controllers\Web\Ajax;

use App\Http\Controllers\Controller;
use App\Models\UserSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function show(Request $r):JsonResponse{return response()->json($r->user()->settings);}
    public function update(Request $r):JsonResponse{$d=$r->validate(['theme'=>'sometimes|in:system,light,dark','locale'=>'sometimes|string|max:10','daily_reminder_time'=>'nullable|date_format:H:i','preferences'=>'nullable|array']);$r->user()->settings()->update($d);return response()->json($r->user()->settings->fresh());}
}
