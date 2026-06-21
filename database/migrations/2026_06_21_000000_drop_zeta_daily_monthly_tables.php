<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('zeta_reports');
        Schema::dropIfExists('daily_controls');
        Schema::dropIfExists('monthly_summaries');
    }

    public function down(): void
    {
        // Tables are removed permanently; no recreation needed
    }
};
