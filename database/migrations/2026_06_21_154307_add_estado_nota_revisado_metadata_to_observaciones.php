<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('observaciones', function (Blueprint $table) {
            $table->string('estado', 20)->default('pendiente')->after('read_at');
            $table->text('nota_admin')->nullable()->after('estado');
            $table->timestamp('revisado_at')->nullable()->after('nota_admin');
            $table->json('metadata')->nullable()->after('revisado_at');
        });
    }

    public function down(): void
    {
        Schema::table('observaciones', function (Blueprint $table) {
            $table->dropColumn('metadata');
            $table->dropColumn('revisado_at');
            $table->dropColumn('nota_admin');
            $table->dropColumn('estado');
        });
    }
};
