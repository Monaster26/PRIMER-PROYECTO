<?php

use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\CreditInvoiceController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProviderController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Rutas Públicas
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return Inertia::render('Home');
})->name('home');

// Ruta de prueba para el detalle de producto
Route::get('/product-test', function () {
    return Inertia::render('ProductDetail');
})->name('product.test');

// Ruta de prueba para el POS (sin middleware para facilitar visualización inicial)
Route::get('/pos-test', function () {
    return Inertia::render('POS/Dashboard');
})->name('pos.test');

/*
|--------------------------------------------------------------------------
| Dashboard
|--------------------------------------------------------------------------
*/
Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

/*
|--------------------------------------------------------------------------
| Rutas Protegidas (requieren login web)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->group(function () {

    // ── Perfil de usuario ──────────────────────────────────────────────
    Route::get('/profile',    [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile',  [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ── Inventario: Categorías ────────────────────────────────────────
    Route::resource('categories', CategoryController::class)
         ->except(['show', 'create', 'edit']);

    // ── Inventario: Productos ─────────────────────────────────────────
    Route::resource('products', ProductController::class);
    Route::get('/products/lookup', [ProductController::class, 'lookup'])->name('products.lookup');

    // ── POS (Terminal de Punto de Venta) ──────────────────────────────
    Route::prefix('pos')->name('pos.')->group(function () {
        Route::get('/',          [PosController::class, 'index'])->name('index');
        Route::get('/employees', [PosController::class, 'employees'])->name('employees');
        Route::post('/login',    [PosController::class, 'login'])->name('login');
        Route::post('/logout',   [PosController::class, 'logout'])->name('logout');
        Route::post('/checkout', [PosController::class, 'checkout'])->name('checkout');
        Route::post('/coupon/validate', [PosController::class, 'validateCoupon'])->name('coupon.validate');
    });

    // ── Órdenes / Ventas ──────────────────────────────────────────────
    Route::resource('orders', OrderController::class)->only(['index', 'show']);
    Route::post('/orders/{order}/return', [OrderController::class, 'processReturn'])->name('orders.return');

    // ── Clientes ──────────────────────────────────────────────────────
    Route::resource('customers', CustomerController::class)->except(['create', 'edit', 'store']);
    Route::post('/customers/quick-register', [CustomerController::class, 'quickRegister'])->name('customers.quick-register');
    Route::get('/customers/{customer}/orders', [CustomerController::class, 'orderHistory'])->name('customers.orders');

    // ── Cupones ───────────────────────────────────────────────────────
    Route::resource('coupons', CouponController::class)->except(['show', 'create', 'edit']);

    // ── Analítica y Reportes ──────────────────────────────────────────
    Route::prefix('analytics')->name('analytics.')->group(function () {
        Route::get('/dashboard', [AnalyticsController::class, 'dashboard'])->name('dashboard');
    });

    // ── Gastos (Egresos) ──────────────────────────────────────────────
    Route::prefix('expenses')->name('expenses.')->group(function () {
        Route::get('/',           [ExpenseController::class, 'index'])->name('index');
        Route::post('/',          [ExpenseController::class, 'store'])->name('store');
        Route::put('/{expense}',  [ExpenseController::class, 'update'])->name('update');
        Route::delete('/{expense}', [ExpenseController::class, 'destroy'])->name('destroy');
    });

    // ── Proveedores ───────────────────────────────────────────────────
    Route::prefix('providers')->name('providers.')->group(function () {
        Route::get('/',                       [ProviderController::class, 'index'])->name('index');
        Route::post('/',                      [ProviderController::class, 'store'])->name('store');
        Route::put('/{provider}',             [ProviderController::class, 'update'])->name('update');
        Route::delete('/{provider}',          [ProviderController::class, 'destroy'])->name('destroy');
        Route::get('/price-matrix',           [ProviderController::class, 'priceMatrix'])->name('price-matrix');
        Route::post('/price-matrix/upsert',   [ProviderController::class, 'upsertPrice'])->name('price-matrix.upsert');
        Route::delete('/price-matrix/{price}',[ProviderController::class, 'deletePrice'])->name('price-matrix.delete');
    });

    // ── Créditos (Cuentas por Cobrar) ─────────────────────────────────
    Route::prefix('credit-invoices')->name('credit-invoices.')->group(function () {
        Route::get('/',                          [CreditInvoiceController::class, 'index'])->name('index');
        Route::post('/',                         [CreditInvoiceController::class, 'store'])->name('store');
        Route::patch('/{invoice}/mark-paid',     [CreditInvoiceController::class, 'markPaid'])->name('mark-paid');
        Route::delete('/{invoice}',              [CreditInvoiceController::class, 'destroy'])->name('destroy');
    });
});

require __DIR__ . '/auth.php';
require __DIR__ . '/admin.php';
