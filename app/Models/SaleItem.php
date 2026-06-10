<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'product_id',
        'quantity',
        'price',
        'total_line',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'price' => 'integer',
        'total_line' => 'integer',
    ];

    /**
     * Automatic calculation rule: total_line = quantity * price.
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($item) {
            $item->total_line = (int)$item->quantity * (int)$item->price;
        });
    }

    /**
     * Get the parent sale.
     */
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    /**
     * Get the product details of this sale item.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
