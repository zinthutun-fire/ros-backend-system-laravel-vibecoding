<?php

namespace App\Events;

use App\Models\TableTransfer;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;

class TableTransferred implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets;

    public function __construct(
        public TableTransfer $transfer
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('cashier'),
            new Channel('tables.' . $this->transfer->from_table_id),
            new Channel('tables.' . $this->transfer->to_table_id),
            new Channel('orders.' . $this->transfer->order_id),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'order_no' => $this->transfer->order->order_no,
            'from_table_no' => $this->transfer->fromTable->table_no,
            'to_table_no' => $this->transfer->toTable->table_no,
            'from_table_id' => $this->transfer->from_table_id,
            'to_table_id' => $this->transfer->to_table_id,
            'order_id' => $this->transfer->order_id,
        ];
    }
}
