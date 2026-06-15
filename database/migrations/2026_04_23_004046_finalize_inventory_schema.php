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
        // Fix Products Table
        Schema::table('products', function (Blueprint $table) {
            // Missing basic info
            if (!Schema::hasColumn('products', 'sku')) {
                $table->string('sku')->nullable()->unique()->after('slug');
            }
            if (!Schema::hasColumn('products', 'barcode')) {
                $table->string('barcode', 50)->nullable()->unique()->after('sku');
            }
            if (!Schema::hasColumn('products', 'brand')) {
                $table->string('brand')->nullable()->after('barcode');
            }
            if (!Schema::hasColumn('products', 'unit')) {
                $table->string('unit')->default('und')->after('brand');
            }

            // Missing financial info
            if (!Schema::hasColumn('products', 'cost_price')) {
                $table->unsignedBigInteger('cost_price')->default(0)->after('unit');
            }
            if (!Schema::hasColumn('products', 'tax_rate')) {
                $table->unsignedTinyInteger('tax_rate')->default(0)->after('price');
            }

            // Missing inventory info
            if (!Schema::hasColumn('products', 'min_stock')) {
                $table->integer('min_stock')->default(5)->after('stock');
            }
            if (!Schema::hasColumn('products', 'max_discount')) {
                $table->unsignedTinyInteger('max_discount')->default(0)->after('min_stock');
            }
            if (!Schema::hasColumn('products', 'has_variants')) {
                $table->boolean('has_variants')->default(false)->after('is_active');
            }
        });

        // Fix Categories Table
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
            $table->dropColumn([
                'sku', 'barcode', 'brand', 'unit', 
                'cost_price', 'tax_rate', 'min_stock', 
                'max_discount', 'has_variants'
            ]);
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn(['sort_order']);
        });
    }
};
