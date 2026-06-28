<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Loss;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\StockMovement;
use App\Models\User;
use Database\Seeders\CategorySeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
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

    public function test_crear_producto_con_sku_unico(): void
    {
        $categorySlug = Category::first()->slug;

        $this->post(route('admin.codigos.store'), [
            'name' => 'Arroz Grano de Oro 1kg',
            'sku' => 'ARROZ-001',
            'category_slug' => $categorySlug,
            'cost_price' => 0,
            'price' => 1500,
            'stock' => 50,
        ])->assertRedirect(route('admin.codigos.index'));

        $this->assertDatabaseHas('products', [
            'sku' => 'ARROZ-001',
            'stock' => 50,
        ]);
    }

    public function test_no_se_puede_crear_producto_con_sku_repetido(): void
    {
        $categorySlug = Category::first()->slug;

        $this->post(route('admin.codigos.store'), [
            'name' => 'Arroz Grano de Oro 1kg',
            'sku' => 'ARROZ-001',
            'category_slug' => $categorySlug,
            'cost_price' => 0,
            'price' => 1500,
            'stock' => 50,
        ]);

        $this->post(route('admin.codigos.store'), [
            'name' => 'Arroz Grano de Oro 2kg',
            'sku' => 'ARROZ-001',
            'category_slug' => $categorySlug,
            'cost_price' => 0,
            'price' => 2800,
            'stock' => 30,
        ])->assertSessionHasErrors('sku');

        $this->assertEquals(1, Product::where('sku', 'ARROZ-001')->count());
    }

    public function test_stock_se_actualiza_al_registrar_movimiento(): void
    {
        $categorySlug = Category::first()->slug;

        $this->post(route('admin.codigos.store'), [
            'name' => 'Arroz Grano de Oro 1kg',
            'sku' => 'ARROZ-001',
            'category_slug' => $categorySlug,
            'cost_price' => 0,
            'price' => 1500,
            'stock' => 50,
        ]);

        $product = Product::where('sku', 'ARROZ-001')->first();

        StockMovement::record($product, -5, 'sale');

        $this->assertSame(45, $product->fresh()->stock);

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'quantity_change' => -5,
            'stock_before' => 50,
            'stock_after' => 45,
        ]);
    }

    public function test_ajustar_cantidad_lote(): void
    {
        $categorySlug = Category::first()->slug;

        $this->post(route('admin.codigos.store'), [
            'name' => 'Leche Entera 1L',
            'sku' => 'LECHE-001',
            'category_slug' => $categorySlug,
            'cost_price' => 2000,
            'price' => 2500,
            'stock' => 50,
        ]);

        $product = Product::where('sku', 'LECHE-001')->first();
        $batch = ProductBatch::create([
            'product_id'      => $product->id,
            'quantity'        => 10,
            'cost_price'      => 2000,
            'expiration_date' => now()->addDays(60),
            'received_at'     => now(),
        ]);

        $this->patch(route('admin.codigos.batches.update', [$product->id, $batch->id]), [
            'quantity' => 7,
        ])->assertOk();

        $this->assertDatabaseHas('product_batches', [
            'id'       => $batch->id,
            'quantity' => 7,
        ]);

        $this->assertDatabaseHas('stock_movements', [
            'product_id'      => $product->id,
            'type'            => 'adjustment',
            'quantity_change' => -3,
        ]);

        $this->assertSame(47, $product->fresh()->stock);
    }

    public function test_baja_por_vencimiento_lote(): void
    {
        $categorySlug = Category::first()->slug;

        $this->post(route('admin.codigos.store'), [
            'name' => 'Yogurt Natural 1kg',
            'sku' => 'YOGURT-001',
            'category_slug' => $categorySlug,
            'cost_price' => 3000,
            'price' => 4500,
            'stock' => 20,
        ]);

        $product = Product::where('sku', 'YOGURT-001')->first();
        $batch = ProductBatch::create([
            'product_id'      => $product->id,
            'quantity'        => 5,
            'cost_price'      => 3000,
            'expiration_date' => now()->subDay(),
            'received_at'     => now(),
        ]);

        $this->delete(route('admin.codigos.batches.destroy', [$product->id, $batch->id]))
            ->assertOk();

        $this->assertDatabaseHas('product_batches', [
            'id'       => $batch->id,
            'quantity' => 0,
        ]);

        $this->assertDatabaseHas('losses', [
            'product_id'   => $product->id,
            'quantity'     => 5,
            'cost_at_loss' => 30.00,
            'total_loss_value' => 150.00,
            'reason'       => 'Vencimiento de lote',
        ]);

        $this->assertDatabaseHas('stock_movements', [
            'product_id'      => $product->id,
            'type'            => 'loss',
            'quantity_change' => -5,
        ]);

        $this->assertSame(15, $product->fresh()->stock);
    }
}
