<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->string('status')->default('completed')->after('coupon_discount');
            $table->string('cancellation_reason')->nullable()->after('status');
            $table->text('cancellation_note')->nullable()->after('cancellation_reason');
            $table->timestamp('cancelled_at')->nullable()->after('cancellation_note');
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete()->after('cancelled_at');
            $table->json('promotion_ids')->nullable()->after('cancelled_by');
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropConstrainedForeignId('cancelled_by');
            $table->dropColumn([
                'status',
                'cancellation_reason',
                'cancellation_note',
                'cancelled_at',
                'promotion_ids',
            ]);
        });
    }
};
