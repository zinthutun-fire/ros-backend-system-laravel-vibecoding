<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Kitchen Order #{{ $order->order_no }}</title>
    <style>
        body { width: 80mm; margin: 0; padding: 2mm; font-family: 'Courier New', monospace; font-size: 10px; line-height: 1.3; }
        h1 { font-size: 14px; text-align: center; margin: 0 0 2mm; }
        h2 { font-size: 12px; text-align: center; margin: 0 0 2mm; }
        .divider { border-top: 1px dashed #000; margin: 2mm 0; }
        .row { display: flex; justify-content: space-between; }
        table { width: 100%; border-collapse: collapse; }
        th, td { text-align: left; padding: 1mm 0; }
        th { border-bottom: 1px dashed #000; }
        .qty { text-align: center; width: 8mm; }
        .price { text-align: right; }
        .mod { padding-left: 4mm; font-size: 9px; color: #555; }
        .note { padding-left: 4mm; font-size: 9px; color: #c00; font-style: italic; }
        .station { text-align: center; font-weight: bold; margin-top: 2mm; }
        @media print {
            @page { margin: 0; size: 80mm auto; }
            body { margin: 0; padding: 2mm; }
        }
    </style>
</head>
<body>
    <h1>RESTAURANT NAME</h1>
    <h2>KITCHEN ORDER</h2>

    <div class="divider"></div>
    <div class="row"><span>Order:</span><span>{{ $order->order_no }}</span></div>
    <div class="row"><span>Table:</span><span>{{ $order->table->table_no }} {{ $order->table->name ? '(' . $order->table->name . ')' : '' }}</span></div>
    <div class="row"><span>Area:</span><span>{{ $order->table->area->name ?? '—' }}</span></div>
    <div class="row"><span>Time:</span><span>{{ $order->created_at->format('H:i') }}</span></div>
    @if($order->notes)
    <div class="row" style="color:#c00"><span>Note:</span><span>{{ $order->notes }}</span></div>
    @endif

    <div class="divider"></div>
    <table>
        <thead><tr><th>Item</th><th class="qty">Qty</th></tr></thead>
        <tbody>
            @foreach($order->items as $item)
            <tr>
                <td>{{ $item->menuItem?->name ?? 'Deleted' }}</td>
                <td class="qty">{{ $item->qty }}</td>
            </tr>
                @foreach($item->modifiers as $mod)
                <tr><td class="mod">+ {{ $mod->name }}</td><td></td></tr>
                @endforeach
                @if($item->note)
                <tr><td class="note">Note: {{ $item->note }}</td><td></td></tr>
                @endif
            @endforeach
        </tbody>
    </table>

    <div class="divider"></div>
    @if($order->items->isNotEmpty() && $order->items->first()->kitchen)
    <div class="station">Station: {{ $order->items->first()->kitchen->name }}</div>
    @endif
    <div style="text-align:center;margin-top:2mm;font-size:9px">*** Print Time: {{ now()->format('H:i:s') }} ***</div>

    <script>
        window.onload = function() { window.print(); };
        window.onafterprint = function() { window.close(); };
    </script>
</body>
</html>
