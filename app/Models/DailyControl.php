<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class DailyControl extends Model
{
    protected $fillable = [
        'date',
        'cash_withdrawals',
        'card_sales_z',
        'mercado_pago_sales',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    public function zetaReport(): HasOne
    {
        return $this->hasOne(ZetaReport::class, 'date', 'date');
    }
}
