<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Monthly Report - {{ \DateTime::createFromFormat('!m', (string)$month)->format('F') }} {{ $year }}</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; color: #333; }
        h1 { text-align: center; font-size: 18px; margin-bottom: 4px; }
        .subtitle { text-align: center; color: #666; font-size: 12px; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        th, td { padding: 5px 8px; text-align: left; border-bottom: 1px solid #ddd; }
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
    @php $monthName = \DateTime::createFromFormat('!m', (string)$month)->format('F'); @endphp
    <h1>Monthly Sales Report</h1>
    <p class="subtitle">{{ $monthName }} {{ $year }}</p>

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
            <div class="value">{{ number_format($cashTotal, 2) }} Ks</div>
        </div>
        <div class="stat-box">
            <div class="label">Card</div>
            <div class="value">{{ number_format($cardTotal, 2) }} Ks</div>
        </div>
    </div>

    <div class="section-title">Daily Breakdown</div>
    <table>
        <thead><tr><th>Day</th><th>Date</th><th>Sales</th></tr></thead>
        <tbody>
            @foreach($chartLabels as $i => $day)
            @php $amount = $chartData[$i]; @endphp
            @if($amount > 0)
            <tr>
                <td>Day {{ $day }}</td>
                <td>{{ \DateTime::createFromFormat('Y-m-d', sprintf('%s-%02d-%02d', $year, $month, (int)$day))->format('M d, Y') }}</td>
                <td>{{ number_format($amount, 2) }} Ks</td>
            </tr>
            @endif
            @endforeach
        </tbody>
    </table>

    <div class="footer">Generated on {{ now()->format('Y-m-d H:i:s') }}</div>
</body>
</html>
