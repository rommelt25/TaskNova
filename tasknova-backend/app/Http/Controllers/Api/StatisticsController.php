<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\StatisticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StatisticsController extends Controller
{
    public function show(Request $request, StatisticsService $statistics): JsonResponse
    {
        return response()->json(['data' => $statistics->forUser($request->user())]);
    }
}
