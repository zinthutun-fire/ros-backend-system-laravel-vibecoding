<?php

namespace App\Repositories;

use App\Models\OrderItem;
use App\Repositories\Contracts\OrderItemRepositoryInterface;

class OrderItemRepository implements OrderItemRepositoryInterface
{
    public function all()
    {
        return OrderItem::with(['order', 'menuItem', 'kitchen', 'modifiers'])->get();
    }

    public function find(int $id)
    {
        return OrderItem::with(['order', 'menuItem', 'kitchen', 'modifiers'])->findOrFail($id);
    }

    public function findByOrder(int $orderId)
    {
        return OrderItem::with(['menuItem', 'kitchen', 'modifiers'])
            ->where('order_id', $orderId)
            ->get();
    }

    public function findByKitchen(int $kitchenId)
    {
        return OrderItem::with(['order.table', 'menuItem', 'modifiers'])
            ->where('kitchen_id', $kitchenId)
            ->orderByDesc('created_at')
            ->get();
    }

    public function findByStatus(string $status)
    {
        return OrderItem::with(['order', 'menuItem', 'kitchen'])
            ->where('status', $status)
            ->get();
    }

    public function create(array $data)
    {
        return OrderItem::create($data);
    }

    public function createMany(array $items)
    {
        $created = [];
        foreach ($items as $item) {
            $created[] = $this->create($item);
        }
        return $created;
    }

    public function update(int $id, array $data)
    {
        $item = $this->find($id);
        $item->update($data);
        return $item;
    }

    public function updateStatus(int $id, string $status)
    {
        $item = $this->find($id);
        $item->update(['status' => $status]);
        return $item;
    }

    public function bulkUpdateStatus(array $ids, string $status)
    {
        OrderItem::whereIn('id', $ids)->update(['status' => $status]);
        return OrderItem::whereIn('id', $ids)->get();
    }

    public function delete(int $id)
    {
        return $this->find($id)->delete();
    }

    public function void(int $id, int $userId, string $reason)
    {
        $item = $this->find($id);
        $item->update([
            'status' => 'voided',
            'void_reason' => $reason,
            'voided_by' => $userId,
        ]);
        return $item;
    }
}
