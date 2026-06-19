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
        if (!Schema::hasColumn('categories', 'icon')) {
            Schema::table('categories', fn(Blueprint $t) => $t->string('icon')->nullable()->after('name'));
        }
        if (!Schema::hasColumn('categories', 'image_path')) {
            Schema::table('categories', fn(Blueprint $t) => $t->string('image_path')->nullable()->after('name'));
        }
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn(['icon', 'image_path']);
        });
    }
};
