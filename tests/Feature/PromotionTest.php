<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\User;
use Database\Seeders\CategorySeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Inertia\Testing\AssertableInertia as Assert;

class PromotionTest extends TestCase
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

    public function test_min_qty_discount_precio_fijo_total_aplica_correctamente(): void
    {
        $categorySlug = Category::first()->slug;

        $this->post(route('admin.codigos.store'), [
            'name' => 'Super 8',
            'sku' => 'SUPER8-001',
            'category_slug' => $categorySlug,
            'cost_price' => 200,
            'price' => 40000,
            'stock' => 100,
        ]);

        $product = Product::where('sku', 'SUPER8-001')->first();

        $promotion = Promotion::create([
            'name' => '3 Super 8 a $1.000',
            'type' => 'min_qty_discount',
            'conditions' => [
                'product_id' => $product->id,
                'min_qty' => 3,
                'special_price' => 1000,
            ],
            'rewards' => [],
            'is_active' => true,
            'priority' => 0,
        ]);

        // 3 unidades a $400 c/u = $1.200 normal - $1.000 precio fijo total = $200 descuento
        $result = $promotion->evaluateCart([
            ['product_id' => $product->id, 'variant_id' => null, 'qty' => 3, 'price' => 40000],
        ]);

        $this->assertTrue($result['applies']);
        $this->assertSame(20000, $result['discount']); // 20000 centavos = $200
    }

    public function test_min_qty_discount_precio_fijo_total_checkout_simulado(): void
    {
        $categorySlug = Category::first()->slug;

        $this->post(route('admin.codigos.store'), [
            'name' => 'Super 8',
            'sku' => 'SUPER8-CHECKOUT',
            'category_slug' => $categorySlug,
            'cost_price' => 200,
            'price' => 40000,
            'stock' => 100,
        ]);

        $product = Product::where('sku', 'SUPER8-CHECKOUT')->first();

        // Crear promoción via controller (mismo flujo que el admin)
        $this->post(route('admin.promociones.store'), [
            'name' => '3 Super 8 a $1.000',
            'type' => 'min_qty_discount',
            'is_active' => true,
            'priority' => 0,
            'conditions' => [
                'product_id' => $product->id,
                'min_qty' => 3,
                'special_price' => 1000,
            ],
        ])->assertRedirect(route('admin.promociones.index'));

        // Recuperar promoción como lo haría el POS: via scope active()
        $promotions = Promotion::active()->get();
        $promotion = $promotions->firstWhere('name', '3 Super 8 a $1.000');
        $this->assertNotNull($promotion, 'La promoción debería estar en el scope active()');

        // Verificar que conditions se guardaron correctamente
        $this->assertSame($product->id, $promotion->conditions['product_id']);
        $this->assertSame(3, $promotion->conditions['min_qty']);
        $this->assertSame(1000, $promotion->conditions['special_price']);

        // Simular el cartForPromo que construye el POS
        $cartForPromo = [
            ['product_id' => $product->id, 'variant_id' => null, 'qty' => 3, 'price' => 40000],
        ];

        $result = $promotion->evaluateCart($cartForPromo);

        $this->assertTrue($result['applies']);
        // 3 × $40.000 = $120.000 - ($1.000 × 100) = $20.000 descuento en centavos
        $this->assertSame(20000, $result['discount']);
    }

    public function test_min_qty_discount_precio_fijo_no_negativo(): void
    {
        $categorySlug = Category::first()->slug;

        $this->post(route('admin.codigos.store'), [
            'name' => 'Super 8',
            'sku' => 'SUPER8-002',
            'category_slug' => $categorySlug,
            'cost_price' => 200,
            'price' => 40000,
            'stock' => 100,
        ]);

        $product = Product::where('sku', 'SUPER8-002')->first();

        $promotion = Promotion::create([
            'name' => 'Super 8 precio inflado',
            'type' => 'min_qty_discount',
            'conditions' => [
                'product_id' => $product->id,
                'min_qty' => 1,
                'special_price' => 99999,
            ],
            'rewards' => [],
            'is_active' => true,
            'priority' => 0,
        ]);

        $result = $promotion->evaluateCart([
            ['product_id' => $product->id, 'variant_id' => null, 'qty' => 1, 'price' => 40000],
        ]);

        $this->assertTrue($result['applies']);
        $this->assertSame(0, $result['discount']);
    }

    public function test_min_qty_discount_porcentaje_sigue_funcionando(): void
    {
        $categorySlug = Category::first()->slug;

        $this->post(route('admin.codigos.store'), [
            'name' => 'Arroz',
            'sku' => 'ARROZ-002',
            'category_slug' => $categorySlug,
            'cost_price' => 500,
            'price' => 30000,
            'stock' => 50,
        ]);

        $product = Product::where('sku', 'ARROZ-002')->first();

        $promotion = Promotion::create([
            'name' => '15% descuento en Arroz',
            'type' => 'min_qty_discount',
            'conditions' => [
                'product_id' => $product->id,
                'min_qty' => 2,
                'discount_pct' => 15,
            ],
            'rewards' => [],
            'is_active' => true,
            'priority' => 0,
        ]);

        $result = $promotion->evaluateCart([
            ['product_id' => $product->id, 'variant_id' => null, 'qty' => 4, 'price' => 30000],
        ]);

        $this->assertTrue($result['applies']);
        $this->assertSame(18000, $result['discount']);
    }

    public function test_creacion_y_evaluacion_integracion(): void
    {
        $categorySlug = Category::first()->slug;

        $this->post(route('admin.codigos.store'), [
            'name' => 'Super 8',
            'sku' => 'SUPER8-003',
            'category_slug' => $categorySlug,
            'cost_price' => 200,
            'price' => 40000,
            'stock' => 100,
        ]);

        $product = Product::where('sku', 'SUPER8-003')->first();

        $this->post(route('admin.promociones.store'), [
            'name' => '3 Super 8 a $1.000',
            'type' => 'min_qty_discount',
            'is_active' => true,
            'priority' => 0,
            'conditions' => [
                'product_id' => $product->id,
                'min_qty' => 3,
                'special_price' => 1000,
            ],
        ])->assertRedirect(route('admin.promociones.index'));

        $promotion = Promotion::where('name', '3 Super 8 a $1.000')->first();
        $this->assertNotNull($promotion);

        $this->assertSame($product->id, $promotion->conditions['product_id']);
        $this->assertSame(1000, $promotion->conditions['special_price']);

        // El scope active() debe encontrarla
        $activePromotions = Promotion::active()->get();
        $this->assertTrue($activePromotions->contains('id', $promotion->id));

        // evaluateCart debe aplicarla
        $result = $promotion->evaluateCart([
            ['product_id' => $product->id, 'variant_id' => null, 'qty' => 3, 'price' => 40000],
        ]);
        $this->assertTrue($result['applies']);
        $this->assertSame(20000, $result['discount']);

        // Con cantidad insuficiente no aplica
        $result2 = $promotion->evaluateCart([
            ['product_id' => $product->id, 'variant_id' => null, 'qty' => 2, 'price' => 40000],
        ]);
        $this->assertFalse($result2['applies']);
        $this->assertSame(0, $result2['discount']);
    }
}
