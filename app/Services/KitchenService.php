<?php

namespace App\Services;

use App\Repositories\Contracts\OrderItemRepositoryInterface;
use App\Repositories\Contracts\OrderRepositoryInterface;
use Illuminate\Support\Facades\DB;

class KitchenService
{
    public function __construct(
        protected OrderItemRepositoryInterface $orderItemRepository,
        protected OrderRepositoryInterface $orderRepository,
    ) {}

    public function getOrdersForKitchen(int $kitchenId, ?string $status = null)
    {
        $query = \App\Models\OrderItem::with(['order.table', 'menuItem', 'modifiers'])
            ->where('kitchen_id', $kitchenId)
            ->where('status', '!=', 'voided')
            ->orderByDesc('created_at');

        if ($status) {
            $statuses = explode(',', $status);
            $query->whereIn('status', $statuses);
        } else {
            $query->whereIn('status', ['pending', 'accepted', 'started']);
        }

        $items = $query->get();
        return $items->groupBy(fn($item) => $item->order_id);
    }

    public function updateItemStatus(array $itemIds, string $status, int $kitchenId): array
    {
        return DB::transaction(function () use ($itemIds, $status, $kitchenId) {
            $items = $this->orderItemRepository->bulkUpdateStatus($itemIds, $status);

            $orderIds = $items->pluck('order_id')->unique();
            foreach ($orderIds as $orderId) {
                $order = $this->orderRepository->find($orderId);
                $newStatus = $order->deriveStatus();
                $this->orderRepository->updateStatus($order->id, $newStatus);
            }

            return $items->toArray();
        });
    }
}
