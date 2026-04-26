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
        // Fix Products table
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'stock')) {
                $table->integer('stock')->default(0)->after('price');
            }
            if (!Schema::hasColumn('products', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('stock'); // true = active
            }
            if (!Schema::hasColumn('products', 'sort_order')) {
                $table->integer('sort_order')->default(0)->after('is_active');
            }
        });

        // Fix Categories table
        Schema::table('categories', function (Blueprint $table) {
            if (!Schema::hasColumn('categories', 'sort_order')) {
                $table->integer('sort_order')->default(0)->after('name');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['stock', 'status', 'sort_order']);
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn(['sort_order']);
        });
    }
};
