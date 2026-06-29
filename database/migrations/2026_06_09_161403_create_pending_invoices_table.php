<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pending_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained()->onDelete('cascade');
            $table->string('invoice_number')->nullable();
            $table->date('issue_date');
            $table->date('due_date');
            $table->date('delivery_date')->nullable();
            $table->unsignedBigInteger('total_amount'); // centavos
            $table->text('notes')->nullable();
            $table->string('status')->default('pending'); // pending, received, paid
            $table->timestamp('received_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pending_invoices');
    }
};
