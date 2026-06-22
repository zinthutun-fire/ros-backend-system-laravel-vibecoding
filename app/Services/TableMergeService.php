<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Repositories\Contracts\TableMergeRepositoryInterface;
use App\Repositories\Contracts\OrderRepositoryInterface;
use App\Repositories\Contracts\TableRepositoryInterface;
use Illuminate\Support\Facades\DB;

class TableMergeService
{
    public function __construct(
        protected TableMergeRepositoryInterface $tableMergeRepository,
        protected OrderRepositoryInterface $orderRepository,
        protected TableRepositoryInterface $tableRepository,
    ) {}

    public function mergeTables(array $tableIds, int $userId): array
    {
        return DB::transaction(function () use ($tableIds, $userId) {
            $groupCode = $this->generateGroupCode();

            $primaryTableId = $tableIds[0];

            $existingOrders = $this->orderRepository->findByTable($primaryTableId);
            $primaryOrder = $existingOrders->first(fn(Order $o) => $o->isActive());

            if (!$primaryOrder) {
                throw new \RuntimeException('No active order found on the primary table to merge');
            }

            $merge = $this->tableMergeRepository->create([
                'group_code' => $groupCode,
                'order_id' => $primaryOrder->id,
                'created_by' => $userId,
            ]);

            $mergedOrderIds = [];
            $extraTax = 0;
            $extraService = 0;

            foreach ($tableIds as $tableId) {
                if ($tableId === $primaryTableId) continue;

                $secondaryOrders = $this->orderRepository->findByTable($tableId);
                $secondaryOrder = $secondaryOrders->first(fn(Order $o) => $o->isActive());

                if ($secondaryOrder) {
                    OrderItem::where('order_id', $secondaryOrder->id)
                        ->update(['order_id' => $primaryOrder->id]);

                    $mergedOrderIds[] = $secondaryOrder->id;
                    $extraTax += (float) $secondaryOrder->tax_total;
                    $extraService += (float) $secondaryOrder->service_charge_total;

                    $secondaryOrder->update(['status' => 'merged']);
                }
            }

            $merge->update(['merged_order_ids' => $mergedOrderIds]);

            $activeItems = OrderItem::where('order_id', $primaryOrder->id)
                ->where('status', '!=', 'voided')
                ->get();

            $total = $activeItems->sum('subtotal');
            $taxTotal = (float) $primaryOrder->tax_total + $extraTax;
            $serviceTotal = (float) $primaryOrder->service_charge_total + $extraService;
            $grand = $total + $taxTotal + $serviceTotal - (float) ($primaryOrder->discount_total ?? 0);

            $primaryOrder->update([
                'total' => $total,
                'tax_total' => $taxTotal,
                'service_charge_total' => $serviceTotal,
                'grand_total' => max($grand, 0),
            ]);

            $this->tableMergeRepository->addTablesToMerge($merge->id, $tableIds);

            foreach ($tableIds as $tableId) {
                $this->tableRepository->updateStatus($tableId, 'occupied');
            }

            return [
                'merge' => $merge->fresh()->load(['tables', 'order']),
                'order' => $primaryOrder->fresh(),
                'group_code' => $groupCode,
            ];
        });
    }

    protected function generateGroupCode(): string
    {
        $prefix = 'MG-';
        $lastMerge = \App\Models\TableMerge::orderByDesc('id')->first();
        $nextId = $lastMerge ? $lastMerge->id + 1 : 1;
        return $prefix . str_pad($nextId, 5, '0', STR_PAD_LEFT);
    }
}
