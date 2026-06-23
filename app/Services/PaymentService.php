<?php

namespace App\Services;

use App\Events\TableStatusChanged;
use App\Models\TableMerge;
use App\Repositories\Contracts\PaymentRepositoryInterface;
use App\Repositories\Contracts\OrderRepositoryInterface;
use App\Repositories\Contracts\TableRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    public function __construct(
        protected PaymentRepositoryInterface $paymentRepository,
        protected OrderRepositoryInterface $orderRepository,
        protected TableRepositoryInterface $tableRepository,
    ) {}

    public function processPayment(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $order = $this->orderRepository->find($data['order_id']);

            $payment = $this->paymentRepository->create([
                'order_id' => $data['order_id'],
                'type' => $data['type'],
                'amount' => $data['amount'],
                'cash_portion' => $data['cash_portion'] ?? null,
                'card_portion' => $data['card_portion'] ?? null,
                'paid_at' => now(),
            ]);

            $totalPaid = $this->paymentRepository->totalForOrder($order->id);

            if ($totalPaid >= (float) $order->grand_total) {
                $this->orderRepository->update($order->id, [
                    'status' => 'paid',
                    'paid_at' => now(),
                ]);

                $this->releaseTablesForOrder($order->id);
            }

            $change = 0;
            if ($data['type'] === 'cash' && isset($data['tendered'])) {
                $change = (float) $data['tendered'] - (float) $data['amount'];
            }

            return [
                'payment' => $payment,
                'change' => $change,
                'order_status' => $order->fresh()->status,
            ];
        });
    }

    public function splitPayment(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $order = $this->orderRepository->find($data['order_id']);
            $totalToCollect = (float) $order->grand_total - (float) $this->paymentRepository->totalForOrder($order->id);
            $splits = $data['splits'];

            $payments = [];
            foreach ($splits as $split) {
                $amount = min($split['amount'], $totalToCollect);
                if ($amount <= 0) continue;

                $payment = $this->paymentRepository->create([
                    'order_id' => $data['order_id'],
                    'type' => $split['method'],
                    'amount' => $amount,
                    'cash_portion' => $split['method'] === 'split_cash_card' ? ($split['cash_portion'] ?? null) : null,
                    'card_portion' => $split['method'] === 'split_cash_card' ? ($split['card_portion'] ?? null) : null,
                    'paid_at' => now(),
                ]);
                $payments[] = $payment;
                $totalToCollect -= $amount;
            }

            $totalPaid = $this->paymentRepository->totalForOrder($order->id);

            if ($totalPaid >= (float) $order->grand_total) {
                $this->orderRepository->update($order->id, [
                    'status' => 'paid',
                    'paid_at' => now(),
                ]);

                $this->releaseTablesForOrder($order->id);
            }

            return [
                'payments' => $payments,
                'total_paid' => $totalPaid,
                'order_status' => $order->fresh()->status,
            ];
        });
    }

    protected function releaseTablesForOrder(int $orderId): void
    {
        $merge = TableMerge::where('order_id', $orderId)->first();

        $tableIds = $merge
            ? $merge->mergeGroupTables()->pluck('table_id')->toArray()
            : [];

        if (empty($tableIds)) {
            $tableIds = [$this->orderRepository->find($orderId)->table_id];
        }

        foreach ($tableIds as $tableId) {
            $this->tableRepository->updateStatus($tableId, 'paid');
            $table = $this->tableRepository->find($tableId);
            if ($table) {
                try {
                    event(new TableStatusChanged($table));
                } catch (\Throwable $e) {
                    Log::error('Broadcast failed for table ' . $tableId . ': ' . $e->getMessage());
                }
            }
        }
    }
}
