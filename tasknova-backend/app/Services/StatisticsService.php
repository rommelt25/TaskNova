<?php

namespace App\Services;

use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class StatisticsService
{
    /** @return array<string, mixed> */
    public function forUser(User $user): array
    {
        $tasks = Task::query()->where('user_id', $user->id);
        $totalTasks = (clone $tasks)->count();
        $completedTasks = (clone $tasks)->where('status', 'completed')->count();
        $pendingTasks = (clone $tasks)->where('status', '!=', 'completed')->count();
        $overdueTasks = (clone $tasks)
            ->where('status', '!=', 'completed')
            ->whereDate('due_date', '<', today())
            ->count();

        return [
            'total_tasks' => $totalTasks,
            'completed_tasks' => $completedTasks,
            'pending_tasks' => $pendingTasks,
            'overdue_tasks' => $overdueTasks,
            'completion_percentage' => $totalTasks === 0 ? 0 : round(($completedTasks / $totalTasks) * 100, 2),
            'tasks_per_category' => $this->tasksPerCategory($user),
            'tasks_per_priority' => $this->tasksPerPriority($user),
            'tasks_per_month' => $this->tasksPerMonth($user),
            'average_completion_time' => $this->averageCompletionTime($user),
            'streak_days' => $this->currentStreak($user),
            'longest_streak' => $this->longestStreak($user),
        ];
    }

    /** @return array<int, array{category: string, total: int}> */
    private function tasksPerCategory(User $user): array
    {
        return Task::query()
            ->leftJoin('categories', 'tasks.category_id', '=', 'categories.id')
            ->where('tasks.user_id', $user->id)
            ->selectRaw("COALESCE(categories.name, 'Sin categoría') as category, COUNT(*) as total")
            ->groupBy('tasks.category_id', 'categories.name')
            ->orderBy('category')
            ->get()
            ->map(fn (object $row) => ['category' => $row->category, 'total' => (int) $row->total])
            ->all();
    }

    /** @return array<int, array{priority: string, total: int}> */
    private function tasksPerPriority(User $user): array
    {
        return Task::query()
            ->where('user_id', $user->id)
            ->selectRaw('priority, COUNT(*) as total')
            ->groupBy('priority')
            ->orderBy('priority')
            ->get()
            ->map(fn (object $row) => ['priority' => $row->priority, 'total' => (int) $row->total])
            ->all();
    }

    /** @return array<int, array{month: string, total: int}> */
    private function tasksPerMonth(User $user): array
    {
        $monthExpression = DB::connection()->getDriverName() === 'pgsql'
            ? "to_char(tasks.created_at, 'YYYY-MM')"
            : "strftime('%Y-%m', tasks.created_at)";

        return Task::query()
            ->where('user_id', $user->id)
            ->selectRaw($monthExpression.' as month, COUNT(*) as total')
            ->groupByRaw($monthExpression)
            ->orderBy('month')
            ->get()
            ->map(fn (object $row) => ['month' => $row->month, 'total' => (int) $row->total])
            ->all();
    }

    private function averageCompletionTime(User $user): float
    {
        $durations = Task::query()
            ->where('user_id', $user->id)
            ->where('status', 'completed')
            ->get(['created_at', 'updated_at'])
            ->map(fn (Task $task) => $task->created_at?->diffInSeconds($task->updated_at) ?? 0);

        return $durations->isEmpty() ? 0 : round((float) $durations->avg(), 2);
    }

    private function currentStreak(User $user): int
    {
        $days = $this->completionDays($user);
        $cursor = today();

        if (! $days->has($cursor->toDateString())) {
            $cursor = $cursor->subDay();
        }

        $streak = 0;
        while ($days->has($cursor->toDateString())) {
            $streak++;
            $cursor = $cursor->subDay();
        }

        return $streak;
    }

    private function longestStreak(User $user): int
    {
        $dates = $this->completionDays($user)->keys()->sort()->values();
        $longest = 0;
        $current = 0;
        $previous = null;

        foreach ($dates as $date) {
            if ($previous !== null && $previous->copy()->addDay()->toDateString() === $date) {
                $current++;
            } else {
                $current = 1;
            }

            $longest = max($longest, $current);
            $previous = Carbon::parse($date);
        }

        return $longest;
    }

    /** @return Collection<string, true> */
    private function completionDays(User $user)
    {
        return Task::query()
            ->where('user_id', $user->id)
            ->where('status', 'completed')
            ->pluck('updated_at')
            ->filter()
            ->map(fn ($date) => Carbon::parse($date)->toDateString())
            ->flip()
            ->map(fn () => true);
    }
}
