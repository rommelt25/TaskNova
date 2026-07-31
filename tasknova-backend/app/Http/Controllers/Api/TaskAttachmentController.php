<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Task\StoreTaskAttachmentRequest;
use App\Http\Resources\TaskAttachmentResource;
use App\Models\Task;
use App\Models\TaskAttachment;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TaskAttachmentController extends Controller
{
    public function index(Task $task)
    {
        $this->authorize('view', $task);

        return TaskAttachmentResource::collection($task->attachments()->latest()->get());
    }

    public function store(StoreTaskAttachmentRequest $request, Task $task): JsonResponse
    {
        $this->authorize('view', $task);

        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());
        $storedName = Str::uuid()->toString().'.'.$extension;
        $path = 'tasks/'.$task->id.'/'.$storedName;

        Storage::disk('public')->putFileAs('tasks/'.$task->id, $file, $storedName);

        $attachment = $task->attachments()->create([
            'user_id' => $request->user()->id,
            'original_name' => $file->getClientOriginalName(),
            'stored_name' => $storedName,
            'mime_type' => $file->getMimeType() ?? 'application/octet-stream',
            'extension' => $extension,
            'size' => $file->getSize(),
            'disk' => 'public',
            'path' => $path,
        ]);

        return (new TaskAttachmentResource($attachment))->response()->setStatusCode(201);
    }

    public function destroy(Task $task, int $attachment): JsonResponse
    {
        $this->authorize('view', $task);
        $this->attachmentFor($task, $attachment)->delete();

        return response()->json(null, 204);
    }

    public function download(Task $task, int $attachment): StreamedResponse
    {
        $this->authorize('view', $task);
        $model = $this->attachmentFor($task, $attachment);

        abort_unless(Storage::disk('public')->exists($model->path), 404);

        return Storage::disk('public')->download($model->path, $model->original_name);
    }

    private function attachmentFor(Task $task, int $attachment): TaskAttachment
    {
        return $task->attachments()->findOrFail($attachment);
    }
}
