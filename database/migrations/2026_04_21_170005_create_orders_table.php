<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Órdenes / Ventas: cabecera del pedido.
     * Canal: POS (caja física) o Web (e-commerce).
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique()->comment('ej: MM-2026-000001');
            $table->foreignId('customer_id')->nullable()->constrained()->onDelete('restrict');
            $table->foreignId('employee_id')->nullable()->constrained()->onDelete('restrict');
            $table->foreignId('coupon_id')->nullable()->constrained()->onDelete('restrict');

            // Totales en centavos
            $table->unsignedBigInteger('subtotal')->default(0);
            $table->unsignedBigInteger('discount_total')->default(0);
            $table->unsignedBigInteger('tax_total')->default(0);
            $table->unsignedBigInteger('total')->default(0);

            $table->enum('payment_method', ['cash', 'card', 'transfer', 'mixed'])->default('cash');
            $table->unsignedBigInteger('cash_tendered')->nullable()->comment('Efectivo entregado');
            $table->unsignedBigInteger('change_due')->nullable()->comment('Cambio devuelto');

            $table->enum('status', ['pending', 'completed', 'cancelled', 'refunded'])->default('pending');
            $table->enum('sale_channel', ['pos', 'web', 'phone'])->default('pos');

            $table->json('promotion_ids')->nullable()->comment('IDs de promociones aplicadas');
            $table->text('notes')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'sale_channel', 'created_at']);
            $table->index(['customer_id', 'created_at']);
            $table->index(['employee_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
