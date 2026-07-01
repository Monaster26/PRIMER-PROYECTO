<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SalePayment;
use App\Models\User;
use Database\Seeders\CategorySeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VentaHistorialTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $cashierA;
    private User $cashierB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RoleSeeder::class, CategorySeeder::class]);

        $this->admin = User::factory()->create(['name' => 'Admin']);
        $this->admin->assignRole('admin');

        $this->cashierA = User::factory()->create(['name' => 'Cajero Alpha']);
        $this->cashierA->assignRole('cashier');

        $this->cashierB = User::factory()->create(['name' => 'Cajero Beta']);
        $this->cashierB->assignRole('cashier');

        $this->actingAs($this->admin);
    }

    private function crearProducto(string $sku = 'PROD-TEST'): Product
    {
        return Product::create([
            'name'          => 'Producto Test',
            'sku'           => $sku,
            'slug'          => 'producto-test-' . $sku,
            'category_id'   => Category::first()->id,
            'category_slug' => Category::first()->slug,
            'price'         => 10000,
            'cost_price'    => 4000,
            'stock'         => 100,
        ]);
    }

    private function crearVenta(User $cashier, string $fecha, int $total = 10000, string $metodo = 'cash'): Sale
    {
        $sale = Sale::create([
            'user_id'        => $cashier->id,
            'type'           => 'pos',
            'total'          => $total,
            'cash_amount'    => $metodo === 'cash' ? $total : 0,
            'card_amount'    => $metodo === 'card' ? $total : 0,
            'transfer_amount'=> $metodo === 'transfer' ? $total : 0,
        ]);

        // created_at no está en $fillable, se asigna manualmente
        $sale->created_at = $fecha;
        $sale->save();

        SalePayment::create([
            'sale_id' => $sale->id,
            'method'  => $metodo,
            'amount'  => $total,
        ]);

        $product = $this->crearProducto('SKU-' . $sale->id);

        SaleItem::create([
            'sale_id'    => $sale->id,
            'product_id' => $product->id,
            'quantity'   => 1,
            'price'      => $total,
            'total_line' => $total,
        ]);

        return $sale;
    }

    public function test_listado_ventas_filtra_por_cajero(): void
    {
        $this->crearVenta($this->cashierA, now()->toDateTimeString());
        $this->crearVenta($this->cashierA, now()->toDateTimeString());
        $this->crearVenta($this->cashierB, now()->toDateTimeString());

        $response = $this->get(route('admin.ventas.index', [
            'cashier_id' => $this->cashierA->id,
        ]));

        $response->assertStatus(200);
        $page = $response->getOriginalContent()->getData()['page'] ?? [];
        $props = $page['props'] ?? [];

        $sales = $props['sales']['data'] ?? [];
        $this->assertCount(2, $sales);

        foreach ($sales as $s) {
            $this->assertSame($this->cashierA->id, $s['cashier']['id']);
        }
    }

    public function test_listado_ventas_filtra_por_rango_fechas(): void
    {
        $this->crearVenta($this->cashierA, '2026-06-01 10:00:00');
        $this->crearVenta($this->cashierA, '2026-06-15 10:00:00');
        $this->crearVenta($this->cashierA, '2026-07-01 10:00:00');

        $response = $this->get(route('admin.ventas.index', [
            'from' => '2026-06-01',
            'to'   => '2026-06-30',
        ]));

        $response->assertStatus(200);
        $page = $response->getOriginalContent()->getData()['page'] ?? [];
        $props = $page['props'] ?? [];

        $sales = $props['sales']['data'] ?? [];
        $this->assertCount(2, $sales);
    }

    public function test_listado_ventas_filtra_por_metodo_pago(): void
    {
        $this->crearVenta($this->cashierA, now()->toDateTimeString(), 10000, 'cash');
        $this->crearVenta($this->cashierA, now()->toDateTimeString(), 10000, 'card');
        $this->crearVenta($this->cashierA, now()->toDateTimeString(), 10000, 'transfer');

        $response = $this->get(route('admin.ventas.index', [
            'payment_method' => 'card',
        ]));

        $response->assertStatus(200);
        $page = $response->getOriginalContent()->getData()['page'] ?? [];
        $props = $page['props'] ?? [];

        $sales = $props['sales']['data'] ?? [];
        $this->assertCount(1, $sales);

        $payments = $sales[0]['payments'] ?? [];
        $methods = array_column($payments, 'method');
        $this->assertContains('card', $methods);
    }
}
