<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Task\StoreSubtaskRequest;
use App\Http\Requests\Task\UpdateSubtaskRequest;
use App\Http\Requests\Task\UpdateSubtaskStatusRequest;
use App\Http\Resources\SubtaskResource;
use App\Models\Subtask;
use App\Models\Task;
use Illuminate\Http\JsonResponse;

class SubtaskController extends Controller
{
    public function index(Task $task)
    {
        $this->authorize('view', $task);

        return SubtaskResource::collection($task->subtasks()->orderBy('position')->orderBy('id')->get());
    }

    public function store(StoreSubtaskRequest $request, Task $task): JsonResponse
    {
        $this->authorize('view', $task);
        $subtask = $task->subtasks()->create($request->validated());

        return (new SubtaskResource($subtask))->response()->setStatusCode(201);
    }

    public function show(Task $task, int $subtask): SubtaskResource
    {
        $this->authorize('view', $task);

        return new SubtaskResource($this->subtaskFor($task, $subtask));
    }

    public function update(UpdateSubtaskRequest $request, Task $task, int $subtask): SubtaskResource
    {
        $this->authorize('view', $task);
        $model = $this->subtaskFor($task, $subtask);
        $model->update($request->validated());

        return new SubtaskResource($model);
    }

    public function updateStatus(UpdateSubtaskStatusRequest $request, Task $task, int $subtask): SubtaskResource
    {
        $this->authorize('view', $task);
        $model = $this->subtaskFor($task, $subtask);
        $model->update($request->validated());

        return new SubtaskResource($model);
    }

    public function destroy(Task $task, int $subtask): JsonResponse
    {
        $this->authorize('view', $task);
        $this->subtaskFor($task, $subtask)->delete();

        return response()->json(null, 204);
    }

    private function subtaskFor(Task $task, int $subtask): Subtask
    {
        return $task->subtasks()->findOrFail($subtask);
    }
}
