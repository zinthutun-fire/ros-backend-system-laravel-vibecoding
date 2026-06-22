<?php

namespace App\Events;

use App\Models\TableMerge;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;

class TableMerged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets;

    public function __construct(
        public TableMerge $merge
    ) {}

    public function broadcastOn(): array
    {
        $channels = [
            new Channel('cashier'),
            new Channel('orders.' . $this->merge->order_id),
        ];

        foreach ($this->merge->tables as $table) {
            $channels[] = new Channel('tables.' . $table->id);
        }

        return $channels;
    }

    public function broadcastWith(): array
    {
        return [
            'group_code' => $this->merge->group_code,
            'order_no' => $this->merge->order->order_no,
            'order_id' => $this->merge->order_id,
            'tables' => $this->merge->tables->map(fn($table) => [
                'id' => $table->id,
                'table_no' => $table->table_no,
            ]),
        ];
    }
}
