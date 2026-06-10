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
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type')->comment('pos or ecommerce');
            $table->unsignedBigInteger('cash_amount')->default(0)->comment('Amount paid in cash in cents');
            $table->unsignedBigInteger('card_amount')->default(0)->comment('Amount paid via card in cents');
            $table->unsignedBigInteger('transfer_amount')->default(0)->comment('Amount paid via digital transfer in cents');
            $table->unsignedBigInteger('total')->comment('Total amount: cash + card + transfer');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
