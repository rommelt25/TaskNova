<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ActivityLogResource;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $perPage = min(max((int) $request->integer('per_page', 15), 1), 100);

        return ActivityLogResource::collection(
            $request->user()->activityLogs()
                ->latest()
                ->paginate($perPage)
                ->withQueryString()
        );
    }

    public function latest(Request $request)
    {
        $limit = min(max((int) $request->integer('limit', 10), 1), 100);

        return ActivityLogResource::collection(
            $request->user()->activityLogs()->latest()->paginate($limit)->withQueryString()
        );
    }
}
