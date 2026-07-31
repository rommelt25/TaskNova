<?php

namespace App\Observers;

use App\Models\Task;
use App\Services\ActivityLogService;
use App\Services\TaskNotificationService;

class TaskObserver
{
    public function __construct(
        private readonly ActivityLogService $activityLogs,
        private readonly TaskNotificationService $notifications,
    ) {}

    public function created(Task $task): void
    {
        $this->activityLogs->record($task->user_id, 'task_created', 'Creaste la tarea "'.$task->title.'".', $task);
    }

    public function updated(Task $task): void
    {
        if ($task->wasChanged('status') && $task->status === 'completed') {
            $this->activityLogs->record($task->user_id, 'task_completed', 'Completaste la tarea "'.$task->title.'".', $task);
            $this->notifications->taskCompleted($task);

            return;
        }

        $this->activityLogs->record($task->user_id, 'task_updated', 'Editaste la tarea "'.$task->title.'".', $task);
    }

    public function deleted(Task $task): void
    {
        $this->activityLogs->record($task->user_id, 'task_deleted', 'Eliminaste la tarea "'.$task->title.'".', $task);
    }
}
