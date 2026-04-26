<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductVariant extends Model
{
    protected $fillable = [
        'product_id',
        'name',
        'sku',
        'barcode',
        'price_override',
        'cost_price',
        'stock',
        'min_stock',
        'image_path',
        'attributes',
        'is_active',
    ];

    protected $casts = [
        'attributes'     => 'array',
        'is_active'      => 'boolean',
        'price_override' => 'integer',
        'cost_price'     => 'integer',
        'stock'          => 'integer',
        'min_stock'      => 'integer',
    ];

    // ─── Relaciones ────────────────────────────────────────

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    // ─── Scopes ────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByBarcode($query, string $barcode)
    {
        return $query->where('barcode', $barcode);
    }

    // ─── Accessors ─────────────────────────────────────────

    /** Precio efectivo: propio si existe, sino hereda del producto padre */
    public function getEffectivePriceAttribute(): int
    {
        return $this->price_override ?? $this->product->price;
    }

    public function getIsLowStockAttribute(): bool
    {
        return $this->stock <= $this->min_stock;
    }
}
