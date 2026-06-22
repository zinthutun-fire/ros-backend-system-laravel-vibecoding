<?php

namespace App\Http\Controllers\Api;

use App\Events\OrderCreated;
use App\Events\OrderUpdated;
use App\Events\TableStatusChanged;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\TableMerge;
use App\Http\Requests\AddOrderItemsRequest;
use App\Http\Requests\StoreDiscountRequest;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\VoidOrderItemRequest;
use App\Http\Resources\OrderResource;
use App\Repositories\Contracts\OrderItemRepositoryInterface;
use App\Repositories\Contracts\OrderRepositoryInterface;
use App\Repositories\Contracts\TableRepositoryInterface;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;

class OrderController extends Controller
{
    public function __construct(
        protected OrderRepositoryInterface $orderRepository,
        protected OrderItemRepositoryInterface $orderItemRepository,
        protected TableRepositoryInterface $tableRepository,
        protected OrderService $orderService,
    ) {}

    public function index(): JsonResponse
    {
        $query = Order::with(['table', 'items', 'createdBy']);

        if ($date = request()->get('date')) {
            $query = $query->whereDate('created_at', $date);
        }

        $orders = $query->orderByDesc('created_at')->paginate(request()->get('per_page', 25));
        return response()->json(OrderResource::collection($orders)->response()->getData(true));
    }

    public function show(int $id): JsonResponse
    {
        return response()->json(new OrderResource($this->orderRepository->find($id)));
    }

    public function showByOrderNo(string $orderNo): JsonResponse
    {
        $order = $this->orderRepository->findByOrderNo($orderNo);
        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }
        return response()->json(new OrderResource($order));
    }

    public function store(StoreOrderRequest $request): JsonResponse
    {
        $order = $this->orderService->createOrder(
            $request->validated(),
            $request->user()->id
        );

        event(new OrderCreated($order));

        return response()->json(new OrderResource($order), 201);
    }

    public function addItems(int $id, AddOrderItemsRequest $request): JsonResponse
    {
        $order = $this->orderService->addItemsToOrder(
            $id,
            $request->validated()['items'],
            $request->user()->id
        );

        event(new OrderUpdated($order));

        return response()->json(new OrderResource($order));
    }

    public function voidItem(int $id, VoidOrderItemRequest $request): JsonResponse
    {
        $order = $this->orderService->voidItem(
            $id,
            $request->user()->id,
            $request->validated()['reason']
        );

        return response()->json(new OrderResource($order));
    }

    public function bill(int $id): JsonResponse
    {
        $order = $this->orderService->getBill($id);

        $merge = TableMerge::where('order_id', $order->id)->first();

        $tableIds = $merge
            ? $merge->mergeGroupTables()->pluck('table_id')->toArray()
            : [$order->table_id];

        foreach ($tableIds as $tableId) {
            $table = $this->tableRepository->updateStatus($tableId, 'payment');
            try {
                if ($table) {
                    event(new TableStatusChanged($table));
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Broadcast failed during bill request: ' . $e->getMessage());
            }
        }

        return response()->json(new OrderResource($order));
    }

    public function applyDiscount(int $id, StoreDiscountRequest $request): JsonResponse
    {
        $order = $this->orderService->applyDiscount(
            $id,
            $request->validated()['type'],
            $request->validated()['value'],
            $request->validated()['reason']
        );

        return response()->json(new OrderResource($order));
    }
}
