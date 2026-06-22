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
use App\Models\TableTransfer;
use App\Models\TaxRate;
use App\Models\User;
use App\Services\ReportService;
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
        $tables = Table::with('area')->orderBy('sort_order')->get();
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
        $menuItems = MenuItem::with(['category', 'kitchen', 'activeModifiers'])->ordered()->get();
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
        return redirect()->route('admin.menu-items')->with('success', 'Modifier added successfully');
    }

    public function deleteModifier($id)
    {
        MenuItemModifier::findOrFail($id)->delete();
        return redirect()->route('admin.menu-items')->with('success', 'Modifier deleted successfully');
    }

    // === USERS ===
    public function users()
    {
        $users = User::with('kitchen')->orderBy('name')->get();
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
        $selectedDate = request('date', today()->toDateString());
        $dailySales = $this->reportService->dailySales($selectedDate);

        $totalSales = $dailySales['total_sales'] ?? 0;
        $orderCount = $dailySales['order_count'] ?? 0;
        $avgOrderValue = $orderCount > 0 ? $totalSales / $orderCount : 0;

        $totalItems = OrderItem::whereHas('order', fn($q) => $q->whereDate('created_at', $selectedDate))
            ->where('status', '!=', 'voided')
            ->sum('qty');

        $paymentMethods = Payment::whereDate('paid_at', $selectedDate)
            ->select('type as method', DB::raw('SUM(amount) as total'))
            ->groupBy('type')
            ->get();

        $ordersByStatus = Order::whereDate('created_at', $selectedDate)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $topItems = $this->reportService->topItems($selectedDate, $selectedDate);

        return view('admin.reports.index', compact(
            'selectedDate', 'totalSales', 'orderCount', 'avgOrderValue',
            'totalItems', 'paymentMethods', 'ordersByStatus', 'topItems'
        ));
    }
}
