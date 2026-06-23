@extends('layouts.cashier')
@section('title', 'Orders')
@section('content')
@php
$initialOrders = $orders->map(fn($o) => [
    'id' => $o->id,
    'order_no' => $o->order_no,
    'table_no' => $o->table->table_no,
    'status' => $o->status,
    'items_count' => $o->items->count(),
    'grand_total' => (float) $o->grand_total,
    'created_at' => $o->created_at->format('M d, H:i'),
    'created_by' => $o->createdBy?->name ?? '—',
    'url' => route('cashier.orders.detail', $o->id),
])->values()->toArray();
$initialPagination = $orders->appends(request()->query())->links('pagination::bootstrap-5')->toHtml();
@endphp
<div x-data='orderList(@json($initialOrders), {{ $orders->total() }}, @json($initialPagination))'>
    {{-- Header & Tabs --}}
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 mb-4">
        <div>
            <h5 class="fw-semibold mb-1">Orders</h5>
            <p class="text-muted small mb-0">
                <span x-text="total"></span> orders
                <span x-show="currentStatus === 'active'" class="badge bg-warning bg-opacity-10 text-warning ms-1">Active</span>
                <span x-show="currentStatus === 'paid'" class="badge bg-success bg-opacity-10 text-success ms-1">Paid</span>
            </p>
        </div>
        <div class="btn-group btn-group-sm" role="group">
            <a :href="'{{ route('cashier.orders') }}?status=active'" class="btn btn-outline-warning"
                :class="currentStatus === 'active' ? 'active' : ''">
                <span class="spinner-grow spinner-grow-sm me-1" style="width:0.4rem;height:0.4rem;"></span>
                Active
            </a>
            <a :href="'{{ route('cashier.orders') }}?status=paid'" class="btn btn-outline-success"
                :class="currentStatus === 'paid' ? 'active' : ''">
                <i class="bi bi-check-circle me-1"></i>
                Paid
            </a>
            <a :href="'{{ route('cashier.orders') }}?status=all'" class="btn btn-outline-dark"
                :class="currentStatus === 'all' ? 'active' : ''">
                <i class="bi bi-grid"></i>
                All
            </a>
        </div>
    </div>

    {{-- Alerts --}}
    @if(session('success'))
    <div class="alert alert-success d-flex align-items-center gap-2 py-2 small" role="alert">
        <i class="bi bi-check-circle"></i> {{ session('success') }}
    </div>
    @endif

    {{-- Filter Form --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('cashier.orders') }}" class="row g-3 align-items-end">
                <input type="hidden" name="status" value="{{ $status }}">
                <div class="col-auto">
                    <label class="form-label small fw-medium">From</label>
                    <input type="date" name="date_from" value="{{ $dateFrom ?? '' }}" class="form-control form-control-sm">
                </div>
                <div class="col-auto">
                    <label class="form-label small fw-medium">To</label>
                    <input type="date" name="date_to" value="{{ $dateTo ?? '' }}" class="form-control form-control-sm">
                </div>
                <div class="col-auto d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm d-flex align-items-center gap-1">
                        <i class="bi bi-search"></i> Filter
                    </button>
                    <a href="{{ route('cashier.orders', ['status' => $status]) }}" class="btn btn-outline-secondary btn-sm">Today</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Orders Table --}}
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Order No</th>
                        <th>Table</th>
                        <th>Status</th>
                        <th class="text-center">Items</th>
                        <th class="text-end">Total</th>
                        <th>Date</th>
                        <th>Created By</th>
                        <th class="text-end pe-3">Action</th>
                    </tr>
                </thead>
                <tbody id="orders-tbody">
                    <template x-for="o in orders" :key="o.id">
                        <tr>
                            <td class="ps-3 fw-semibold" x-text="o.order_no"></td>
                            <td>
                                <span class="d-inline-flex align-items-center gap-1">
                                    <i class="bi bi-table text-muted small"></i>
                                    <span x-text="o.table_no"></span>
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-status"
                                    :class="{
                                        'bg-success': o.status === 'paid',
                                        'bg-info': o.status === 'processing',
                                        'bg-secondary': o.status === 'new',
                                        'bg-danger': o.status === 'cancelled',
                                        'bg-success': o.status === 'completed',
                                        'bg-purple': o.status === 'merged',
                                        'bg-warning text-dark': !['paid','processing','new','cancelled','completed','merged'].includes(o.status)
                                    }" x-text="o.status"></span>
                            </td>
                            <td class="text-center fw-medium" x-text="o.items_count"></td>
                            <td class="text-end fw-semibold"><span x-text="o.grand_total.toFixed(2)"></span> Ks</td>
                            <td class="text-muted small" x-text="o.created_at"></td>
                            <td class="text-muted" x-text="o.created_by"></td>
                            <td class="text-end pe-3">
                                <a :href="o.url" class="btn btn-sm btn-outline-primary">
                                    View <i class="bi bi-chevron-right"></i>
                                </a>
                            </td>
                        </tr>
                    </template>
                    <template x-if="!orders.length">
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-receipt fs-1 text-secondary mb-2 d-block"></i>
                                <p class="fw-medium mb-0">No orders found</p>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pagination --}}
    <div id="orders-pagination" class="d-flex justify-content-center mt-3" x-html="paginationHtml">
        {{ $orders->appends(request()->query())->links('pagination::bootstrap-5') }}
    </div>
</div>

<style>
    .bg-purple { background-color: #6f42c1 !important; }
    .bg-opacity-10 { --bs-bg-opacity: 0.1; }
</style>

<script>
function orderList(initialOrders, initialTotal, initialPagination) {
    return {
        orders: initialOrders,
        total: initialTotal,
        paginationHtml: initialPagination,
        currentStatus: '{{ $status }}',

        init() {
            setInterval(() => this.poll(), 10000);
        },

        async poll() {
            try {
                const url = new URL('{{ route('cashier.orders.data') }}', window.location.origin);
                url.searchParams.set('status', this.currentStatus);
                const params = new URLSearchParams(window.location.search);
                ['date_from', 'date_to', 'page'].forEach(k => {
                    if (params.has(k)) url.searchParams.set(k, params.get(k));
                });
                const res = await fetch(url);
                const data = await res.json();
                this.orders = data.orders;
                this.total = data.total;
                this.paginationHtml = data.pagination_html;
            } catch (e) {}
        },
    };
}
</script>
@endsection
