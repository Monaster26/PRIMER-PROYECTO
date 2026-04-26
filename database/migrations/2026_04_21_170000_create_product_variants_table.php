<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Variantes de producto: talla, color, peso, etc.
     * Cada variante tiene su propio SKU, barcode y stock.
     */
    public function up(): void
    {
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('name')->comment('ej: 500ml, Rojo L, Pack x6');
            $table->string('sku')->unique();
            $table->string('barcode')->nullable()->index();
            $table->unsignedBigInteger('price_override')->nullable()->comment('Precio específico, null = hereda del producto');
            $table->unsignedBigInteger('cost_price')->default(0);
            $table->integer('stock')->default(0);
            $table->integer('min_stock')->default(3);
            $table->string('image_path')->nullable();
            $table->json('attributes')->nullable()->comment('{"color":"rojo","talla":"L","peso":"500g"}');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['product_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
