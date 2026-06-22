@extends('layouts.admin')
@section('title', 'Reports')
@section('content')
<div class="flex justify-between items-center mb-6">
    <h2 class="text-xl font-semibold">Reports</h2>
    <div class="flex items-center space-x-3">
        <input type="date" id="report-date" value="{{ $selectedDate }}" class="px-3 py-2 border rounded-lg text-sm">
        <button onclick="window.location.href='{{ route('admin.reports') }}?date='+document.getElementById('report-date').value" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700">Filter</button>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
    <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-green-500">
        <p class="text-sm text-gray-500">Total Sales</p>
        <p class="text-2xl font-bold">${{ number_format($totalSales, 2) }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-blue-500">
        <p class="text-sm text-gray-500">Order Count</p>
        <p class="text-2xl font-bold">{{ $orderCount }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-purple-500">
        <p class="text-sm text-gray-500">Avg Order Value</p>
        <p class="text-2xl font-bold">${{ number_format($avgOrderValue, 2) }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-orange-500">
        <p class="text-sm text-gray-500">Items Sold</p>
        <p class="text-2xl font-bold">{{ $totalItems }}</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold mb-4">Payment Methods</h3>
        <canvas id="paymentChart" height="200"></canvas>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold mb-4">Orders by Status</h3>
        <div class="space-y-3">
            @foreach($ordersByStatus as $status => $count)
            <div>
                <div class="flex justify-between text-sm mb-1"><span class="capitalize">{{ $status }}</span><span>{{ $count }}</span></div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="h-2 rounded-full @switch($status) @case('paid') bg-green-500 @break @case('processing') bg-blue-500 @break @case('new') bg-gray-500 @break @case('cancelled') bg-red-500 @break @default bg-yellow-500 @endswitch" style="width: {{ $orderCount > 0 ? ($count / $orderCount * 100) : 0 }}%"></div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

<div class="mt-6 bg-white rounded-xl shadow-sm p-6">
    <h3 class="text-lg font-semibold mb-4">Top Selling Items</h3>
    <table class="w-full text-sm">
        <thead><tr class="text-left text-gray-500 border-b"><th class="pb-2">Item</th><th class="pb-2">Qty Sold</th><th class="pb-2">Revenue</th></tr></thead>
        <tbody>
            @foreach($topItems as $item)
            <tr class="border-b border-gray-100">
                <td class="py-2 font-medium">{{ $item['menu_item']['name'] ?? 'N/A' }}</td>
                <td class="py-2">{{ $item['total_qty'] ?? 0 }}</td>
                <td class="py-2">${{ number_format($item['total_revenue'] ?? 0, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
new Chart(document.getElementById('paymentChart'), {
    type: 'doughnut',
    data: {
        labels: @json($paymentMethods->pluck('method')),
        datasets: [{
            data: @json($paymentMethods->pluck('total')),
            backgroundColor: ['#10b981', '#3b82f6', '#f59e0b', '#ef4444']
        }]
    },
    options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
});
</script>
@endpush
@endsection
