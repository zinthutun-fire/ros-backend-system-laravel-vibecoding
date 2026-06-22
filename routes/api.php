<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TableController;
use App\Http\Controllers\Api\TableAreaController;
use App\Http\Controllers\Api\KitchenController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\MenuItemController;
use App\Http\Controllers\Api\MenuItemModifierController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\KitchenOrderController;
use App\Http\Controllers\Api\CashierController;
use App\Http\Controllers\Api\TableTransferController;
use App\Http\Controllers\Api\TableMergeController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\TaxRateController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\ReportController;

// Public routes
Route::post('/login', [AuthController::class, 'login']);

// Authenticated routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Tables - waiter, cashier, admin
    Route::middleware('role:waiter,cashier,admin')->group(function () {
        Route::get('/tables', [TableController::class, 'index']);
        Route::get('/tables/{id}', [TableController::class, 'show']);
        Route::get('/table-areas', [TableAreaController::class, 'index']);
        Route::get('/table-areas/{id}', [TableAreaController::class, 'show']);
    });

    // Tables - admin only (CRUD)
    Route::middleware('role:admin')->group(function () {
        Route::post('/tables', [TableController::class, 'store']);
        Route::put('/tables/{id}', [TableController::class, 'update']);
        Route::delete('/tables/{id}', [TableController::class, 'destroy']);
        Route::post('/tables/reset', [TableController::class, 'reset']);

        Route::post('/table-areas', [TableAreaController::class, 'store']);
        Route::put('/table-areas/{id}', [TableAreaController::class, 'update']);
        Route::delete('/table-areas/{id}', [TableAreaController::class, 'destroy']);

        Route::post('/kitchens', [KitchenController::class, 'store']);
        Route::put('/kitchens/{id}', [KitchenController::class, 'update']);
        Route::delete('/kitchens/{id}', [KitchenController::class, 'destroy']);

        Route::post('/categories', [CategoryController::class, 'store']);
        Route::put('/categories/{id}', [CategoryController::class, 'update']);
        Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);

        Route::post('/menu-items', [MenuItemController::class, 'store']);
        Route::put('/menu-items/{id}', [MenuItemController::class, 'update']);
        Route::delete('/menu-items/{id}', [MenuItemController::class, 'destroy']);

        Route::post('/menu-item-modifiers', [MenuItemModifierController::class, 'store']);
        Route::put('/menu-item-modifiers/{id}', [MenuItemModifierController::class, 'update']);
        Route::delete('/menu-item-modifiers/{id}', [MenuItemModifierController::class, 'destroy']);

        Route::post('/register', [AuthController::class, 'register']);

        Route::get('/users', [UserController::class, 'index']);
        Route::get('/users/{id}', [UserController::class, 'show']);
        Route::post('/users', [UserController::class, 'store']);
        Route::put('/users/{id}', [UserController::class, 'update']);
        Route::delete('/users/{id}', [UserController::class, 'destroy']);

        Route::get('/kitchens', [KitchenController::class, 'index']);
        Route::get('/kitchens/{id}', [KitchenController::class, 'show']);

        Route::post('/tax-rates', [TaxRateController::class, 'store']);
        Route::put('/tax-rates/{id}', [TaxRateController::class, 'update']);
        Route::delete('/tax-rates/{id}', [TaxRateController::class, 'destroy']);
    });

    // Menu - all authenticated
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/categories/{id}', [CategoryController::class, 'show']);
    Route::get('/menu-items', [MenuItemController::class, 'index']);
    Route::get('/menu-items/{id}', [MenuItemController::class, 'show']);
    Route::get('/menu-items/{id}/modifiers', [MenuItemModifierController::class, 'index']);
    Route::get('/menu-item-modifiers', [MenuItemModifierController::class, 'index']);
    Route::get('/menu-item-modifiers/{id}', [MenuItemModifierController::class, 'show']);
    Route::get('/tax-rates', [TaxRateController::class, 'index']);
    Route::get('/tax-rates/{id}', [TaxRateController::class, 'show']);

    // Orders - waiter and admin
    Route::middleware('role:waiter,admin')->group(function () {
        Route::post('/orders', [OrderController::class, 'store']);
        Route::post('/orders/{id}/items', [OrderController::class, 'addItems']);
    });

    // Orders - waiter, cashier, admin (read)
    Route::middleware('role:waiter,cashier,admin')->group(function () {
        Route::get('/orders', [OrderController::class, 'index']);
        Route::get('/orders/by-order-no/{orderNo}', [OrderController::class, 'showByOrderNo']);
        Route::get('/orders/{id}', [OrderController::class, 'show']);
        Route::get('/orders/{id}/bill', [OrderController::class, 'bill']);
        Route::patch('/order-items/{id}/void', [OrderController::class, 'voidItem']);
    });

    // Discounts - cashier and admin
    Route::middleware('role:cashier,admin')->group(function () {
        Route::post('/orders/{id}/discount', [OrderController::class, 'applyDiscount']);
    });

    // Kitchen routes
    Route::middleware('role:kitchen')->group(function () {
        Route::get('/kitchen/orders', [KitchenOrderController::class, 'orders']);
        Route::patch('/kitchen/item-status', [KitchenOrderController::class, 'updateItemStatus']);
    });

    // Table transfers - waiter and admin
    Route::middleware('role:waiter,admin')->group(function () {
        Route::post('/table-transfers', [TableTransferController::class, 'store']);
    });

    // Table merges - waiter and admin
    Route::middleware('role:waiter,admin')->group(function () {
        Route::post('/table-merges', [TableMergeController::class, 'store']);
    });

    // Payments - cashier and admin
    Route::middleware('role:cashier,admin')->group(function () {
        Route::post('/payments', [CashierController::class, 'processPayment']);
        Route::get('/payments', [PaymentController::class, 'index']);
        Route::get('/payments/{id}', [PaymentController::class, 'show']);
    });

    // Cashier + Waiter (waiter can close tables from mobile view)
    Route::middleware('role:cashier,waiter,admin')->group(function () {
        Route::get('/cashier/orders', [CashierController::class, 'activeOrders']);
        Route::patch('/tables/{id}/close', [TableController::class, 'close']);
    });

    // Reports - admin and cashier
    Route::middleware('role:admin,cashier')->group(function () {
        Route::get('/reports/daily-sales', [ReportController::class, 'dailySales']);
        Route::get('/reports/top-items', [ReportController::class, 'topItems']);
    });

    Route::middleware('role:admin')->group(function () {
        Route::get('/reports/monthly-sales', [ReportController::class, 'monthlySales']);
        Route::get('/reports/yearly-sales', [ReportController::class, 'yearlySales']);
        Route::get('/reports/table-utilization', [ReportController::class, 'tableUtilization']);
    });
});
