<?php

namespace App\Http\Controllers\Web\Ajax;

use App\Http\Controllers\Controller;
use App\Services\CalendarService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    public function __invoke(Request $request, CalendarService $service): JsonResponse
    {
        $start = $request->query('start');
        $end = $request->query('end');

        return response()->json($service->getEvents($request->user(), $start, $end));
    }
}
