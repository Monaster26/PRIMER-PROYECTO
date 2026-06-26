<?php

use App\Http\Controllers\Admin\CashMovementController;
use App\Http\Controllers\Admin\CashSessionController;
use App\Http\Controllers\Admin\ControlZetaController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ExpenseController;
use App\Http\Controllers\Admin\InventoryAdjustmentController;
use App\Http\Controllers\Admin\LossController;
use App\Http\Controllers\Admin\ObservacionController;
use App\Http\Controllers\Admin\PendingInvoiceController;
use App\Http\Controllers\Admin\PosController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\PurchaseOrderController;
use App\Http\Controllers\Admin\ReporteDiarioController;
use App\Http\Controllers\Admin\SaleController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Admin\SupplierProductPriceController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {

    Route::middleware('role:admin,cashier')->group(function () {
        // POS — Caja Rápida
        Route::get('pos', [PosController::class, 'index'])->name('pos');
        Route::get('pos/lookup/{code}', [PosController::class, 'lookup'])->name('pos.lookup');
        Route::post('pos/checkout', [PosController::class, 'checkout'])->name('pos.checkout');
        Route::post('pos/cash-movement', [CashMovementController::class, 'store'])->name('pos.cash-movement');
        Route::post('pos/open-session', [PosController::class, 'openSession'])->name('pos.open-session');
        Route::post('pos/close-session', [CashSessionController::class, 'closeFromPos'])->name('pos.close-session');
        Route::get('pos/close-summary/{cashSession}', [CashSessionController::class, 'showCloseSummary'])
            ->name('pos.close-summary');
        Route::post('pos/observacion', [ObservacionController::class, 'store'])->name('pos.observacion');
        Route::get('pos/reprint/{sale}', [PosController::class, 'reprint'])->name('pos.reprint');
    });

    Route::middleware('role:admin')->group(function () {
        // Dashboard
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        // Arqueo de Caja
        Route::get('arqueo-caja', [CashSessionController::class, 'index'])->name('arqueo-caja.index');
        Route::post('arqueo-caja', [CashSessionController::class, 'store'])->name('arqueo-caja.store');
        Route::post('arqueo-caja/{cashSession}/close', [CashSessionController::class, 'close'])
            ->name('arqueo-caja.close');
        Route::delete('arqueo-caja/{cashSession}', [CashSessionController::class, 'destroy'])
            ->name('arqueo-caja.destroy');

        // Corte Diario (independiente de sesión de caja)
        Route::get('reporte-diario', [ReporteDiarioController::class, 'index'])->name('reporte-diario');

        // Catálogo de productos
        Route::resource('codigos', AdminProductController::class)
            ->parameters(['codigos' => 'product'])
            ->except(['show', 'create', 'edit']);
        Route::post('codigos/{product}/add-stock', [AdminProductController::class, 'addStock'])
            ->name('codigos.add-stock');
        Route::get('codigos/search-sku', [AdminProductController::class, 'searchSkuAjax'])
            ->name('codigos.search-sku');
        Route::get('codigos/search-name', [AdminProductController::class, 'searchNameAjax'])
            ->name('codigos.search-name');
        Route::post('codigos/importar', [AdminProductController::class, 'import'])
            ->name('codigos.import');

        // Categorías
        Route::get('categorias', [AdminCategoryController::class, 'index'])
            ->name('categorias.index');
        Route::post('categorias', [AdminCategoryController::class, 'store'])
            ->name('categorias.store');
        Route::put('categorias/{category}', [AdminCategoryController::class, 'update'])
            ->name('categorias.update');
        Route::delete('categorias/{category}', [AdminCategoryController::class, 'destroy'])
            ->name('categorias.destroy');

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

        // Z Mensual
        Route::get('z-mensual', [ControlZetaController::class, 'index'])->name('z-mensual.index');
        Route::put('z-mensual/{controlZeta}', [ControlZetaController::class, 'update'])->name('z-mensual.update');

        // Gastos
        Route::resource('gastos', ExpenseController::class)
            ->except(['show', 'create', 'edit']);

        // Facturas pendientes
        Route::resource('facturas-pendientes', PendingInvoiceController::class)
            ->parameters(['facturas-pendientes' => 'pendingInvoice'])
            ->except(['show', 'create', 'edit']);

        // Pérdidas
        Route::resource('perdida', LossController::class)
            ->parameters(['perdida' => 'loss'])
            ->except(['show', 'create', 'edit']);

        // Pedidos a proveedores
        Route::resource('pedidos', PurchaseOrderController::class)
            ->parameters(['pedidos' => 'purchaseOrder'])
            ->except(['show', 'create', 'edit']);
        Route::post('pedidos/{purchaseOrder}/receive', [PurchaseOrderController::class, 'receive'])
            ->name('pedidos.receive');

        // Usuarios
        Route::get('cajeros', [UserController::class, 'cashiers'])->name('cajeros');
        Route::post('cajeros', [UserController::class, 'store'])->name('cajeros.store');
        Route::put('cajeros/{user}', [UserController::class, 'update'])->name('cajeros.update');
        Route::delete('cajeros/{user}', [UserController::class, 'destroy'])->name('cajeros.destroy');
        Route::get('clientes', [UserController::class, 'clients'])->name('clientes');

        // Observaciones
        Route::get('observaciones', [ObservacionController::class, 'index'])->name('observaciones.index');
        Route::get('observaciones/unread-count', [ObservacionController::class, 'unreadCount'])
            ->name('observaciones.unread-count');
        Route::put('observaciones/{observacion}/mark-read', [ObservacionController::class, 'markAsRead'])
            ->name('observaciones.mark-read');
        Route::put('observaciones/{observacion}', [ObservacionController::class, 'update'])
            ->name('observaciones.update');

        // Auditoria de Inventario
        Route::prefix('inventory-adjustments')->name('inventory-adjustments.')->group(function () {
            Route::get('/', [InventoryAdjustmentController::class, 'index'])->name('index');
            Route::get('/create', [InventoryAdjustmentController::class, 'create'])->name('create');
            Route::post('/scan', [InventoryAdjustmentController::class, 'scanProduct'])->name('scan');
            Route::post('/', [InventoryAdjustmentController::class, 'store'])->name('store');
        });
    });
});
