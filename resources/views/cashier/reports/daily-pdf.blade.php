<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Daily Report - {{ $date }}</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; color: #333; }
        h1 { text-align: center; font-size: 18px; margin-bottom: 4px; }
        .subtitle { text-align: center; color: #666; font-size: 12px; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        th, td { padding: 6px 8px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f3f4f6; font-weight: 600; font-size: 10px; text-transform: uppercase; }
        .stat-grid { display: flex; gap: 12px; margin-bottom: 20px; }
        .stat-box { flex: 1; border: 1px solid #ddd; border-radius: 6px; padding: 12px; text-align: center; }
        .stat-box .label { font-size: 10px; color: #666; }
        .stat-box .value { font-size: 18px; font-weight: bold; margin-top: 4px; }
        .section-title { font-size: 13px; font-weight: bold; margin: 16px 0 8px; }
        .footer { text-align: center; color: #999; font-size: 9px; margin-top: 24px; border-top: 1px solid #eee; padding-top: 12px; }
    </style>
</head>
<body>
    <h1>Daily Sales Report</h1>
    <p class="subtitle">{{ $date }}</p>

    <div class="stat-grid">
        <div class="stat-box">
            <div class="label">Total Sales</div>
            <div class="value">{{ number_format($report['total_sales'], 2) }} Ks</div>
        </div>
        <div class="stat-box">
            <div class="label">Orders</div>
            <div class="value">{{ $report['order_count'] }}</div>
        </div>
        <div class="stat-box">
            <div class="label">Cash</div>
            <div class="value">{{ number_format($report['cash_sales'], 2) }} Ks</div>
        </div>
        <div class="stat-box">
            <div class="label">Card</div>
            <div class="value">{{ number_format($report['card_sales'], 2) }} Ks</div>
        </div>
    </div>

    <div class="section-title">Payment Summary</div>
    <table>
        <thead>
            <tr><th>Order</th><th>Type</th><th>Amount</th><th>Time</th></tr>
        </thead>
        <tbody>
            @forelse($report['payments'] as $payment)
            <tr>
                <td>{{ $payment->order->order_no ?? '—' }}</td>
                <td>{{ ucfirst($payment->type) }}</td>
                <td>{{ number_format($payment->amount, 2) }} Ks</td>
                <td>{{ $payment->paid_at->format('H:i') }}</td>
            </tr>
            @empty
            <tr><td colspan="4" style="text-align:center;color:#999;">No payments</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="section-title">Table Status</div>
    <table>
        <thead><tr><th>Status</th><th>Count</th></tr></thead>
        <tbody>
            @foreach($utilization['table_statuses'] as $status => $count)
            <tr><td>{{ ucfirst($status) }}</td><td>{{ $count }}</td></tr>
            @endforeach
        </tbody>
    </table>

    @if(count($topItems) > 0)
    <div class="section-title">Top Items</div>
    <table>
        <thead><tr><th>Item</th><th>Qty</th><th>Revenue</th></tr></thead>
        <tbody>
            @foreach($topItems as $item)
            <tr>
                <td>{{ $item['menu_item']['name'] ?? 'Item #' . $item['menu_item_id'] }}</td>
                <td>{{ $item['total_qty'] }}</td>
                <td>{{ number_format($item['total_revenue'], 2) }} Ks</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <div class="footer">Generated on {{ now()->format('Y-m-d H:i:s') }}</div>
</body>
</html>
