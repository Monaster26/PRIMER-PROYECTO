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
        Schema::table('cash_sessions', function (Blueprint $table) {
            $table->json('apertura_desglose')->nullable()->after('diferencia_descuadre');
            $table->json('cierre_desglose')->nullable()->after('apertura_desglose');
        });
    }

    public function down(): void
    {
        Schema::table('cash_sessions', function (Blueprint $table) {
            $table->dropColumn(['apertura_desglose', 'cierre_desglose']);
        });
    }
};
