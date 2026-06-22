<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('observaciones', function (Blueprint $table) {
            $table->integer('monto_diferencia')->nullable()->after('detalle');
            $table->timestamp('read_at')->nullable()->after('monto_diferencia');
        });
    }

    public function down(): void
    {
        Schema::table('observaciones', function (Blueprint $table) {
            $table->dropColumn('read_at');
            $table->dropColumn('monto_diferencia');
        });
    }
};
