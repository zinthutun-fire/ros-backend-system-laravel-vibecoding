<?php

use App\Http\Controllers\Web\AdminController;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\CashierController;
use App\Http\Controllers\Web\KitchenWebController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'app' => 'Restaurant Management System API',
        'version' => '1.0.0',
        'status' => 'running',
    ]);
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'loginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

Route::middleware(['auth', 'role:admin,manager'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

    Route::get('/dashboard/data', [AdminController::class, 'dashboardData'])->name('dashboard.data');

    Route::get('/tables', [AdminController::class, 'tables'])->name('tables');
    Route::post('/tables', [AdminController::class, 'storeTable'])->name('tables.store');
    Route::put('/tables/{id}', [AdminController::class, 'updateTable'])->name('tables.update');
    Route::delete('/tables/{id}', [AdminController::class, 'deleteTable'])->name('tables.delete');

    Route::get('/areas', [AdminController::class, 'areas'])->name('areas');
    Route::post('/areas', [AdminController::class, 'storeArea'])->name('areas.store');
    Route::put('/areas/{id}', [AdminController::class, 'updateArea'])->name('areas.update');
    Route::delete('/areas/{id}', [AdminController::class, 'deleteArea'])->name('areas.delete');

    Route::get('/kitchens', [AdminController::class, 'kitchens'])->name('kitchens');
    Route::post('/kitchens', [AdminController::class, 'storeKitchen'])->name('kitchens.store');
    Route::put('/kitchens/{id}', [AdminController::class, 'updateKitchen'])->name('kitchens.update');
    Route::delete('/kitchens/{id}', [AdminController::class, 'deleteKitchen'])->name('kitchens.delete');

    Route::get('/categories', [AdminController::class, 'categories'])->name('categories');
    Route::post('/categories', [AdminController::class, 'storeCategory'])->name('categories.store');
    Route::put('/categories/{id}', [AdminController::class, 'updateCategory'])->name('categories.update');
    Route::delete('/categories/{id}', [AdminController::class, 'deleteCategory'])->name('categories.delete');

    Route::get('/menu-items', [AdminController::class, 'menuItems'])->name('menu-items');
    Route::post('/menu-items', [AdminController::class, 'storeMenuItem'])->name('menu-items.store');
    Route::put('/menu-items/{id}', [AdminController::class, 'updateMenuItem'])->name('menu-items.update');
    Route::delete('/menu-items/{id}', [AdminController::class, 'deleteMenuItem'])->name('menu-items.delete');
    Route::post('/menu-items/modifiers', [AdminController::class, 'storeModifier'])->name('menu-items.modifiers.store');
    Route::put('/menu-items/modifiers/{id}', [AdminController::class, 'updateModifier'])->name('menu-items.modifiers.update');
    Route::delete('/menu-items/modifiers/{id}', [AdminController::class, 'deleteModifier'])->name('menu-items.modifiers.delete');

    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::post('/users', [AdminController::class, 'storeUser'])->name('users.store');
    Route::put('/users/{id}', [AdminController::class, 'updateUser'])->name('users.update');
    Route::delete('/users/{id}', [AdminController::class, 'deleteUser'])->name('users.delete');

    Route::get('/tax-rates', [AdminController::class, 'taxRates'])->name('tax-rates');
    Route::post('/tax-rates', [AdminController::class, 'storeTaxRate'])->name('tax-rates.store');
    Route::put('/tax-rates/{id}', [AdminController::class, 'updateTaxRate'])->name('tax-rates.update');
    Route::delete('/tax-rates/{id}', [AdminController::class, 'deleteTaxRate'])->name('tax-rates.delete');

    Route::get('/orders', [AdminController::class, 'orders'])->name('orders');
    Route::get('/orders/data', [AdminController::class, 'ordersData'])->name('orders.data');
    Route::put('/orders/{id}/cancel', [AdminController::class, 'cancelOrder'])->name('orders.cancel');

    Route::get('/reports', [AdminController::class, 'reports'])->name('reports');
    Route::get('/reports/csv', [AdminController::class, 'reportsCsv'])->name('reports.csv');
    Route::get('/reports/pdf', [AdminController::class, 'reportsPdf'])->name('reports.pdf');
});

