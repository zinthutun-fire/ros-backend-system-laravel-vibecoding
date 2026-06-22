<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Table;
use Illuminate\Support\Facades\DB;

class ReportService
{
    public function dailySales(string $date): array
    {
        $payments = Payment::whereDate('paid_at', $date)->get();
        $orders = Order::whereDate('created_at', $date)->where('status', 'paid')->get();

        return [
            'date' => $date,
            'total_sales' => (float) $payments->sum('amount'),
            'order_count' => $orders->count(),
            'cash_sales' => (float) $payments->where('type', 'cash')->sum('amount'),
            'card_sales' => (float) $payments->whereIn('type', ['card', 'split_cash_card'])->sum('amount'),
            'payments' => $payments,
        ];
    }

    public function monthlySales(int $year, int $month): array
    {
        $startDate = "{$year}-{$month}-01";
        $endDate = date('Y-m-t', strtotime($startDate));

        $payments = Payment::whereBetween('paid_at', [$startDate, $endDate])->get();
        $orders = Order::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'paid')
            ->get();

        return [
            'year' => $year,
            'month' => $month,
            'total_sales' => (float) $payments->sum('amount'),
            'order_count' => $orders->count(),
            'daily_breakdown' => $payments->groupBy(fn($p) => $p->paid_at->format('Y-m-d'))
                ->map(fn($day) => (float) $day->sum('amount')),
        ];
    }

    public function yearlySales(int $year): array
    {
        $startDate = "{$year}-01-01";
        $endDate = "{$year}-12-31";

        $payments = Payment::whereBetween('paid_at', [$startDate, $endDate])->get();
        $orders = Order::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'paid')
            ->get();

        return [
            'year' => $year,
            'total_sales' => (float) $payments->sum('amount'),
            'order_count' => $orders->count(),
            'monthly_breakdown' => $payments->groupBy(fn($p) => $p->paid_at->format('Y-m'))
                ->map(fn($month) => (float) $month->sum('amount')),
        ];
    }

    public function topItems(string $from, string $to, int $limit = 10): array
    {
        return OrderItem::select(
                'menu_item_id',
                DB::raw('SUM(qty) as total_qty'),
                DB::raw('SUM(subtotal) as total_revenue')
            )
            ->whereHas('order', fn($q) => $q->whereBetween('created_at', [$from, $to])
                ->where('status', 'paid'))
            ->where('status', '!=', 'voided')
            ->groupBy('menu_item_id')
            ->orderByDesc('total_qty')
            ->limit($limit)
            ->with('menuItem')
            ->get()
            ->toArray();
    }

    public function tableUtilization(string $date): array
    {
        $totalTables = Table::count();
        $occupiedTables = Table::whereIn('status', ['occupied', 'ordering', 'payment'])->count();

        $orders = Order::whereDate('created_at', $date)
            ->where('status', 'paid')
            ->get();

        return [
            'date' => $date,
            'total_tables' => $totalTables,
            'occupied_tables' => $occupiedTables,
            'utilization_rate' => $totalTables > 0 ? round(($occupiedTables / $totalTables) * 100, 2) : 0,
            'total_orders' => $orders->count(),
            'table_statuses' => Table::select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->pluck('count', 'status'),
        ];
    }
}
