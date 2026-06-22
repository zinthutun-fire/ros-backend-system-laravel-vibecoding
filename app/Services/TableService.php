<?php

namespace App\Services;

use App\Models\Order;
use App\Repositories\Contracts\TableRepositoryInterface;
use App\Repositories\Contracts\TableTransferRepositoryInterface;
use App\Repositories\Contracts\OrderRepositoryInterface;
use Illuminate\Support\Facades\DB;

class TableService
{
    public function __construct(
        protected TableRepositoryInterface $tableRepository,
        protected TableTransferRepositoryInterface $tableTransferRepository,
        protected OrderRepositoryInterface $orderRepository,
    ) {}

    public function transferTable(int $orderId, int $fromTableId, int $toTableId, int $userId): array
    {
        return DB::transaction(function () use ($orderId, $fromTableId, $toTableId, $userId) {
            $order = $this->orderRepository->find($orderId);
            $fromTable = $this->tableRepository->find($fromTableId);
            $toTable = $this->tableRepository->find($toTableId);

            if (!$toTable->isAvailable()) {
                throw new \RuntimeException('Target table is not available');
            }

            $this->orderRepository->update($orderId, ['table_id' => $toTableId]);

            $transfer = $this->tableTransferRepository->create([
                'from_table_id' => $fromTableId,
                'to_table_id' => $toTableId,
                'order_id' => $orderId,
                'user_id' => $userId,
            ]);

            $this->tableRepository->updateStatus($fromTableId, 'available');
            $this->tableRepository->updateStatus($toTableId, 'occupied');

            return [
                'transfer' => $transfer,
                'order' => $this->orderRepository->find($orderId),
            ];
        });
    }

    public function closeTable(int $tableId): void
    {
        $activeOrders = $this->orderRepository->findByTable($tableId)
            ->whereNotIn('status', ['paid', 'cancelled']);

        if ($activeOrders->isNotEmpty()) {
            throw new \RuntimeException('Table has unpaid orders');
        }

        $this->tableRepository->updateStatus($tableId, 'available');
    }

    public function resetAll(): void
    {
        DB::transaction(function () {
            Order::query()->delete();
            $this->tableRepository->all()->each(function ($table) {
                $this->tableRepository->updateStatus($table->id, 'available');
            });
        });
    }
}
