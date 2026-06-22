<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    public function __construct(
        protected CategoryRepositoryInterface $categoryRepository
    ) {}

    public function index(): JsonResponse
    {
        $categories = $this->categoryRepository->all();
        return response()->json(CategoryResource::collection($categories));
    }

    public function show(int $id): JsonResponse
    {
        $category = $this->categoryRepository->find($id);
        $category->load('menuItems.modifiers');
        return response()->json(new CategoryResource($category));
    }

    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $category = $this->categoryRepository->create($request->validated());
        return response()->json(new CategoryResource($category), 201);
    }

    public function update(int $id, UpdateCategoryRequest $request): JsonResponse
    {
        $category = $this->categoryRepository->update($id, $request->validated());
        return response()->json(new CategoryResource($category));
    }

    public function destroy(int $id): JsonResponse
    {
        $this->categoryRepository->delete($id);
        return response()->json(['message' => 'Category deleted successfully']);
    }
}
