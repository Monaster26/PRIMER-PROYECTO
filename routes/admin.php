<?php

use App\Http\Controllers\Admin\CashMovementController;
use App\Http\Controllers\Admin\CashSessionController;
use App\Http\Controllers\Admin\DailyControlController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ExpenseController;
use App\Http\Controllers\Admin\LossController;
use App\Http\Controllers\Admin\MonthlySummaryController;
use App\Http\Controllers\Admin\PendingInvoiceController;
use App\Http\Controllers\Admin\PosController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\PurchaseOrderController;
use App\Http\Controllers\Admin\SaleController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Admin\SupplierProductPriceController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ZetaReportController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {

    Route::middleware('role:admin,cashier')->group(function () {
        // Dashboard
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        // POS — Caja Rápida
        Route::get('pos', [PosController::class, 'index'])->name('pos');
        Route::get('pos/lookup/{code}', [PosController::class, 'lookup'])->name('pos.lookup');
        Route::post('pos/checkout', [PosController::class, 'checkout'])->name('pos.checkout');
        Route::post('pos/cash-movement', [CashMovementController::class, 'store'])->name('pos.cash-movement');

        // Arqueo de Caja (cashier ve solo sus propias sesiones)
        Route::get('arqueo-caja', [CashSessionController::class, 'index'])->name('arqueo-caja.index');
        Route::post('arqueo-caja', [CashSessionController::class, 'store'])->name('arqueo-caja.store');
        Route::post('arqueo-caja/{cashSession}/close', [CashSessionController::class, 'close'])
            ->name('arqueo-caja.close');
        Route::delete('arqueo-caja/{cashSession}', [CashSessionController::class, 'destroy'])
            ->name('arqueo-caja.destroy');
    });

    Route::middleware('role:admin')->group(function () {
        // Catálogo de productos
        Route::resource('codigos', AdminProductController::class)
            ->parameters(['codigos' => 'product'])
            ->except(['show', 'create', 'edit']);
        Route::post('codigos/{product}/add-stock', [AdminProductController::class, 'addStock'])
            ->name('codigos.add-stock');
        Route::post('codigos/importar', [AdminProductController::class, 'import'])
            ->name('codigos.import');

        // Ventas
        Route::resource('ventas', SaleController::class)
            ->except(['show', 'create', 'edit']);

        // Proveedores
        Route::resource('proveedores', SupplierController::class)
            ->parameters(['proveedores' => 'supplier'])
            ->except(['show', 'create', 'edit']);

        // Comparativa de precios
        Route::resource('comparativa-precios', SupplierProductPriceController::class)
            ->except(['show', 'create', 'edit']);

        // Control diario
        Route::resource('control-diario', DailyControlController::class)
            ->except(['show', 'create', 'edit']);

        // Gastos
        Route::resource('gastos', ExpenseController::class)
            ->except(['show', 'create', 'edit']);

        // Facturas pendientes
        Route::resource('facturas-pendientes', PendingInvoiceController::class)
            ->parameters(['facturas-pendientes' => 'pendingInvoice'])
            ->except(['show', 'create', 'edit']);

        // Pérdidas
        Route::resource('perdida', LossController::class)
            ->except(['show', 'create', 'edit']);

        // Reportes Zeta
        Route::resource('zeta', ZetaReportController::class)
            ->except(['show', 'create', 'edit']);

        // Resumen mensual
        Route::resource('resumen-mensual', MonthlySummaryController::class)
            ->except(['show', 'create', 'edit']);

        // Pedidos a proveedores
        Route::resource('pedidos', PurchaseOrderController::class)
            ->except(['show', 'create', 'edit']);
        Route::post('pedidos/{purchaseOrder}/receive', [PurchaseOrderController::class, 'receive'])
            ->name('pedidos.receive');

        // Usuarios
        Route::get('cajeros', [UserController::class, 'cashiers'])->name('cajeros');
        Route::post('cajeros', [UserController::class, 'store'])->name('cajeros.store');
        Route::put('cajeros/{user}', [UserController::class, 'update'])->name('cajeros.update');
        Route::get('clientes', [UserController::class, 'clients'])->name('clientes');
    });
});
