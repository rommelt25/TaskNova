<?php

namespace App\Observers;

use App\Models\Task;

class SubtaskCleanupObserver
{
    public function deleting(Task $task): void
    {
        $task->subtasks()->delete();
    }
}
