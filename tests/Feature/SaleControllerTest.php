<?php

namespace Tests\Feature;

use App\Models\CashSession;
use App\Models\Category;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockMovement;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Database\Seeders\CategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SaleControllerTest extends TestCase
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

    private function createSaleWithItems(int $qty = 2): Sale
    {
        $category = Category::first();

        $product = Product::create([
            'name' => 'Test Product',
            'sku' => 'TST-' . uniqid(),
            'slug' => 'test-' . uniqid(),
            'category_id' => $category->id,
            'category_slug' => $category->slug,
            'price' => 1000,
            'cost_price' => 400,
            'stock' => 100,
        ]);

        $sale = Sale::create([
            'user_id' => $this->admin->id,
            'type' => 'pos',
            'total' => 1000 * $qty,
            'cash_amount' => 1000 * $qty,
            'status' => 'completed',
        ]);

        SaleItem::create([
            'sale_id' => $sale->id,
            'product_id' => $product->id,
            'quantity' => $qty,
            'price' => 1000,
            'total_line' => 1000 * $qty,
        ]);

        return $sale;
    }

    public function test_cancelar_revierte_stock_de_todos_los_items(): void
    {
        $sale = $this->createSaleWithItems(3);
        $product = $sale->items->first()->product;
        $stockBefore = $product->fresh()->stock;

        $response = $this->patch(route('admin.ventas.cancelar', $sale->id), [
            'reason' => 'customer_return',
        ]);

        $response->assertSessionHas('success');

        // Stock restaurado
        $this->assertEquals($stockBefore + 3, $product->fresh()->stock);

        // StockMovement registrado
        $movement = StockMovement::where('reference_id', $sale->id)
            ->where('type', 'return_in')
            ->first();
        $this->assertNotNull($movement);
        $this->assertEquals(3, $movement->quantity_change);
    }

    public function test_cancelar_con_sesion_cerrada_es_bloqueado(): void
    {
        $sale = $this->createSaleWithItems(1);

        // Crear sesión cerrada que cubre la venta
        CashSession::create([
            'user_id' => $sale->user_id,
            'date' => $sale->created_at->toDateString(),
            'opened_at' => $sale->created_at->subHour(),
            'closed_at' => $sale->created_at->addHour(),
            'opening_balance' => 0,
        ]);

        $response = $this->patch(route('admin.ventas.cancelar', $sale->id), [
            'reason' => 'customer_return',
        ]);

        $response->assertSessionHas('error');

        // Venta sigue completed
        $this->assertEquals('completed', $sale->fresh()->status);
    }

    public function test_no_se_puede_cancelar_venta_ya_cancelada(): void
    {
        $sale = $this->createSaleWithItems(1);

        // Primera cancelación
        $this->patch(route('admin.ventas.cancelar', $sale->id), [
            'reason' => 'customer_return',
        ]);

        // Segunda cancelación
        $response = $this->patch(route('admin.ventas.cancelar', $sale->id), [
            'reason' => 'cashier_error',
        ]);

        $response->assertSessionHas('error');
        $this->assertEquals('cancelled', $sale->fresh()->status);
    }

    public function test_cancelar_con_promocion_decrementa_used_count(): void
    {
        $category = Category::first();

        $product = Product::create([
            'name' => 'Promo Product',
            'sku' => 'PRO-001',
            'slug' => 'promo-001',
            'category_id' => $category->id,
            'category_slug' => $category->slug,
            'price' => 1000,
            'cost_price' => 500,
            'stock' => 50,
        ]);

        $promo = Promotion::create([
            'name' => 'Test Promo',
            'type' => 'special_price',
            'conditions' => ['product_id' => $product->id, 'special_price' => 800],
            'rewards' => [],
            'is_active' => true,
        ]);
        $promo->used_count = 5;
        $promo->save();

        $sale = Sale::create([
            'user_id' => $this->admin->id,
            'type' => 'pos',
            'total' => 1000,
            'cash_amount' => 1000,
            'promo_discount' => 200,
            'promotion_ids' => [$promo->id],
            'status' => 'completed',
        ]);

        SaleItem::create([
            'sale_id' => $sale->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => 1000,
            'total_line' => 1000,
        ]);

        $this->patch(route('admin.ventas.cancelar', $sale->id), [
            'reason' => 'customer_return',
        ]);

        $this->assertEquals(4, $promo->fresh()->used_count);
    }

    public function test_cancelar_venta_legacy_sin_promotion_ids_no_rompe(): void
    {
        $sale = $this->createSaleWithItems(1);
        $sale->update(['promotion_ids' => null, 'status' => 'completed']);

        $response = $this->patch(route('admin.ventas.cancelar', $sale->id), [
            'reason' => 'cashier_error',
            'note' => 'Producto en mal estado',
        ]);

        $response->assertSessionHas('success');
        $this->assertEquals('cancelled', $sale->fresh()->status);
        $this->assertEquals('cashier_error', $sale->fresh()->cancellation_reason);
        $this->assertEquals('Producto en mal estado', $sale->fresh()->cancellation_note);
    }

    public function test_venta_cancelada_excluida_de_dashboard(): void
    {
        $this->createSaleWithItems(1); // completed
        $cancelled = $this->createSaleWithItems(2);
        $cancelled->update(['status' => 'cancelled']);

        $response = $this->get(route('admin.dashboard'));
        $page = $response->getOriginalContent()->getData()['page']['props'];

        // todaySales should only count completed
        $this->assertSame(10, $page['todaySales']); // 1000¢ / 100 = 10
    }

    public function test_venta_cancelada_excluida_de_reporte_diario(): void
    {
        $this->createSaleWithItems(1);
        $cancelled = $this->createSaleWithItems(2);
        $cancelled->update(['status' => 'cancelled']);

        $response = $this->get(route('admin.reporte-diario'));
        $page = $response->getOriginalContent()->getData()['page']['props'];

        $this->assertSame(10, $page['summary']['grossSales']); // solo completed: 1000¢/100 = 10
    }
}
