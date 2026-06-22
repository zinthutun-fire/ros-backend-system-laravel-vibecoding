<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Table;
use App\Repositories\Contracts\OrderRepositoryInterface;
use App\Repositories\Contracts\OrderItemRepositoryInterface;
use App\Repositories\Contracts\MenuItemRepositoryInterface;
use App\Repositories\Contracts\MenuItemModifierRepositoryInterface;
use App\Repositories\Contracts\TableRepositoryInterface;
use App\Repositories\Contracts\TaxRateRepositoryInterface;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function __construct(
        protected OrderRepositoryInterface $orderRepository,
        protected OrderItemRepositoryInterface $orderItemRepository,
        protected MenuItemRepositoryInterface $menuItemRepository,
        protected MenuItemModifierRepositoryInterface $modifierRepository,
        protected TableRepositoryInterface $tableRepository,
        protected TaxRateRepositoryInterface $taxRateRepository,
    ) {}

    public function createOrder(array $data, int $userId): Order
    {
        return DB::transaction(function () use ($data, $userId) {
            $orderNo = $this->orderRepository->generateOrderNo();
            $table = $this->tableRepository->find($data['table_id']);

            $order = $this->orderRepository->create([
                'order_no' => $orderNo,
                'table_id' => $data['table_id'],
                'status' => 'new',
                'total' => 0,
                'tax_total' => 0,
                'service_charge_total' => 0,
                'discount_total' => 0,
                'grand_total' => 0,
                'created_by' => $userId,
            ]);

            $items = $this->processItems($data['items'], $order->id);
            $this->orderItemRepository->createMany($items);

            $this->recalculateOrderTotals($order);
            $this->tableRepository->updateStatus($data['table_id'], 'occupied');

            return $this->orderRepository->find($order->id);
        });
    }

    public function addItemsToOrder(int $orderId, array $items, int $userId): Order
    {
        return DB::transaction(function () use ($orderId, $items) {
            $order = $this->orderRepository->find($orderId);

            if (!$order->canAddItems()) {
                throw new \RuntimeException('Cannot add items to order with status: ' . $order->status);
            }

            $processedItems = $this->processItems($items, $orderId);
            $this->orderItemRepository->createMany($processedItems);

            $this->recalculateOrderTotals($order);
            $newStatus = $order->deriveStatus();
            if ($newStatus !== $order->status) {
                $this->orderRepository->updateStatus($order->id, $newStatus);
            }

            return $this->orderRepository->find($order->id);
        });
    }

    public function processItems(array $items, int $orderId): array
    {
        $processed = [];

        foreach ($items as $item) {
            $menuItem = $this->menuItemRepository->find($item['menu_item_id']);
            $qty = $item['qty'];
            $price = (float) $menuItem->price;
            $modifiersTotal = 0;

            $modifierRecords = [];
            if (!empty($item['modifiers'])) {
                foreach ($item['modifiers'] as $mod) {
                    if (isset($mod['modifier_id'])) {
                        $modifier = $this->modifierRepository->find($mod['modifier_id']);
                        $modifiersTotal += (float) $modifier->price_adjustment;
                        $modifierRecords[] = [
                            'modifier_id' => $modifier->id,
                            'name' => $modifier->name,
                            'price_adjustment' => $modifier->price_adjustment,
                        ];
                    } elseif (isset($mod['name'])) {
                        $adjustment = $mod['price_adjustment'] ?? 0;
                        $modifiersTotal += (float) $adjustment;
                        $modifierRecords[] = [
                            'modifier_id' => null,
                            'name' => $mod['name'],
                            'price_adjustment' => $adjustment,
                        ];
                    }
                }
            }

            $itemTotal = ($price + $modifiersTotal) * $qty;

            $processed[] = [
                'order_id' => $orderId,
                'menu_item_id' => $menuItem->id,
                'kitchen_id' => $menuItem->kitchen_id,
                'qty' => $qty,
                'price' => $price,
                'subtotal' => $price * $qty,
                'note' => $item['note'] ?? null,
                'status' => 'pending',
                'modifiers' => $modifierRecords,
            ];
        }

        return $processed;
    }

    public function recalculateOrderTotals(Order $order): Order
    {
        $items = $order->activeItems()->with('modifiers')->get();

        $subtotal = 0;
        foreach ($items as $item) {
            $itemSubtotal = (float) $item->subtotal;
            foreach ($item->modifiers as $mod) {
                $itemSubtotal += (float) $mod->price_adjustment * $item->qty;
            }
            $subtotal += $itemSubtotal;
        }

        $taxRate = $this->taxRateRepository->default();
        $taxTotal = $taxRate ? round($subtotal * (float) $taxRate->rate / 100, 2) : 0;

        $serviceCharge = $this->taxRateRepository->byType('service_charge')->first();
        $serviceChargeTotal = $serviceCharge ? round($subtotal * (float) $serviceCharge->rate / 100, 2) : 0;

        $discountTotal = (float) $order->discount_total;
        $grandTotal = round($subtotal + $taxTotal + $serviceChargeTotal - $discountTotal, 2);

        $order->update([
            'total' => $subtotal,
            'tax_total' => $taxTotal,
            'service_charge_total' => $serviceChargeTotal,
            'grand_total' => $grandTotal,
        ]);

        return $order;
    }

    public function voidItem(int $itemId, int $userId, string $reason): Order
    {
        return DB::transaction(function () use ($itemId, $userId, $reason) {
            $item = $this->orderItemRepository->void($itemId, $userId, $reason);
            $order = $this->orderRepository->find($item->order_id);

            $newStatus = $order->deriveStatus();
            $this->orderRepository->updateStatus($order->id, $newStatus);

            $this->recalculateOrderTotals($order);

            return $this->orderRepository->find($order->id);
        });
    }

    public function applyDiscount(int $orderId, string $type, float $value, string $reason): Order
    {
        return DB::transaction(function () use ($orderId, $type, $value, $reason) {
            $order = $this->orderRepository->find($orderId);

            $total = (float) $order->total;
            $discount = $type === 'percentage'
                ? round($total * $value / 100, 2)
                : min($value, $total);

            $order->update([
                'discount_total' => $discount,
                'notes' => $reason,
            ]);

            $this->recalculateOrderTotals($order);

            return $this->orderRepository->find($order->id);
        });
    }

    public function getBill(int $orderId): Order
    {
        return $this->orderRepository->find($orderId);
    }
}
