<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->string('origin_type')->nullable()->after('user_id');
            $table->unsignedBigInteger('origin_id')->nullable()->after('origin_type');
            $table->index(['origin_type', 'origin_id']);
        });
    }

    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropIndex(['origin_type', 'origin_id']);
            $table->dropColumn(['origin_type', 'origin_id']);
        });
    }
};
