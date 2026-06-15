<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Devoluciones vinculadas a ítems de pedido.
     * El stock se reintegra automáticamente al aprobar.
     */
    public function up(): void
    {
        Schema::create('returns', function (Blueprint $table) {
            $table->id();
            $table->string('return_number')->unique()->comment('ej: DEV-2026-000001');
            $table->foreignId('order_id')->constrained()->onDelete('restrict');
            $table->foreignId('employee_id')->nullable()->constrained()->onDelete('set null');
            $table->json('items')->comment('[{"order_item_id":1,"qty":2,"reason":"defectuoso"}]');
            $table->unsignedBigInteger('refund_amount')->default(0)->comment('Monto a devolver en centavos');
            $table->enum('refund_method', ['cash', 'card', 'store_credit'])->default('cash');
            $table->enum('status', ['pending', 'approved', 'rejected', 'completed'])->default('pending');
            $table->text('notes')->nullable();
            $table->boolean('stock_restored')->default(false)->comment('Si el stock fue reintegrado');
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index(['order_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('returns');
    }
};
