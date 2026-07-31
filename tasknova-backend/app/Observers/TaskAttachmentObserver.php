<?php

namespace App\Observers;

use App\Models\TaskAttachment;
use Illuminate\Support\Facades\Storage;

class TaskAttachmentObserver
{
    public function deleting(TaskAttachment $attachment): void
    {
        Storage::disk('public')->delete($attachment->path);
    }
}
