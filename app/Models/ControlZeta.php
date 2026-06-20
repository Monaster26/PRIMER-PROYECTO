<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ControlZeta extends Model
{
    public $timestamps = false;

    protected $table = 'control_zetas';

    protected $fillable = [
        'cash_session_id',
        'fecha_apertura',
        'cajero',
        'esperado_caja',
        'efectivo_neto',
        'red_compra_neto',
        'transferencia',
        'red_compra_total',
        'porcentaje_banco',
        'sobrante',
        'faltante',
        'observaciones',
    ];

    protected $casts = [
        'fecha_apertura' => 'datetime',
    ];

    public function cashSession(): BelongsTo
    {
        return $this->belongsTo(CashSession::class);
    }
}
