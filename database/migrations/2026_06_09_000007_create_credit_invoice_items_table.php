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
        Schema::create('credit_invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('credit_invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained();
            $table->integer('quantity');
            $table->unsignedBigInteger('unit_price')->comment('Unit price in cents');
            $table->unsignedBigInteger('line_total')->comment('quantity * unit_price in cents');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credit_invoice_items');
    }
};
