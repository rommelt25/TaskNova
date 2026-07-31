<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\TaskResource;
use App\Models\Category;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TrashController extends Controller
{
    public function tasks(Request $request)
    {
        $perPage = min(max((int) $request->integer('per_page', 15), 1), 100);

        return TaskResource::collection(
            Task::onlyTrashed()
                ->where('user_id', $request->user()->id)
                ->with(['user', 'category'])
                ->latest('deleted_at')
                ->paginate($perPage)
                ->withQueryString()
        );
    }

    public function categories(Request $request)
    {
        $perPage = min(max((int) $request->integer('per_page', 15), 1), 100);

        return CategoryResource::collection(
            Category::onlyTrashed()
                ->where('user_id', $request->user()->id)
                ->withCount('tasks')
                ->latest('deleted_at')
                ->paginate($perPage)
                ->withQueryString()
        );
    }

    public function restoreTask(Request $request, int $task): TaskResource
    {
        $model = Task::onlyTrashed()->where('user_id', $request->user()->id)->findOrFail($task);
        $model->restore();

        return new TaskResource($model->load(['user', 'sharedWith', 'category']));
    }

    public function restoreCategory(Request $request, int $category): CategoryResource
    {
        $model = Category::onlyTrashed()->where('user_id', $request->user()->id)->findOrFail($category);
        $model->restore();

        return new CategoryResource($model->loadCount('tasks'));
    }

    public function forceDeleteTask(Request $request, int $task): JsonResponse
    {
        $model = Task::onlyTrashed()->where('user_id', $request->user()->id)->findOrFail($task);
        $model->forceDelete();

        return response()->json(null, 204);
    }

    public function forceDeleteCategory(Request $request, int $category): JsonResponse
    {
        $model = Category::onlyTrashed()->where('user_id', $request->user()->id)->findOrFail($category);
        $model->forceDelete();

        return response()->json(null, 204);
    }
}
