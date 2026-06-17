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
            $table->foreignId('user_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->string('type')->after('user_id')->default('pos');
            $table->unsignedBigInteger('cash_amount')->default(0)->after('type');
            $table->unsignedBigInteger('card_amount')->default(0)->after('cash_amount');
            $table->unsignedBigInteger('transfer_amount')->default(0)->after('card_amount');
        });

        DB::statement('UPDATE sales SET user_id = cashier_id, type = \'pos\'');
        DB::statement("UPDATE sales SET cash_amount = total WHERE payment_method = 'cash'");
        DB::statement("UPDATE sales SET card_amount = total WHERE payment_method = 'card'");
        DB::statement("UPDATE sales SET transfer_amount = total WHERE payment_method = 'transfer'");
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
            $table->dropColumn(['type', 'cash_amount', 'card_amount', 'transfer_amount']);
        });
    }
};
