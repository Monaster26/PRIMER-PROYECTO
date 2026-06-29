<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PendingInvoiceItem extends Model
{
    protected $fillable = [
        'pending_invoice_id',
        'product_id',
        'product_name',
        'category_name',
        'subcategory_name',
        'unit_cost',
        'previous_cost',
        'quantity_ordered',
        'quantity_received',
        'is_new_product',
    ];

    protected function casts(): array
    {
        return [
            'unit_cost'        => 'integer',
            'previous_cost'    => 'integer',
            'quantity_ordered' => 'integer',
            'quantity_received'=> 'integer',
            'is_new_product'   => 'boolean',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(PendingInvoice::class, 'pending_invoice_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getPriceChangeDirectionAttribute(): string
    {
        if ($this->is_new_product) return 'new';
        if ($this->previous_cost === 0) return 'new';
        if ($this->unit_cost > $this->previous_cost) return 'up';
        if ($this->unit_cost < $this->previous_cost) return 'down';
        return 'same';
    }

    public function getPriceChangeAmountAttribute(): int
    {
        return $this->unit_cost - $this->previous_cost;
    }

    public function getPriceChangePercentAttribute(): float
    {
        if ($this->previous_cost <= 0) return 0;
        return round(($this->price_change_amount / $this->previous_cost) * 100, 1);
    }

    public function getLineTotalAttribute(): int
    {
        return $this->unit_cost * $this->quantity_ordered;
    }
}
