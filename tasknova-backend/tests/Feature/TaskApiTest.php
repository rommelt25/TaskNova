<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TaskApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_authenticated_user_can_create_update_and_delete_a_task(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $createdTask = $this->postJson('/api/tasks', [
            'title' => 'Entregar avance',
            'description' => 'Preparar el backend',
            'subject' => 'Desarrollo de aplicaciones',
            'priority' => 'high',
            'status' => 'pending',
            'due_date' => '2026-08-01',
        ])
            ->assertCreated()
            ->assertJsonPath('data.access', 'owner')
            ->assertJsonPath('data.can_manage', true);

        $taskId = $createdTask->json('data.id');

        $this->patchJson("/api/tasks/{$taskId}", [
            'status' => 'completed',
        ])
            ->assertOk()
            ->assertJsonPath('data.status', 'completed');

        $this->deleteJson("/api/tasks/{$taskId}")->assertNoContent();
        $this->assertDatabaseMissing('tasks', ['id' => $taskId]);
    }

    public function test_a_task_can_be_shared_for_read_only_access(): void
    {
        $owner = User::factory()->create();
        $recipient = User::factory()->create();
        $task = Task::factory()->for($owner)->create(['title' => 'Tarea compartida']);

        Sanctum::actingAs($owner);

        $this->postJson("/api/tasks/{$task->id}/shares", ['email' => $recipient->email])
            ->assertCreated()
            ->assertJsonPath('data.shared_with.0.id', $recipient->id);

        Sanctum::actingAs($recipient);

        $this->getJson('/api/tasks?scope=shared')
            ->assertOk()
            ->assertJsonPath('data.0.id', $task->id)
            ->assertJsonPath('data.0.access', 'shared')
            ->assertJsonPath('data.0.can_manage', false);

        $this->getJson("/api/tasks/{$task->id}")->assertOk();
        $this->patchJson("/api/tasks/{$task->id}", ['status' => 'completed'])->assertForbidden();
    }

    public function test_a_user_cannot_view_a_task_that_was_not_shared(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $task = Task::factory()->for($owner)->create();

        Sanctum::actingAs($otherUser);

        $this->getJson("/api/tasks/{$task->id}")->assertForbidden();
    }
}
