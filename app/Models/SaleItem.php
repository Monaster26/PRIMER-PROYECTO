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
        'net_price',
        'tax_amount',
        'tax_rate',
        'total_line',
    ];

    protected $casts = [
        'quantity'   => 'integer',
        'price'      => 'integer',
        'net_price'  => 'integer',
        'tax_amount' => 'integer',
        'tax_rate'   => 'integer',
        'total_line' => 'integer',
    ];

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
