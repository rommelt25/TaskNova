<?php

namespace App\Observers;

use App\Models\Task;

class TaskAttachmentCleanupObserver
{
    public function deleting(Task $task): void
    {
        $task->attachments()->each(fn ($attachment) => $attachment->delete());
    }
}
