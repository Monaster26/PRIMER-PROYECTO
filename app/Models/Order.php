<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'order_number',
        'customer_id',
        'employee_id',
        'coupon_id',
        'subtotal',
        'discount_total',
        'tax_total',
        'total',
        'payment_method',
        'cash_tendered',
        'change_due',
        'status',
        'sale_channel',
        'promotion_ids',
        'notes',
        'completed_at',
    ];

    protected $casts = [
        'subtotal'       => 'integer',
        'discount_total' => 'integer',
        'tax_total'      => 'integer',
        'total'          => 'integer',
        'cash_tendered'  => 'integer',
        'change_due'     => 'integer',
        'promotion_ids'  => 'array',
        'completed_at'   => 'datetime',
    ];

    // ─── Eventos del modelo ─────────────────────────────────

    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            if (empty($order->order_number)) {
                $order->order_number = static::generateOrderNumber($order->sale_channel);
            }
        });
    }

    public static function generateOrderNumber(string $channel = 'pos'): string
    {
        $prefix = match ($channel) {
            'web'   => 'WEB',
            'phone' => 'TEL',
            default => 'MM',
        };
        $count = static::withTrashed()->count() + 1;
        return sprintf('%s-%s-%06d', $prefix, now()->format('Y'), $count);
    }

    // ─── Relaciones ────────────────────────────────────────

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function couponUsage(): HasMany
    {
        return $this->hasMany(CouponUsage::class);
    }

    public function returns(): HasMany
    {
        return $this->hasMany(\App\Models\OrderReturn::class);
    }

    // ─── Scopes ────────────────────────────────────────────

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeByChannel($query, string $channel)
    {
        return $query->where('sale_channel', $channel);
    }

    public function scopePos($query)
    {
        return $query->where('sale_channel', 'pos');
    }

    public function scopeWeb($query)
    {
        return $query->where('sale_channel', 'web');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('created_at', now()->month)
                     ->whereYear('created_at', now()->year);
    }

    // ─── Accessors ─────────────────────────────────────────

    public function getTotalFormattedAttribute(): string
    {
        return '$' . number_format($this->total / 100, 0, ',', '.');
    }

    public function getIsCompletedAttribute(): bool
    {
        return $this->status === 'completed';
    }
}
