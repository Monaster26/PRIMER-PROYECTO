<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\CategorySeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
