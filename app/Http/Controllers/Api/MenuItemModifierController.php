<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MenuItemModifierResource;
use App\Repositories\Contracts\MenuItemModifierRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MenuItemModifierController extends Controller
{
    public function __construct(
        protected MenuItemModifierRepositoryInterface $modifierRepository
    ) {}

    public function index(): JsonResponse
    {
        return response()->json(MenuItemModifierResource::collection($this->modifierRepository->all()));
    }

    public function show(int $id): JsonResponse
    {
        return response()->json(new MenuItemModifierResource($this->modifierRepository->find($id)));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'menu_item_id' => 'required|integer|exists:menu_items,id',
            'name' => 'required|string|max:255',
            'price_adjustment' => 'sometimes|numeric|min:0',
            'sort_order' => 'sometimes|integer|min:0',
            'is_active' => 'sometimes|boolean',
        ]);
        return response()->json(new MenuItemModifierResource($this->modifierRepository->create($validated)), 201);
    }

    public function update(int $id, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'price_adjustment' => 'sometimes|numeric|min:0',
            'sort_order' => 'sometimes|integer|min:0',
            'is_active' => 'sometimes|boolean',
        ]);
        return response()->json(new MenuItemModifierResource($this->modifierRepository->update($id, $validated)));
    }

    public function destroy(int $id): JsonResponse
    {
        $this->modifierRepository->delete($id);
        return response()->json(['message' => 'Modifier deleted successfully']);
    }
}
