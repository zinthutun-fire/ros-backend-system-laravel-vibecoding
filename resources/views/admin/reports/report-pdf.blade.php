<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><title>Report {{ $from }} - {{ $to }}</title>
<style>
body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #333; }
h1 { font-size: 18px; margin-bottom: 4px; }
.sub { color: #666; font-size: 12px; margin-bottom: 20px; }
table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
th, td { border: 1px solid #ddd; padding: 6px 8px; text-align: left; }
th { background: #f5f5f5; font-weight: 600; }
.text-right { text-align: right; }
.summary { margin-bottom: 24px; }
.summary td { border: none; padding: 2px 8px; }
.summary td:first-child { font-weight: 600; width: 200px; }
</style>
</head>
<body>
<h1>Sales Report</h1>
<p class="sub">{{ $from }} → {{ $to }}</p>

<table class="summary">
    <tr><td>Total Sales</td><td>{{ number_format($totalSales, 2) }} Ks</td></tr>
    <tr><td>Order Count</td><td>{{ $orderCount }}</td></tr>
    <tr><td>Avg Order Value</td><td>{{ number_format($avgOrderValue, 2) }} Ks</td></tr>
</table>

<h3>Payment Methods</h3>
<table>
    <tr><th>Method</th><th class="text-right">Total</th></tr>
    @foreach($paymentMethods as $pm)
        <tr><td>{{ $pm->method }}</td><td class="text-right">{{ number_format((float) $pm->total, 2) }} Ks</td></tr>
    @endforeach
</table>

<h3>Top Items</h3>
<table>
    <tr><th>Item</th><th class="text-right">Qty</th><th class="text-right">Revenue</th></tr>
    @forelse($topItems as $item)
        <tr>
            <td>{{ $item['menu_item']['name'] ?? 'N/A' }}</td>
            <td class="text-right">{{ $item['total_qty'] ?? 0 }}</td>
            <td class="text-right">{{ number_format($item['total_revenue'] ?? 0, 2) }} Ks</td>
        </tr>
    @empty
        <tr><td colspan="3" style="text-align:center;color:#999;">No data</td></tr>
    @endforelse
</table>
</body>
</html>