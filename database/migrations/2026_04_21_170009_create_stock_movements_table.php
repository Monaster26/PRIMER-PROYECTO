<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Movimientos de stock: auditoría completa de entradas y salidas.
     */
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('restrict');
            $table->foreignId('product_variant_id')->nullable()->constrained()->onDelete('restrict');

            // Tipo de movimiento
            $table->enum('type', [
                'purchase',    // Compra a proveedor
                'sale',        // Venta normal
                'return_in',   // Devolución de cliente
                'return_out',  // Devolución a proveedor
                'adjustment',  // Ajuste manual de inventario
                'damage',      // Merma / producto dañado
                'transfer',    // Transferencia entre sucursales
            ])->index();

            $table->integer('quantity_change')->comment('Positivo = entrada, negativo = salida');
            $table->integer('stock_before');
            $table->integer('stock_after');
            $table->unsignedBigInteger('unit_cost')->nullable()->comment('Costo unitario en centavos');

            // Referencia polimórfica al origen del movimiento
            $table->nullableMorphs('reference'); // reference_id, reference_type

            $table->foreignId('employee_id')->nullable()->constrained()->onDelete('set null');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['product_id', 'type', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