Route::middleware(['auth', 'role:cashier,waiter'])->prefix('cashier')->name('cashier.')->group(function () {
    Route::get('/', [CashierController::class, 'dashboard'])->name('dashboard');
    Route::get('/dashboard', [CashierController::class, 'dashboard'])->name('dashboard');
    Route::get('/dashboard/data', [CashierController::class, 'dashboardData'])->name('dashboard.data');

    Route::get('/orders', [CashierController::class, 'orders'])->name('orders');
    Route::get('/orders/data', [CashierController::class, 'ordersData'])->name('orders.data');
    Route::get('/orders/{id}/data', [CashierController::class, 'orderDetailData'])->name('orders.detail.data');
    Route::get('/orders/{id}', [CashierController::class, 'orderDetail'])->name('orders.detail');
    Route::post('/orders/payment', [CashierController::class, 'processPayment'])->name('orders.payment');
    Route::post('/orders/split', [CashierController::class, 'splitPay'])->name('orders.split');
    Route::post('/orders/void-item', [CashierController::class, 'voidItem'])->name('orders.void-item');
    Route::post('/orders/discount', [CashierController::class, 'applyDiscount'])->name('orders.discount');
    Route::put('/orders/{id}/close-table', [CashierController::class, 'closeTable'])->name('orders.close-table');

    Route::get('/orders/{id}/receipt', [CashierController::class, 'receipt'])->name('receipt');

    Route::get('/tables', [CashierController::class, 'tables'])->name('tables');
    Route::get('/tables/data', [CashierController::class, 'tableData'])->name('tables.data');
    Route::get('/tables/{id}/detail', [CashierController::class, 'tableDetail'])->name('tables.detail');
    Route::post('/tables/transfer', [CashierController::class, 'transfer'])->name('tables.transfer');
    Route::post('/tables/merge', [CashierController::class, 'merge'])->name('tables.merge');
    Route::post('/tables/{id}/close', [CashierController::class, 'closeTableById'])->name('tables.close');

    Route::get('/reports/daily', [CashierController::class, 'dailyReport'])->name('reports.daily');
    Route::get('/reports/daily/csv', [CashierController::class, 'dailyReportCsv'])->name('reports.daily.csv');
    Route::get('/reports/daily/pdf', [CashierController::class, 'dailyReportPdf'])->name('reports.daily.pdf');
    Route::get('/reports/monthly', [CashierController::class, 'monthlyReport'])->name('reports.monthly');
    Route::get('/reports/monthly/csv', [CashierController::class, 'monthlyReportCsv'])->name('reports.monthly.csv');
    Route::get('/reports/monthly/pdf', [CashierController::class, 'monthlyReportPdf'])->name('reports.monthly.pdf');
});

Route::middleware(['auth', 'role:kitchen'])->prefix('kitchen')->name('kitchen.')->group(function () {
    Route::get('/', [KitchenWebController::class, 'dashboard'])->name('dashboard');
    Route::get('/dashboard', [KitchenWebController::class, 'dashboard'])->name('dashboard');
    Route::get('/dashboard/data', [KitchenWebController::class, 'dashboardData'])->name('dashboard.data');
    Route::get('/orders', [KitchenWebController::class, 'orders'])->name('orders');
    Route::get('/orders/data', [KitchenWebController::class, 'ordersData'])->name('orders.data');
    Route::patch('/orders/status', [KitchenWebController::class, 'updateStatus'])->name('orders.status');
    Route::get('/orders/{id}/print', [KitchenWebController::class, 'printOrder'])->name('orders.print');
    Route::get('/pending-count', [KitchenWebController::class, 'pendingCount'])->name('pending-count');
    Route::get('/display', [KitchenWebController::class, 'display'])->name('display');
});
