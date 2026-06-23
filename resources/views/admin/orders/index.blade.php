@extends('layouts.admin')
@section('title', 'Orders')
@section('content')
@php
$initialOrders = $orders->map(fn($o) => [
    'id' => $o->id,
    'order_no' => $o->order_no,
    'table_no' => $o->table->table_no,
    'status' => $o->status,
    'items_count' => $o->items_count,
    'grand_total' => (float) $o->grand_total,
    'created_by' => $o->createdBy?->name ?? '—',
    'created_at' => $o->created_at->format('M d, H:i'),
    'can_cancel' => in_array($o->status, ['new', 'processing']),
    'cancel_url' => route('admin.orders.cancel', $o->id),
])->values()->toArray();
$initialPagination = $orders->appends(request()->query())->links()->toHtml();
$currentStatus = request('status', '');
$currentSearch = request('search', '');
@endphp
<div x-data="ordersManager('{{ $currentStatus }}', '{{ $currentSearch }}', {{ json_encode($initialOrders) }}, '{{ $initialPagination }}')">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-semibold">Orders</h2>
        <div class="flex space-x-2">
            <input type="text" x-model="search" placeholder="Search by order no..." class="px-3 py-2 border rounded-lg text-sm w-48" @keyup.enter="applySearch()">
            <select @change="filter($event.target.value)" class="px-3 py-2 border rounded-lg text-sm">
                <option value="">All Status</option>
                <option value="new" {{ $currentStatus === 'new' ? 'selected' : '' }}>New</option>
                <option value="processing" {{ $currentStatus === 'processing' ? 'selected' : '' }}>Processing</option>
                <option value="completed" {{ $currentStatus === 'completed' ? 'selected' : '' }}>Completed</option>
                <option value="paid" {{ $currentStatus === 'paid' ? 'selected' : '' }}>Paid</option>
                <option value="cancelled" {{ $currentStatus === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
            </select>
        </div>
    </div>
    @if(session('success'))<div class="bg-green-50 text-green-700 px-4 py-3 rounded-lg mb-4 text-sm">{{ session('success') }}</div>@endif
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50"><tr class="text-left text-gray-500"><th class="px-4 py-3">Order No</th><th class="px-4 py-3">Table</th><th class="px-4 py-3">Status</th><th class="px-4 py-3">Items</th><th class="px-4 py-3">Total</th><th class="px-4 py-3">Created By</th><th class="px-4 py-3">Date</th><th class="px-4 py-3">Actions</th></tr></thead>
            <tbody>
                <template x-for="o in orders" :key="o.id">
                    <tr class="border-t">
                        <td class="px-4 py-3 font-medium" x-text="o.order_no"></td>
                        <td class="px-4 py-3" x-text="o.table_no"></td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 rounded-full text-xs" :class="statusClass(o.status)" x-text="o.status"></span>
                        </td>
                        <td class="px-4 py-3" x-text="o.items_count"></td>
                        <td class="px-4 py-3"><span x-text="o.grand_total.toFixed(2)"></span> Ks</td>
                        <td class="px-4 py-3" x-text="o.created_by"></td>
                        <td class="px-4 py-3 text-xs text-gray-500" x-text="o.created_at"></td>
                        <td class="px-4 py-3">
                            <template x-if="o.can_cancel">
                                <form :action="o.cancel_url" method="POST" class="inline" onsubmit="return confirm('Cancel this order?')">
                                    @csrf @method('PUT')
                                    <button class="text-red-600 hover:text-red-800 text-xs">Cancel</button>
                                </form>
                            </template>
                            <template x-if="!o.can_cancel">
                                <span class="text-xs text-gray-400">—</span>
                            </template>
                        </td>
                    </tr>
                </template>
                <template x-if="!orders.length">
                    <tr><td colspan="8" class="px-4 py-8 text-center text-gray-400">No orders found</td></tr>
                </template>
            </tbody>
        </table>
    </div>
    <div class="mt-4" x-html="paginationHtml">{{ $orders->links() }}</div>
</div>

<script>
function ordersManager(status, search, initialOrders, initialPagination) {
    return {
        orders: initialOrders,
        paginationHtml: initialPagination,
        currentStatus: status,
        search: search,

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

        filter(val) {
            this.currentStatus = val;
            this.poll();
        },

        applySearch() {
            this.poll();
        },

        async poll() {
            try {
                let url = '/admin/orders/data';
                const params = [];
                if (this.currentStatus) params.push('status=' + this.currentStatus);
                if (this.search) params.push('search=' + encodeURIComponent(this.search));
                if (params.length) url += '?' + params.join('&');
                const res = await fetch(url);
                const data = await res.json();
                this.orders = data.orders;
                this.paginationHtml = data.pagination_html;
            } catch (e) {}
        },
    };
}
</script>
@endsection