<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->foreignId('coupon_id')->nullable()->constrained()->nullOnDelete()->after('total');
            $table->unsignedBigInteger('discount_total')->default(0)->after('coupon_id');
            $table->unsignedBigInteger('promo_discount')->default(0)->after('discount_total');
            $table->unsignedBigInteger('coupon_discount')->default(0)->after('promo_discount');
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropConstrainedForeignId('coupon_id');
            $table->dropColumn(['discount_total', 'promo_discount', 'coupon_discount']);
        });
    }
};
