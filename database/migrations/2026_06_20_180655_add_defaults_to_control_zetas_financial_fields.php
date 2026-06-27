<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }
        DB::statement('ALTER TABLE control_zetas MODIFY efectivo_neto INT NOT NULL DEFAULT 0');
        DB::statement('ALTER TABLE control_zetas MODIFY red_compra_neto INT NOT NULL DEFAULT 0');
        DB::statement('ALTER TABLE control_zetas MODIFY transferencia INT NOT NULL DEFAULT 0');
        DB::statement('ALTER TABLE control_zetas MODIFY red_compra_total INT NOT NULL DEFAULT 0');
        DB::statement('ALTER TABLE control_zetas MODIFY porcentaje_banco INT NOT NULL DEFAULT 0');
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }
        DB::statement('ALTER TABLE control_zetas MODIFY efectivo_neto INT NOT NULL');
        DB::statement('ALTER TABLE control_zetas MODIFY red_compra_neto INT NOT NULL');
        DB::statement('ALTER TABLE control_zetas MODIFY transferencia INT NOT NULL');
        DB::statement('ALTER TABLE control_zetas MODIFY red_compra_total INT NOT NULL');
        DB::statement('ALTER TABLE control_zetas MODIFY porcentaje_banco INT NOT NULL');
    }
};
