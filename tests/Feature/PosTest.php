<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\Sale;
use App\Models\StockMovement;
use App\Models\User;
use Database\Seeders\CategorySeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PosTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RoleSeeder::class, CategorySeeder::class]);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');

        $this->actingAs($this->admin);
    }

    public function test_venta_descuenta_stock_correctamente(): void
    {
        $product = Product::create([
            'name' => 'Arroz Grano de Oro 1kg',
            'sku' => 'ARROZ-001',
            'slug' => 'arroz-grano-de-oro-1kg',
            'category_id' => Category::first()->id,
            'category_slug' => Category::first()->slug,
            'price' => 100000,
            'stock' => 10,
        ]);

        $response = $this->post(route('admin.pos.checkout'), [
            'items' => [
                ['product_id' => $product->id, 'quantity' => 3],
            ],
            'payments' => [
                ['method' => 'cash', 'amount' => 3000],
            ],
        ]);

        $response->assertJsonStructure([
            'success',
            'sale_id',
        ]);

        $this->assertSame(7, $product->fresh()->stock);

        $sale = Sale::find($response['sale_id']);
        $this->assertNotNull($sale);
        $this->assertSame(252102, $sale->net_total);
        $this->assertSame(47898, $sale->tax_total);
        $this->assertSame(300000, $sale->total);
        $saleItem = $sale->items()->first();
        $this->assertSame(84034, $saleItem->net_price);
        $this->assertSame(15966, $saleItem->tax_amount);
        $this->assertSame(19, $saleItem->tax_rate);

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'type' => 'sale',
            'quantity_change' => -3,
            'stock_before' => 10,
            'stock_after' => 7,
        ]);
    }

    public function test_no_se_puede_vender_producto_sin_stock_suficiente(): void
    {
        $product = Product::create([
            'name' => 'Arroz Grano de Oro 1kg',
            'sku' => 'ARROZ-001',
            'slug' => 'arroz-grano-de-oro-1kg',
            'category_id' => Category::first()->id,
            'category_slug' => Category::first()->slug,
            'price' => 100000,
            'stock' => 2,
        ]);

        $response = $this->post(route('admin.pos.checkout'), [
            'items' => [
                ['product_id' => $product->id, 'quantity' => 5],
            ],
            'payments' => [
                ['method' => 'cash', 'amount' => 5000],
            ],
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'error' => "Stock insuficiente para {$product->name} (disponible: {$product->stock})",
        ]);

        $this->assertSame(2, $product->fresh()->stock);
    }

    public function test_checkout_no_vende_stock_insuficiente_con_transaccion(): void
    {
        $product = Product::create([
            'name' => 'Arroz Grano de Oro 1kg',
            'sku' => 'ARROZ-001',
            'slug' => 'arroz-grano-de-oro-1kg',
            'category_id' => Category::first()->id,
            'category_slug' => Category::first()->slug,
            'price' => 100000,
            'stock' => 2,
        ]);

        $response = $this->post(route('admin.pos.checkout'), [
            'items' => [
                ['product_id' => $product->id, 'quantity' => 5],
            ],
            'payments' => [
                ['method' => 'cash', 'amount' => 5000],
            ],
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'error' => "Stock insuficiente para {$product->name} (disponible: {$product->stock})",
        ]);

        $this->assertSame(2, $product->fresh()->stock);

        $this->assertDatabaseCount('sales', 0);
        $this->assertDatabaseCount('sale_items', 0);
        $this->assertDatabaseCount('sale_payments', 0);
        $this->assertDatabaseCount('stock_movements', 0);
    }

    public function test_cupon_valido_aplica_descuento(): void
    {
        $coupon = Coupon::create([
            'code' => 'BIENVENIDO10',
            'type' => 'fixed',
            'value' => 500,
            'is_active' => true,
            'max_uses' => 100,
            'min_order_amount' => 0,
        ]);

        $product = Product::create([
            'name' => 'Arroz 1kg',
            'sku' => 'ARROZ-TEST',
            'slug' => 'arroz-test',
            'category_id' => Category::first()->id,
            'category_slug' => Category::first()->slug,
            'price' => 100000,
            'stock' => 10,
        ]);

        $response = $this->post(route('admin.pos.checkout'), [
            'items' => [
                ['product_id' => $product->id, 'quantity' => 2],
            ],
            'payments' => [
                ['method' => 'cash', 'amount' => 2000],
            ],
            'coupon_code' => 'BIENVENIDO10',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success', 'sale_id', 'discount_total', 'applied_promotions', 'coupon_discount',
        ]);

        $sale = Sale::find($response['sale_id']);
        $this->assertNotNull($sale);
        // total: 200000 (2×100000) - 500 (coupon) = 199500
        $this->assertSame(199500, $sale->total);
        $this->assertSame(500, $sale->discount_total);
        $this->assertSame(500, $sale->coupon_discount);
        $this->assertSame(0, $sale->promo_discount);
        $this->assertEquals($coupon->id, $sale->coupon_id);
    }

    public function test_cupon_invalido_rechaza_venta_sin_crear_registros(): void
    {
        $product = Product::create([
            'name' => 'Arroz 1kg',
            'sku' => 'ARROZ-TEST2',
            'slug' => 'arroz-test2',
            'category_id' => Category::first()->id,
            'category_slug' => Category::first()->slug,
            'price' => 100000,
            'stock' => 10,
        ]);

        $response = $this->post(route('admin.pos.checkout'), [
            'items' => [
                ['product_id' => $product->id, 'quantity' => 2],
            ],
            'payments' => [
                ['method' => 'cash', 'amount' => 2000],
            ],
            'coupon_code' => 'CODIGO_INEXISTENTE',
        ]);

        $response->assertStatus(422);
        $response->assertJson(['error' => 'Cupón inválido o expirado.']);

        $this->assertDatabaseCount('sales', 0);
        $this->assertDatabaseCount('sale_items', 0);
        $this->assertDatabaseCount('stock_movements', 0);
    }
}
