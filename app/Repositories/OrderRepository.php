<?php

namespace App\Repositories;

use App\Models\Order;
use App\Repositories\Contracts\OrderRepositoryInterface;

class OrderRepository implements OrderRepositoryInterface
{
    public function all()
    {
        return Order::with(['table', 'items', 'createdBy'])
            ->orderByDesc('created_at')
            ->get();
    }

    public function find(int $id)
    {
        return Order::with(['table.area', 'items.modifiers', 'items.kitchen', 'items.menuItem', 'createdBy', 'paidBy', 'payments', 'merge.tables'])
            ->findOrFail($id);
    }

    public function findByOrderNo(string $orderNo)
    {
        return Order::with(['table.area', 'items', 'createdBy'])
            ->where('order_no', $orderNo)
            ->firstOrFail();
    }

    public function findByTable(int $tableId)
    {
        return Order::with(['table.area', 'items.modifiers', 'items.kitchen', 'createdBy'])
            ->where('table_id', $tableId)
            ->orderByDesc('created_at')
            ->get();
    }

    public function findByStatus(string $status)
    {
        return Order::with(['table', 'items', 'createdBy'])
            ->where('status', $status)
            ->orderByDesc('created_at')
            ->get();
    }

    public function active()
    {
        return Order::with(['table', 'items', 'createdBy'])
            ->whereNotIn('status', ['paid', 'cancelled'])
            ->orderByDesc('created_at')
            ->get();
    }

    public function today()
    {
        return Order::with(['table', 'items', 'createdBy'])
            ->whereDate('created_at', today())
            ->orderByDesc('created_at')
            ->get();
    }

    public function betweenDates(string $from, string $to)
    {
        return Order::with(['table', 'items', 'createdBy', 'payments'])
            ->whereBetween('created_at', [$from, $to])
            ->orderByDesc('created_at')
            ->get();
    }

    public function create(array $data)
    {
        return Order::create($data);
    }

    public function update(int $id, array $data)
    {
        $order = $this->find($id);
        $order->update($data);
        return $order;
    }

    public function updateStatus(int $id, string $status)
    {
        $order = $this->find($id);
        $order->update(['status' => $status]);
        return $order;
    }

    public function paginate(int $perPage = 25)
    {
        return Order::with(['table', 'items', 'createdBy'])
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public function generateOrderNo(): string
    {
        $prefix = 'ORD-' . now()->format('Ymd');
        $lastOrder = Order::where('order_no', 'like', $prefix . '-%')
            ->orderByDesc('order_no')
            ->first();

        if ($lastOrder) {
            $lastNumber = (int) substr($lastOrder->order_no, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . '-' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
}
