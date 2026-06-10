<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductProviderPrice extends Model
{
    use HasFactory;

    protected $table = 'product_provider_prices';

    protected $fillable = [
        'product_id',
        'provider_id',
        'price',
    ];

    protected $casts = [
        'price' => 'integer',
    ];

    /**
     * Get the product associated with this provider price.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the provider offering this price.
     */
    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }
}
