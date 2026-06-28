<?php

use App\Models\Expense;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->string('type', 50)->nullable()->after('category');
            $table->string('concept', 255)->nullable()->after('type');
            $table->string('beneficiary', 255)->nullable()->after('concept');
            $table->string('payment_method', 20)->nullable()->after('transfer_spent');
            $table->unsignedBigInteger('amount')->default(0)->after('payment_method');
            $table->text('observation')->nullable()->after('total_expense');
            $table->string('receipt_file', 255)->nullable()->after('observation');
            $table->foreignId('user_id')->nullable()->after('receipt_file')->constrained()->nullOnDelete();
        });

        Expense::query()->each(function (Expense $expense) {
            $expense->concept = $expense->category;

            $expense->type = match (true) {
                str_contains(mb_strtolower($expense->category), 'sueldo'),
                str_contains(mb_strtolower($expense->category), 'salario') => 'sueldo',
                str_contains(mb_strtolower($expense->category), 'arriendo'),
                str_contains(mb_strtolower($expense->category), 'renta') => 'arriendo',
                str_contains(mb_strtolower($expense->category), 'luz'),
                str_contains(mb_strtolower($expense->category), 'electricidad'),
                str_contains(mb_strtolower($expense->category), 'agua'),
                str_contains(mb_strtolower($expense->category), 'gas'),
                str_contains(mb_strtolower($expense->category), 'servicio') => 'servicio',
                str_contains(mb_strtolower($expense->category), 'gasto comun'),
                str_contains(mb_strtolower($expense->category), 'gasto común') => 'gasto_comun',
                str_contains(mb_strtolower($expense->category), 'mantencion'),
                str_contains(mb_strtolower($expense->category), 'mantención'),
                str_contains(mb_strtolower($expense->category), 'reparacion'),
                str_contains(mb_strtolower($expense->category), 'reparación') => 'mantencion',
                str_contains(mb_strtolower($expense->category), 'impuesto'),
                str_contains(mb_strtolower($expense->category), 'iva'),
                str_contains(mb_strtolower($expense->category), 'tributo') => 'impuesto',
                $expense->supplier_id || $expense->provider_id => 'proveedor',
                default => 'otros',
            };

            $cash = (int) ($expense->cash_spent ?? 0);
            $transfer = (int) ($expense->transfer_spent ?? 0);

            if ($cash > 0 && $transfer === 0) {
                $expense->payment_method = 'efectivo';
            } elseif ($transfer > 0 && $cash === 0) {
                $expense->payment_method = 'transferencia';
            } else {
                $expense->payment_method = $cash >= $transfer ? 'efectivo' : 'transferencia';
            }

            $expense->amount = $cash + $transfer;

            $expense->beneficiary = $expense->supplier?->company_name
                ?? $expense->provider?->name
                ?? null;

            $expense->saveQuietly();
        });
    }

    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
            $table->dropColumn([
                'type', 'concept', 'beneficiary',
                'payment_method', 'amount', 'observation', 'receipt_file',
            ]);
        });
    }
};
