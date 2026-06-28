<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Loss extends Model
{
    protected $fillable = [
        'date',
        'product_id',
        'quantity',
        'cost_at_loss',
        'total_loss_value',
        'reason',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Loss $loss) {
            if (empty($loss->cost_at_loss)) {
                $product = $loss->product()->first();
                if ($product) {
                    $loss->cost_at_loss = $product->cost_price / 100;
                }
            }
            $loss->total_loss_value = $loss->quantity * $loss->cost_at_loss;
        });
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
