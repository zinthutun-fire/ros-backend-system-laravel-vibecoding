@extends('layouts.cashier')
@section('title', 'Daily Report')
@section('content')
<div>
    {{-- Header --}}
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 mb-4">
        <h5 class="fw-semibold mb-0">Daily Report</h5>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('cashier.reports.daily.csv', request()->query()) }}"
                class="btn btn-success btn-sm d-flex align-items-center gap-1">
                <i class="bi bi-download"></i> CSV
            </a>
            <a href="{{ route('cashier.reports.daily.pdf', request()->query()) }}"
                class="btn btn-danger btn-sm d-flex align-items-center gap-1">
                <i class="bi bi-file-earmark-pdf"></i> PDF
            </a>
            <form method="GET" action="{{ route('cashier.reports.daily') }}" class="d-flex gap-2">
                <input type="date" name="date" value="{{ $date }}" class="form-control form-control-sm">
                <button type="submit" class="btn btn-primary btn-sm">View</button>
            </form>
        </div>
    </div>

    {{-- Stat Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm stat-card border-start border-success border-4 h-100">
                <div class="card-body">
                    <small class="text-muted text-uppercase fw-semibold d-block" style="font-size:0.7rem;">Total Sales</small>
                    <span class="fs-4 fw-bold">{{ number_format($report['total_sales'], 2) }} Ks</span>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm stat-card border-start border-primary border-4 h-100">
                <div class="card-body">
                    <small class="text-muted text-uppercase fw-semibold d-block" style="font-size:0.7rem;">Orders</small>
                    <span class="fs-4 fw-bold">{{ $report['order_count'] }}</span>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm stat-card border-start border-warning border-4 h-100">
                <div class="card-body">
                    <small class="text-muted text-uppercase fw-semibold d-block" style="font-size:0.7rem;">Tables Occupied</small>
                    <span class="fs-4 fw-bold">{{ $utilization['occupied_tables'] }}/{{ $utilization['total_tables'] }}</span>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm stat-card border-start border-danger border-4 h-100">
                <div class="card-body">
                    <small class="text-muted text-uppercase fw-semibold d-block" style="font-size:0.7rem;">Utilization</small>
                    <span class="fs-4 fw-bold">{{ $utilization['utilization_rate'] }}%</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Payment & Table Status --}}
    <div class="row g-3 mb-4">
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white">
                    <h6 class="mb-0 fw-semibold">Payment Summary</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-column gap-2">
                        <div class="d-flex justify-content-between align-items-center p-3 bg-success bg-opacity-10 rounded-3">
                            <span class="fw-medium small">Cash Sales</span>
                            <span class="fw-bold">{{ number_format($report['cash_sales'], 2) }} Ks</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center p-3 bg-info bg-opacity-10 rounded-3">
                            <span class="fw-medium small">Card Sales</span>
                            <span class="fw-bold">{{ number_format($report['card_sales'], 2) }} Ks</span>
                        </div>
                        <hr class="my-1">
                        <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded-3">
                            <span class="fw-semibold small">Total</span>
                            <span class="fw-bold fs-6">{{ number_format($report['total_sales'], 2) }} Ks</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white">
                    <h6 class="mb-0 fw-semibold">Table Status</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-column gap-2">
                        @foreach($utilization['table_statuses'] as $status => $count)
                        <div class="d-flex justify-content-between align-items-center p-3 rounded-3
                            @switch($status)
                                @case('available') bg-success bg-opacity-10 @break
                                @case('occupied') bg-danger bg-opacity-10 @break
                                @case('ordering') bg-warning bg-opacity-10 @break
                                @case('payment') bg-info bg-opacity-10 @break
                                @default bg-light
                            @endswitch">
                            <span class="fw-medium small text-capitalize">{{ $status }}</span>
                            <span class="fw-bold">{{ $count }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Top Items --}}
    @if(count($topItems) > 0)
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white">
            <h6 class="mb-0 fw-semibold">Top Items</h6>
        </div>
        <div class="table-responsive">
            <table class="table table-sm small align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Item</th>
                        <th>Qty Sold</th>
                        <th>Revenue</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($topItems as $item)
                    <tr>
                        <td class="ps-3 fw-medium">{{ $item['menu_item']['name'] ?? 'Item #' . $item['menu_item_id'] }}</td>
                        <td>{{ $item['total_qty'] }}</td>
                        <td>{{ number_format($item['total_revenue'], 2) }} Ks</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- Payments Table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white">
            <h6 class="mb-0 fw-semibold">Payments ({{ $report['payments']->count() }})</h6>
        </div>
        <div class="table-responsive">
            <table class="table table-sm small align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Order</th>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Time</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($report['payments'] as $payment)
                    <tr>
                        <td class="ps-3 fw-medium">{{ $payment->order->order_no ?? '—' }}</td>
                        <td class="text-capitalize">{{ $payment->type }}</td>
                        <td>{{ number_format($payment->amount, 2) }} Ks</td>
                        <td class="text-muted">{{ $payment->paid_at->format('H:i') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center py-4 text-muted">No payments for this date</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
