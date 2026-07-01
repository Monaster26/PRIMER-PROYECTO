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

class DashboardTest extends TestCase
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

    private function fetchDashboard(): array
    {
        $response = $this->get(route('admin.dashboard'));
        $response->assertStatus(200);
        $page = $response->getOriginalContent()->getData()['page'] ?? null;
        $this->assertNotNull($page);
        return $page['props'];
    }

    public function test_ganancia_hoy_es_neta_tras_descontar_promociones(): void
    {
        $category = Category::first();

        // Product: price=1200¢ ($12), cost_price=867¢ ($8.67)
        $product = Product::create([
            'name' => 'Test Bug',
            'sku' => 'BUG-001',
            'slug' => 'test-bug-001',
            'category_id' => $category->id,
            'category_slug' => $category->slug,
            'price' => 1200,
            'cost_price' => 867,
            'stock' => 100,
        ]);

        // 100 units → gross profit = (1200-867)*100 = 33300¢
        // promo_discount = 20000¢ → net = 33300-20000 = 13300¢
        // Display (÷100) → net = 133, todaySales = 1000, margin = 13.3%
        $sale = Sale::create([
            'user_id' => $this->admin->id,
            'type' => 'pos',
            'total' => 100000,
            'cash_amount' => 100000,
            'promo_discount' => 20000,
            'coupon_discount' => 0,
        ]);

        SaleItem::create([
            'sale_id' => $sale->id,
            'product_id' => $product->id,
            'quantity' => 100,
            'price' => 1200,
            'total_line' => 120000,
        ]);

        $props = $this->fetchDashboard();

        $this->assertSame(133, $props['todayProfit']);
        $this->assertSame(13.3, $props['todayMargin']);
    }

    public function test_ganancia_hoy_bruta_si_sin_descuentos(): void
    {
        $category = Category::first();

        $product = Product::create([
            'name' => 'No Discount',
            'sku' => 'NO-DISC',
            'slug' => 'no-disc',
            'category_id' => $category->id,
            'category_slug' => $category->slug,
            'price' => 1000,
            'cost_price' => 400,
            'stock' => 50,
        ]);

        $sale = Sale::create([
            'user_id' => $this->admin->id,
            'type' => 'pos',
            'total' => 2000,
            'cash_amount' => 2000,
        ]);

        SaleItem::create([
            'sale_id' => $sale->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'price' => 1000,
            'total_line' => 2000,
        ]);

        $props = $this->fetchDashboard();

        // Gross = (1000-400)*2 = 1200¢ → 1200/100 = 12
        $this->assertSame(12, $props['todayProfit']);
    }
}
