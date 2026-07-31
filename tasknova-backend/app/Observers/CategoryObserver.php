<?php

namespace App\Observers;

use App\Models\Category;
use App\Services\ActivityLogService;

class CategoryObserver
{
    public function __construct(private readonly ActivityLogService $activityLogs) {}

    public function created(Category $category): void
    {
        $this->activityLogs->record($category->user_id, 'category_created', 'Creaste la categoría "'.$category->name.'".', $category);
    }

    public function updated(Category $category): void
    {
        $this->activityLogs->record($category->user_id, 'category_updated', 'Editaste la categoría "'.$category->name.'".', $category);
    }

    public function deleted(Category $category): void
    {
        $this->activityLogs->record($category->user_id, 'category_deleted', 'Eliminaste la categoría "'.$category->name.'".', $category);
    }
}
