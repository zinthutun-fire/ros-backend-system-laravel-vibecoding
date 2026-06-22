<?php

namespace App\Events;

use App\Models\Payment;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;

class PaymentCompleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets;

    public function __construct(
        public Payment $payment
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('cashier'),
            new Channel('orders.' . $this->payment->order_id),
            new Channel('tables.' . $this->payment->order->table_id),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'order_no' => $this->payment->order->order_no,
            'table_no' => $this->payment->order->table->table_no,
            'amount' => (float) $this->payment->amount,
            'type' => $this->payment->type,
            'paid_at' => $this->payment->paid_at->toIso8601String(),
        ];
    }
}
