<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'supplier_id',
        'provider_id',
        'type',
        'concept',
        'beneficiary',
        'payment_method',
        'amount',
        'observation',
        'receipt_file',
        'user_id',
        'category',
        'cash_spent',
        'transfer_spent',
        'total_expense',
    ];

    protected $casts = [
        'date' => 'date',
        'cash_spent' => 'integer',
        'transfer_spent' => 'integer',
        'total_expense' => 'integer',
        'amount' => 'integer',
    ];

    public const TYPES = [
        'proveedor', 'servicio', 'sueldo', 'arriendo',
        'gasto_comun', 'mantencion', 'impuesto', 'otros',
    ];

    public const TYPE_LABELS = [
        'proveedor' => 'Proveedor',
        'servicio' => 'Servicio',
        'sueldo' => 'Sueldo',
        'arriendo' => 'Arriendo',
        'gasto_comun' => 'Gasto Común',
        'mantencion' => 'Mantención',
        'impuesto' => 'Impuesto',
        'otros' => 'Otros',
    ];

    protected static function booted(): void
    {
        static::saving(function (Expense $expense) {
            $expense->total_expense = ($expense->cash_spent ?? 0) + ($expense->transfer_spent ?? 0);
        });
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeOfType(Builder $query, ?string $type): Builder
    {
        return $type ? $query->where('type', $type) : $query;
    }

    public function scopeByDateRange(Builder $query, ?string $from, ?string $to): Builder
    {
        if ($from) $query->where('date', '>=', $from);
        if ($to) $query->where('date', '<=', $to);
        return $query;
    }

    public function scopeByPaymentMethod(Builder $query, ?string $method): Builder
    {
        return $method ? $query->where('payment_method', $method) : $query;
    }

    public function scopeSearchBeneficiary(Builder $query, ?string $search): Builder
    {
        return $search
            ? $query->where('beneficiary', 'like', "%{$search}%")
            : $query;
    }

    public function scopeByUser(Builder $query, ?int $userId): Builder
    {
        return $userId ? $query->where('user_id', $userId) : $query;
    }
}
