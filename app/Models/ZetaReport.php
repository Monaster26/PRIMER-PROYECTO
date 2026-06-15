<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ZetaReport extends Model
{
    protected $fillable = [
        'date',
        'cashier_id',
        'total_z',
        'net_cash',
        'transfers',
        'pos_card_total',
        'surplus',
        'deficit',
        'observations',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (ZetaReport $zeta) {
            $declared = (float) $zeta->net_cash + (float) $zeta->transfers + (float) $zeta->pos_card_total;
            $expected = (float) $zeta->total_z;
            $balance = $declared - $expected;

            $zeta->surplus = $balance > 0 ? $balance : 0;
            $zeta->deficit = $balance < 0 ? abs($balance) : 0;
        });

        static::saved(function (ZetaReport $zeta) {
            DailyControl::updateOrCreate(
                ['date' => $zeta->date],
                [
                    'cash_withdrawals' => (float) $zeta->net_cash,
                    'card_sales_z' => (float) $zeta->pos_card_total,
                    'mercado_pago_sales' => (float) $zeta->transfers,
                ],
            );
        });
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }
}
