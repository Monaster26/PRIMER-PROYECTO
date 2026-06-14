<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->date('date')->nullable()->index();
            $table->timestamp('opened_at');
            $table->timestamp('closed_at')->nullable();
            $table->decimal('opening_balance', 12, 2);
            $table->decimal('real_balance', 12, 2)->default(0.00);
            $table->timestamps();

            $table->index(['date', 'user_id']);
        });

        Schema::create('cash_denominations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cash_session_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['opening', 'closing']);
            $table->decimal('denomination_value', 12, 2);
            $table->integer('quantity');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_denominations');
        Schema::dropIfExists('cash_sessions');
    }
};
