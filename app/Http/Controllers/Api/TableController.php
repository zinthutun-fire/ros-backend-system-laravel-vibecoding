<?php

namespace App\Http\Controllers\Api;

use App\Events\TableStatusChanged;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTableRequest;
use App\Http\Requests\UpdateTableRequest;
use App\Http\Resources\TableResource;
use App\Repositories\Contracts\TableRepositoryInterface;
use App\Services\TableService;
use Illuminate\Http\JsonResponse;

class TableController extends Controller
{
    public function __construct(
        protected TableRepositoryInterface $tableRepository,
        protected TableService $tableService,
    ) {}

    public function index(): JsonResponse
    {
        $tables = $this->tableRepository->paginate(request()->get('per_page', 100));
        $tables->load(['area', 'mergeGroups' => fn($q) => $q->with(['order', 'tables'])]);
        return response()->json(TableResource::collection($tables)->response()->getData(true));
    }

    public function show(int $id): JsonResponse
    {
        $table = $this->tableRepository->find($id);
        $table->load([
            'activeOrders',
            'allOrders.items.menuItem',
            'allOrders.items.kitchen',
            'mergeGroups' => fn($q) => $q->with(['order', 'tables']),
        ]);
        return response()->json(new TableResource($table));
    }

    public function store(StoreTableRequest $request): JsonResponse
    {
        $table = $this->tableRepository->create($request->validated());
        return response()->json(new TableResource($table), 201);
    }

    public function update(int $id, UpdateTableRequest $request): JsonResponse
    {
        $table = $this->tableRepository->update($id, $request->validated());
        return response()->json(new TableResource($table));
    }

    public function destroy(int $id): JsonResponse
    {
        $this->tableRepository->delete($id);
        return response()->json(['message' => 'Table deleted successfully']);
    }

    public function reset(): JsonResponse
    {
        $this->tableService->resetAll();
        return response()->json(['message' => 'All tables reset successfully']);
    }

    public function close(int $id): JsonResponse
    {
        $table = $this->tableRepository->updateStatus($id, 'available');

        try {
            event(new TableStatusChanged($table));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Broadcast failed during table close: ' . $e->getMessage());
        }

        return response()->json([
            'message' => 'Table closed successfully',
            'data' => new TableResource($table),
        ]);
    }
}
