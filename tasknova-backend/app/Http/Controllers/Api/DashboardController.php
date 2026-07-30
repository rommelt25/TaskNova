<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function summary(Request $request): JsonResponse
    {
        $tasks = Task::query()->where('user_id', $request->user()->id);
        $total = (clone $tasks)->count();
        $completed = (clone $tasks)->where('status', 'completed')->count();
        $pending = (clone $tasks)->where('status', '!=', 'completed')->count();
        $overdue = (clone $tasks)->where('status', '!=', 'completed')->whereDate('due_date', '<', today())->count();

        return response()->json(['data' => [
            'total_tasks' => $total,
            'pending_tasks' => $pending,
            'completed_tasks' => $completed,
            'overdue_tasks' => $overdue,
            'completion_percentage' => $total ? round(($completed / $total) * 100) : 0,
        ]]);
    }

    public function upcomingTasks(Request $request)
    {
        $tasks = Task::query()
            ->with('category:id,user_id,name,color,icon')
            ->where('user_id', $request->user()->id)
            ->where('status', '!=', 'completed')
            ->whereDate('due_date', '>=', today())
            ->orderBy('due_date')
            ->orderBy('due_time')
            ->limit(5)
            ->get();

        return TaskResource::collection($tasks);
    }

    public function recentActivity(Request $request): JsonResponse
    {
        $activities = Task::query()
            ->where('user_id', $request->user()->id)
            ->latest('updated_at')
            ->limit(8)
            ->get()
            ->map(fn (Task $task) => [
                'id' => $task->id,
                'title' => $task->title,
                'type' => $task->status === 'completed' ? 'completed' : ($task->created_at?->equalTo($task->updated_at) ? 'created' : 'updated'),
                'created_at' => $task->updated_at?->toISOString(),
            ]);

        return response()->json(['data' => $activities]);
    }
}
