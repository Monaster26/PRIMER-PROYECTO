<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            if (!Schema::hasColumn('sales', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('id')->constrained()->nullOnDelete();
            }
            if (!Schema::hasColumn('sales', 'type')) {
                $table->string('type')->after('user_id')->default('pos');
            }
            if (!Schema::hasColumn('sales', 'cash_amount')) {
                $table->unsignedBigInteger('cash_amount')->default(0)->after('type');
            }
            if (!Schema::hasColumn('sales', 'card_amount')) {
                $table->unsignedBigInteger('card_amount')->default(0)->after('cash_amount');
            }
            if (!Schema::hasColumn('sales', 'transfer_amount')) {
                $table->unsignedBigInteger('transfer_amount')->default(0)->after('card_amount');
            }
        });

        if (Schema::hasColumn('sales', 'cashier_id')) {
            DB::statement('UPDATE sales SET user_id = cashier_id, type = \'pos\'');
        }
        if (Schema::hasColumn('sales', 'payment_method')) {
            DB::statement("UPDATE sales SET cash_amount = total WHERE payment_method = 'cash'");
            DB::statement("UPDATE sales SET card_amount = total WHERE payment_method = 'card'");
            DB::statement("UPDATE sales SET transfer_amount = total WHERE payment_method = 'transfer'");
        }
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
            $table->dropColumn(['type', 'cash_amount', 'card_amount', 'transfer_amount']);
        });
    }
};
