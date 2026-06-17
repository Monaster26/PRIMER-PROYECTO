<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropForeign(['cashier_id']);
            $table->dropColumn(['date', 'cashier_id', 'payment_method']);
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->date('date');
            $table->foreignId('cashier_id')->nullable()->constrained('users');
            $table->string('payment_method', 20)->default('cash');
        });
    }
};
