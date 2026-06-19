<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $columns = [];
        foreach (['unit_price', 'cost_price', 'subtotal'] as $c) {
            if (Schema::hasColumn('sale_items', $c)) {
                $columns[] = $c;
            }
        }
        if ($columns) {
            Schema::table('sale_items', fn(Blueprint $t) => $t->dropColumn($columns));
        }
    }

    public function down(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            if (!Schema::hasColumn('sale_items', 'unit_price')) {
                $table->decimal('unit_price', 12, 2);
            }
            if (!Schema::hasColumn('sale_items', 'cost_price')) {
                $table->decimal('cost_price', 12, 2);
            }
            if (!Schema::hasColumn('sale_items', 'subtotal')) {
                $table->decimal('subtotal', 12, 2);
            }
        });
    }
};
