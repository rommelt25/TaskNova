<?php

namespace App\Console\Commands;

use App\Services\TaskNotificationService;
use Illuminate\Console\Command;

class SendTaskDueNotifications extends Command
{
    protected $signature = 'notifications:send-task-reminders';

    protected $description = 'Send due-soon and overdue task notifications.';

    public function handle(TaskNotificationService $notifications): int
    {
        $notifications->sendDueReminders();

        return self::SUCCESS;
    }
}
