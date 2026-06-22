<?php

namespace App\Repositories;

use App\Models\Payment;
use App\Repositories\Contracts\PaymentRepositoryInterface;

class PaymentRepository implements PaymentRepositoryInterface
{
    public function all()
    {
        return Payment::with('order')->orderByDesc('paid_at')->get();
    }

    public function find(int $id)
    {
        return Payment::with('order')->findOrFail($id);
    }

    public function findByOrder(int $orderId)
    {
        return Payment::where('order_id', $orderId)->orderByDesc('paid_at')->get();
    }

    public function today()
    {
        return Payment::with('order')
            ->whereDate('paid_at', today())
            ->orderByDesc('paid_at')
            ->get();
    }

    public function betweenDates(string $from, string $to)
    {
        return Payment::with('order')
            ->whereBetween('paid_at', [$from, $to])
            ->orderByDesc('paid_at')
            ->get();
    }

    public function create(array $data)
    {
        return Payment::create($data);
    }

    public function totalForOrder(int $orderId)
    {
        return Payment::where('order_id', $orderId)->sum('amount');
    }
}
