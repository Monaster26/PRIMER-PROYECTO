<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Provider extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'contact_info',
    ];

    /**
     * Get the individual product price offers from this provider.
     */
    public function productPrices(): HasMany
    {
        return $this->hasMany(ProductProviderPrice::class);
    }

    /**
     * Get the products supplied by this provider.
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_provider_prices')
                    ->withPivot('price')
                    ->withTimestamps();
    }

    /**
     * Get the expenses associated with this provider.
     */
    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }
}
