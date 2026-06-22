<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;

class OrderUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets;

    public function __construct(
        public Order $order
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('cashier'),
            new Channel('orders.' . $this->order->id),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'order_no' => $this->order->order_no,
            'table_no' => $this->order->table->table_no,
            'status' => $this->order->status,
            'items' => $this->order->items->map(fn($item) => [
                'id' => $item->id,
                'name' => $item->menuItem->name,
                'qty' => $item->qty,
                'status' => $item->status,
                'kitchen' => $item->kitchen->name,
            ]),
            'total' => (float) $this->order->total,
            'grand_total' => (float) $this->order->grand_total,
        ];
    }
}
