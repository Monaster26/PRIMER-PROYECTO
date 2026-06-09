<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tabla de productos con soporte para inventario, POS y e-commerce.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->onDelete('restrict');
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('sku')->unique()->comment('Stock Keeping Unit interno');
            $table->string('barcode')->nullable()->index()->comment('EAN-13 / UPC para escáner');
            $table->string('brand')->nullable();
            $table->string('unit')->default('und')->comment('und, kg, lt, ml, g');

            // Precios en centavos para evitar decimales flotantes
            $table->unsignedBigInteger('cost_price')->default(0)->comment('Precio de costo en centavos');
            $table->unsignedBigInteger('price')->comment('Precio de venta en centavos');
            $table->unsignedTinyInteger('tax_rate')->default(0)->comment('IVA en porcentaje: 0, 5, 19');

            // Inventario
            $table->integer('stock')->default(0);
            $table->integer('min_stock')->default(5)->comment('Alerta de stock mínimo');

            // Restricciones de venta
            $table->unsignedTinyInteger('max_discount')->default(0)->comment('Descuento máximo permitido %');

            $table->string('image_path')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('has_variants')->default(false)->comment('Si tiene variantes, el stock se calcula desde product_variants');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['category_id', 'is_active']);
            if (Schema::getConnection()->getDriverName() !== 'sqlite') {
                $table->fullText(['name', 'description', 'brand'], 'products_search');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
