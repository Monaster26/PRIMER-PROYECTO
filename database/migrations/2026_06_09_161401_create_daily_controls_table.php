<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_controls', function (Blueprint $table) {
            $table->id();
            $table->date('date')->unique()->index();
            $table->decimal('cash_withdrawals', 12, 2)->default(0.00);
            $table->decimal('card_sales_z', 12, 2)->default(0.00);
            $table->decimal('mercado_pago_sales', 12, 2)->default(0.00);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_controls');
    }
};
