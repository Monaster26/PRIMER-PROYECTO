<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
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
}
