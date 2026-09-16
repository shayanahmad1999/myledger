<?php

namespace App\Http\Controllers\Web\Ajax;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuditController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json(
            AuditLog::where('user_id', $request->user()->id)
                ->latest()
                ->paginate(min(100, max(10, (int) $request->input('per_page', 20))))
        );
    }
}
