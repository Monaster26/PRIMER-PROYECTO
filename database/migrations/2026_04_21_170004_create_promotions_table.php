<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Motor de promociones: Compra X → Recibe Y (Buy X Get Y).
     */
    public function up(): void
    {
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('type', ['buy_x_get_y', 'bundle_discount', 'min_qty_discount'])->default('buy_x_get_y');
            $table->json('conditions')->comment('{"buy_product_id":1,"buy_qty":2}');
            $table->json('rewards')->comment('{"get_product_id":3,"get_qty":1,"discount_pct":100}');
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('priority')->default(0)->comment('Mayor número = mayor prioridad');
            $table->boolean('is_exclusive')->default(false)->comment('No se acumula con otras promos');
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['is_active', 'priority', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }
};
