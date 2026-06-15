<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('sale_items');
        Schema::dropIfExists('sales');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type')->comment('pos or ecommerce');
            $table->unsignedBigInteger('cash_amount')->default(0);
            $table->unsignedBigInteger('card_amount')->default(0);
            $table->unsignedBigInteger('transfer_amount')->default(0);
            $table->unsignedBigInteger('total');
            $table->timestamps();
        });

        Schema::create('sale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained();
            $table->integer('quantity');
            $table->unsignedBigInteger('price');
            $table->unsignedBigInteger('total_line');
            $table->timestamps();
        });
    }
};
