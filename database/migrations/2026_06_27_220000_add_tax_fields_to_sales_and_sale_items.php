<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->unsignedBigInteger('net_total')->default(0)->after('total');
            $table->unsignedBigInteger('tax_total')->default(0)->after('net_total');
        });

        Schema::table('sale_items', function (Blueprint $table) {
            $table->unsignedBigInteger('net_price')->default(0)->after('price');
            $table->unsignedBigInteger('tax_amount')->default(0)->after('net_price');
            $table->unsignedTinyInteger('tax_rate')->default(19)->after('tax_amount');
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn(['net_total', 'tax_total']);
        });

        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropColumn(['net_price', 'tax_amount', 'tax_rate']);
        });
    }
};
