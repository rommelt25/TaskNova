<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;

class ActivityLogService
{
    /** @param array<string, mixed> $metadata */
    public function record(int $userId, string $type, string $description, ?Model $subject = null, array $metadata = []): ActivityLog
    {
        return ActivityLog::create([
            'user_id' => $userId,
            'type' => $type,
            'description' => $description,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'metadata' => $metadata ?: null,
        ]);
    }
}
