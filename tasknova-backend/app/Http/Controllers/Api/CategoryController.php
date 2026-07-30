<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Category\ListCategoriesRequest;
use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    public function index(ListCategoriesRequest $request)
    {
        $filters = $request->validated();
        $categories = $request->user()->categories()
            ->withCount('tasks')
            ->when(isset($filters['search']), fn ($query) => $query->where('name', 'like', '%'.$filters['search'].'%'))
            ->orderBy('name')
            ->paginate($filters['per_page'] ?? 10)
            ->withQueryString();

        return CategoryResource::collection($categories);
    }

    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $category = $request->user()->categories()->create($request->validated());

        return (new CategoryResource($category->loadCount('tasks')))->response()->setStatusCode(201);
    }

    public function update(UpdateCategoryRequest $request, Category $category): CategoryResource
    {
        $category->update($request->validated());

        return new CategoryResource($category->loadCount('tasks'));
    }

    public function destroy(Category $category): JsonResponse
    {
        abort_unless($category->user_id === request()->user()->id, 403);
        $category->delete();

        return response()->json(null, 204);
    }
}
