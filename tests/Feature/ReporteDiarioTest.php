<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Database\Seeders\CategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReporteDiarioTest extends TestCase
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

    private function fetchReport(array $params = []): array
    {
        $response = $this->get(route('admin.reporte-diario', $params));
        $response->assertStatus(200);
        $page = $response->getOriginalContent()->getData()['page'] ?? null;
        $this->assertNotNull($page);
        return $page;
    }

    public function test_ganancia_neta_descuenta_promociones(): void
    {
        $category = Category::first();

        $product = Product::create([
            'name' => 'Test Product',
            'sku' => 'TEST-001',
            'slug' => 'test-product-001',
            'category_id' => $category->id,
            'category_slug' => $category->slug,
            'price' => 1000,
            'cost_price' => 400,
            'stock' => 100,
        ]);

        // Sale 1: 2 items, with promo discount
        $sale1 = Sale::create([
            'user_id' => $this->admin->id,
            'type' => 'pos',
            'total' => 1800,
            'cash_amount' => 1800,
            'promo_discount' => 200,
            'coupon_discount' => 0,
        ]);

        SaleItem::create([
            'sale_id' => $sale1->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'price' => 1000,
            'total_line' => 2000,
        ]);

        // Sale 2: 1 item, with coupon discount
        $sale2 = Sale::create([
            'user_id' => $this->admin->id,
            'type' => 'pos',
            'total' => 900,
            'cash_amount' => 900,
            'promo_discount' => 0,
            'coupon_discount' => 100,
        ]);

        SaleItem::create([
            'sale_id' => $sale2->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => 1000,
            'total_line' => 1000,
        ]);

        $page = $this->fetchReport();
        $props = $page['props'];

        // Gross profit = ((1000-400)*2 + (1000-400)*1) / 100 = (1200+600)/100 = 18
        $this->assertSame(18, $props['grossProfit']);

        // Promo discount = 200/100 = 2
        $this->assertSame(2, $props['discounts']['promo']);
        // Coupon discount = 100/100 = 1
        $this->assertSame(1, $props['discounts']['coupon']);
        // Total discount = 2+1 = 3
        $this->assertSame(3, $props['discounts']['total']);
        // Discount counts
        $this->assertSame(1, $props['discounts']['promo_count']);
        $this->assertSame(1, $props['discounts']['coupon_count']);
        // Net profit = 18 - 2 - 1 = 15
        $this->assertSame(15, $props['summary']['netProfit']);
    }

    public function test_cmv_se_calcula_correctamente(): void
    {
        $category = Category::first();

        $product = Product::create([
            'name' => 'Test CMV',
            'sku' => 'TEST-CMV',
            'slug' => 'test-cmv',
            'category_id' => $category->id,
            'category_slug' => $category->slug,
            'price' => 1000,
            'cost_price' => 400,
            'stock' => 50,
        ]);

        $sale = Sale::create([
            'user_id' => $this->admin->id,
            'type' => 'pos',
            'total' => 3000,
            'cash_amount' => 3000,
        ]);

        SaleItem::create([
            'sale_id' => $sale->id,
            'product_id' => $product->id,
            'quantity' => 3,
            'price' => 1000,
            'total_line' => 3000,
        ]);

        $page = $this->fetchReport();
        $props = $page['props'];

        // CMV = 400 * 3 / 100 = 12
        $this->assertSame(12, $props['cmv']);
    }

    public function test_by_category_incluye_profit_y_cost(): void
    {
        $categories = Category::take(2)->get();
        $catA = $categories[0];
        $catB = $categories[1];

        $productA = Product::create([
            'name' => 'Product A',
            'sku' => 'TEST-CAT-A',
            'slug' => 'test-cat-a',
            'category_id' => $catA->id,
            'category_slug' => $catA->slug,
            'price' => 1000,
            'cost_price' => 400,
            'stock' => 50,
        ]);

        $productB = Product::create([
            'name' => 'Product B',
            'sku' => 'TEST-CAT-B',
            'slug' => 'test-cat-b',
            'category_id' => $catB->id,
            'category_slug' => $catB->slug,
            'price' => 2000,
            'cost_price' => 800,
            'stock' => 50,
        ]);

        $sale = Sale::create([
            'user_id' => $this->admin->id,
            'type' => 'pos',
            'total' => 4000,
            'cash_amount' => 4000,
        ]);

        SaleItem::create([
            'sale_id' => $sale->id,
            'product_id' => $productA->id,
            'quantity' => 2,
            'price' => 1000,
            'total_line' => 2000,
        ]);

        SaleItem::create([
            'sale_id' => $sale->id,
            'product_id' => $productB->id,
            'quantity' => 1,
            'price' => 2000,
            'total_line' => 2000,
        ]);

        $page = $this->fetchReport();
        $props = $page['props'];
        $byCategory = $props['byCategory'];

        $this->assertCount(2, $byCategory);

        $catAResult = collect($byCategory)->firstWhere('category', $catA->name);
        $this->assertNotNull($catAResult);
        $this->assertArrayHasKey('profit', $catAResult);
        $this->assertArrayHasKey('cost', $catAResult);
        $this->assertSame(2, $catAResult['qty']);
        // (1000-400)*2/100 = 12
        $this->assertSame(12, $catAResult['profit']);
        // 400*2/100 = 8
        $this->assertSame(8, $catAResult['cost']);

        $catBResult = collect($byCategory)->firstWhere('category', $catB->name);
        $this->assertNotNull($catBResult);
        $this->assertSame(1, $catBResult['qty']);
        // (2000-800)*1/100 = 12
        $this->assertSame(12, $catBResult['profit']);
        // 800*1/100 = 8
        $this->assertSame(8, $catBResult['cost']);
    }

    public function test_sin_descuentos_discounts_total_cero(): void
    {
        $category = Category::first();

        $product = Product::create([
            'name' => 'No Discount',
            'sku' => 'TEST-NODISC',
            'slug' => 'test-nodisc',
            'category_id' => $category->id,
            'category_slug' => $category->slug,
            'price' => 500,
            'cost_price' => 200,
            'stock' => 50,
        ]);

        $sale = Sale::create([
            'user_id' => $this->admin->id,
            'type' => 'pos',
            'total' => 500,
            'cash_amount' => 500,
        ]);

        SaleItem::create([
            'sale_id' => $sale->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => 500,
            'total_line' => 500,
        ]);

        $page = $this->fetchReport();
        $props = $page['props'];

        $this->assertSame(0, $props['discounts']['total']);
        $this->assertSame(0, $props['discounts']['promo']);
        $this->assertSame(0, $props['discounts']['coupon']);
    }

    public function test_margen_cero_si_ventas_brutas_cero(): void
    {
        $page = $this->fetchReport();
        $props = $page['props'];

        $this->assertSame(0, $props['summary']['grossSales']);
        $this->assertSame(0, $props['summary']['netProfit']);
        $this->assertSame(0, $props['cmv']);
        $this->assertSame(0, $props['grossProfit']);
    }
}
