<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;

class ItemStatusUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets;

    public function __construct(
        public Order $order,
        public int $kitchenId,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('cashier'),
            new Channel('orders.' . $this->order->id),
            new Channel('kitchen.' . $this->kitchenId),
        ];
    }

    public function broadcastWith(): array
    {
        $kitchenItems = $this->order->items->where('kitchen_id', $this->kitchenId);

        return [
            'order_no' => $this->order->order_no,
            'table_no' => $this->order->table->table_no,
            'status' => $this->order->status,
            'grand_total' => (float) $this->order->grand_total,
            'kitchen_id' => $this->kitchenId,
            'kitchen_name' => $kitchenItems->first()?->kitchen?->name,
            'items' => $kitchenItems->values()->map(fn($item) => [
                'id' => $item->id,
                'name' => $item->menuItem->name,
                'qty' => $item->qty,
                'status' => $item->status,
            ]),
        ];
    }
}
