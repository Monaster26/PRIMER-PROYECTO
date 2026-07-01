<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pending_invoices', function (Blueprint $table) {
            $table->unsignedBigInteger('tax_amount')->default(0)->after('total_amount');
        });
    }

    public function down(): void
    {
        Schema::table('pending_invoices', function (Blueprint $table) {
            $table->dropColumn('tax_amount');
        });
    }
};
