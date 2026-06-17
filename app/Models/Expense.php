<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'supplier_id',
        'category',
        'cash_spent',
        'transfer_spent',
        'total_expense',
        'concept',
    ];

    protected $casts = [
        'date' => 'date',
        'cash_spent' => 'integer',
        'transfer_spent' => 'integer',
        'total_expense' => 'integer',
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
}
