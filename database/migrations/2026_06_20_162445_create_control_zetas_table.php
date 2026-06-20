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
        Schema::create('control_zetas', function (Blueprint $table) {
            $table->id();
            $table->date('fecha');
            $table->string('cajero');
            $table->integer('esperado_caja');
            $table->integer('efectivo_neto');
            $table->integer('red_compra_neto');
            $table->integer('transferencia');
            $table->integer('red_compra_total');
            $table->integer('porcentaje_banco');
            $table->integer('sobrante')->default(0);
            $table->integer('faltante')->default(0);
            $table->text('observaciones')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('control_zetas');
    }
};
