<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Uso de cupones por orden: previene el doble uso.
     */
    public function up(): void
    {
        Schema::create('coupon_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coupon_id')->constrained()->onDelete('cascade');
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('customer_id')->nullable()->constrained()->onDelete('set null');
            $table->unsignedBigInteger('discount_applied')->comment('Monto descontado en centavos');
            $table->timestamps();

            $table->unique(['coupon_id', 'order_id']);
            $table->index(['coupon_id', 'customer_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupon_usages');
    }
};
