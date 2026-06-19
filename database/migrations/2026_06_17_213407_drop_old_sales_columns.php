<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('sales', 'date')) {
            Schema::table('sales', fn(Blueprint $t) => $t->dropColumn('date'));
        }
        if (Schema::hasColumn('sales', 'payment_method')) {
            Schema::table('sales', fn(Blueprint $t) => $t->dropColumn('payment_method'));
        }
        if (Schema::hasColumn('sales', 'cashier_id')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->dropForeign(['cashier_id']);
                $table->dropColumn('cashier_id');
            });
        }
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            if (!Schema::hasColumn('sales', 'date')) {
                $table->date('date');
            }
            if (!Schema::hasColumn('sales', 'cashier_id')) {
                $table->foreignId('cashier_id')->nullable()->constrained('users');
            }
            if (!Schema::hasColumn('sales', 'payment_method')) {
                $table->string('payment_method', 20)->default('cash');
            }
        });
    }
};
