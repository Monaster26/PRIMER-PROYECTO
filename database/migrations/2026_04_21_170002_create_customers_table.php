<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Clientes del mini-market.
     * Email y preferencias se almacenan encriptados.
     */
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('email')->nullable()->comment('Almacenado encriptado con AES-256-CBC');
            $table->string('email_hash')->nullable()->unique()->comment('Hash para búsquedas sin desencriptar');
            $table->string('phone')->nullable()->index();
            $table->string('document_number')->nullable()->index()->comment('Cédula / NIT');
            $table->date('birth_date')->nullable();
            $table->unsignedInteger('loyalty_points')->default(0);
            $table->boolean('is_vip')->default(false);
            $table->text('preferences')->nullable()->comment('JSON encriptado con preferencias del cliente');
            $table->string('acquisition_channel')->nullable()->comment('pos, web, referral');
            $table->unsignedBigInteger('total_spent')->default(0)->comment('Total gastado en centavos');
            $table->unsignedInteger('order_count')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['name']);
            $table->index(['is_vip', 'loyalty_points']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
