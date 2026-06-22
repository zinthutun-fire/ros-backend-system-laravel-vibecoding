@extends('layouts.cashier')
@section('title', 'Receipt - ' . $order->order_no)
@section('content')
<div class="mx-auto" style="max-width:400px;">
    @if(session('success'))
    <div class="alert alert-success d-flex align-items-center gap-2 py-2 small" role="alert">
        <i class="bi bi-check-circle"></i> {{ session('success') }}
    </div>
    @endif

    <div class="receipt bg-white rounded-3 border p-4 mb-4">
        <div class="text-center mb-3">
            <h6 class="fw-bold mb-1">Restaurant Name</h6>
            <small class="text-muted d-block">123 Main Street, City</small>
            <small class="text-muted d-block">Tel: (555) 123-4567</small>
        </div>

        <hr style="border-top:2px dashed #dee2e6;">
        <div class="small d-flex flex-column gap-1 mb-3">
            <div class="d-flex justify-content-between"><span class="text-muted">Order:</span><span class="fw-medium">{{ $order->order_no }}</span></div>
            <div class="d-flex justify-content-between"><span class="text-muted">Table:</span><span class="fw-medium">{{ $order->table->table_no }}</span></div>
            <div class="d-flex justify-content-between"><span class="text-muted">Date:</span><span class="fw-medium">{{ $order->created_at->format('M d, Y H:i') }}</span></div>
            <div class="d-flex justify-content-between"><span class="text-muted">Cashier:</span><span class="fw-medium">{{ $order->paidBy?->name ?? $order->createdBy?->name ?? '—' }}</span></div>
        </div>
        <hr style="border-top:2px dashed #dee2e6;">

        <table class="table table-sm small mb-3">
            <thead>
                <tr class="text-muted">
                    <th class="ps-0 border-0 fw-medium">Item</th>
                    <th class="text-center border-0 fw-medium">Qty</th>
                    <th class="text-end pe-0 border-0 fw-medium">Price</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                <tr>
                    <td class="ps-0 border-0">
                        {{ $item->menuItem?->name ?? 'Deleted Item' }}
                        @if($item->modifiers->count() > 0)<br><small class="text-muted">{{ $item->modifiers->pluck('name')->implode(', ') }}</small>@endif
                        @if($item->status === 'voided')<br><small class="text-danger">(Voided)</small>@endif
                    </td>
                    <td class="text-center border-0">{{ $item->qty }}</td>
                    <td class="text-end pe-0 border-0">${{ number_format($item->subtotal, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <hr style="border-top:2px dashed #dee2e6;">
        <div class="small d-flex flex-column gap-1">
            <div class="d-flex justify-content-between"><span class="text-muted">Subtotal</span><span>${{ number_format($order->total, 2) }}</span></div>
            <div class="d-flex justify-content-between"><span class="text-muted">Tax</span><span>${{ number_format($order->tax_total, 2) }}</span></div>
            <div class="d-flex justify-content-between"><span class="text-muted">Service Charge</span><span>${{ number_format($order->service_charge_total, 2) }}</span></div>
            @if((float)$order->discount_total > 0)
            <div class="d-flex justify-content-between text-danger"><span>Discount</span><span>-${{ number_format($order->discount_total, 2) }}</span></div>
            @endif
            <hr style="border-top:2px dashed #dee2e6;">
            <div class="d-flex justify-content-between fw-bold fs-6"><span>Total</span><span>${{ number_format($order->grand_total, 2) }}</span></div>
        </div>

        @if($order->payments->count() > 0)
        <hr style="border-top:2px dashed #dee2e6;">
        <div class="small">
            <p class="fw-medium mb-1">Payment{{ $order->payments->count() > 1 ? 's' : '' }}:</p>
            @foreach($order->payments as $payment)
            <div class="d-flex justify-content-between"><span class="text-muted capitalize">{{ $payment->type }}</span><span>${{ number_format($payment->amount, 2) }}</span></div>
            @endforeach
        </div>
        @endif

        <div class="text-center mt-4 text-muted small">
            <p class="mb-1">Thank you for your visit!</p>
            <p>*** Receipt ***</p>
        </div>
    </div>

    <div class="d-flex justify-content-center gap-2 no-print flex-wrap">
        <button onclick="window.print()" class="btn btn-primary btn-sm d-flex align-items-center gap-1">
            <i class="bi bi-printer"></i> Print Receipt
        </button>
        <a href="{{ route('cashier.orders.detail', $order->id) }}" class="btn btn-outline-secondary btn-sm">Back to Order</a>
        @if($order->status === 'paid')
        <form method="POST" action="{{ route('cashier.orders.close-table', $order->id) }}" class="d-inline">
            @csrf @method('PUT')
            <button type="submit" class="btn btn-dark btn-sm d-flex align-items-center gap-1">
                <i class="bi bi-x-lg"></i> Close Table
            </button>
        </form>
        @endif
    </div>
</div>


@endsection
