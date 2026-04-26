<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'sku',
        'barcode',
        'brand',
        'unit',
        'cost_price',
        'price',
        'tax_rate',
        'stock',
        'min_stock',
        'max_discount',
        'image_path',
        'is_active',
        'has_variants',
        'sort_order',
        'sub_category',
    ];

    protected $casts = [
        'is_active'    => 'boolean',
        'has_variants' => 'boolean',
        'cost_price'   => 'integer',
        'price'        => 'integer',
        'stock'        => 'integer',
        'min_stock'    => 'integer',
        'max_discount' => 'integer',
        'tax_rate'     => 'integer',
        'sort_order'   => 'integer',
    ];

    // ─── Relaciones ────────────────────────────────────────

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
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

    public function scopeLowStock($query)
    {
        return $query->whereColumn('stock', '<=', 'min_stock')->where('has_variants', false);
    }

    public function scopeByBarcode($query, string $barcode)
    {
        return $query->where('barcode', $barcode);
    }

    public function scopeBySku($query, string $sku)
    {
        return $query->where('sku', $sku);
    }

    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
              ->orWhere('sku', 'like', "%{$term}%")
              ->orWhere('barcode', 'like', "%{$term}%")
              ->orWhere('brand', 'like', "%{$term}%");
        });
    }

    // ─── Accessors ─────────────────────────────────────────

    /** Precio formateado en pesos colombianos */
    public function getPriceFormattedAttribute(): string
    {
        return '$' . number_format($this->price / 100, 0, ',', '.');
    }

    /** Retorno de inversión en porcentaje */
    public function getMarginPctAttribute(): float
    {
        if ($this->cost_price <= 0) return 0;
        return round((($this->price - $this->cost_price) / $this->cost_price) * 100, 2);
    }

    /** Stock efectivo: suma de variantes o stock propio */
    public function getEffectiveStockAttribute(): int
    {
        if ($this->has_variants) {
            return $this->variants()->sum('stock');
        }
        return $this->stock;
    }

    public function getIsLowStockAttribute(): bool
    {
        return $this->effective_stock <= $this->min_stock;
    }
}
