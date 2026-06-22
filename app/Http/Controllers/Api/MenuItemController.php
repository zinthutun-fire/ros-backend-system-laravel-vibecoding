<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMenuItemRequest;
use App\Http\Requests\UpdateMenuItemRequest;
use App\Http\Resources\MenuItemResource;
use App\Repositories\Contracts\MenuItemRepositoryInterface;
use Illuminate\Http\JsonResponse;

class MenuItemController extends Controller
{
    public function __construct(
        protected MenuItemRepositoryInterface $menuItemRepository
    ) {}

    public function index(): JsonResponse
    {
        $menuItems = $this->menuItemRepository->paginate(request()->get('per_page', 50));

        if (request()->wantsJson()) {
            return response()->json(MenuItemResource::collection($menuItems)->response()->getData(true));
        }

        return response()->json(MenuItemResource::collection($menuItems)->response()->getData(true));
    }

    public function show(int $id): JsonResponse
    {
        return response()->json(new MenuItemResource($this->menuItemRepository->find($id)));
    }

    public function store(StoreMenuItemRequest $request): JsonResponse
    {
        $data = $request->validated();
        if (!isset($data['has_modifiers'])) {
            $data['has_modifiers'] = false;
        }
        $item = $this->menuItemRepository->create($data);
        return response()->json(new MenuItemResource($item), 201);
    }

    public function update(int $id, UpdateMenuItemRequest $request): JsonResponse
    {
        $item = $this->menuItemRepository->update($id, $request->validated());
        return response()->json(new MenuItemResource($item));
    }

    public function destroy(int $id): JsonResponse
    {
        $this->menuItemRepository->delete($id);
        return response()->json(['message' => 'Menu item deleted successfully']);
    }
}
