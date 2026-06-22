<?php

namespace App\Events;

use App\Models\Table;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;

class TableStatusChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets;

    public function __construct(
        public Table $table
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('cashier'),
            new Channel('tables.' . $this->table->id),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'table_id' => $this->table->id,
            'table_no' => $this->table->table_no,
            'status' => $this->table->status,
        ];
    }
}
