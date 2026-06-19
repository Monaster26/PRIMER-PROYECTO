<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            if (!Schema::hasColumn('sale_items', 'price')) {
                $table->unsignedBigInteger('price')->after('quantity')->comment('Unit price in cents');
            }
            if (!Schema::hasColumn('sale_items', 'total_line')) {
                $table->unsignedBigInteger('total_line')->after('price')->comment('Quantity * price in cents');
            }
        });

        if (Schema::hasColumn('sale_items', 'subtotal')) {
            DB::statement('UPDATE sale_items SET price = unit_price * 100, total_line = subtotal * 100');
        }
    }

    public function down(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropColumn(['price', 'total_line']);
        });
    }
};
