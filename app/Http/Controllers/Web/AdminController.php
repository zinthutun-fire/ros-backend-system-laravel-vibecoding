<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Kitchen;
use App\Models\MenuItem;
use App\Models\MenuItemModifier;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Table;
use App\Models\TableArea;
use App\Models\TaxRate;
use App\Models\User;
use App\Services\ReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function __construct(
        protected ReportService $reportService
    ) {}

    public function dashboard()
    {
        $data = $this->dashboardDataInternal();
        return view('admin.dashboard', $data);
    }

    public function dashboardData()
    {
        return response()->json($this->dashboardDataInternal());
    }

    private function dashboardDataInternal(): array
    {
        $todayOrders = Order::whereDate('created_at', today())->count();
        $todayRevenue = (float) Order::whereDate('created_at', today())->where('status', 'paid')->sum('grand_total');
        $activeTables = Table::whereIn('status', ['occupied', 'ordering', 'payment'])->count();
        $totalTables = Table::count();
        $totalUsers = User::count();
        $pendingOrders = Order::whereNotIn('status', ['paid', 'cancelled'])->count();

        $recentOrders = Order::with('table', 'createdBy')
            ->latest()
            ->take(5)
            ->get()
            ->map(fn($o) => [
                'order_no' => $o->order_no,
                'table_no' => $o->table->table_no,
                'status' => $o->status,
                'grand_total' => (float) $o->grand_total,
                'created_by' => $o->createdBy?->name ?? '—',
                'url' => route('admin.orders'),
            ])->values()->toArray();

        $dailySales = $this->reportService->dailySales(today()->toDateString());

        return compact(
            'todayOrders', 'todayRevenue', 'activeTables', 'totalTables',
            'totalUsers', 'pendingOrders', 'recentOrders', 'dailySales'
        );
    }

    // === TABLES ===
    public function tables()
    {
        $query = Table::with('area');

        if ($search = request('search')) {
            $query->where('table_no', 'like', "%{$search}%");
        }

        if ($areaId = request('area_id')) {
            $query->where('area_id', $areaId);
        }

        $tables = $query->orderBy('sort_order')->get();
        $areas = TableArea::orderBy('sort_order')->get();
        return view('admin.tables.index', compact('tables', 'areas'));
    }

    public function storeTable(Request $request)
    {
        $data = $request->validate([
            'table_no' => 'required|string|unique:tables,table_no',
            'name' => 'nullable|string|max:255',
            'capacity' => 'required|integer|min:1',
            'area_id' => 'required|integer|exists:table_areas,id',
            'sort_order' => 'sometimes|integer|min:0',
        ]);
        Table::create($data);
        return redirect()->route('admin.tables')->with('success', 'Table created successfully');
    }

    public function updateTable(Request $request, $id)
    {
        $table = Table::findOrFail($id);
        $data = $request->validate([
            'table_no' => 'required|string|unique:tables,table_no,' . $id,
            'name' => 'nullable|string|max:255',
            'capacity' => 'required|integer|min:1',
            'area_id' => 'required|integer|exists:table_areas,id',
            'sort_order' => 'sometimes|integer|min:0',
        ]);
        $table->update($data);
        return redirect()->route('admin.tables')->with('success', 'Table updated successfully');
    }

    public function deleteTable($id)
    {
        Table::findOrFail($id)->delete();
        return redirect()->route('admin.tables')->with('success', 'Table deleted successfully');
    }

    // === AREAS ===
    public function areas()
    {
        $areas = TableArea::withCount('tables')->orderBy('sort_order')->get();
        return view('admin.areas.index', compact('areas'));
    }

    public function storeArea(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'sort_order' => 'sometimes|integer|min:0',
        ]);
        TableArea::create($data);
        return redirect()->route('admin.areas')->with('success', 'Area created successfully');
    }

    public function updateArea(Request $request, $id)
    {
        $area = TableArea::findOrFail($id);
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'sort_order' => 'sometimes|integer|min:0',
        ]);
        $area->update($data);
        return redirect()->route('admin.areas')->with('success', 'Area updated successfully');
    }

    public function deleteArea($id)
    {
        TableArea::findOrFail($id)->delete();
        return redirect()->route('admin.areas')->with('success', 'Area deleted successfully');
    }

    // === KITCHENS ===
    public function kitchens()
    {
        $kitchens = Kitchen::withCount('menuItems', 'users')->orderBy('name')->get();
        return view('admin.kitchens.index', compact('kitchens'));
    }

    public function storeKitchen(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:kitchens,code',
            'status' => 'sometimes|in:active,inactive',
        ]);
        Kitchen::create($data);
        return redirect()->route('admin.kitchens')->with('success', 'Kitchen created successfully');
    }

    public function updateKitchen(Request $request, $id)
    {
        $kitchen = Kitchen::findOrFail($id);
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:kitchens,code,' . $id,
            'status' => 'sometimes|in:active,inactive',
        ]);
        $kitchen->update($data);
        return redirect()->route('admin.kitchens')->with('success', 'Kitchen updated successfully');
    }

    public function deleteKitchen($id)
    {
        Kitchen::findOrFail($id)->delete();
        return redirect()->route('admin.kitchens')->with('success', 'Kitchen deleted successfully');
    }

    // === CATEGORIES ===
    public function categories()
    {
        $categories = Category::withCount('menuItems')->ordered()->get();
        return view('admin.categories.index', compact('categories'));
    }

    public function storeCategory(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'sort_order' => 'sometimes|integer|min:0',
            'is_active' => 'sometimes|boolean',
        ]);
        Category::create($data);
        return redirect()->route('admin.categories')->with('success', 'Category created successfully');
    }

    public function updateCategory(Request $request, $id)
    {
        $category = Category::findOrFail($id);
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'sort_order' => 'sometimes|integer|min:0',
            'is_active' => 'sometimes|boolean',
        ]);
        $category->update($data);
        return redirect()->route('admin.categories')->with('success', 'Category updated successfully');
    }

    public function deleteCategory($id)
    {
        Category::findOrFail($id)->delete();
        return redirect()->route('admin.categories')->with('success', 'Category deleted successfully');
    }

    // === MENU ITEMS ===
    public function menuItems()
    {
        $query = MenuItem::with(['category', 'kitchen', 'activeModifiers']);

        if ($search = request('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        if ($categoryId = request('category_id')) {
            $query->where('category_id', $categoryId);
        }

        if ($kitchenId = request('kitchen_id')) {
            $query->where('kitchen_id', $kitchenId);
        }

        $menuItems = $query->ordered()->get();
        $categories = Category::active()->ordered()->get();
        $kitchens = Kitchen::active()->get();
        return view('admin.menu-items.index', compact('menuItems', 'categories', 'kitchens'));
    }

    public function storeMenuItem(Request $request)
    {
        $data = $request->validate([
            'category_id' => 'required|integer|exists:categories,id',
            'kitchen_id' => 'required|integer|exists:kitchens,id',
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'has_modifiers' => 'sometimes|boolean',
            'status' => 'sometimes|in:available,unavailable',
        ]);
        MenuItem::create($data);
        return redirect()->route('admin.menu-items')->with('success', 'Menu item created successfully');
    }

    public function updateMenuItem(Request $request, $id)
    {
        $item = MenuItem::findOrFail($id);
        $data = $request->validate([
            'category_id' => 'sometimes|integer|exists:categories,id',
            'kitchen_id' => 'sometimes|integer|exists:kitchens,id',
            'name' => 'sometimes|string|max:255',
            'price' => 'sometimes|numeric|min:0',
            'description' => 'nullable|string',
            'has_modifiers' => 'sometimes|boolean',
            'status' => 'sometimes|in:available,unavailable',
        ]);
        $item->update($data);
        return redirect()->route('admin.menu-items')->with('success', 'Menu item updated successfully');
    }

    public function deleteMenuItem($id)
    {
        MenuItem::findOrFail($id)->delete();
        return redirect()->route('admin.menu-items')->with('success', 'Menu item deleted successfully');
    }

    public function storeModifier(Request $request)
    {
        $data = $request->validate([
            'menu_item_id' => 'required|integer|exists:menu_items,id',
            'name' => 'required|string|max:255',
            'price_adjustment' => 'sometimes|numeric|min:0',
            'sort_order' => 'sometimes|integer|min:0',
        ]);
        MenuItemModifier::create($data);
        MenuItem::where('id', $data['menu_item_id'])->where('has_modifiers', false)->update(['has_modifiers' => true]);
        return redirect()->route('admin.menu-items')->with('success', 'Modifier added successfully');
    }

    public function updateModifier(Request $request, $id)
    {
        $modifier = MenuItemModifier::findOrFail($id);
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'price_adjustment' => 'sometimes|numeric|min:0',
            'sort_order' => 'sometimes|integer|min:0',
            'is_active' => 'sometimes|boolean',
        ]);
        $modifier->update($data);
        return redirect()->route('admin.menu-items')->with('success', 'Modifier updated successfully');
    }

    public function deleteModifier($id)
    {
        $modifier = MenuItemModifier::findOrFail($id);
        $menuItemId = $modifier->menu_item_id;
        $modifier->delete();
        if (MenuItemModifier::where('menu_item_id', $menuItemId)->count() === 0) {
            MenuItem::where('id', $menuItemId)->update(['has_modifiers' => false]);
        }
        return redirect()->route('admin.menu-items')->with('success', 'Modifier deleted successfully');
    }

    // === USERS ===
    public function users()
    {
        $query = User::with('kitchen');

        if ($search = request('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        if ($role = request('role')) {
            $query->where('role', $role);
        }

        $users = $query->orderBy('name')->get();
        $kitchens = Kitchen::active()->get();
        return view('admin.users.index', compact('users', 'kitchens'));
    }

    public function storeUser(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role' => 'required|in:admin,manager,waiter,cashier,kitchen',
            'kitchen_id' => 'nullable|integer|exists:kitchens,id',
            'is_active' => 'sometimes|boolean',
        ]);
        $data['password'] = Hash::make($data['password']);
        User::create($data);
        return redirect()->route('admin.users')->with('success', 'User created successfully');
    }

    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => 'nullable|string|min:8',
            'role' => 'required|in:admin,manager,waiter,cashier,kitchen',
            'kitchen_id' => 'nullable|integer|exists:kitchens,id',
            'is_active' => 'sometimes|boolean',
        ]);
        if (empty($data['password'])) {
            unset($data['password']);
        } else {
            $data['password'] = Hash::make($data['password']);
        }
        $user->update($data);
        return redirect()->route('admin.users')->with('success', 'User updated successfully');
    }

    public function deleteUser($id)
    {
        User::findOrFail($id)->delete();
        return redirect()->route('admin.users')->with('success', 'User deleted successfully');
    }

    // === TAX RATES ===
    public function taxRates()
    {
        $taxRates = TaxRate::orderBy('type')->orderBy('name')->get();
        return view('admin.tax-rates.index', compact('taxRates'));
    }

    public function storeTaxRate(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'rate' => 'required|numeric|min:0|max:100',
            'type' => 'required|in:tax,service_charge',
            'is_default' => 'sometimes|boolean',
            'is_active' => 'sometimes|boolean',
        ]);
        TaxRate::create($data);
        return redirect()->route('admin.tax-rates')->with('success', 'Tax rate created successfully');
    }

    public function updateTaxRate(Request $request, $id)
    {
        $rate = TaxRate::findOrFail($id);
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'rate' => 'required|numeric|min:0|max:100',
            'type' => 'required|in:tax,service_charge',
            'is_default' => 'sometimes|boolean',
            'is_active' => 'sometimes|boolean',
        ]);
        $rate->update($data);
        return redirect()->route('admin.tax-rates')->with('success', 'Tax rate updated successfully');
    }

    public function deleteTaxRate($id)
    {
        TaxRate::findOrFail($id)->delete();
        return redirect()->route('admin.tax-rates')->with('success', 'Tax rate deleted successfully');
    }

    // === ORDERS ===
    public function orders()
    {
        $query = Order::with(['table', 'createdBy', 'payments'])
            ->withCount('items');

        if ($search = request('search')) {
            $query->where('order_no', 'like', "%{$search}%");
        }

        if ($status = request('status')) {
            $query->byStatus($status);
        }

        $orders = $query->latest()->paginate(25);
        return view('admin.orders.index', compact('orders'));
    }

    public function ordersData()
    {
        $query = Order::with(['table', 'createdBy', 'payments'])
            ->withCount('items');

        if ($search = request('search')) {
            $query->where('order_no', 'like', "%{$search}%");
        }

        if ($status = request('status')) {
            $query->byStatus($status);
        }

        $page = request('page', 1);
        $orders = $query->latest()->paginate(25, ['*'], 'page', $page);

        $items = $orders->map(fn($o) => [
            'id' => $o->id,
            'order_no' => $o->order_no,
            'table_no' => $o->table->table_no,
            'status' => $o->status,
            'items_count' => $o->items_count,
            'grand_total' => (float) $o->grand_total,
            'created_by' => $o->createdBy?->name ?? '—',
            'created_at' => $o->created_at->format('M d, H:i'),
            'can_cancel' => in_array($o->status, ['new', 'processing']),
            'cancel_url' => route('admin.orders.cancel', $o->id),
        ])->values()->toArray();

        $paginationHtml = $orders->appends(request()->query())->links()->toHtml();

        return response()->json([
            'orders' => $items,
            'total' => $orders->total(),
            'pagination_html' => $paginationHtml,
        ]);
    }

    public function cancelOrder($id)
    {
        $order = Order::findOrFail($id);
        $order->update(['status' => 'cancelled']);
        return redirect()->route('admin.orders')->with('success', 'Order cancelled successfully');
    }

    // === REPORTS ===
    public function reports()
    {
        $from = request('from', today()->toDateString());
        $to = request('to', today()->toDateString());

        // Ensure proper datetime ranges for queries (bare dates match only midnight)
        $fromDt = $from . ' 00:00:00';
        $toDt = $to . ' 23:59:59';

        $totalSales = (float) Payment::whereBetween('paid_at', [$fromDt, $toDt])->sum('amount');
        $orderCount = Order::whereBetween('created_at', [$fromDt, $toDt])->where('status', 'paid')->count();
        $avgOrderValue = $orderCount > 0 ? $totalSales / $orderCount : 0;

        $totalItems = OrderItem::whereHas('order', fn($q) => $q->whereBetween('created_at', [$fromDt, $toDt]))
            ->where('status', '!=', 'voided')
            ->sum('qty');

        $paymentMethods = Payment::whereBetween('paid_at', [$fromDt, $toDt])
            ->select('type as method', DB::raw('SUM(amount) as total'))
            ->groupBy('type')
            ->get();

        $ordersByStatus = Order::whereBetween('created_at', [$fromDt, $toDt])
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $topItems = $this->reportService->topItems($fromDt, $toDt);

        return view('admin.reports.index', compact(
            'from', 'to', 'totalSales', 'orderCount', 'avgOrderValue',
            'totalItems', 'paymentMethods', 'ordersByStatus', 'topItems'
        ));
    }

    public function reportsCsv()
    {
        $from = request('from', today()->toDateString());
        $to = request('to', today()->toDateString());
        $fromDt = $from . ' 00:00:00';
        $toDt = $to . ' 23:59:59';

        $filename = "report_{$from}_to_{$to}.csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($fromDt, $toDt, $from, $to) {
            $output = fopen('php://output', 'w');

            fputcsv($output, ['Report', $from, 'to', $to]);
            fputcsv($output, []);

            $totalSales = (float) Payment::whereBetween('paid_at', [$fromDt, $toDt])->sum('amount');
            $orderCount = Order::whereBetween('created_at', [$fromDt, $toDt])->where('status', 'paid')->count();
            fputcsv($output, ['Total Sales', number_format($totalSales, 2)]);
            fputcsv($output, ['Order Count', $orderCount]);
            fputcsv($output, []);

            fputcsv($output, ['Payment Method', 'Total']);
            $payments = Payment::whereBetween('paid_at', [$fromDt, $toDt])
                ->select('type as method', DB::raw('SUM(amount) as total'))
                ->groupBy('type')
                ->get();
            foreach ($payments as $p) {
                fputcsv($output, [$p->method, number_format((float) $p->total, 2)]);
            }
            fputcsv($output, []);

            fputcsv($output, ['Item', 'Qty Sold', 'Revenue']);
            $topItems = $this->reportService->topItems($fromDt, $toDt);
            foreach ($topItems as $item) {
                fputcsv($output, [
                    $item['menu_item']['name'] ?? 'N/A',
                    $item['total_qty'] ?? 0,
                    number_format((float) ($item['total_revenue'] ?? 0), 2),
                ]);
            }

            fclose($output);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function reportsPdf()
    {
        $from = request('from', today()->toDateString());
        $to = request('to', today()->toDateString());
        $fromDt = $from . ' 00:00:00';
        $toDt = $to . ' 23:59:59';

        $totalSales = (float) Payment::whereBetween('paid_at', [$fromDt, $toDt])->sum('amount');
        $orderCount = Order::whereBetween('created_at', [$fromDt, $toDt])->where('status', 'paid')->count();
        $avgOrderValue = $orderCount > 0 ? $totalSales / $orderCount : 0;

        $paymentMethods = Payment::whereBetween('paid_at', [$fromDt, $toDt])
            ->select('type as method', DB::raw('SUM(amount) as total'))
            ->groupBy('type')
            ->get();

        $topItems = $this->reportService->topItems($fromDt, $toDt);

        $pdf = Pdf::loadView('admin.reports.report-pdf', compact(
            'from', 'to', 'totalSales', 'orderCount', 'avgOrderValue',
            'paymentMethods', 'topItems'
        ));

        $filename = "report_{$from}_to_{$to}.pdf";
        return $pdf->download($filename);
    }
}
