@extends('layouts.cashier')
@section('title', 'Dashboard')
@section('content')
@php
$initialData = json_encode([
    'stats' => [
        'todayOrders' => $todayOrders,
        'todayRevenue' => (float) $todayRevenue,
        'activeTables' => $activeTables,
        'pendingOrders' => $pendingOrders,
    ],
    'pendingPayments' => $pendingPaymentTables->map(fn($e) => $e->type === 'merge' ? [
        'type' => 'merge',
        'tables' => $e->tables->pluck('table_no')->values()->toArray(),
        'group_code' => $e->group_code,
        'order_id' => $e->order_id,
        'total' => (float) $e->total,
        'url' => route('cashier.orders.detail', $e->order_id),
    ] : [
        'type' => 'single',
        'table_no' => $e->table->table_no,
        'area' => $e->table->area?->name ?? '—',
        'order_id' => $e->order_id,
        'url' => $e->order_id ? route('cashier.orders.detail', $e->order_id) : '#',
    ])->values()->toArray(),
    'mergedGroups' => $mergedGroups->map(fn($mg) => [
        'group_code' => $mg->group_code,
        'tables' => $mg->tables->pluck('table_no')->values()->toArray(),
        'order_id' => $mg->order_id,
        'order_no' => $mg->order?->order_no ?? '—',
        'url' => route('cashier.orders.detail', $mg->order_id),
    ])->values()->toArray(),
    'recentOrders' => $recentOrders->map(fn($o) => [
        'id' => $o->id,
        'order_no' => $o->order_no,
        'table_no' => $o->table->table_no,
        'status' => $o->status,
        'grand_total' => (float) $o->grand_total,
        'created_by' => $o->createdBy?->name ?? '—',
        'url' => route('cashier.orders.detail', $o->id),
    ])->values()->toArray(),
]);
@endphp
<div x-data="dashboardNotifier({{ $initialData }})">
    {{-- Stat Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm stat-card border-start border-primary border-4 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-primary bg-opacity-10 rounded p-2">
                            <i class="bi bi-receipt text-primary fs-4"></i>
                        </div>
                        <div>
                            <small class="text-muted text-uppercase fw-semibold d-block" style="font-size:0.7rem;">Today's Orders</small>
                            <span class="fs-4 fw-bold" x-text="stats.todayOrders.toLocaleString()">{{ $todayOrders }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm stat-card border-start border-success border-4 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-success bg-opacity-10 rounded p-2">
                            <i class="bi bi-cash-stack text-success fs-4"></i>
                        </div>
                        <div>
                            <small class="text-muted text-uppercase fw-semibold d-block" style="font-size:0.7rem;">Today's Revenue</small>
                            <span class="fs-4 fw-bold" x-text="'$' + stats.todayRevenue.toFixed(2)">${{ number_format($todayRevenue, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm stat-card border-start border-warning border-4 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-warning bg-opacity-10 rounded p-2">
                            <i class="bi bi-table text-warning fs-4"></i>
                        </div>
                        <div>
                            <small class="text-muted text-uppercase fw-semibold d-block" style="font-size:0.7rem;">Active Tables</small>
                            <span class="fs-4 fw-bold" x-text="stats.activeTables.toLocaleString()">{{ $activeTables }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm stat-card border-start border-danger border-4 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-danger bg-opacity-10 rounded p-2">
                            <i class="bi bi-hourglass-split text-danger fs-4"></i>
                        </div>
                        <div>
                            <small class="text-muted text-uppercase fw-semibold d-block" style="font-size:0.7rem;">Pending Orders</small>
                            <span class="fs-4 fw-bold" x-text="stats.pendingOrders.toLocaleString()">{{ $pendingOrders }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Panels Row --}}
    <div class="row g-3 mb-4">
        {{-- Pending Payments --}}
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-bell text-danger fs-5" x-show="pendingPayments.length > 0"></i>
                        <i class="bi bi-bell-slash text-muted fs-5" x-show="!pendingPayments.length"></i>
                        <h6 class="mb-0 fw-semibold">Pending Payments</h6>
                    </div>
                    <span x-show="pendingPayments.length > 0" class="badge bg-danger rounded-pill" x-text="pendingPayments.length"></span>
                </div>
                <div class="card-body">
                    <template x-if="pendingPayments.length > 0">
                        <div class="d-flex flex-column gap-2">
                            <template x-for="entry in pendingPayments" :key="entry.type === 'merge' ? 'mg:' + entry.group_code : 't:' + entry.table_no">
                                <a :href="entry.url"
                                    class="text-decoration-none d-block p-3 bg-danger bg-opacity-10 border border-danger border-opacity-25 rounded-3 hover-bg-danger-transition">
                                    <template x-if="entry.type === 'merge'">
                                        <div>
                                            <div class="d-flex align-items-center justify-content-between mb-1">
                                                <div class="d-flex align-items-center gap-1">
                                                    <i class="bi bi-table text-danger small"></i>
                                                    <template x-for="(t, idx) in entry.tables" :key="idx">
                                                        <span class="fw-semibold text-dark small">
                                                            Table <span x-text="t"></span>
                                                            <span x-show="!$el.nextElementSibling === null" class="text-muted mx-1">+</span>
                                                        </span>
                                                    </template>
                                                    <span class="badge bg-purple text-purple bg-opacity-10" style="font-size:0.6rem;" x-text="entry.group_code"></span>
                                                </div>
                                                <span class="badge bg-danger">Bill Requested</span>
                                            </div>
                                            <div class="small fw-bold text-dark ms-0">
                                                Total: $<span x-text="entry.total.toFixed(2)"></span>
                                            </div>
                                        </div>
                                    </template>
                                    <template x-if="entry.type === 'single'">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="bg-danger bg-opacity-10 rounded p-2">
                                                    <i class="bi bi-table text-danger"></i>
                                                </div>
                                                <div>
                                                    <span class="fw-semibold text-dark small">Table <span x-text="entry.table_no"></span></span>
                                                    <small class="text-muted d-block" x-text="entry.area"></small>
                                                </div>
                                            </div>
                                            <span class="badge bg-danger">Bill Requested</span>
                                        </div>
                                    </template>
                                </a>
                            </template>
                        </div>
                    </template>
                    <template x-if="!pendingPayments.length">
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-check-circle fs-1 text-secondary mb-2 d-block"></i>
                            <p class="small fw-medium mb-0">No pending payment requests</p>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        {{-- Merged Tables --}}
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white d-flex align-items-center gap-2">
                    <i class="bi bi-layers text-purple fs-5"></i>
                    <h6 class="mb-0 fw-semibold">Merged Tables</h6>
                </div>
                <div class="card-body">
                    <template x-if="mergedGroups.length > 0">
                        <div class="d-flex flex-column gap-2">
                            <template x-for="group in mergedGroups" :key="group.group_code">
                                <div class="p-3 bg-purple bg-opacity-10 border border-purple border-opacity-25 rounded-3">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="bg-purple bg-opacity-10 rounded p-2">
                                                <i class="bi bi-layers text-purple"></i>
                                            </div>
                                            <div>
                                                <span class="badge bg-purple bg-opacity-10 text-purple fw-bold font-monospace" style="font-size:0.6rem;" x-text="group.group_code"></span>
                                                <div class="d-flex gap-1 mt-1 flex-wrap">
                                                    <template x-for="t in group.tables" :key="t">
                                                        <span class="badge bg-white text-purple border border-purple border-opacity-50" x-text="t"></span>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>
                                        <a :href="group.url" class="btn btn-primary btn-sm d-flex align-items-center gap-1">
                                            Order <span x-text="group.order_no"></span>
                                            <i class="bi bi-chevron-right"></i>
                                        </a>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>
                    <template x-if="!mergedGroups.length">
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-layers fs-1 text-secondary mb-2 d-block"></i>
                            <p class="small fw-medium mb-0">No merged tables</p>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>

    {{-- Recent Orders --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-clock-history text-muted"></i>
                <h6 class="mb-0 fw-semibold">Recent Orders</h6>
            </div>
            <small class="text-muted">Last 10 orders</small>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 small">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Order</th>
                            <th>Table</th>
                            <th>Status</th>
                            <th class="text-end">Total</th>
                            <th>By</th>
                            <th class="text-end pe-3">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="order in recentOrders" :key="order.id">
                            <tr>
                                <td class="ps-3 fw-semibold" x-text="order.order_no"></td>
                                <td x-text="order.table_no"></td>
                                <td>
                                    <span class="badge badge-status"
                                        :class="{
                                            'bg-success text-white': order.status === 'paid',
                                            'bg-info text-white': order.status === 'processing',
                                            'bg-secondary text-white': order.status === 'new',
                                            'bg-danger text-white': order.status === 'cancelled',
                                            'bg-warning text-dark': !['paid','processing','new','cancelled'].includes(order.status)
                                        }" x-text="order.status"></span>
                                </td>
                                <td class="text-end fw-semibold">$<span x-text="order.grand_total.toFixed(2)"></span></td>
                                <td class="text-muted" x-text="order.created_by"></td>
                                <td class="text-end pe-3">
                                    <a :href="order.url" class="btn btn-sm btn-outline-primary">
                                        View <i class="bi bi-chevron-right"></i>
                                    </a>
                                </td>
                            </tr>
                        </template>
                        <template x-if="!recentOrders.length">
                            <tr><td colspan="6" class="text-center py-4 text-muted">No recent orders</td></tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Notification Toast --}}
    <div x-show="notification" x-cloak x-transition
        class="position-fixed bottom-0 end-0 p-3" style="z-index: 1050;">
        <div class="toast show bg-white border shadow-lg" role="alert">
            <div class="toast-body d-flex align-items-center gap-3">
                <div class="bg-danger bg-opacity-10 rounded p-2">
                    <i class="bi bi-bell text-danger"></i>
                </div>
                <div class="flex-grow-1">
                    <strong class="d-block small text-truncate" x-text="notification?.table"></strong>
                    <small class="text-muted" x-text="notification?.message"></small>
                </div>
                <a :href="notification?.url" class="btn btn-primary btn-sm">View</a>
                <button @click="notification = null" class="btn-close"></button>
            </div>
        </div>
    </div>
</div>

<style>
    .hover-bg-danger-transition:hover { background-color: rgba(220,53,69,0.15) !important; }
    .bg-purple { background-color: #6f42c1 !important; }
    .text-purple { color: #6f42c1 !important; }
    .border-purple { border-color: #6f42c1 !important; }
    .border-opacity-25 { --bs-border-opacity: 0.25; }
    .border-opacity-50 { --bs-border-opacity: 0.5; }
    .bg-opacity-10 { --bs-bg-opacity: 0.1; }
</style>

<script>
function dashboardNotifier(initial) {
    return {
        stats: initial.stats,
        pendingPayments: initial.pendingPayments,
        mergedGroups: initial.mergedGroups,
        recentOrders: initial.recentOrders,
        notification: null,
        prevKeys: new Set(initial.pendingPayments.map(e => e.type === 'merge' ? 'mg:' + e.group_code : 't:' + e.table_no)),

        init() {
            setInterval(() => this.poll(), 10000);
        },

        async poll() {
            try {
                const res = await fetch('{{ route('cashier.dashboard.data') }}');
                const data = await res.json();
                this.stats = data.stats;
                this.pendingPayments = data.pendingPayments;
                this.mergedGroups = data.mergedGroups;
                this.recentOrders = data.recentOrders;

                const currentKeys = new Set(data.pendingPayments.map(e => e.type === 'merge' ? 'mg:' + e.group_code : 't:' + e.table_no));
                const newKey = [...currentKeys].find(k => !this.prevKeys.has(k));
                if (newKey) {
                    const entry = data.pendingPayments.find(e => (e.type === 'merge' ? 'mg:' + e.group_code : 't:' + e.table_no) === newKey);
                    if (entry) {
                        const label = entry.type === 'merge'
                            ? 'Tables ' + entry.tables.join(' + ')
                            : 'Table ' + entry.table_no;
                        this.notification = {
                            table: label,
                            message: 'requested a bill',
                            url: entry.url,
                        };
                        setTimeout(() => this.notification = null, 8000);
                    }
                }
                this.prevKeys = currentKeys;
            } catch (e) {}
        },
    };
}
</script>
@endsection
