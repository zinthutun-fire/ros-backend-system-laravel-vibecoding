<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\KitchenService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KitchenWebController extends Controller
{
    public function __construct(
        protected KitchenService $kitchenService
    ) {}

    public function dashboard()
    {
        $kitchenId = Auth::user()->kitchen_id;

        $pendingItems = OrderItem::where('kitchen_id', $kitchenId)
            ->where('status', 'pending')->count();

        $inProgressItems = OrderItem::where('kitchen_id', $kitchenId)
            ->whereIn('status', ['accepted', 'started'])->count();

        $completedToday = OrderItem::where('kitchen_id', $kitchenId)
            ->where('status', 'completed')
            ->whereDate('updated_at', today())->count();

        $activeOrders = OrderItem::where('kitchen_id', $kitchenId)
            ->whereIn('status', ['pending', 'accepted', 'started'])
            ->distinct('order_id')->count('order_id');

        $kitchen = Auth::user()->kitchen;

        return view('kitchen.dashboard', compact(
            'pendingItems', 'inProgressItems', 'completedToday', 'activeOrders', 'kitchen'
        ));
    }

    public function orders()
    {
        $orders = $this->ordersDataInternal();
        return view('kitchen.orders.index', compact('orders'));
    }

    public function updateStatus(Request $request)
    {
        $data = $request->validate([
            'item_ids' => 'required|array|min:1',
            'item_ids.*' => 'required|integer|exists:order_items,id',
            'status' => 'required|string|in:pending,accepted,started,completed',
        ]);

        $kitchenId = Auth::user()->kitchen_id;

        $this->kitchenService->updateItemStatus(
            $data['item_ids'],
            $data['status'],
            $kitchenId
        );

        return redirect()->route('kitchen.orders')
            ->with('success', 'Item status updated to ' . $data['status']);
    }

    public function printOrder($id)
    {
        $kitchenId = Auth::user()->kitchen_id;

        $order = Order::with([
            'table.area',
            'items' => fn($q) => $q->where('kitchen_id', $kitchenId),
            'items.menuItem',
            'items.modifiers',
            'items.kitchen',
        ])->findOrFail($id);

        return view('kitchen.orders.print', compact('order'));
    }

    public function dashboardData()
    {
        $kitchenId = Auth::user()->kitchen_id;

        $pendingItems = OrderItem::where('kitchen_id', $kitchenId)
            ->where('status', 'pending')->count();

        $inProgressItems = OrderItem::where('kitchen_id', $kitchenId)
            ->whereIn('status', ['accepted', 'started'])->count();

        $completedToday = OrderItem::where('kitchen_id', $kitchenId)
            ->where('status', 'completed')
            ->whereDate('updated_at', today())->count();

        $activeOrders = OrderItem::where('kitchen_id', $kitchenId)
            ->whereIn('status', ['pending', 'accepted', 'started'])
            ->distinct('order_id')->count('order_id');

        return response()->json(compact(
            'pendingItems', 'inProgressItems', 'completedToday', 'activeOrders'
        ));
    }

    public function ordersData()
    {
        return response()->json(['orders' => $this->ordersDataInternal()]);
    }

    private function ordersDataInternal(): array
    {
        $kitchenId = Auth::user()->kitchen_id;

        $groupedItems = $this->kitchenService->getOrdersForKitchen($kitchenId);

        return $groupedItems->map(function ($items, $orderId) {
            $first = $items->first();
            $hasPending = $items->contains('status', 'pending');
            $hasStarted = $items->contains('status', 'started');

            if ($hasPending) {
                $statusLabel = 'New';
                $statusClass = 'bg-yellow-900 text-yellow-300';
            } elseif ($hasStarted) {
                $statusLabel = 'Cooking';
                $statusClass = 'bg-orange-900 text-orange-300';
            } else {
                $statusLabel = 'In Progress';
                $statusClass = 'bg-blue-900 text-blue-300';
            }

            return [
                'order_id' => $orderId,
                'order_no' => $first->order->order_no,
                'table_no' => $first->order->table->table_no,
                'area_name' => $first->order->table->area->name ?? '',
                'elapsed' => $first->order->created_at->diffInMinutes(now()),
                'notes' => $first->order->notes,
                'status_label' => $statusLabel,
                'status_class' => $statusClass,
                'has_pending' => $hasPending,
                'non_completed' => $items->contains(fn($i) => $i->status !== 'completed'),
                'items' => $items->sortByDesc('status')->values()->map(fn($item) => [
                    'id' => $item->id,
                    'qty' => $item->qty,
                    'name' => $item->menuItem?->name ?? 'Deleted',
                    'status' => $item->status,
                    'status_class' => match($item->status) {
                        'pending' => 'bg-yellow-900 text-yellow-300',
                        'accepted' => 'bg-blue-900 text-blue-300',
                        'started' => 'bg-orange-900 text-orange-300',
                        'completed' => 'bg-green-900 text-green-300',
                        default => 'bg-gray-700 text-gray-400',
                    },
                    'modifiers' => $item->modifiers->pluck('name')->toArray(),
                    'note' => $item->note,
                    'is_pending' => $item->status === 'pending',
                    'is_accepted' => $item->status === 'accepted',
                    'is_started' => $item->status === 'started',
                    'is_completed' => $item->status === 'completed',
                    'can_done' => in_array($item->status, ['accepted', 'started']),
                ])->toArray(),
            ];
        })->sortBy(fn($o) => $o['has_pending'] ? 0 : (str_contains($o['status_label'], 'Cooking') ? 1 : 2))
          ->values()->toArray();
    }

    public function pendingCount()
    {
        $kitchenId = Auth::user()->kitchen_id;

        $count = OrderItem::where('kitchen_id', $kitchenId)
            ->where('status', 'pending')
            ->count();

        return response()->json(['count' => $count]);
    }

    public function display()
    {
        $kitchenId = Auth::user()->kitchen_id;

        $groupedItems = $this->kitchenService->getOrdersForKitchen($kitchenId);

        $orders = $groupedItems->map(function ($items, $orderId) {
            $first = $items->first();
            return [
                'order_id' => $orderId,
                'order_no' => $first->order->order_no,
                'table_no' => $first->order->table->table_no,
                'table_name' => $first->order->table->name,
                'area_name' => $first->order->table->area->name ?? '',
                'items' => $items,
                'elapsed' => $first->order->created_at->diffInMinutes(now()),
                'created_at' => $first->order->created_at,
            ];
        })->sortByDesc(fn($o) => $o['items']->contains('status', 'pending') ? 0 : 1)
          ->values();

        return view('kitchen.display', compact('orders'));
    }
}
