<?php

namespace App\Observers;

use App\Models\User;
use App\Services\ActivityLogService;
use Laravel\Sanctum\PersonalAccessToken;

class PersonalAccessTokenObserver
{
    public function __construct(private readonly ActivityLogService $activityLogs) {}

    public function created(PersonalAccessToken $token): void
    {
        if ($token->tokenable_type !== User::class) {
            return;
        }

        $this->activityLogs->record((int) $token->tokenable_id, 'login', 'Iniciaste sesión.');
    }

    public function deleted(PersonalAccessToken $token): void
    {
        if ($token->tokenable_type !== User::class) {
            return;
        }

        $this->activityLogs->record((int) $token->tokenable_id, 'logout', 'Cerraste sesión.');
    }
}
