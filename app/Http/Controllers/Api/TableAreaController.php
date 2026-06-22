<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TableAreaResource;
use App\Repositories\Contracts\TableAreaRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TableAreaController extends Controller
{
    public function __construct(
        protected TableAreaRepositoryInterface $areaRepository
    ) {}

    public function index(): JsonResponse
    {
        $areas = $this->areaRepository->all();
        return response()->json(TableAreaResource::collection($areas));
    }

    public function show(int $id): JsonResponse
    {
        $area = $this->areaRepository->find($id);
        $area->load('tables');
        return response()->json(new TableAreaResource($area));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sort_order' => 'sometimes|integer|min:0',
        ]);
        $area = $this->areaRepository->create($validated);
        return response()->json(new TableAreaResource($area), 201);
    }

    public function update(int $id, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'sort_order' => 'sometimes|integer|min:0',
        ]);
        $area = $this->areaRepository->update($id, $validated);
        return response()->json(new TableAreaResource($area));
    }

    public function destroy(int $id): JsonResponse
    {
        $this->areaRepository->delete($id);
        return response()->json(['message' => 'Area deleted successfully']);
    }
}
