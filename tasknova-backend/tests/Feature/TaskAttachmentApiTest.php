<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\TaskAttachment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TaskAttachmentApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_authorized_user_can_upload_list_download_and_delete_an_attachment(): void
    {
        Storage::fake('public');
        $owner = User::factory()->create();
        $task = Task::factory()->for($owner)->create();
        Sanctum::actingAs($owner);

        $response = $this->postJson("/api/tasks/{$task->id}/attachments", [
            'file' => UploadedFile::fake()->create('avance.pdf', 100, 'application/pdf'),
        ])->assertCreated()
            ->assertJsonPath('data.original_name', 'avance.pdf')
            ->assertJsonPath('data.extension', 'pdf')
            ->assertJsonPath('data.size', 102400);

        $attachmentId = $response->json('data.id');
        $path = $this->app->make(TaskAttachment::class)->findOrFail($attachmentId)->path;
        Storage::disk('public')->assertExists($path);

        $this->getJson("/api/tasks/{$task->id}/attachments")
            ->assertOk()
            ->assertJsonPath('data.0.id', $attachmentId);

        $this->get("/api/tasks/{$task->id}/attachments/{$attachmentId}/download")
            ->assertOk()
            ->assertDownload('avance.pdf');

        $this->deleteJson("/api/tasks/{$task->id}/attachments/{$attachmentId}")->assertNoContent();
        Storage::disk('public')->assertMissing($path);
        $this->assertDatabaseMissing('task_attachments', ['id' => $attachmentId]);
    }

    public function test_attachment_upload_rejects_invalid_extensions_and_files_larger_than_twenty_megabytes(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $task = Task::factory()->for($user)->create();
        Sanctum::actingAs($user);

        $this->postJson("/api/tasks/{$task->id}/attachments", [
            'file' => UploadedFile::fake()->create('malware.exe', 10, 'application/octet-stream'),
        ])->assertUnprocessable()->assertJsonValidationErrors('file');

        $this->postJson("/api/tasks/{$task->id}/attachments", [
            'file' => UploadedFile::fake()->create('muy-grande.pdf', 20481, 'application/pdf'),
        ])->assertUnprocessable()->assertJsonValidationErrors('file');
    }

    public function test_only_task_owner_or_shared_user_can_manage_attachments(): void
    {
        Storage::fake('public');
        $owner = User::factory()->create();
        $sharedUser = User::factory()->create();
        $outsider = User::factory()->create();
        $task = Task::factory()->for($owner)->create();
        $task->sharedWith()->attach($sharedUser->id);

        Sanctum::actingAs($outsider);
        $this->getJson("/api/tasks/{$task->id}/attachments")->assertForbidden();
        $this->postJson("/api/tasks/{$task->id}/attachments", [
            'file' => UploadedFile::fake()->create('archivo.pdf', 10, 'application/pdf'),
        ])->assertForbidden();

        Sanctum::actingAs($sharedUser);
        $response = $this->postJson("/api/tasks/{$task->id}/attachments", [
            'file' => UploadedFile::fake()->create('compartido.pdf', 10, 'application/pdf'),
        ])->assertCreated();

        $this->deleteJson("/api/tasks/{$task->id}/attachments/{$response->json('data.id')}")->assertNoContent();
    }

    public function test_deleting_a_task_removes_attachment_records_and_physical_files(): void
    {
        Storage::fake('public');
        $owner = User::factory()->create();
        $task = Task::factory()->for($owner)->create();
        Sanctum::actingAs($owner);

        $response = $this->postJson("/api/tasks/{$task->id}/attachments", [
            'file' => UploadedFile::fake()->create('evidencia.png', 25, 'image/png'),
        ])->assertCreated();

        $attachment = TaskAttachment::findOrFail($response->json('data.id'));
        Storage::disk('public')->assertExists($attachment->path);

        $this->deleteJson("/api/tasks/{$task->id}")->assertNoContent();

        Storage::disk('public')->assertMissing($attachment->path);
        $this->assertDatabaseMissing('task_attachments', ['id' => $attachment->id]);
    }
}
