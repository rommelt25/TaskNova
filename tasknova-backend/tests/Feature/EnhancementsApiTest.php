<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class EnhancementsApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_notifications_are_scoped_and_can_be_marked_read_or_deleted(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $notification = $user->appNotifications()->create([
            'title' => 'Recordatorio',
            'message' => 'Revisa tu tarea.',
            'type' => 'task_due_soon',
        ]);
        $otherNotification = $otherUser->appNotifications()->create([
            'title' => 'Privada',
            'message' => 'No visible.',
            'type' => 'task_due_soon',
        ]);

        Sanctum::actingAs($user);

        $this->getJson('/api/notifications')
            ->assertOk()
            ->assertJsonPath('data.0.id', $notification->id);

        $this->patchJson("/api/notifications/{$notification->id}/read")
            ->assertOk()
            ->assertJsonPath('data.read_at', fn ($readAt) => $readAt !== null);

        $this->patchJson('/api/notifications/read-all')
            ->assertOk()
            ->assertJsonPath('data.updated', 0);

        $this->deleteJson("/api/notifications/{$notification->id}")->assertNoContent();
        $this->deleteJson("/api/notifications/{$otherNotification->id}")->assertNotFound();
    }

    public function test_task_events_create_notifications_and_activity_entries(): void
    {
        $owner = User::factory()->create();
        $recipient = User::factory()->create();
        $task = Task::factory()->for($owner)->create(['title' => 'Entregar informe', 'status' => 'pending']);

        Sanctum::actingAs($owner);

        $this->patchJson("/api/tasks/{$task->id}/status", ['status' => 'completed'])->assertOk();
        $this->postJson("/api/tasks/{$task->id}/shares", ['email' => $recipient->email])->assertCreated();

        $this->assertDatabaseHas('notifications', [
            'user_id' => $owner->id,
            'type' => 'task_completed',
        ]);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $recipient->id,
            'type' => 'task_shared',
        ]);
        $this->assertDatabaseHas('activity_logs', ['user_id' => $owner->id, 'type' => 'task_completed']);
        $this->assertDatabaseHas('activity_logs', ['user_id' => $owner->id, 'type' => 'task_shared']);

        $this->getJson('/api/activity')->assertOk()->assertJsonPath('data.0.type', 'task_shared');
        $this->getJson('/api/activity/latest?limit=1')->assertOk()->assertJsonCount(1, 'data');
    }

    public function test_statistics_and_exports_contain_only_the_authenticated_users_information(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $category = $user->categories()->create(['name' => 'Trabajo', 'color' => '#2864E6']);
        Task::factory()->for($user)->create([
            'category_id' => $category->id,
            'status' => 'completed',
            'priority' => 'high',
            'due_date' => now()->subDay()->toDateString(),
        ]);
        Task::factory()->for($otherUser)->create();

        Sanctum::actingAs($user);

        $this->getJson('/api/statistics')
            ->assertOk()
            ->assertJsonPath('data.total_tasks', 1)
            ->assertJsonPath('data.completed_tasks', 1)
            ->assertJsonPath('data.overdue_tasks', 0)
            ->assertJsonPath('data.tasks_per_category.0.category', 'Trabajo');

        $this->getJson('/api/export/tasks/json')
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->get('/api/export/tasks/csv')
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $this->getJson('/api/export/categories/json')
            ->assertOk()
            ->assertJsonPath('data.0.id', $category->id);

        $this->getJson('/api/export/profile/json')
            ->assertOk()
            ->assertJsonPath('data.id', null);
    }

    public function test_trash_preferences_and_due_reminders_work_without_exposing_other_users_data(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $category = $user->categories()->create(['name' => 'Personal', 'color' => '#2864E6']);
        $task = Task::factory()->for($user)->create([
            'category_id' => $category->id,
            'title' => 'Pagar servicio',
            'due_date' => now()->toDateString(),
            'status' => 'pending',
        ]);
        $otherTask = Task::factory()->for($otherUser)->create();

        Artisan::call('notifications:send-task-reminders');
        $this->assertDatabaseHas('notifications', ['user_id' => $user->id, 'type' => 'task_due_soon']);

        Sanctum::actingAs($user);

        $this->getJson('/api/preferences')
            ->assertOk()
            ->assertJsonPath('data.timezone', 'America/Lima');
        $this->putJson('/api/preferences', ['theme' => 'dark', 'notifications_enabled' => false])
            ->assertOk()
            ->assertJsonPath('data.theme', 'dark')
            ->assertJsonPath('data.notifications_enabled', false);

        $task->delete();
        $otherTask->delete();

        $this->getJson('/api/trash/tasks')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $task->id);
        $this->postJson("/api/trash/tasks/{$task->id}/restore")
            ->assertOk()
            ->assertJsonPath('data.id', $task->id);
        $task->delete();
        $this->deleteJson("/api/trash/tasks/{$task->id}/force")->assertNoContent();

        $category->delete();
        $this->getJson('/api/trash/categories')
            ->assertOk()
            ->assertJsonPath('data.0.id', $category->id);
        $this->postJson("/api/trash/categories/{$category->id}/restore")->assertOk();
    }
}
