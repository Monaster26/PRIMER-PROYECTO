<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreditInvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'credit_invoice_id',
        'product_id',
        'quantity',
        'unit_price',
        'line_total',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'integer',
        'line_total' => 'integer',
    ];

    /**
     * Automatic calculation and parent aggregate update logic.
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($item) {
            $item->line_total = (int)$item->quantity * (int)$item->unit_price;
        });

        static::saved(function ($item) {
            if ($item->creditInvoice) {
                $item->creditInvoice->recalculateTotal();
            }
        });

        static::deleted(function ($item) {
            if ($item->creditInvoice) {
                $item->creditInvoice->recalculateTotal();
            }
        });
    }

    /**
     * Get the parent credit invoice.
     */
    public function creditInvoice(): BelongsTo
    {
        return $this->belongsTo(CreditInvoice::class);
    }

    /**
     * Get the product details of this credit invoice line item.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
