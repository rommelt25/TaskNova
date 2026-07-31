<?php

namespace App\Observers;

use App\Models\SharedTask;
use App\Services\ActivityLogService;
use App\Services\TaskNotificationService;

class SharedTaskObserver
{
    public function __construct(
        private readonly ActivityLogService $activityLogs,
        private readonly TaskNotificationService $notifications,
    ) {}

    public function created(SharedTask $sharedTask): void
    {
        $sharedTask->loadMissing(['task', 'user']);

        if ($sharedTask->task === null) {
            return;
        }

        $this->activityLogs->record(
            $sharedTask->task->user_id,
            'task_shared',
            'Compartiste la tarea "'.$sharedTask->task->title.'".',
            $sharedTask->task,
            ['recipient_id' => $sharedTask->user_id]
        );

        $this->notifications->taskShared($sharedTask);
    }
}
