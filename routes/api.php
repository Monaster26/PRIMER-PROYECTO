<?php

use App\Http\Controllers\PosController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CustomerController;
use App\Http\Middleware\PosAuthentication;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — Mini-Market
|--------------------------------------------------------------------------
| Prefijo: /api
| Auth:    Laravel Sanctum (tokens)
| POS:     Middleware PosAuthentication (X-POS-Token header)
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| Rutas POS (autenticación por PIN + Session Token)
|--------------------------------------------------------------------------
*/
Route::prefix('pos')->name('api.pos.')->group(function () {

    // Login POS — pública (sin token previo)
    Route::post('/login',    [PosController::class, 'login'])->name('login');
    Route::get('/employees', [PosController::class, 'employees'])->name('employees');

    // Endpoints protegidos por PosAuthentication
    Route::middleware(PosAuthentication::class)->group(function () {
        Route::post('/logout',          [PosController::class, 'logout'])->name('logout');
        Route::post('/checkout',        [PosController::class, 'checkout'])->name('checkout');
        Route::post('/coupon/validate', [PosController::class, 'validateCoupon'])->name('coupon.validate');
        Route::get('/product/lookup',   [ProductController::class, 'lookup'])->name('product.lookup');
        Route::post('/customer/quick',  [CustomerController::class, 'quickRegister'])->name('customer.quick');
    });
});

/*
|--------------------------------------------------------------------------
| Rutas REST Autenticadas (Sanctum Token)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    // Búsqueda de productos
    Route::get('/products/lookup', [ProductController::class, 'lookup'])->name('api.products.lookup');

    // Búsqueda de clientes (para autocompletar)
    Route::get('/customers/search', function (\Illuminate\Http\Request $request) {
        $request->validate(['q' => 'required|string|min:2']);
        return \App\Models\Customer::search($request->q)
            ->limit(10)
            ->get(['id', 'name', 'phone', 'document_number', 'loyalty_points', 'is_vip']);
    })->name('api.customers.search');

    // Stock de un producto
    Route::get('/products/{product}/stock', function (\App\Models\Product $product) {
        return response()->json([
            'stock'          => $product->effective_stock,
            'is_low_stock'   => $product->is_low_stock,
            'min_stock'      => $product->min_stock,
        ]);
    })->name('api.products.stock');
});
