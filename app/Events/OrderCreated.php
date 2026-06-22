<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;

class OrderCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets;

    public function __construct(
        public Order $order
    ) {}

    public function broadcastOn(): array
    {
        $channels = [
            new Channel('cashier'),
            new Channel('orders.' . $this->order->id),
        ];

        $kitchenIds = $this->order->items->pluck('kitchen_id')->unique();
        foreach ($kitchenIds as $kitchenId) {
            $channels[] = new Channel('kitchen.' . $kitchenId);
        }

        return $channels;
    }

    public function broadcastWith(): array
    {
        return [
            'order_no' => $this->order->order_no,
            'table_no' => $this->order->table->table_no,
            'table_id' => $this->order->table_id,
            'status' => $this->order->status,
            'total' => (float) $this->order->total,
            'grand_total' => (float) $this->order->grand_total,
            'items' => $this->order->items->map(fn($item) => [
                'id' => $item->id,
                'menu_item_id' => $item->menu_item_id,
                'name' => $item->menuItem->name,
                'qty' => $item->qty,
                'price' => (float) $item->price,
                'kitchen_id' => $item->kitchen_id,
                'kitchen' => $item->kitchen->name,
                'status' => $item->status,
                'modifiers' => $item->modifiers->pluck('name'),
            ]),
        ];
    }
}
