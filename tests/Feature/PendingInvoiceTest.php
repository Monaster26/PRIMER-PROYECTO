<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Employee;
use App\Models\PendingInvoice;
use App\Models\PendingInvoiceItem;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\User;
use Database\Seeders\CategorySeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PendingInvoiceTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Supplier $supplier;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RoleSeeder::class, CategorySeeder::class]);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');

        Employee::create([
            'name'      => 'Admin Test',
            'email'     => 'admin@test.cl',
            'pin'       => '1234',
            'role'      => 'admin',
            'is_active' => true,
        ]);

        $this->supplier = Supplier::create([
            'company_name' => 'Proveedor Test',
            'category'     => 'Test',
        ]);

        $this->actingAs($this->admin);
    }

    public function test_crear_pedido_con_productos_existentes(): void
    {
        $category = Category::first();
        $product = Product::create([
            'name'        => 'Arroz Test 1kg',
            'slug'        => 'arroz-test-1kg',
            'sku'         => 'ARROZ-001',
            'category_id' => $category->id,
            'cost_price'  => 80000,
            'price'       => 120000,
            'stock'       => 10,
            'is_active'   => true,
        ]);

        $this->post(route('admin.facturas-pendientes.store'), [
            'supplier_id'    => $this->supplier->id,
            'invoice_number' => 'FAC-001',
            'issue_date'     => '2026-06-01',
            'due_date'       => '2026-06-30',
            'notes'          => 'Compra mensual',
            'items'          => [
                [
                    'product_id'       => $product->id,
                    'product_name'     => 'Arroz Test 1kg',
                    'unit_cost'        => 850,
                    'quantity_ordered' => 10,
                    'is_new_product'   => false,
                    'category_name'    => $category->name,
                    'subcategory_name' => '',
                ],
            ],
        ])->assertRedirect(route('admin.facturas-pendientes.index'));

        $this->assertDatabaseHas('pending_invoices', [
            'supplier_id'    => $this->supplier->id,
            'invoice_number' => 'FAC-001',
            'total_amount'   => 85000 * 10,
            'status'         => 'pending',
        ]);

        $this->assertDatabaseHas('pending_invoice_items', [
            'product_name'    => 'Arroz Test 1kg',
            'product_id'      => $product->id,
            'unit_cost'       => 85000,
            'previous_cost'   => 80000,
            'quantity_ordered'=> 10,
        ]);
    }

    public function test_crear_pedido_con_producto_rapido(): void
    {
        $category = Category::first();

        $this->post(route('admin.facturas-pendientes.store'), [
            'supplier_id'    => $this->supplier->id,
            'invoice_number' => 'FAC-002',
            'issue_date'     => '2026-06-01',
            'due_date'       => '2026-06-30',
            'items'          => [
                [
                    'product_id'       => null,
                    'product_name'     => 'Producto Rápido Test',
                    'unit_cost'        => 500,
                    'quantity_ordered' => 5,
                    'is_new_product'   => true,
                    'category_name'    => $category->name,
                    'subcategory_name' => '',
                ],
            ],
        ])->assertRedirect();

        $this->assertDatabaseHas('pending_invoice_items', [
            'product_name'   => 'Producto Rápido Test',
            'is_new_product' => true,
            'previous_cost'  => 0,
            'quantity_ordered' => 5,
        ]);
    }

    public function test_recibir_pedido_incrementa_stock(): void
    {
        $category = Category::first();
        $product = Product::create([
            'name'        => 'Coca-Cola 1.5L',
            'slug'        => 'coca-cola-1-5l',
            'sku'         => 'COCA-001',
            'category_id' => $category->id,
            'cost_price'  => 80000,
            'price'       => 150000,
            'stock'       => 20,
            'is_active'   => true,
        ]);

        $invoice = PendingInvoice::create([
            'supplier_id'  => $this->supplier->id,
            'issue_date'   => '2026-06-01',
            'due_date'     => '2026-06-30',
            'total_amount' => 800000,
            'status'       => 'pending',
        ]);

        PendingInvoiceItem::create([
            'pending_invoice_id' => $invoice->id,
            'product_id'         => $product->id,
            'product_name'       => 'Coca-Cola 1.5L',
            'unit_cost'          => 80000,
            'previous_cost'      => 80000,
            'quantity_ordered'   => 10,
            'is_new_product'     => false,
        ]);

        $this->post(route('admin.facturas-pendientes.receive', $invoice->id), [
            'items' => [
                [
                    'id'                => $invoice->items->first()->id,
                    'quantity_received' => 10,
                ],
            ],
        ])->assertRedirect();

        $this->assertDatabaseHas('pending_invoices', [
            'id'     => $invoice->id,
            'status' => 'received',
        ]);

        $this->assertNotNull($invoice->fresh()->received_at);

        $this->assertSame(30, $product->fresh()->stock);

        $this->assertDatabaseHas('stock_movements', [
            'product_id'      => $product->id,
            'type'            => 'purchase',
            'quantity_change' => 10,
        ]);
    }

    public function test_recibir_pedido_crea_producto_nuevo_en_catalogo(): void
    {
        $category = Category::first();

        $invoice = PendingInvoice::create([
            'supplier_id'  => $this->supplier->id,
            'issue_date'   => '2026-06-01',
            'due_date'     => '2026-06-30',
            'total_amount' => 95000,
            'status'       => 'pending',
        ]);

        $item = PendingInvoiceItem::create([
            'pending_invoice_id' => $invoice->id,
            'product_id'         => null,
            'product_name'       => 'Arroz Nuevo Test 1kg',
            'category_name'      => $category->name,
            'unit_cost'          => 95000,
            'previous_cost'      => 0,
            'quantity_ordered'   => 2,
            'is_new_product'     => true,
        ]);

        $this->post(route('admin.facturas-pendientes.receive', $invoice->id), [
            'items' => [
                [
                    'id'                => $item->id,
                    'quantity_received' => 2,
                ],
            ],
        ])->assertRedirect();

        $product = Product::where('name', 'Arroz Nuevo Test 1kg')->first();
        $this->assertNotNull($product);
        $this->assertTrue($product->is_active);
        $this->assertSame(95000, $product->cost_price);
        $this->assertSame(190000, $product->price);

        $this->assertSame(2, $product->stock);

        $this->assertDatabaseHas('stock_movements', [
            'product_id'      => $product->id,
            'type'            => 'purchase',
            'quantity_change' => 2,
        ]);
    }

    public function test_recibir_pedido_actualiza_cost_price_si_cambio(): void
    {
        $category = Category::first();
        $product = Product::create([
            'name'        => 'Leche Test 1L',
            'slug'        => 'leche-test-1l',
            'sku'         => 'LECHE-001',
            'category_id' => $category->id,
            'cost_price'  => 60000,
            'price'       => 90000,
            'stock'       => 5,
            'is_active'   => true,
        ]);

        $invoice = PendingInvoice::create([
            'supplier_id'  => $this->supplier->id,
            'issue_date'   => '2026-06-01',
            'due_date'     => '2026-06-30',
            'total_amount' => 700000,
            'status'       => 'pending',
        ]);

        $item = PendingInvoiceItem::create([
            'pending_invoice_id' => $invoice->id,
            'product_id'         => $product->id,
            'product_name'       => 'Leche Test 1L',
            'unit_cost'          => 70000,
            'previous_cost'      => 60000,
            'quantity_ordered'   => 10,
            'is_new_product'     => false,
        ]);

        $this->post(route('admin.facturas-pendientes.receive', $invoice->id), [
            'items' => [
                [
                    'id'                => $item->id,
                    'quantity_received' => 10,
                ],
            ],
        ])->assertRedirect();

        $this->assertSame(70000, $product->fresh()->cost_price);
    }

    public function test_no_se_puede_editar_pedido_recibido(): void
    {
        $invoice = PendingInvoice::create([
            'supplier_id'  => $this->supplier->id,
            'issue_date'   => '2026-06-01',
            'due_date'     => '2026-06-30',
            'total_amount' => 100000,
            'status'       => 'received',
            'received_at'  => now(),
        ]);

        $this->put(route('admin.facturas-pendientes.update', $invoice->id), [
            'supplier_id' => $this->supplier->id,
            'issue_date'  => '2026-06-01',
            'due_date'    => '2026-06-30',
            'items'       => [
                [
                    'product_id'       => null,
                    'product_name'     => 'Item',
                    'unit_cost'        => 100,
                    'quantity_ordered' => 1,
                    'is_new_product'   => false,
                ],
            ],
        ])->assertSessionHas('error');
    }

    public function test_comparativa_de_precios_correcta(): void
    {
        $item = new PendingInvoiceItem([
            'product_name'   => 'Test',
            'unit_cost'      => 100000,
            'previous_cost'  => 80000,
            'is_new_product' => false,
        ]);

        $this->assertSame('up', $item->price_change_direction);
        $this->assertSame(20000, $item->price_change_amount);
        $this->assertSame(25.0, $item->price_change_percent);

        $itemDown = new PendingInvoiceItem([
            'product_name'   => 'Test Down',
            'unit_cost'      => 60000,
            'previous_cost'  => 80000,
            'is_new_product' => false,
        ]);

        $this->assertSame('down', $itemDown->price_change_direction);
        $this->assertSame(-20000, $itemDown->price_change_amount);
        $this->assertSame(-25.0, $itemDown->price_change_percent);

        $itemSame = new PendingInvoiceItem([
            'product_name'   => 'Test Same',
            'unit_cost'      => 80000,
            'previous_cost'  => 80000,
            'is_new_product' => false,
        ]);

        $this->assertSame('same', $itemSame->price_change_direction);

        $itemNew = new PendingInvoiceItem([
            'product_name'   => 'Test New',
            'unit_cost'      => 100000,
            'previous_cost'  => 0,
            'is_new_product' => true,
        ]);

        $this->assertSame('new', $itemNew->price_change_direction);
    }

    public function test_marcar_como_pagada(): void
    {
        $invoice = PendingInvoice::create([
            'supplier_id'  => $this->supplier->id,
            'issue_date'   => '2026-06-01',
            'due_date'     => '2026-06-30',
            'total_amount' => 200000,
            'status'       => 'received',
            'received_at'  => now(),
        ]);

        $this->patch(route('admin.facturas-pendientes.mark-paid', $invoice->id))
            ->assertRedirect();

        $this->assertDatabaseHas('pending_invoices', [
            'id'     => $invoice->id,
            'status' => 'paid',
        ]);
    }

    public function test_no_se_puede_eliminar_factura_recibida(): void
    {
        $invoice = PendingInvoice::create([
            'supplier_id'  => $this->supplier->id,
            'issue_date'   => '2026-06-01',
            'due_date'     => '2026-06-30',
            'total_amount' => 100000,
            'status'       => 'received',
            'received_at'  => now(),
        ]);

        $this->delete(route('admin.facturas-pendientes.destroy', $invoice->id))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('pending_invoices', ['id' => $invoice->id]);
    }

    public function test_overdue_accessor(): void
    {
        $invoice = new PendingInvoice([
            'due_date' => now()->subDay(),
            'status'   => 'pending',
        ]);
        $this->assertTrue($invoice->is_overdue);

        $invoicePaid = new PendingInvoice([
            'due_date' => now()->subDay(),
            'status'   => 'paid',
        ]);
        $this->assertFalse($invoicePaid->is_overdue);

        $invoiceFuture = new PendingInvoice([
            'due_date' => now()->addMonth(),
            'status'   => 'pending',
        ]);
        $this->assertFalse($invoiceFuture->is_overdue);
    }
}
