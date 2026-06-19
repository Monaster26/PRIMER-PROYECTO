<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cash_sessions', function (Blueprint $table) {
            $table->bigInteger('efectivo_esperado')->nullable()->after('total_ingresos');
            $table->bigInteger('diferencia_efectivo')->nullable()->after('efectivo_esperado');
            $table->bigInteger('redcompra_esperado')->nullable()->after('diferencia_efectivo');
            $table->bigInteger('diferencia_redcompra')->nullable()->after('redcompra_esperado');
            $table->bigInteger('transferencia_esperado')->nullable()->after('diferencia_redcompra');
            $table->bigInteger('diferencia_transferencia')->nullable()->after('transferencia_esperado');
        });
    }

    public function down(): void
    {
        Schema::table('cash_sessions', function (Blueprint $table) {
            $table->dropColumn([
                'efectivo_esperado',
                'diferencia_efectivo',
                'redcompra_esperado',
                'diferencia_redcompra',
                'transferencia_esperado',
                'diferencia_transferencia',
            ]);
        });
    }
};
