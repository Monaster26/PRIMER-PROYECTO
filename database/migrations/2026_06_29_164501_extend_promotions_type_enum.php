<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE promotions MODIFY COLUMN type ENUM(
                'buy_x_get_y','bundle_discount','min_qty_discount',
                'special_price','category_discount'
            ) NOT NULL DEFAULT 'buy_x_get_y'");
        } elseif (DB::getDriverName() === 'sqlite') {
            // SQLite CHECK constraint from $table->enum() rejects new values;
            // replace the column with TEXT (no CHECK) so any value is accepted.
            DB::statement("ALTER TABLE promotions ADD COLUMN new_type TEXT NOT NULL DEFAULT 'buy_x_get_y'");
            DB::statement("UPDATE promotions SET new_type = type");
            DB::statement("ALTER TABLE promotions DROP COLUMN type");
            DB::statement("ALTER TABLE promotions RENAME COLUMN new_type TO type");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE promotions MODIFY COLUMN type ENUM(
                'buy_x_get_y','bundle_discount','min_qty_discount'
            ) NOT NULL DEFAULT 'buy_x_get_y'");
        }
        // SQLite: no-op (can't restore CHECK constraint without table recreation)
    }
};
