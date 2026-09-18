<?php

namespace App\Http\Controllers\Web\Ajax;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $r): JsonResponse
    {
        return response()->json($r->user()->notifications()->latest()->paginate(30));
    }

    public function read(Request $r, string $id): JsonResponse
    {
        $n = $r->user()->notifications()->where('id', $id)->firstOrFail();
        $n->markAsRead();
        return response()->json(['message' => 'Notification marked as read.']);
    }

    public function readAll(Request $r): JsonResponse
    {
        $r->user()->unreadNotifications->markAsRead();
        return response()->json(['message' => 'All notifications marked as read.']);
    }
}
