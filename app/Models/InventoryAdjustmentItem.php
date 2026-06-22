<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryAdjustmentItem extends Model
{
    protected $fillable = [
        'inventory_adjustment_id',
        'product_id',
        'system_stock',
        'counted_stock',
        'difference',
        'cost_price',
    ];

    protected function casts(): array
    {
        return [
            'system_stock' => 'integer',
            'counted_stock' => 'integer',
            'difference' => 'integer',
            'cost_price' => 'integer',
        ];
    }

    public function inventoryAdjustment(): BelongsTo
    {
        return $this->belongsTo(InventoryAdjustment::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
