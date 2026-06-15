<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Sistema de cupones: descuentos fijos o porcentuales.
     */
    public function up(): void
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique()->comment('Código del cupón ej: AHORRA20');
            $table->text('description')->nullable();
            $table->enum('type', ['fixed', 'percentage'])->default('percentage');
            $table->unsignedInteger('value')->comment('Valor: % o centavos según type');
            $table->unsignedBigInteger('min_order_amount')->default(0)->comment('Monto mínimo de compra en centavos');
            $table->unsignedBigInteger('max_discount_amount')->nullable()->comment('Límite de descuento en centavos');
            $table->unsignedInteger('max_uses')->nullable()->comment('Null = ilimitado');
            $table->unsignedInteger('max_uses_per_customer')->default(1);
            $table->unsignedInteger('used_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->json('applicable_category_ids')->nullable()->comment('Null = aplica a todo');
            $table->timestamps();

            $table->index(['code', 'is_active', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
