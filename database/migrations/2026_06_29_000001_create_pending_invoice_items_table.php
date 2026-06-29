<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pending_invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pending_invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('product_name');
            $table->string('category_name')->nullable();
            $table->string('subcategory_name')->nullable();
            $table->unsignedBigInteger('unit_cost');
            $table->unsignedBigInteger('previous_cost')->default(0);
            $table->integer('quantity_ordered');
            $table->integer('quantity_received')->default(0);
            $table->boolean('is_new_product')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pending_invoice_items');
    }
};
