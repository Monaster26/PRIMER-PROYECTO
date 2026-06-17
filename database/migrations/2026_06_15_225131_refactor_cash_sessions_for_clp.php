<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ─── Agregar columnas de denominaciones CLP a cash_sessions ───
        Schema::table('cash_sessions', function (Blueprint $table) {
            // --- Apertura: cantidad de billetes/monedas ---
            $table->integer('cant_20k_apertura')->default(0)->after('real_balance');
            $table->integer('cant_10k_apertura')->default(0)->after('cant_20k_apertura');
            $table->integer('cant_5k_apertura')->default(0)->after('cant_10k_apertura');
            $table->integer('cant_2k_apertura')->default(0)->after('cant_5k_apertura');
            $table->integer('cant_1k_apertura')->default(0)->after('cant_2k_apertura');
            $table->integer('cant_500_apertura')->default(0)->after('cant_1k_apertura');
            $table->integer('cant_100_apertura')->default(0)->after('cant_500_apertura');
            $table->integer('cant_50_apertura')->default(0)->after('cant_100_apertura');
            $table->integer('cant_10_apertura')->default(0)->after('cant_50_apertura');

            // --- Cierre: cantidad de billetes/monedas ---
            $table->integer('cant_20k_cierre')->nullable()->default(null)->after('cant_10_apertura');
            $table->integer('cant_10k_cierre')->nullable()->default(null)->after('cant_20k_cierre');
            $table->integer('cant_5k_cierre')->nullable()->default(null)->after('cant_10k_cierre');
            $table->integer('cant_2k_cierre')->nullable()->default(null)->after('cant_5k_cierre');
            $table->integer('cant_1k_cierre')->nullable()->default(null)->after('cant_2k_cierre');
            $table->integer('cant_500_cierre')->nullable()->default(null)->after('cant_1k_cierre');
            $table->integer('cant_100_cierre')->nullable()->default(null)->after('cant_500_cierre');
            $table->integer('cant_50_cierre')->nullable()->default(null)->after('cant_100_cierre');
            $table->integer('cant_10_cierre')->nullable()->default(null)->after('cant_50_cierre');

            // --- Totales calculados (CLP, sin decimales) ---
            $table->bigInteger('total_efectivo_apertura')->default(0)->after('cant_10_cierre');
            $table->bigInteger('total_efectivo_cierre')->nullable()->default(null)->after('total_efectivo_apertura');

            // --- Otros medios de pago ---
            $table->bigInteger('total_red_compra')->default(0)->after('total_efectivo_cierre');
            $table->bigInteger('total_transferencia')->default(0)->after('total_red_compra');
            $table->bigInteger('total_retiros')->default(0)->after('total_transferencia');

            // --- Resumen y descuadre ---
            $table->bigInteger('total_caja_esperado')->nullable()->default(null)->after('total_retiros');
            $table->bigInteger('diferencia_descuadre')->nullable()->default(null)->after('total_caja_esperado');
        });

        // ─── Eliminar tabla de denominaciones normalizada ───
        Schema::dropIfExists('cash_denominations');
    }

    public function down(): void
    {
        // Recrear tabla de denominaciones
        if (!Schema::hasTable('cash_denominations')) {
            Schema::create('cash_denominations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('cash_session_id')->constrained()->onDelete('cascade');
                $table->enum('type', ['opening', 'closing']);
                $table->decimal('denomination_value', 12, 2);
                $table->integer('quantity');
                $table->timestamps();
            });
        }

        Schema::table('cash_sessions', function (Blueprint $table) {
            $table->dropColumn([
                'cant_20k_apertura',
                'cant_10k_apertura',
                'cant_5k_apertura',
                'cant_2k_apertura',
                'cant_1k_apertura',
                'cant_500_apertura',
                'cant_100_apertura',
                'cant_50_apertura',
                'cant_10_apertura',
                'cant_20k_cierre',
                'cant_10k_cierre',
                'cant_5k_cierre',
                'cant_2k_cierre',
                'cant_1k_cierre',
                'cant_500_cierre',
                'cant_100_cierre',
                'cant_50_cierre',
                'cant_10_cierre',
                'total_efectivo_apertura',
                'total_efectivo_cierre',
                'total_red_compra',
                'total_transferencia',
                'total_retiros',
                'total_caja_esperado',
                'diferencia_descuadre',
            ]);
        });
    }
};
