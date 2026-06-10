<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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

    protected $appends = [
        'current_stock',
        'alert_stock',
        'min_price',
        'max_price',
        'savings_gap',
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

    public function providerPrices(): HasMany
    {
        return $this->hasMany(ProductProviderPrice::class);
    }

    public function providers(): BelongsToMany
    {
        return $this->belongsToMany(Provider::class, 'product_provider_prices')
                    ->withPivot('price')
                    ->withTimestamps();
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function creditInvoiceItems(): HasMany
    {
        return $this->hasMany(CreditInvoiceItem::class);
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

    /** Accessors/mutators mapping current_stock to stock */
    public function getCurrentStockAttribute(): int
    {
        return $this->stock;
    }

    public function setCurrentStockAttribute($value): void
    {
        $this->attributes['stock'] = $value;
    }

    /** Accessors/mutators mapping alert_stock to min_stock */
    public function getAlertStockAttribute(): int
    {
        return $this->min_stock ?? 0;
    }

    public function setAlertStockAttribute($value): void
    {
        $this->attributes['min_stock'] = $value;
    }

    /** Supplier cost matrix: minPrice = MIN(supplier_costs) */
    public function getMinPriceAttribute(): int
    {
        return $this->providerPrices()->min('price') ?? $this->cost_price;
    }

    /** Supplier cost matrix: maxPrice = MAX(supplier_costs) */
    public function getMaxPriceAttribute(): int
    {
        return $this->providerPrices()->max('price') ?? $this->cost_price;
    }

    /** Supplier cost matrix: suggestedSupplier = Provider associated with minPrice */
    public function getSuggestedSupplierAttribute(): ?Provider
    {
        $minPriceEntry = $this->providerPrices()->orderBy('price', 'asc')->first();
        return $minPriceEntry ? $minPriceEntry->provider : null;
    }

    /** Supplier cost matrix: savingsGap = maxPrice - minPrice */
    public function getSavingsGapAttribute(): int
    {
        return max(0, $this->max_price - $this->min_price);
    }
}
