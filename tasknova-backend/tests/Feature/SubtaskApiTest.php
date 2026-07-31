<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SubtaskApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_authorized_user_can_manage_subtasks(): void
    {
        $owner = User::factory()->create();
        $task = Task::factory()->for($owner)->create();
        Sanctum::actingAs($owner);

        $created = $this->postJson("/api/tasks/{$task->id}/subtasks", [
            'title' => 'Investigar fuentes',
            'description' => 'Revisar referencias confiables.',
            'position' => 2,
        ])->assertCreated()
            ->assertJsonPath('data.task_id', $task->id)
            ->assertJsonPath('data.completed', false);

        $subtaskId = $created->json('data.id');

        $this->postJson("/api/tasks/{$task->id}/subtasks", [
            'title' => 'Preparar borrador',
            'position' => 1,
        ])->assertCreated();

        $this->getJson("/api/tasks/{$task->id}/subtasks")
            ->assertOk()
            ->assertJsonPath('data.0.title', 'Preparar borrador')
            ->assertJsonPath('data.1.id', $subtaskId);

        $this->getJson("/api/tasks/{$task->id}/subtasks/{$subtaskId}")
            ->assertOk()
            ->assertJsonPath('data.title', 'Investigar fuentes');

        $this->putJson("/api/tasks/{$task->id}/subtasks/{$subtaskId}", [
            'title' => 'Investigar fuentes actualizadas',
            'completed' => true,
            'position' => 0,
        ])->assertOk()
            ->assertJsonPath('data.completed', true)
            ->assertJsonPath('data.position', 0);

        $this->patchJson("/api/tasks/{$task->id}/subtasks/{$subtaskId}/status", ['completed' => false])
            ->assertOk()
            ->assertJsonPath('data.completed', false);

        $this->deleteJson("/api/tasks/{$task->id}/subtasks/{$subtaskId}")->assertNoContent();
        $this->assertDatabaseMissing('subtasks', ['id' => $subtaskId]);
    }

    public function test_subtask_validation_requires_a_title_and_valid_status_payload(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->for($user)->create();
        Sanctum::actingAs($user);

        $this->postJson("/api/tasks/{$task->id}/subtasks", ['title' => ''])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('title');

        $subtaskId = $this->postJson("/api/tasks/{$task->id}/subtasks", ['title' => 'Subtarea válida'])
            ->assertCreated()
            ->json('data.id');

        $this->patchJson("/api/tasks/{$task->id}/subtasks/{$subtaskId}/status", ['completed' => 'no'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('completed');
    }

    public function test_only_task_owner_or_shared_user_can_access_its_subtasks(): void
    {
        $owner = User::factory()->create();
        $sharedUser = User::factory()->create();
        $outsider = User::factory()->create();
        $task = Task::factory()->for($owner)->create();
        $task->sharedWith()->attach($sharedUser->id);

        Sanctum::actingAs($outsider);
        $this->getJson("/api/tasks/{$task->id}/subtasks")->assertForbidden();
        $this->postJson("/api/tasks/{$task->id}/subtasks", ['title' => 'No permitida'])->assertForbidden();

        Sanctum::actingAs($sharedUser);
        $subtaskId = $this->postJson("/api/tasks/{$task->id}/subtasks", ['title' => 'Subtarea compartida'])
            ->assertCreated()
            ->json('data.id');

        $this->patchJson("/api/tasks/{$task->id}/subtasks/{$subtaskId}/status", ['completed' => true])
            ->assertOk()
            ->assertJsonPath('data.completed', true);
    }

    public function test_subtasks_are_deleted_when_their_task_is_deleted(): void
    {
        $owner = User::factory()->create();
        $task = Task::factory()->for($owner)->create();
        $subtask = $task->subtasks()->create(['title' => 'Eliminar con tarea']);
        Sanctum::actingAs($owner);

        $this->deleteJson("/api/tasks/{$task->id}")->assertNoContent();

        $this->assertDatabaseMissing('subtasks', ['id' => $subtask->id]);
    }
}
