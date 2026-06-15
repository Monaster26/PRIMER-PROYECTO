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
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained()->cascadeOnDelete();
            $table->string('category');
            $table->unsignedBigInteger('cash_spent')->default(0)->comment('Spent in cash in cents');
            $table->unsignedBigInteger('transfer_spent')->default(0)->comment('Spent via digital transfer in cents');
            $table->unsignedBigInteger('total_expense')->comment('totalExpense = cash_spent + transfer_spent');
            $table->date('date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
