<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Líneas de pedido: items individuales dentro de una orden.
     */
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('restrict');
            $table->foreignId('product_variant_id')->nullable()->constrained()->onDelete('restrict');

            $table->string('product_name')->comment('Snapshot del nombre al momento de venta');
            $table->string('product_sku')->comment('Snapshot del SKU');
            $table->unsignedSmallInteger('quantity');
            $table->unsignedBigInteger('unit_price')->comment('Precio unitario en centavos al momento de venta');
            $table->unsignedBigInteger('cost_price')->default(0)->comment('Costo al momento de venta para calcular ROI');
            $table->unsignedTinyInteger('discount_pct')->default(0)->comment('% de descuento aplicado');
            $table->unsignedBigInteger('discount_amount')->default(0);
            $table->unsignedBigInteger('tax_amount')->default(0);
            $table->unsignedBigInteger('line_total')->comment('Total de la línea');
            $table->json('promotion_applied')->nullable()->comment('Detalle de promo aplicada');
            $table->timestamps();

            $table->index(['order_id']);
            $table->index(['product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
