<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CashSession extends Model
{
    protected $fillable = [
        'date',
        'user_id',
        'opened_at',
        'closed_at',
        'opening_balance',
        'real_balance',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'opened_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (CashSession $session) {
            if (!$session->date && $session->opened_at) {
                $session->date = $session->opened_at instanceof \Carbon\Carbon
                    ? $session->opened_at->toDateString()
                    : \Carbon\Carbon::parse($session->opened_at)->toDateString();
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function denominations(): HasMany
    {
        return $this->hasMany(CashDenomination::class);
    }

    public function zetaReport(): HasOne
    {
        return $this->hasOne(ZetaReport::class, 'date', 'date')
            ->whereColumn('cashier_id', 'user_id');
    }

    public function dailyControl(): HasOne
    {
        return $this->hasOne(DailyControl::class, 'date', 'date');
    }
}
