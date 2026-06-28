<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CashSession extends Model
{
    protected $fillable = [
        'date',
        'user_id',
        'opened_at',
        'closed_at',
        'opening_balance',
        'real_balance',
        // Denominaciones apertura
        'cant_20k_apertura',
        'cant_10k_apertura',
        'cant_5k_apertura',
        'cant_2k_apertura',
        'cant_1k_apertura',
        'cant_500_apertura',
        'cant_100_apertura',
        'cant_50_apertura',
        'cant_10_apertura',
        // Denominaciones cierre
        'cant_20k_cierre',
        'cant_10k_cierre',
        'cant_5k_cierre',
        'cant_2k_cierre',
        'cant_1k_cierre',
        'cant_500_cierre',
        'cant_100_cierre',
        'cant_50_cierre',
        'cant_10_cierre',
        // Totales calculados
        'total_efectivo_apertura',
        'total_efectivo_cierre',
        // Otros medios de pago
        'total_red_compra',
        'total_transferencia',
        'total_retiros',
        'total_ingresos',
        // Resumen
        'total_caja_esperado',
        'efectivo_esperado',
        'diferencia_efectivo',
        // Desglose JSON
        'apertura_desglose',
        'cierre_desglose',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'opened_at' => 'datetime',
            'closed_at' => 'datetime',
            'opening_balance' => 'integer',
            'real_balance' => 'integer',
            // Cast a integer para cantidades
            'cant_20k_apertura' => 'integer',
            'cant_10k_apertura' => 'integer',
            'cant_5k_apertura' => 'integer',
            'cant_2k_apertura' => 'integer',
            'cant_1k_apertura' => 'integer',
            'cant_500_apertura' => 'integer',
            'cant_100_apertura' => 'integer',
            'cant_50_apertura' => 'integer',
            'cant_10_apertura' => 'integer',
            'cant_20k_cierre' => 'integer',
            'cant_10k_cierre' => 'integer',
            'cant_5k_cierre' => 'integer',
            'cant_2k_cierre' => 'integer',
            'cant_1k_cierre' => 'integer',
            'cant_500_cierre' => 'integer',
            'cant_100_cierre' => 'integer',
            'cant_50_cierre' => 'integer',
            'cant_10_cierre' => 'integer',
            // Cast a integer para montos CLP (sin decimales)
            'total_efectivo_apertura' => 'integer',
            'total_efectivo_cierre' => 'integer',
            'total_red_compra' => 'integer',
            'total_transferencia' => 'integer',
            'total_retiros' => 'integer',
            'total_ingresos' => 'integer',
            'total_caja_esperado' => 'integer',
            'efectivo_esperado' => 'integer',
            'diferencia_efectivo' => 'integer',
            'apertura_desglose' => 'array',
            'cierre_desglose' => 'array',
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

        static::saving(function (CashSession $session) {
            // Calcular total efectivo apertura
            $session->total_efectivo_apertura =
                ($session->cant_20k_apertura * 20000) +
                ($session->cant_10k_apertura * 10000) +
                ($session->cant_5k_apertura * 5000) +
                ($session->cant_2k_apertura * 2000) +
                ($session->cant_1k_apertura * 1000) +
                ($session->cant_500_apertura * 500) +
                ($session->cant_100_apertura * 100) +
                ($session->cant_50_apertura * 50) +
                ($session->cant_10_apertura * 10);

            // Calcular total efectivo cierre (si tiene datos)
            if (!is_null($session->cant_20k_cierre)) {
                $session->total_efectivo_cierre =
                    ($session->cant_20k_cierre * 20000) +
                    ($session->cant_10k_cierre * 10000) +
                    ($session->cant_5k_cierre * 5000) +
                    ($session->cant_2k_cierre * 2000) +
                    ($session->cant_1k_cierre * 1000) +
                    ($session->cant_500_cierre * 500) +
                    ($session->cant_100_cierre * 100) +
                    ($session->cant_50_cierre * 50) +
                    ($session->cant_10_cierre * 10);
            }

            // Calcular total caja esperado y diferencia si hay cierre
            if ($session->total_efectivo_cierre !== null) {
                $session->total_caja_esperado =
                    $session->total_efectivo_cierre +
                    $session->total_red_compra +
                    $session->total_transferencia +
                    $session->total_ingresos -
                    $session->total_retiros;


            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cashMovements(): HasMany
    {
        return $this->hasMany(CashMovement::class, 'sesion_caja_id');
    }

}
