<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\SharedTask;
use App\Models\Task;
use App\Models\TaskAttachment;
use App\Observers\CategoryObserver;
use App\Observers\PersonalAccessTokenObserver;
use App\Observers\SharedTaskObserver;
use App\Observers\TaskAttachmentCleanupObserver;
use App\Observers\TaskAttachmentObserver;
use App\Observers\TaskObserver;
use App\Policies\TaskPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\PersonalAccessToken;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Task::class, TaskPolicy::class);
        Task::observe(TaskObserver::class);
        Task::observe(TaskAttachmentCleanupObserver::class);
        Category::observe(CategoryObserver::class);
        SharedTask::observe(SharedTaskObserver::class);
        TaskAttachment::observe(TaskAttachmentObserver::class);
        PersonalAccessToken::observe(PersonalAccessTokenObserver::class);
    }
}
