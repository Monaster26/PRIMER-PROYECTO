<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider_id',
        'category',
        'cash_spent',
        'transfer_spent',
        'total_expense',
        'date',
    ];

    protected $casts = [
        'cash_spent' => 'integer',
        'transfer_spent' => 'integer',
        'total_expense' => 'integer',
        'date' => 'date',
    ];

    /**
     * Automatic calculation rule: totalExpense = cash_spent + transfer_spent.
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($expense) {
            $expense->total_expense = (int)$expense->cash_spent + (int)$expense->transfer_spent;
        });
    }

    /**
     * Get the supplier (provider) associated with this expense.
     */
    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }
}
