@extends('layouts.cashier')
@section('title', 'Order ' . $order->order_no)
@section('content')
@php
$initialData = json_encode([
    'status' => $order->status,
    'status_class' => match($order->status) {
        'paid' => 'bg-success',
        'processing' => 'bg-info',
        'new' => 'bg-secondary',
        'cancelled' => 'bg-danger',
        default => 'bg-warning text-dark',
    },
    'grand_total' => (float) $order->grand_total,
    'total' => (float) $order->total,
    'tax_total' => (float) $order->tax_total,
    'service_charge_total' => (float) $order->service_charge_total,
    'discount_total' => (float) $order->discount_total,
    'items_count' => $order->items->where('status', '!=', 'voided')->sum('qty'),
    'is_active' => $order->isActive(),
    'items' => $order->items->map(fn($item) => [
        'id' => $item->id,
        'name' => $item->menuItem?->name ?? 'Deleted Item',
        'modifiers' => $item->modifiers->pluck('name')->implode(', '),
        'qty' => $item->qty,
        'subtotal' => (float) $item->subtotal,
        'status' => $item->status,
        'status_class' => $item->status === 'voided' ? 'bg-danger' : 'bg-info',
    ])->values()->toArray(),
    'payments' => $order->payments->map(fn($p) => [
        'type' => $p->type,
        'amount' => (float) $p->amount,
        'time' => $p->created_at->format('H:i'),
    ])->values()->toArray(),
    'is_merged' => $mergeInfo !== null,
]);
@endphp
<div x-data="{ showPayment: false, showSplit: false, showDiscount: false }">
<div x-data="orderDetailManager({{ $order->id }}, {{ $initialData }})">
    {{-- Header --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2">
                        <h5 class="fw-semibold mb-0">
                            Order
                            @if($mergeInfo)
                            #{{ implode(' + #', $mergeInfo['order_numbers']) }}
                            @else
                            #{{ $order->order_no }}
                            @endif
                        </h5>
                        <span class="badge badge-status" :class="statusClass" x-text="status">{{ $order->status }}</span>
                    </div>
                    @if($mergeInfo)
                    <p class="text-muted small mt-1 d-flex align-items-center gap-1">
                        <i class="bi bi-layers text-purple"></i>
                        Tables:
                        @foreach($mergeInfo['tables'] as $tableId => $tableNo)
                        <span class="badge bg-purple bg-opacity-10 text-purple border border-purple border-opacity-25 ms-1">{{ $tableNo }}</span>
                        @endforeach
                        <span class="badge bg-purple bg-opacity-10 text-purple font-monospace ms-1">{{ $mergeInfo['group_code'] }}</span>
                    </p>
                    @else
                    <p class="text-muted small mt-1 d-flex align-items-center gap-1">
                        <i class="bi bi-table"></i>
                        Table {{ $order->table->table_no }} ({{ $order->table->area->name }})
                    </p>
                    @endif
                </div>
                <div class="d-flex gap-2">
                    <template x-if="isActive">
                        <div class="d-flex gap-2">
                            <button @click="showPayment = true" class="btn btn-success btn-sm d-flex align-items-center gap-1">
                                <i class="bi bi-credit-card"></i> Payment
                            </button>
                            <button @click="showSplit = true" class="btn btn-primary btn-sm d-flex align-items-center gap-1">
                                <i class="bi bi-diagram-2"></i> Split
                            </button>
                            <button @click="showDiscount = true" class="btn btn-warning btn-sm d-flex align-items-center gap-1">
                                <i class="bi bi-percent"></i> Discount
                            </button>
                        </div>
                    </template>
                    <template x-if="status === 'paid'">
                        <form method="POST" action="{{ route('cashier.orders.close-table', $order->id) }}" class="d-inline">
                            @csrf @method('PUT')
                            <button type="submit" class="btn btn-dark btn-sm d-flex align-items-center gap-1">
                                <i class="bi bi-x-lg"></i> Close {{ $mergeInfo ? 'Tables' : 'Table' }}
                            </button>
                        </form>
                    </template>
                </div>
            </div>
        </div>
    </div>

    {{-- Alerts --}}
    @if(session('success'))
    <div class="alert alert-success d-flex align-items-center gap-2 py-2 small" role="alert">
        <i class="bi bi-check-circle"></i> {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger d-flex align-items-center gap-2 py-2 small" role="alert">
        <i class="bi bi-exclamation-circle"></i> {{ session('error') }}
    </div>
    @endif

    {{-- Main Content --}}
    <div class="row g-4">
        {{-- Items --}}
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h6 class="fw-semibold mb-0">Order Items</h6>
                        <span class="badge bg-light text-dark d-flex align-items-center gap-1">
                            <i class="bi bi-box-seam"></i>
                            <span x-text="itemsCount"></span> item<span x-show="itemsCount !== 1">s</span>
                        </span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm small mb-0">
                            <thead class="table-secondary">
                                <tr>
                                    <th>Item</th>
                                    <th>Modifiers</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-end">Price</th>
                                    <th>Status</th>
                                    <th class="text-end"></th>
                                </tr>
                            </thead>
                            <tbody id="order-items-tbody">
                                <template x-for="item in items" :key="item.id">
                                    <tr>
                                        <td class="fw-medium" x-text="item.name"></td>
                                        <td class="text-muted small">
                                            <span x-text="item.modifiers || '—'"></span>
                                        </td>
                                        <td class="text-center fw-medium" x-text="item.qty"></td>
                                        <td class="text-end fw-medium"><span x-text="item.subtotal.toFixed(2)"></span> Ks</td>
                                        <td>
                                            <span class="badge badge-status" :class="item.status_class" x-text="item.status"></span>
                                        </td>
                                        <td class="text-end">
                                            <template x-if="item.status !== 'voided' && isActive">
                                                <form method="POST" action="{{ route('cashier.orders.void-item') }}" class="d-inline" onsubmit="return confirm('Void this item?')">
                                                    @csrf
                                                    <input type="hidden" name="item_id" :value="item.id">
                                                    <input type="hidden" name="reason" value="Voided by cashier">
                                                    <button class="btn btn-sm btn-outline-danger py-0 px-1 small" style="font-size:0.75rem;">Void</button>
                                                </form>
                                            </template>
                                        </td>
                                    </tr>
                                </template>
                                <template x-if="!items.length">
                                    <tr><td colspan="6" class="text-center py-4 text-muted">No items in this order</td></tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    {{-- Payments --}}
                    <template x-if="payments.length > 0">
                        <div class="mt-4 pt-3 border-top">
                            <h6 class="fw-semibold small mb-2">Payments</h6>
                            <div class="d-flex flex-column gap-1">
                                <template x-for="p in payments" :key="p.time + p.type">
                                    <div class="d-flex align-items-center justify-content-between p-2 bg-light rounded">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="bg-success bg-opacity-10 rounded p-1">
                                                <i class="bi bi-check text-success small"></i>
                                            </div>
                                            <span class="small fw-medium text-capitalize" x-text="p.type"></span>
                                            <small class="text-muted" x-text="'— ' + p.time"></small>
                                        </div>
                                        <span class="small fw-bold"><span x-text="p.amount.toFixed(2)"></span> Ks</span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        {{-- Summary --}}
        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="fw-semibold mb-3">Order Summary</h6>
                    <div class="d-flex flex-column gap-2 small">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Subtotal</span>
                            <span class="fw-semibold"><span x-text="summary.total.toFixed(2)"></span> Ks</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Tax</span>
                            <span class="fw-semibold"><span x-text="summary.tax_total.toFixed(2)"></span> Ks</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Service Charge</span>
                            <span class="fw-semibold"><span x-text="summary.service_charge_total.toFixed(2)"></span> Ks</span>
                        </div>
                        <template x-if="summary.discount_total > 0">
                            <div class="d-flex justify-content-between text-danger bg-danger bg-opacity-10 p-2 rounded">
                                <span class="fw-medium">Discount</span>
                                <span class="fw-bold">-<span x-text="summary.discount_total.toFixed(2)"></span> Ks</span>
                            </div>
                        </template>
                        <hr class="my-1">
                        <div class="d-flex justify-content-between fw-bold fs-6">
                            <span>Total</span>
                            <span><span x-text="summary.grand_total.toFixed(2)"></span> Ks</span>
                        </div>
                    </div>
                    <hr>
                    <div class="text-muted small d-flex flex-column gap-1">
                        <div class="d-flex align-items-center gap-1">
                            <i class="bi bi-clock"></i>
                            Created: {{ $order->created_at->format('M d, Y H:i') }} by {{ $order->createdBy?->name ?? '—' }}
                        </div>
                        @if($order->paid_at)
                        <div class="d-flex align-items-center gap-1 text-success">
                            <i class="bi bi-check-circle"></i>
                            Paid: {{ $order->paid_at->format('M d, Y H:i') }}
                        </div>
                        @endif
                        @if($order->paidBy)
                        <div class="d-flex align-items-center gap-1">
                            <i class="bi bi-person"></i>
                            Paid by: {{ $order->paidBy->name ?? '—' }}
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

    {{-- Payment Modal --}}
    <div x-show="showPayment" x-cloak @click.away="showPayment = false" class="position-fixed" style="top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.5);z-index:1050;">
        <div style="height:100%;display:flex;align-items:center;justify-content:center;">
            <div @click.stop class="bg-white rounded-3 shadow-lg mx-3" style="max-width:480px;width:100%;">
                <div class="p-4">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="bg-success bg-opacity-10 rounded p-2">
                            <i class="bi bi-credit-card text-success"></i>
                        </div>
                        <h5 class="mb-0 fw-semibold">Process Payment</h5>
                    </div>
                    <p class="small text-muted bg-light rounded p-3 mb-3">
                        Total due: <strong class="text-dark">{{ number_format($order->grand_total, 2) }} Ks</strong>
                    </p>
                    <form method="POST" action="{{ route('cashier.orders.payment') }}">
                        @csrf
                        <input type="hidden" name="order_id" value="{{ $order->id }}">
                        <input type="hidden" name="amount" value="{{ $order->grand_total }}">
                        <div class="mb-3">
                            <label class="form-label small fw-medium">Payment Method</label>
                            <select name="type" class="form-select form-select-sm" x-on:change="document.getElementById('cash-fields').style.display = ($event.target.value === 'cash' || $event.target.value === 'split_cash_card') ? 'block' : 'none'">
                                <option value="cash">Cash</option>
                                <option value="card">Card</option>
                                <option value="split_cash_card">Split Cash/Card</option>
                            </select>
                        </div>
                        <div id="cash-fields" class="mb-3">
                            <label class="form-label small fw-medium">Amount Tendered (Ks)</label>
                            <input type="number" step="0.01" name="tendered" value="{{ $order->grand_total }}" class="form-control form-control-sm">
                        </div>
                        <div class="d-flex justify-content-end gap-2">
                            <button type="button" @click="showPayment = false" class="btn btn-sm btn-secondary">Cancel</button>
                            <button type="submit" class="btn btn-sm btn-success">Pay {{ number_format($order->grand_total, 2) }} Ks</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Split Payment Modal --}}
    <div x-show="showSplit" x-cloak @click.away="showSplit = false" class="position-fixed" style="top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.5);z-index:1050;">
        <div style="height:100%;display:flex;align-items:center;justify-content:center;">
            <div @click.stop class="bg-white rounded-3 shadow-lg mx-3" style="max-width:520px;width:100%;">
                <div class="p-4">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="bg-primary bg-opacity-10 rounded p-2">
                            <i class="bi bi-diagram-2 text-primary"></i>
                        </div>
                        <h5 class="mb-0 fw-semibold">Split Payment</h5>
                    </div>
                    <p class="small text-muted bg-light rounded p-3 mb-3">
                        Total due: <strong class="text-dark">{{ number_format($order->grand_total, 2) }} Ks</strong>
                    </p>
                    <form method="POST" action="{{ route('cashier.orders.split') }}">
                        @csrf
                        <input type="hidden" name="order_id" value="{{ $order->id }}">
                        <div x-data="{ splits: [{ method: 'cash', amount: {{ $order->grand_total / 2 }} }, { method: 'card', amount: {{ $order->grand_total / 2 }} }] }">
                            <template x-for="(split, index) in splits" :key="index">
                                <div class="row g-2 mb-2 align-items-end">
                                    <div class="col-5">
                                        <label class="form-label small fw-medium" x-text="'Split ' + (index + 1)"></label>
                                        <select x-model="split.method" :name="'splits[' + index + '][method]'" class="form-select form-select-sm">
                                            <option value="cash">Cash</option>
                                            <option value="card">Card</option>
                                            <option value="split_cash_card">Cash+Card</option>
                                        </select>
                                    </div>
                                    <div class="col-5">
                                        <label class="form-label small fw-medium">Amount</label>
                                        <input type="number" step="0.01" x-model="split.amount" :name="'splits[' + index + '][amount]'" class="form-control form-control-sm">
                                    </div>
                                    <div class="col-2 d-flex align-items-end">
                                        <button type="button" @click="splits.splice(index, 1)" class="btn btn-sm btn-outline-danger py-1" x-show="splits.length > 2">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </template>
                            <button type="button" @click="splits.push({ method: 'cash', amount: 0 })" class="btn btn-sm btn-link text-decoration-none p-0 d-flex align-items-center gap-1">
                                <i class="bi bi-plus"></i> Add another split
                            </button>
                            <hr>
                            <div class="d-flex justify-content-end gap-2">
                                <button type="button" @click="showSplit = false" class="btn btn-sm btn-secondary">Cancel</button>
                                <button type="submit" class="btn btn-sm btn-primary">Process Split</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Discount Modal --}}
    <div x-show="showDiscount" x-cloak @click.away="showDiscount = false" class="position-fixed" style="top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.5);z-index:1050;">
        <div style="height:100%;display:flex;align-items:center;justify-content:center;">
            <div @click.stop class="bg-white rounded-3 shadow-lg mx-3" style="max-width:420px;width:100%;">
                <div class="p-4">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="bg-warning bg-opacity-10 rounded p-2">
                            <i class="bi bi-percent text-warning"></i>
                        </div>
                        <h5 class="mb-0 fw-semibold">Apply Discount</h5>
                    </div>
                    <p class="small text-muted bg-light rounded p-3 mb-3">
                        Subtotal: <strong class="text-dark">{{ number_format($order->total, 2) }} Ks</strong>
                    </p>
                    <form method="POST" action="{{ route('cashier.orders.discount') }}">
                        @csrf
                        <input type="hidden" name="order_id" value="{{ $order->id }}">
                        <div class="mb-3">
                            <label class="form-label small fw-medium">Type</label>
                            <select name="discount_type" class="form-select form-select-sm">
                                <option value="fixed">Fixed (Ks)</option>
                                <option value="percentage">Percentage (%)</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-medium">Value</label>
                            <input type="number" step="0.01" name="discount_value" required class="form-control form-control-sm">
                        </div>
                        <div class="d-flex justify-content-end gap-2">
                        <button type="button" @click="showDiscount = false" class="btn btn-sm btn-secondary">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-warning">Apply</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    .bg-purple { background-color: #6f42c1 !important; }
    .text-purple { color: #6f42c1 !important; }
    .border-purple { border-color: #6f42c1 !important; }
    .bg-opacity-10 { --bs-bg-opacity: 0.1; }
    .border-opacity-25 { --bs-border-opacity: 0.25; }
    .capitalize { text-transform: capitalize; }
</style>

<script>
function orderDetailManager(orderId, initial) {
    return {
        status: initial.status,
        statusClass: initial.status_class,
        items: initial.items,
        payments: initial.payments,
        itemsCount: initial.items_count,
        isActive: initial.is_active,
        isMerged: initial.is_merged,
        summary: {
            total: initial.total,
            tax_total: initial.tax_total,
            service_charge_total: initial.service_charge_total,
            discount_total: initial.discount_total,
            grand_total: initial.grand_total,
        },

        init() {
            setInterval(() => this.poll(), 5000);
        },

        async poll() {
            try {
                const res = await fetch('/cashier/orders/' + orderId + '/data');
                const data = await res.json();
                this.status = data.status;
                this.statusClass = data.status_class;
                this.items = data.items;
                this.payments = data.payments;
                this.itemsCount = data.items_count;
                this.isActive = data.is_active;
                this.summary = {
                    total: data.total,
                    tax_total: data.tax_total,
                    service_charge_total: data.service_charge_total,
                    discount_total: data.discount_total,
                    grand_total: data.grand_total,
                };
            } catch (e) {}
        },
    };
}
</script>
@endsection
