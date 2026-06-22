<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Table;
use App\Models\TableMerge;
use App\Models\TaxRate;
use App\Services\PaymentService;
use App\Services\ReportService;
use App\Services\TableMergeService;
use App\Services\TableService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CashierController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService,
        protected ReportService $reportService,
        protected TableService $tableService,
        protected TableMergeService $tableMergeService,
    ) {}

    public function dashboard()
    {
        $todayOrders = Order::today()->count();
        $todayRevenue = Order::today()->where('status', 'paid')->sum('grand_total');
        $activeTables = Table::whereIn('status', ['occupied', 'ordering', 'payment'])->count();
        $pendingOrders = Order::active()->count();
        $recentOrders = Order::with('table', 'createdBy')->latest()->take(10)->get();

        $pendingPayments = Table::where('status', 'payment')->with('orders')->get();
        $mergedGroups = TableMerge::with(['tables', 'order' => fn($q) => $q->active()])
            ->whereHas('order', fn($q) => $q->active())
            ->latest()
            ->get();

        $pendingPaymentTables = collect();
        $processedTableIds = collect();

        foreach ($pendingPayments as $table) {
            $merge = $mergedGroups->first(fn($mg) => $mg->tables->pluck('id')->contains($table->id));
            if ($merge) {
                if ($processedTableIds->doesntContain($merge->id)) {
                    $processedTableIds->push($merge->id);
                    $pendingPaymentTables->push((object) [
                        'type' => 'merge',
                        'tables' => $merge->tables,
                        'order' => $merge->order,
                        'group_code' => $merge->group_code,
                        'order_id' => $merge->order_id,
                        'total' => $merge->order?->grand_total ?? 0,
                    ]);
                }
            } else {
                $pendingPaymentTables->push((object) [
                    'type' => 'single',
                    'table' => $table,
                    'order_id' => $table->orders->first()?->id,
                ]);
            }
        }

        return view('cashier.dashboard', compact(
            'todayOrders', 'todayRevenue', 'activeTables', 'pendingOrders', 'recentOrders',
            'pendingPaymentTables', 'mergedGroups'
        ));
    }

    public function dashboardData()
    {
        $data = $this->buildDashboardData();
        return response()->json($data);
    }

    private function buildDashboardData(): array
    {
        $todayOrders = Order::today()->count();
        $todayRevenue = (float) Order::today()->where('status', 'paid')->sum('grand_total');
        $activeTables = Table::whereIn('status', ['occupied', 'ordering', 'payment'])->count();
        $pendingOrders = Order::active()->count();

        $recentOrders = Order::with('table', 'createdBy')->latest()->take(10)->get()->map(fn($o) => [
            'id' => $o->id,
            'order_no' => $o->order_no,
            'table_no' => $o->table->table_no,
            'status' => $o->status,
            'grand_total' => (float) $o->grand_total,
            'created_by' => $o->createdBy?->name ?? '—',
            'url' => route('cashier.orders.detail', $o->id),
        ])->values()->toArray();

        $pendingPayments = Table::where('status', 'payment')->with('orders', 'area')->get();
        $mergedGroups = TableMerge::with(['tables', 'order' => fn($q) => $q->active()])
            ->whereHas('order', fn($q) => $q->active())
            ->latest()
            ->get();

        $pendingPaymentItems = [];
        $processedTableIds = collect();

        foreach ($pendingPayments as $table) {
            $merge = $mergedGroups->first(fn($mg) => $mg->tables->pluck('id')->contains($table->id));
            if ($merge) {
                if ($processedTableIds->doesntContain($merge->id)) {
                    $processedTableIds->push($merge->id);
                    $pendingPaymentItems[] = [
                        'type' => 'merge',
                        'tables' => $merge->tables->map(fn($t) => $t->table_no)->values()->toArray(),
                        'group_code' => $merge->group_code,
                        'order_id' => $merge->order_id,
                        'total' => (float) ($merge->order?->grand_total ?? 0),
                        'url' => route('cashier.orders.detail', $merge->order_id),
                    ];
                }
            } else {
                $orderId = $table->orders->first()?->id;
                $pendingPaymentItems[] = [
                    'type' => 'single',
                    'table_no' => $table->table_no,
                    'area' => $table->area?->name ?? '—',
                    'order_id' => $orderId,
                    'url' => $orderId ? route('cashier.orders.detail', $orderId) : '#',
                ];
            }
        }

        $mergedGroupItems = $mergedGroups->map(fn($mg) => [
            'group_code' => $mg->group_code,
            'tables' => $mg->tables->map(fn($t) => $t->table_no)->values()->toArray(),
            'order_id' => $mg->order_id,
            'order_no' => $mg->order?->order_no ?? '—',
            'url' => route('cashier.orders.detail', $mg->order_id),
        ])->values()->toArray();

        return [
            'stats' => [
                'todayOrders' => $todayOrders,
                'todayRevenue' => $todayRevenue,
                'activeTables' => $activeTables,
                'pendingOrders' => $pendingOrders,
            ],
            'pendingPayments' => $pendingPaymentItems,
            'mergedGroups' => $mergedGroupItems,
            'recentOrders' => $recentOrders,
        ];
    }

    public function tables()
    {
        return view('cashier.tables.index');
    }

    public function tableData()
    {
        $tables = Table::with([
            'area',
            'activeOrders',
            'mergeGroups' => fn($q) => $q->whereHas('order', fn($oq) => $oq->active()),
        ])->orderBy('table_no')->get()->map(fn($t) => [
            'id' => $t->id,
            'table_no' => $t->table_no,
            'status' => $t->status,
            'area' => $t->area?->name ?? '—',
            'is_merged' => $t->mergeGroups->isNotEmpty(),
            'merged_group_code' => $t->mergeGroups->first()?->group_code,
            'order_id' => $t->activeOrders->first()?->id
                ?? $t->mergeGroups->first()?->order_id,
        ]);
        return response()->json($tables);
    }

    public function tableDetail($id)
    {
        $table = Table::with([
            'orders' => fn($q) => $q->with([
                'items.menuItem',
                'items.modifiers',
            ])->active()->latest(),
            'mergeGroups' => fn($q) => $q->with('tables')->whereHas('order', fn($oq) => $oq->active()),
        ])->findOrFail($id);

        if ($table->orders->isEmpty() && $table->mergeGroups->isNotEmpty()) {
            $mergeGroup = $table->mergeGroups->first();
            $primaryOrder = $mergeGroup->order;
            $primaryOrder->loadMissing(['items.menuItem', 'items.modifiers']);
            $orders = collect([$primaryOrder])->map(fn($order) => [
                'id' => $order->id,
                'order_no' => $order->order_no,
                'status' => $order->status,
                'grand_total' => (float) $order->grand_total,
                'items' => $order->items->map(fn($item) => [
                    'name' => $item->menuItem?->name ?? 'Unknown',
                    'qty' => $item->qty,
                    'price' => (float) $item->price,
                    'subtotal' => (float) $item->subtotal,
                    'modifiers' => $item->modifiers->map(fn($m) => $m->name)->implode(', '),
                ]),
                'merge' => [
                    'group_code' => $mergeGroup->group_code,
                    'tables' => $mergeGroup->tables->pluck('table_no')->toArray(),
                ],
            ]);
        } else {
            $orders = $table->orders->map(fn($order) => [
                'id' => $order->id,
                'order_no' => $order->order_no,
                'status' => $order->status,
                'grand_total' => (float) $order->grand_total,
                'items' => $order->items->map(fn($item) => [
                    'name' => $item->menuItem?->name ?? 'Unknown',
                    'qty' => $item->qty,
                    'price' => (float) $item->price,
                    'subtotal' => (float) $item->subtotal,
                    'modifiers' => $item->modifiers->map(fn($m) => $m->name)->implode(', '),
                ]),
                'merge' => $table->mergeGroups->first() ? [
                    'group_code' => $table->mergeGroups->first()->group_code,
                    'tables' => $table->mergeGroups->first()->tables->pluck('table_no')->toArray(),
                ] : null,
            ]);
        }

        return response()->json(['orders' => $orders]);
    }

    public function transfer(Request $request)
    {
        $data = $request->validate([
            'order_id' => 'required|integer|exists:orders,id',
            'from_table_id' => 'required|integer|exists:tables,id',
            'to_table_id' => 'required|integer|exists:tables,id|different:from_table_id',
        ]);

        $result = $this->tableService->transferTable(
            $data['order_id'],
            $data['from_table_id'],
            $data['to_table_id'],
            Auth::id()
        );

        return response()->json($result);
    }

    public function merge(Request $request)
    {
        $data = $request->validate([
            'table_ids' => 'required|array|min:2',
            'table_ids.*' => 'required|integer|exists:tables,id|distinct',
        ]);

        $result = $this->tableMergeService->mergeTables(
            $data['table_ids'],
            Auth::id()
        );

        return response()->json($result, 201);
    }

    public function orders()
    {
        $status = request('status', 'active');
        $dateFrom = request('date_from');
        $dateTo = request('date_to');

        $query = Order::with(['table.area', 'createdBy', 'items.menuItem']);

        if ($dateFrom && $dateTo) {
            $query->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']);
        } elseif ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        } elseif ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        } else {
            $query->whereDate('created_at', today());
        }

        if ($status === 'active') {
            $query->active();
        } elseif ($status !== 'all') {
            $query->byStatus($status);
        }

        $orders = $query->latest()->paginate(20);
        return view('cashier.orders.index', compact('orders', 'status', 'dateFrom', 'dateTo'));
    }

    public function ordersData()
    {
        $status = request('status', 'active');
        $dateFrom = request('date_from');
        $dateTo = request('date_to');
        $page = request('page', 1);

        $query = Order::with(['table.area', 'createdBy', 'items.menuItem']);

        if ($dateFrom && $dateTo) {
            $query->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']);
        } elseif ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        } elseif ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        } else {
            $query->whereDate('created_at', today());
        }

        if ($status === 'active') {
            $query->active();
        } elseif ($status !== 'all') {
            $query->byStatus($status);
        }

        $orders = $query->latest()->paginate(20, ['*'], 'page', $page);

        $items = $orders->map(fn($o) => [
            'id' => $o->id,
            'order_no' => $o->order_no,
            'table_no' => $o->table->table_no,
            'status' => $o->status,
            'items_count' => $o->items->count(),
            'grand_total' => (float) $o->grand_total,
            'created_at' => $o->created_at->format('M d, H:i'),
            'created_by' => $o->createdBy?->name ?? '—',
            'url' => route('cashier.orders.detail', $o->id),
        ])->values()->toArray();

        $pagination = $orders->toArray();
        $paginationHtml = $orders->appends(request()->query())->links('pagination::bootstrap-5')->toHtml();

        return response()->json([
            'orders' => $items,
            'total' => $orders->total(),
            'pagination_html' => $paginationHtml,
        ]);
    }

    public function orderDetail($id)
    {
        $order = Order::with([
            'table.area',
            'items.menuItem.category',
            'items.menuItem.activeModifiers',
            'items.modifiers',
            'createdBy',
            'paidBy',
            'payments',
        ])->findOrFail($id);

        $mergeInfo = null;
        $merge = TableMerge::where('order_id', $order->id)->first();
        if ($merge) {
            $mergedOrderNos = collect([$order->order_no]);
            if (!empty($merge->merged_order_ids)) {
                $mergedOrderNos = $mergedOrderNos->merge(
                    Order::whereIn('id', $merge->merged_order_ids)->pluck('order_no')
                );
            }
            $mergeInfo = [
                'group_code' => $merge->group_code,
                'tables' => $merge->tables()->pluck('table_no', 'tables.id')->toArray(),
                'order_numbers' => $mergedOrderNos->toArray(),
            ];
        }

        $taxRates = TaxRate::active()->get();

        return view('cashier.orders.show', compact('order', 'taxRates', 'mergeInfo'));
    }

    public function orderDetailData($id)
    {
        $order = Order::with([
            'table.area',
            'items.menuItem',
            'items.modifiers',
            'createdBy',
            'paidBy',
            'payments',
        ])->findOrFail($id);

        $merge = TableMerge::where('order_id', $order->id)->first();

        $statusClasses = match($order->status) {
            'paid' => 'bg-success',
            'processing' => 'bg-info',
            'new' => 'bg-secondary',
            'cancelled' => 'bg-danger',
            default => 'bg-warning text-dark',
        };

        return response()->json([
            'status' => $order->status,
            'status_class' => $statusClasses,
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
            'is_merged' => $merge !== null,
        ]);
    }

    public function processPayment(Request $request)
    {
        $data = $request->validate([
            'order_id' => 'required|integer|exists:orders,id',
            'type' => 'required|in:cash,card,split_cash_card',
            'amount' => 'required|numeric|min:0',
            'tendered' => 'nullable|numeric|min:0',
            'cash_portion' => 'nullable|numeric|min:0',
            'card_portion' => 'nullable|numeric|min:0',
        ]);

        $data['tendered'] = $data['tendered'] ?? $data['amount'];
        $result = $this->paymentService->processPayment($data);

        $order = Order::with('table')->findOrFail($data['order_id']);

        if ($result['order_status'] === 'paid') {
            return redirect()->route('cashier.receipt', $data['order_id'])
                ->with('success', 'Payment completed successfully');
        }

        return redirect()->route('cashier.orders.detail', $data['order_id'])
            ->with('success', 'Partial payment of $' . number_format($data['amount'], 2) . ' recorded');
    }

    public function splitPay(Request $request)
    {
        $data = $request->validate([
            'order_id' => 'required|integer|exists:orders,id',
            'splits' => 'required|array|min:2',
            'splits.*.method' => 'required|in:cash,card,split_cash_card',
            'splits.*.amount' => 'required|numeric|min:0.01',
            'splits.*.cash_portion' => 'nullable|numeric|min:0',
            'splits.*.card_portion' => 'nullable|numeric|min:0',
        ]);

        $result = $this->paymentService->splitPayment($data);

        return redirect()->route('cashier.receipt', $data['order_id'])
            ->with('success', 'Split payment completed');
    }

    public function voidItem(Request $request)
    {
        $data = $request->validate([
            'item_id' => 'required|integer|exists:order_items,id',
            'reason' => 'nullable|string|max:255',
        ]);

        $item = OrderItem::findOrFail($data['item_id']);
        $orderId = $item->order_id;

        DB::transaction(function () use ($item, $data) {
            $item->update([
                'status' => 'voided',
                'void_reason' => $data['reason'] ?? null,
                'voided_by' => Auth::id(),
            ]);

            $order = $item->order;
            $activeItems = $order->activeItems;
            $newTotal = $activeItems->sum('subtotal');

            if ($activeItems->isEmpty()) {
                $order->update(['status' => 'cancelled']);
            } else {
                $ratio = $order->total > 0 ? $newTotal / (float) $order->total : 1;
                $newTax = round(($order->tax_total ?? 0) * $ratio, 2);
                $newService = round(($order->service_charge_total ?? 0) * $ratio, 2);
                $grand = $newTotal + $newTax + $newService - (float) ($order->discount_total ?? 0);

                $order->update([
                    'total' => $newTotal,
                    'tax_total' => $newTax,
                    'service_charge_total' => $newService,
                    'grand_total' => max($grand, 0),
                ]);
            }
        });

        $order = Order::find($orderId);
        $order->update(['status' => $order->deriveStatus()]);

        return redirect()->route('cashier.orders.detail', $orderId)
            ->with('success', 'Item voided successfully');
    }

    public function applyDiscount(Request $request)
    {
        $data = $request->validate([
            'order_id' => 'required|integer|exists:orders,id',
            'discount_type' => 'required|in:fixed,percentage',
            'discount_value' => 'required|numeric|min:0',
        ]);

        $order = Order::findOrFail($data['order_id']);
        $subtotal = (float) $order->total;

        $discount = $data['discount_type'] === 'percentage'
            ? min($subtotal * ($data['discount_value'] / 100), $subtotal)
            : min($data['discount_value'], $subtotal);

        $order->update([
            'discount_total' => $discount,
            'grand_total' => max($subtotal + (float) $order->tax_total + (float) $order->service_charge_total - $discount, 0),
        ]);

        return redirect()->route('cashier.orders.detail', $data['order_id'])
            ->with('success', 'Discount applied successfully');
    }

    public function receipt($id)
    {
        $order = Order::with([
            'table.area',
            'items.menuItem',
            'items.modifiers',
            'payments',
            'createdBy',
            'paidBy',
        ])->findOrFail($id);

        return view('cashier.orders.receipt', compact('order'));
    }

    public function closeTable($id)
    {
        $order = Order::with('table')->findOrFail($id);

        if ($order->status !== 'paid') {
            return redirect()->route('cashier.orders.detail', $id)
                ->with('error', 'Order must be paid before closing the table');
        }

        $merge = TableMerge::where('order_id', $order->id)->first();

        $tableIds = $merge
            ? $merge->mergeGroupTables()->pluck('table_id')->toArray()
            : [$order->table_id];

        $released = [];
        foreach ($tableIds as $tableId) {
            $table = Table::find($tableId);
            if ($table && !$table->orders()->active()->exists()) {
                $table->update(['status' => 'available']);
                $released[] = $table->table_no;
            }
        }

        $label = count($released) > 1
            ? 'Tables ' . implode(', ', $released)
            : 'Table ' . ($released[0] ?? '');

        return redirect()->route('cashier.orders')
            ->with('success', $label . ' closed');
    }

    public function closeTableById($id)
    {
        $table = Table::findOrFail($id);

        if ($table->status !== 'paid') {
            return response()->json(['error' => 'Table must be paid before closing'], 422);
        }

        $table->update(['status' => 'available']);

        return response()->json(['message' => 'Table ' . $table->table_no . ' is now available']);
    }

    public function dailyReport(Request $request)
    {
        $date = $request->get('date', today()->toDateString());
        $report = $this->reportService->dailySales($date);
        $topItems = $this->reportService->topItems($date, $date);
        $utilization = $this->reportService->tableUtilization($date);

        return view('cashier.reports.daily', compact('report', 'topItems', 'utilization', 'date'));
    }

    public function monthlyReport(Request $request)
    {
        $year = $request->get('year', (int) now()->format('Y'));
        $month = $request->get('month', (int) now()->format('m'));
        $report = $this->reportService->monthlySales($year, $month);
        $dailyBreakdown = $report['daily_breakdown'];

        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $chartLabels = [];
        $chartData = [];
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $key = sprintf('%s-%02d-%02d', $year, $month, $d);
            $chartLabels[] = (string) $d;
            $chartData[] = (float) ($dailyBreakdown[$key] ?? 0);
        }

        $payments = \App\Models\Payment::whereYear('paid_at', $year)
            ->whereMonth('paid_at', $month)
            ->get();
        $cashTotal = (float) $payments->where('type', 'cash')->sum('amount');
        $cardTotal = (float) $payments->whereIn('type', ['card', 'split_cash_card'])->sum('amount');

        return view('cashier.reports.monthly', compact(
            'report', 'year', 'month', 'daysInMonth',
            'chartLabels', 'chartData', 'cashTotal', 'cardTotal'
        ));
    }

    public function dailyReportCsv(Request $request)
    {
        $date = $request->get('date', today()->toDateString());
        $report = $this->reportService->dailySales($date);
        $topItems = $this->reportService->topItems($date, $date);

        $filename = "daily-report-{$date}.csv";
        $handle = fopen('php://temp', 'w+');

        fputcsv($handle, ['Daily Report - ' . $date]);
        fputcsv($handle, []);
        fputcsv($handle, ['Metric', 'Value']);
        fputcsv($handle, ['Total Sales', number_format($report['total_sales'], 2)]);
        fputcsv($handle, ['Orders', $report['order_count']]);
        fputcsv($handle, ['Cash Sales', number_format($report['cash_sales'], 2)]);
        fputcsv($handle, ['Card Sales', number_format($report['card_sales'], 2)]);
        fputcsv($handle, []);

        if (count($topItems) > 0) {
            fputcsv($handle, ['Top Items']);
            fputcsv($handle, ['Item', 'Qty', 'Revenue']);
            foreach ($topItems as $item) {
                fputcsv($handle, [
                    $item['menu_item']['name'] ?? 'Item #' . $item['menu_item_id'],
                    $item['total_qty'],
                    number_format($item['total_revenue'], 2),
                ]);
            }
            fputcsv($handle, []);
        }

        fputcsv($handle, ['Payments']);
        fputcsv($handle, ['Order', 'Type', 'Amount', 'Time']);
        foreach ($report['payments'] as $payment) {
            fputcsv($handle, [
                $payment->order->order_no ?? '—',
                $payment->type,
                number_format($payment->amount, 2),
                $payment->paid_at->format('H:i'),
            ]);
        }

        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);

        return response($content, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function dailyReportPdf(Request $request)
    {
        $date = $request->get('date', today()->toDateString());
        $report = $this->reportService->dailySales($date);
        $topItems = $this->reportService->topItems($date, $date);
        $utilization = $this->reportService->tableUtilization($date);

        $pdf = Pdf::loadView('cashier.reports.daily-pdf', compact('report', 'topItems', 'utilization', 'date'));
        return $pdf->download("daily-report-{$date}.pdf");
    }

    public function monthlyReportCsv(Request $request)
    {
        $year = $request->get('year', (int) now()->format('Y'));
        $month = $request->get('month', (int) now()->format('m'));
        $report = $this->reportService->monthlySales($year, $month);

        $payments = \App\Models\Payment::whereYear('paid_at', $year)
            ->whereMonth('paid_at', $month)->get();
        $cashTotal = (float) $payments->where('type', 'cash')->sum('amount');
        $cardTotal = (float) $payments->whereIn('type', ['card', 'split_cash_card'])->sum('amount');

        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $dailyBreakdown = $report['daily_breakdown'];

        $filename = "monthly-report-{$year}-{$month}.csv";
        $handle = fopen('php://temp', 'w+');

        $monthName = \DateTime::createFromFormat('!m', (string) $month)->format('F');
        fputcsv($handle, ["Monthly Report - {$monthName} {$year}"]);
        fputcsv($handle, []);
        fputcsv($handle, ['Metric', 'Value']);
        fputcsv($handle, ['Total Sales', number_format($report['total_sales'], 2)]);
        fputcsv($handle, ['Orders', $report['order_count']]);
        fputcsv($handle, ['Cash Sales', number_format($cashTotal, 2)]);
        fputcsv($handle, ['Card Sales', number_format($cardTotal, 2)]);
        fputcsv($handle, []);

        fputcsv($handle, ['Daily Breakdown']);
        fputcsv($handle, ['Day', 'Date', 'Sales']);
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $key = sprintf('%s-%02d-%02d', $year, $month, $d);
            $amount = (float) ($dailyBreakdown[$key] ?? 0);
            if ($amount > 0) {
                $dateObj = \DateTime::createFromFormat('Y-m-d', $key);
                fputcsv($handle, [
                    "Day {$d}",
                    $dateObj->format('M d, Y'),
                    number_format($amount, 2),
                ]);
            }
        }

        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);

        return response($content, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function monthlyReportPdf(Request $request)
    {
        $year = $request->get('year', (int) now()->format('Y'));
        $month = $request->get('month', (int) now()->format('m'));
        $report = $this->reportService->monthlySales($year, $month);
        $dailyBreakdown = $report['daily_breakdown'];

        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $chartLabels = [];
        $chartData = [];
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $key = sprintf('%s-%02d-%02d', $year, $month, $d);
            $chartLabels[] = (string) $d;
            $chartData[] = (float) ($dailyBreakdown[$key] ?? 0);
        }

        $payments = \App\Models\Payment::whereYear('paid_at', $year)
            ->whereMonth('paid_at', $month)->get();
        $cashTotal = (float) $payments->where('type', 'cash')->sum('amount');
        $cardTotal = (float) $payments->whereIn('type', ['card', 'split_cash_card'])->sum('amount');

        $pdf = Pdf::loadView('cashier.reports.monthly-pdf', compact(
            'report', 'year', 'month', 'daysInMonth',
            'chartLabels', 'chartData', 'cashTotal', 'cardTotal'
        ));
        $monthName = \DateTime::createFromFormat('!m', (string) $month)->format('F');
        return $pdf->download("monthly-report-{$year}-{$month}.pdf");
    }
}
