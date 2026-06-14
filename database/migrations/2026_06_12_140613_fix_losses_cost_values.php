<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('UPDATE losses SET cost_at_loss = cost_at_loss / 100, total_loss_value = total_loss_value / 100 WHERE cost_at_loss > 0');
    }

    public function down(): void
    {
        DB::statement('UPDATE losses SET cost_at_loss = cost_at_loss * 100, total_loss_value = total_loss_value * 100 WHERE cost_at_loss > 0');
    }
};
