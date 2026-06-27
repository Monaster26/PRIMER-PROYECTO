<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductBatch extends Model
{
    protected $fillable = [
        'product_id',
        'quantity',
        'cost_price',
        'expiration_date',
        'received_at',
        'notes',
    ];

    protected $casts = [
        'quantity'         => 'integer',
        'cost_price'       => 'integer',
        'expiration_date'  => 'date:Y-m-d',
        'received_at'      => 'date:Y-m-d',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function scopeActive($query)
    {
        return $query->where('quantity', '>', 0);
    }

    public function scopeExpiringSoon($query)
    {
        return $query->whereNotNull('expiration_date')
            ->whereBetween('expiration_date', [Carbon::today(), Carbon::today()->addDays(30)]);
    }

    public function scopeExpired($query)
    {
        return $query->whereNotNull('expiration_date')
            ->where('expiration_date', '<', Carbon::today());
    }

    public function getStatusAttribute(): string
    {
        if (!$this->expiration_date) return 'ok';
        if ($this->expiration_date->lt(Carbon::today())) return 'expired';
        if ($this->expiration_date->lte(Carbon::today()->addDays(30))) return 'warning';
        return 'ok';
    }

    protected static function booted()
    {
        static::created(function ($batch) {
            $closest = static::where('product_id', $batch->product_id)
                ->where('quantity', '>', 0)
                ->whereNotNull('expiration_date')
                ->orderBy('expiration_date', 'asc')
                ->value('expiration_date');
            $batch->product()->update(['expiration_date' => $closest]);
        });
    }
}
