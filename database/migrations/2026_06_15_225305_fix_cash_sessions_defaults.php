<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cash_sessions', function (Blueprint $table) {
            $table->decimal('opening_balance', 12, 2)->default(0)->change();
            $table->decimal('real_balance', 12, 2)->default(0)->change();
        });
    }

    public function down(): void
    {
        Schema::table('cash_sessions', function (Blueprint $table) {
            $table->decimal('opening_balance', 12, 2)->default(null)->change();
            $table->decimal('real_balance', 12, 2)->default(null)->change();
        });
    }
};
