<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderReturn extends Model
{
    protected $table = 'returns';

    protected $fillable = [
        'return_number',
        'order_id',
        'employee_id',
        'items',
        'refund_amount',
        'refund_method',
        'status',
        'notes',
        'stock_restored',
        'processed_at',
    ];

    protected $casts = [
        'items'          => 'array',
        'refund_amount'  => 'integer',
        'stock_restored' => 'boolean',
        'processed_at'   => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (OrderReturn $return) {
            if (empty($return->return_number)) {
                $count = static::count() + 1;
                $return->return_number = sprintf('DEV-%s-%06d', now()->format('Y'), $count);
            }
        });
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function getRefundAmountFormattedAttribute(): string
    {
        return '$' . number_format($this->refund_amount / 100, 0, ',', '.');
    }
}
