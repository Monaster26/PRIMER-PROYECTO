<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'cash_amount',
        'card_amount',
        'transfer_amount',
        'total',
    ];

    protected $casts = [
        'cash_amount' => 'integer',
        'card_amount' => 'integer',
        'transfer_amount' => 'integer',
        'total' => 'integer',
    ];

    /**
     * Automatic calculation rule: totalSales = cash + card + transfer.
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($sale) {
            $sale->total = (int)$sale->cash_amount + (int)$sale->card_amount + (int)$sale->transfer_amount;
        });
    }

    /**
     * Get the user/cashier who registered this sale.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the individual items purchased in this sale.
     */
    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }
}
