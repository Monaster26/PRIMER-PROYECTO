<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
        'product_variant_id',
        'product_name',
        'product_sku',
        'quantity',
        'unit_price',
        'cost_price',
        'discount_pct',
        'discount_amount',
        'tax_amount',
        'line_total',
        'promotion_applied',
    ];

    protected $casts = [
        'quantity'          => 'integer',
        'unit_price'        => 'integer',
        'cost_price'        => 'integer',
        'discount_pct'      => 'integer',
        'discount_amount'   => 'integer',
        'tax_amount'        => 'integer',
        'line_total'        => 'integer',
        'promotion_applied' => 'array',
    ];

    // ─── Relaciones ────────────────────────────────────────

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    // ─── Accessors ─────────────────────────────────────────

    public function getLineTotalFormattedAttribute(): string
    {
        return '$' . number_format($this->line_total / 100, 0, ',', '.');
    }

    /** Ganancia bruta en centavos */
    public function getGrossProfitAttribute(): int
    {
        return $this->line_total - ($this->cost_price * $this->quantity);
    }

    /** Margen bruto de este ítem en % */
    public function getGrossMarginPctAttribute(): float
    {
        $cost = $this->cost_price * $this->quantity;
        if ($cost <= 0) return 0;
        return round(($this->gross_profit / $cost) * 100, 2);
    }
}
