<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'cash_amount',
        'card_amount',
        'transfer_amount',
        'total',
        'coupon_id',
        'discount_total',
        'promo_discount',
        'coupon_discount',
        'net_total',
        'tax_total',
        'status',
        'cancellation_reason',
        'cancellation_note',
        'cancelled_at',
        'cancelled_by',
        'promotion_ids',
    ];

    protected $casts = [
        'promotion_ids' => 'array',
    ];

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SalePayment::class);
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }
}
