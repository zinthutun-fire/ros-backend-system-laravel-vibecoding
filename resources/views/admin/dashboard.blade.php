@extends('layouts.admin')
@section('title', 'Dashboard')
@section('content')
@php
$initialData = json_encode([
    'todayOrders' => $todayOrders,
    'todayRevenue' => (float) $todayRevenue,
    'activeTables' => $activeTables,
    'totalTables' => $totalTables,
    'totalUsers' => $totalUsers,
    'pendingOrders' => $pendingOrders,
    'recentOrders' => $recentOrders,
    'dailySales' => $dailySales,
]);
@endphp
<div x-data="dashboardManager({{ $initialData }})">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
        <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-blue-500">
            <p class="text-sm text-gray-500">Today's Orders</p>
            <p class="text-2xl font-bold" x-text="stats.todayOrders">{{ $todayOrders }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-green-500">
            <p class="text-sm text-gray-500">Today's Revenue</p>
            <p class="text-2xl font-bold"><span x-text="stats.todayRevenue.toFixed(2)">{{ number_format($todayRevenue, 2) }}</span> Ks</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-yellow-500">
            <p class="text-sm text-gray-500">Active Tables</p>
            <p class="text-2xl font-bold"><span x-text="stats.activeTables">{{ $activeTables }}</span>/<span x-text="stats.totalTables">{{ $totalTables }}</span></p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-purple-500">
            <p class="text-sm text-gray-500">Pending Orders</p>
            <p class="text-2xl font-bold" x-text="stats.pendingOrders">{{ $pendingOrders }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-red-500">
            <p class="text-sm text-gray-500">Total Users</p>
            <p class="text-2xl font-bold" x-text="stats.totalUsers">{{ $totalUsers }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold mb-4">Recent Orders</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead><tr class="text-left text-gray-500 border-b"><th class="pb-2">Order</th><th class="pb-2">Table</th><th class="pb-2">Status</th><th class="pb-2">Total</th><th class="pb-2">By</th></tr></thead>
                    <tbody>
                        <template x-for="order in recentOrders" :key="order.order_no">
                            <tr class="border-b border-gray-100">
                                <td class="py-2 font-medium"><a :href="order.url" class="text-blue-600 hover:underline" x-text="order.order_no"></a></td>
                                <td class="py-2" x-text="order.table_no"></td>
                                <td class="py-2">
                                    <span class="px-2 py-0.5 rounded-full text-xs" :class="statusClass(order.status)" x-text="order.status"></span>
                                </td>
                                <td class="py-2"><span x-text="order.grand_total.toFixed(2)"></span> Ks</td>
                                <td class="py-2" x-text="order.created_by"></td>
                            </tr>
                        </template>
                        <template x-if="!recentOrders.length">
                            <tr><td colspan="5" class="py-4 text-center text-gray-400">No recent orders</td></tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold mb-4">Today's Sales</h3>
            <p class="text-3xl font-bold text-green-600 mb-2"><span x-text="dailySales.total_sales.toFixed(2)">{{ number_format($dailySales['total_sales'] ?? 0, 2) }}</span> Ks</p>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between"><span class="text-gray-500">Orders</span><span x-text="dailySales.order_count">{{ $dailySales['order_count'] ?? 0 }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Cash</span><span><span x-text="dailySales.cash_sales.toFixed(2)">{{ number_format($dailySales['cash_sales'] ?? 0, 2) }}</span> Ks</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Card</span><span><span x-text="dailySales.card_sales.toFixed(2)">{{ number_format($dailySales['card_sales'] ?? 0, 2) }}</span> Ks</span></div>
            </div>
        </div>
    </div>
</div>

<script>
function dashboardManager(initial) {
    return {
        stats: {
            todayOrders: initial.todayOrders,
            todayRevenue: initial.todayRevenue,
            activeTables: initial.activeTables,
            totalTables: initial.totalTables,
            totalUsers: initial.totalUsers,
            pendingOrders: initial.pendingOrders,
        },
        recentOrders: initial.recentOrders,
        dailySales: initial.dailySales,

        init() {
            setInterval(() => this.poll(), 10000);
        },

        statusClass(status) {
            const map = {
                paid: 'bg-green-100 text-green-700',
                processing: 'bg-blue-100 text-blue-700',
                'new': 'bg-gray-100 text-gray-700',
                cancelled: 'bg-red-100 text-red-700',
            };
            return map[status] || 'bg-yellow-100 text-yellow-700';
        },

        async poll() {
            try {
                const res = await fetch('/admin/dashboard/data');
                const data = await res.json();
                this.stats.todayOrders = data.todayOrders;
                this.stats.todayRevenue = data.todayRevenue;
                this.stats.activeTables = data.activeTables;
                this.stats.totalTables = data.totalTables;
                this.stats.totalUsers = data.totalUsers;
                this.stats.pendingOrders = data.pendingOrders;
                this.recentOrders = data.recentOrders;
                this.dailySales = data.dailySales;
            } catch (e) {}
        },
    };
}
</script>
@endsection
