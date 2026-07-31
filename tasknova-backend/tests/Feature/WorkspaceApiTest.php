<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class WorkspaceApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_categories_are_scoped_to_the_authenticated_user_and_enforce_unique_names(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $otherUser->categories()->create(['name' => 'Privada', 'color' => '#2864E6']);
        Sanctum::actingAs($user);

        $categoryId = $this->postJson('/api/categories', ['name' => 'Estudios', 'color' => '#2864E6', 'icon' => '📚'])
            ->assertCreated()
            ->assertJsonPath('data.name', 'Estudios')
            ->json('data.id');

        $this->postJson('/api/categories', ['name' => 'Estudios', 'color' => '#2864E6'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('name');

        $this->getJson('/api/categories')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $categoryId);
    }

    public function test_calendar_dashboard_and_status_endpoint_use_authenticated_users_real_tasks(): void
    {
        $user = User::factory()->create();
        $category = $user->categories()->create(['name' => 'Trabajo', 'color' => '#8554F5']);
        $task = Task::factory()->for($user)->create([
            'title' => 'Preparar informe',
            'category_id' => $category->id,
            'subject' => $category->name,
            'status' => 'pending',
            'priority' => 'high',
            'due_date' => now()->addDay()->toDateString(),
            'due_time' => '09:30:00',
        ]);
        Sanctum::actingAs($user);

        $this->patchJson("/api/tasks/{$task->id}/status", ['status' => 'completed'])
            ->assertOk()
            ->assertJsonPath('data.status', 'completed');

        $task->refresh()->update(['status' => 'pending']);
        $month = $task->due_date->format('Y-m');
        $this->getJson("/api/calendar?month={$month}&category_id={$category->id}")
            ->assertOk()
            ->assertJsonPath('data.0.id', $task->id)
            ->assertJsonPath('data.0.category.name', 'Trabajo')
            ->assertJsonPath('data.0.due_time', '09:30');

        $this->getJson('/api/dashboard/summary')
            ->assertOk()
            ->assertJsonPath('data.total_tasks', 1)
            ->assertJsonPath('data.pending_tasks', 1);

        $this->getJson('/api/dashboard/upcoming-tasks')
            ->assertOk()
            ->assertJsonPath('data.0.id', $task->id);

        $this->getJson('/api/dashboard/recent-activity')
            ->assertOk()
            ->assertJsonPath('data.0.title', 'Preparar informe');
    }
}
