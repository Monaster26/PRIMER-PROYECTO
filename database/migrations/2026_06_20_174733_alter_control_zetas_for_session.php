<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('control_zetas', function (Blueprint $table) {
            $table->foreignId('cash_session_id')->nullable()->constrained('cash_sessions')->cascadeOnDelete();
            $table->renameColumn('fecha', 'fecha_apertura');
        });

        DB::statement('ALTER TABLE control_zetas MODIFY fecha_apertura DATETIME NOT NULL');
        DB::statement('ALTER TABLE control_zetas MODIFY esperado_caja INT NULL');
    }

    public function down(): void
    {
        Schema::table('control_zetas', function (Blueprint $table) {
            $table->dropForeign(['cash_session_id']);
            $table->dropColumn('cash_session_id');
            $table->renameColumn('fecha_apertura', 'fecha');
        });

        DB::statement('ALTER TABLE control_zetas MODIFY fecha DATE NOT NULL');
        DB::statement('ALTER TABLE control_zetas MODIFY esperado_caja INT NOT NULL');
    }
};
