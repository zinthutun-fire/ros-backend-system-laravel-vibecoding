@extends('layouts.cashier')
@section('title', 'Monthly Report')
@section('content')
<div>
    {{-- Header --}}
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 mb-4">
        <div>
            <h5 class="fw-semibold mb-0">Monthly Report</h5>
            <small class="text-muted">{{ DateTime::createFromFormat('!m', $month)->format('F') }} {{ $year }}</small>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('cashier.reports.monthly.csv', request()->query()) }}"
                class="btn btn-success btn-sm d-flex align-items-center gap-1">
                <i class="bi bi-download"></i> CSV
            </a>
            <a href="{{ route('cashier.reports.monthly.pdf', request()->query()) }}"
                class="btn btn-danger btn-sm d-flex align-items-center gap-1">
                <i class="bi bi-file-earmark-pdf"></i> PDF
            </a>
            <form method="GET" action="{{ route('cashier.reports.monthly') }}" class="d-flex gap-2">
                <select name="month" class="form-select form-select-sm" style="width:auto;">
                    @for($m = 1; $m <= 12; $m++)
                    <option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>{{ DateTime::createFromFormat('!m', (string)$m)->format('F') }}</option>
                    @endfor
                </select>
                <select name="year" class="form-select form-select-sm" style="width:auto;">
                    @for($y = now()->year - 2; $y <= now()->year; $y++)
                    <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
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
                    <small class="text-muted text-uppercase fw-semibold d-block" style="font-size:0.7rem;">Total Orders</small>
                    <span class="fs-4 fw-bold">{{ $report['order_count'] }}</span>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm stat-card border-start border-warning border-4 h-100">
                <div class="card-body">
                    <small class="text-muted text-uppercase fw-semibold d-block" style="font-size:0.7rem;">Cash Sales</small>
                    <span class="fs-4 fw-bold">{{ number_format($cashTotal, 2) }} Ks</span>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm stat-card border-start border-danger border-4 h-100">
                <div class="card-body">
                    <small class="text-muted text-uppercase fw-semibold d-block" style="font-size:0.7rem;">Card Sales</small>
                    <span class="fs-4 fw-bold">{{ number_format($cardTotal, 2) }} Ks</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Chart --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white">
            <h6 class="mb-0 fw-semibold">Daily Sales Trend</h6>
        </div>
        <div class="card-body">
            <div style="height:300px;">
                <canvas id="dailySalesChart"></canvas>
            </div>
        </div>
    </div>

    {{-- Daily Breakdown --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white">
            <h6 class="mb-0 fw-semibold">Daily Breakdown</h6>
        </div>
        <div class="table-responsive">
            <table class="table table-sm small align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Day</th>
                        <th>Date</th>
                        <th>Sales</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($chartLabels as $i => $day)
                    @php $amount = $chartData[$i]; @endphp
                    <tr class="{{ $amount > 0 ? '' : 'text-muted' }}">
                        <td class="ps-3 fw-medium">Day {{ $day }}</td>
                        <td>{{ DateTime::createFromFormat('Y-m-d', sprintf('%s-%02d-%02d', $year, $month, (int)$day))->format('M d, Y') }}</td>
                        <td>{{ number_format($amount, 2) }} Ks</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('dailySalesChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($chartLabels) !!},
            datasets: [{
                label: 'Daily Sales (Ks)',
                data: {!! json_encode($chartData) !!},
                backgroundColor: 'rgba(13, 110, 253, 0.7)',
                borderColor: 'rgba(13, 110, 253, 1)',
                borderWidth: 1,
                borderRadius: 4,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) { return value.toFixed(2) + ' Ks'; }
                    }
                },
                x: {
                    grid: { display: false }
                }
            }
        }
    });
});
</script>
@endpush
