<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CreditInvoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'customer_name',
        'total_amount',
        'status',
    ];

    protected $casts = [
        'total_amount' => 'integer',
    ];

    /**
     * Recalculates and saves the total invoice amount based on active items.
     */
    public function recalculateTotal(): void
    {
        $this->total_amount = $this->items()->sum('line_total');
        $this->save();
    }

    /**
     * Get the customer account associated with this credit invoice.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the line items of this credit invoice.
     */
    public function items(): HasMany
    {
        return $this->hasMany(CreditInvoiceItem::class);
    }
}
