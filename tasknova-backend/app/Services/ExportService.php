<?php

namespace App\Services;

use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Collection;

class ExportService
{
    /** @return Collection<int, array<string, mixed>> */
    public function tasks(User $user): Collection
    {
        return Task::query()
            ->with('category:id,name')
            ->where('user_id', $user->id)
            ->orderBy('id')
            ->get()
            ->map(fn (Task $task) => [
                'id' => $task->id,
                'title' => $task->title,
                'description' => $task->description,
                'subject' => $task->subject,
                'category_id' => $task->category_id,
                'category' => $task->category?->name,
                'priority' => $task->priority,
                'status' => $task->status,
                'due_date' => $task->due_date?->toDateString(),
                'due_time' => $task->due_time?->format('H:i'),
                'created_at' => $task->created_at?->toISOString(),
                'updated_at' => $task->updated_at?->toISOString(),
            ]);
    }

    /** @return Collection<int, array<string, mixed>> */
    public function categories(User $user): Collection
    {
        return $user->categories()
            ->orderBy('id')
            ->get()
            ->map(fn ($category) => [
                'id' => $category->id,
                'name' => $category->name,
                'color' => $category->color,
                'icon' => $category->icon,
                'created_at' => $category->created_at?->toISOString(),
                'updated_at' => $category->updated_at?->toISOString(),
            ]);
    }

    /** @return array<string, mixed> */
    public function profile(User $user): array
    {
        $profile = $user->profile;

        return [
            'id' => $profile?->id,
            'first_name' => $profile?->first_name,
            'last_name' => $profile?->last_name,
            'phone' => $profile?->phone,
            'birth_date' => $profile?->birth_date?->toDateString(),
            'gender' => $profile?->gender,
            'institution' => $profile?->institution,
            'education_level' => $profile?->education_level,
            'career' => $profile?->career,
            'grade' => $profile?->grade,
            'academic_cycle' => $profile?->academic_cycle,
            'department' => $profile?->department,
            'province' => $profile?->province,
            'district' => $profile?->district,
            'avatar' => $profile?->avatar,
            'created_at' => $profile?->created_at?->toISOString(),
            'updated_at' => $profile?->updated_at?->toISOString(),
        ];
    }
}
