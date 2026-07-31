<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Task\ListTasksRequest;
use App\Http\Requests\Task\ShareTaskRequest;
use App\Http\Requests\Task\StoreTaskRequest;
use App\Http\Requests\Task\UpdateTaskRequest;
use App\Http\Requests\Task\UpdateTaskStatusRequest;
use App\Http\Resources\TaskResource;
use App\Http\Resources\UserResource;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class TaskController extends Controller
{
    public function index(ListTasksRequest $request)
    {
        $filters = $request->validated();
        $user = $request->user();
        $scope = $filters['scope'] ?? 'all';

        $sort = $filters['sort'] ?? 'due_date';
        $direction = $filters['direction'] ?? 'asc';

        $tasks = Task::query()
            ->with([
                'user:id,name,email,created_at',
                'sharedWith:id,name,email,created_at',
                'category:id,user_id,name,color,icon',
            ])
            ->when($scope === 'owned', fn ($query) => $query->where('user_id', $user->id))
            ->when($scope === 'shared', fn ($query) => $query->whereHas(
                'sharedWith',
                fn ($sharedWith) => $sharedWith->whereKey($user->id)
            ))
            ->when($scope === 'all', function ($query) use ($user) {
                $query->where(function ($accessibleTasks) use ($user) {
                    $accessibleTasks
                        ->where('user_id', $user->id)
                        ->orWhereHas('sharedWith', fn ($sharedWith) => $sharedWith->whereKey($user->id));
                });
            })
            ->when(($filters['status'] ?? null) === 'overdue', fn ($query) => $query->where('status', '!=', 'completed')->whereDate('due_date', '<', today()))
            ->when(isset($filters['status']) && $filters['status'] !== 'overdue', fn ($query) => $query->where('status', $filters['status']))
            ->when(isset($filters['priority']), fn ($query) => $query->where('priority', $filters['priority']))
            ->when(isset($filters['category_id']), fn ($query) => $query->where('category_id', $filters['category_id']))
            ->when(isset($filters['subject']), fn ($query) => $query->where('subject', 'like', '%'.$filters['subject'].'%'))
            ->when(isset($filters['search']), fn ($query) => $query->where(fn ($search) => $search->where('title', 'like', '%'.$filters['search'].'%')->orWhere('description', 'like', '%'.$filters['search'].'%')))
            ->when(isset($filters['due_from']), fn ($query) => $query->whereDate('due_date', '>=', $filters['due_from']))
            ->when(isset($filters['due_to']), fn ($query) => $query->whereDate('due_date', '<=', $filters['due_to']))
            ->orderBy($sort, $direction)
            ->orderByDesc('id')
            ->paginate($filters['per_page'] ?? 15)
            ->withQueryString();

        return TaskResource::collection($tasks);
    }

    public function store(StoreTaskRequest $request): JsonResponse
    {
        $this->authorize('create', Task::class);

        $attributes = $request->validated();
        $this->syncSubjectWithCategory($attributes);
        $task = $request->user()->tasks()->create($attributes);

        return (new TaskResource($task->load(['user', 'sharedWith', 'category'])))->response()->setStatusCode(201);
    }

    public function show(Task $task): TaskResource
    {
        $this->authorize('view', $task);

        return new TaskResource($task->load(['user', 'sharedWith', 'category']));
    }

    public function update(UpdateTaskRequest $request, Task $task): TaskResource
    {
        $this->authorize('update', $task);

        $attributes = $request->validated();
        $this->syncSubjectWithCategory($attributes);
        $task->update($attributes);

        return new TaskResource($task->load(['user', 'sharedWith', 'category']));
    }

    public function updateStatus(UpdateTaskStatusRequest $request, Task $task): TaskResource
    {
        $this->authorize('update', $task);
        $task->update($request->validated());

        return new TaskResource($task->load(['user', 'sharedWith', 'category']));
    }

    public function destroy(Task $task): JsonResponse
    {
        $this->authorize('delete', $task);

        $task->delete();

        return response()->json(null, 204);
    }

    public function shares(Task $task)
    {
        $this->authorize('share', $task);

        return UserResource::collection($task->sharedWith()->orderBy('name')->get());
    }

    public function share(ShareTaskRequest $request, Task $task): JsonResponse
    {
        $this->authorize('share', $task);

        $recipient = User::where('email', $request->validated('email'))->firstOrFail();

        if ($recipient->id === $task->user_id) {
            throw ValidationException::withMessages([
                'email' => ['No puedes compartir una tarea contigo mismo.'],
            ]);
        }

        $alreadyShared = $task->sharedWith()->whereKey($recipient->id)->exists();
        $task->sharedWith()->syncWithoutDetaching([$recipient->id]);

        return (new TaskResource($task->load(['user', 'sharedWith'])))
            ->response()
            ->setStatusCode($alreadyShared ? 200 : 201);
    }

    public function unshare(Task $task, User $user): JsonResponse
    {
        $this->authorize('share', $task);

        if ($user->id === $task->user_id) {
            throw ValidationException::withMessages([
                'user' => ['El propietario no puede eliminarse de la tarea.'],
            ]);
        }

        $task->sharedWith()->detach($user->id);

        return response()->json(null, 204);
    }

    /** @param array<string, mixed> $attributes */
    private function syncSubjectWithCategory(array &$attributes): void
    {
        if (! isset($attributes['category_id']) || $attributes['category_id'] === null) {
            return;
        }

        $attributes['subject'] = \App\Models\Category::findOrFail($attributes['category_id'])->name;
    }
}
