<?php

namespace App\Http\Controllers\Api;

use App\Events\ItemStatusUpdated;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateItemStatusRequest;
use App\Http\Resources\OrderItemResource;
use App\Services\KitchenService;
use Illuminate\Http\JsonResponse;

class KitchenOrderController extends Controller
{
    public function __construct(
        protected KitchenService $kitchenService
    ) {}

    public function orders(): JsonResponse
    {
        $user = request()->user();
        $kitchenId = $user->kitchen_id;

        if (!$kitchenId) {
            return response()->json(['message' => 'No kitchen assigned to user'], 400);
        }

        $status = request()->get('status');
        $groupedItems = $this->kitchenService->getOrdersForKitchen($kitchenId, $status);

        $result = $groupedItems->map(function ($items, $orderId) {
            $first = $items->first();
            return [
                'order_id' => $orderId,
                'order_no' => $first->order->order_no,
                'table_no' => $first->order->table->table_no,
                'table_id' => $first->order->table_id,
                'items' => OrderItemResource::collection($items),
                'created_at' => $first->order->created_at,
            ];
        })->values();

        return response()->json($result);
    }

    public function updateItemStatus(UpdateItemStatusRequest $request): JsonResponse
    {
        $user = request()->user();
        $kitchenId = $user->kitchen_id;

        $items = $this->kitchenService->updateItemStatus(
            $request->validated()['item_ids'],
            $request->validated()['status'],
            $kitchenId
        );

        $orderIds = collect($items)->pluck('order_id')->unique();
        foreach ($orderIds as $orderId) {
            $order = \App\Models\Order::with('items.kitchen', 'items.menuItem', 'table')->find($orderId);
            if ($order) {
                event(new ItemStatusUpdated($order, $kitchenId));
            }
        }

        return response()->json(['message' => 'Item statuses updated successfully', 'items' => $items]);
    }
}
