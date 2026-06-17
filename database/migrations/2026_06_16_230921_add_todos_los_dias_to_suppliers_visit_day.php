<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE suppliers MODIFY COLUMN visit_day ENUM('Lunes','Martes','Miércoles','Jueves','Viernes','Sábado','Domingo','Todos los días') NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE suppliers MODIFY COLUMN visit_day ENUM('Lunes','Martes','Miércoles','Jueves','Viernes','Sábado','Domingo') NULL");
    }
};
