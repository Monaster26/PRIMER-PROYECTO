<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('zeta_reports', function (Blueprint $table) {
            $table->id();
            $table->date('date')->unique();
            $table->foreignId('cashier_id')->constrained('users');
            $table->decimal('total_z', 12, 2);
            $table->decimal('net_cash', 12, 2);
            $table->decimal('transfers', 12, 2);
            $table->decimal('pos_card_total', 12, 2);
            $table->decimal('surplus', 12, 2)->default(0.00);
            $table->decimal('deficit', 12, 2)->default(0.00);
            $table->text('observations')->nullable();
            $table->enum('status', ['pending_review', 'audited'])->default('pending_review');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('zeta_reports');
    }
};
