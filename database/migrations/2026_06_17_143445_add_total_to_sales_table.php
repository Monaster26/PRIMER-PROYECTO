<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasColumn('sales', 'total')) {
            return;
        }
        Schema::table('sales', function (Blueprint $table) {
            $table->unsignedBigInteger('total')->default(0)->after('transfer_amount');
        });

        DB::statement('UPDATE sales s SET s.total = (SELECT COALESCE(SUM(subtotal * 100), 0) FROM sale_items WHERE sale_id = s.id)');
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn('total');
        });
    }
};
