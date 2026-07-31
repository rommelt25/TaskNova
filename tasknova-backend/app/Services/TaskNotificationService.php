<?php

namespace App\Services;

use App\Models\SharedTask;
use App\Models\Task;
use App\Models\User;

class TaskNotificationService
{
    public function taskCompleted(Task $task): void
    {
        $this->notify($task->user, 'Tarea completada', 'Completaste la tarea "'.$task->title.'".', 'task_completed');
    }

    public function taskShared(SharedTask $sharedTask): void
    {
        $task = $sharedTask->task;
        $recipient = $sharedTask->user;

        if ($task === null || $recipient === null) {
            return;
        }

        $this->notify(
            $recipient,
            'Nueva tarea compartida',
            'Se compartió contigo la tarea "'.$task->title.'".',
            'task_shared'
        );
    }

    public function sendDueReminders(): void
    {
        Task::query()
            ->with('user.preference')
            ->where('status', '!=', 'completed')
            ->whereDate('due_date', '=', today())
            ->each(function (Task $task): void {
                $this->notifyOnce(
                    $task->user,
                    'Tarea próxima a vencer',
                    'La tarea "'.$task->title.'" vence hoy.',
                    'task_due_soon'
                );
            });

        Task::query()
            ->with('user.preference')
            ->where('status', '!=', 'completed')
            ->whereDate('due_date', '<', today())
            ->each(function (Task $task): void {
                $this->notifyOnce(
                    $task->user,
                    'Tarea vencida',
                    'La tarea "'.$task->title.'" está vencida.',
                    'task_overdue'
                );
            });
    }

    private function notify(?User $user, string $title, string $message, string $type): void
    {
        if ($user === null || $user->preference?->notifications_enabled === false) {
            return;
        }

        $user->appNotifications()->create([
            'title' => $title,
            'message' => $message,
            'type' => $type,
        ]);
    }

    private function notifyOnce(?User $user, string $title, string $message, string $type): void
    {
        if ($user === null || $user->preference?->notifications_enabled === false) {
            return;
        }

        $alreadyNotified = $user->appNotifications()
            ->where('type', $type)
            ->where('message', $message)
            ->exists();

        if (! $alreadyNotified) {
            $this->notify($user, $title, $message, $type);
        }
    }
}
