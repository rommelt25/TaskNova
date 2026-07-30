<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Calendar\ListCalendarRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use Carbon\Carbon;

class CalendarController extends Controller
{
    public function index(ListCalendarRequest $request)
    {
        $filters = $request->validated();
        $month = Carbon::createFromFormat('Y-m', $filters['month'])->startOfMonth();

        $tasks = Task::query()
            ->with('category:id,user_id,name,color,icon')
            ->where('user_id', $request->user()->id)
            ->whereBetween('due_date', [$month->toDateString(), $month->copy()->endOfMonth()->toDateString()])
            ->when(isset($filters['category_id']), fn ($query) => $query->where('category_id', $filters['category_id']))
            ->when(isset($filters['priority']), fn ($query) => $query->where('priority', $filters['priority']))
            ->when(($filters['status'] ?? null) === 'overdue', fn ($query) => $query->where('status', '!=', 'completed')->whereDate('due_date', '<', today()))
            ->when(isset($filters['status']) && $filters['status'] !== 'overdue', fn ($query) => $query->where('status', $filters['status']))
            ->orderBy('due_date')
            ->orderBy('due_time')
            ->get();

        return TaskResource::collection($tasks);
    }
}
