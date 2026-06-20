<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Observacion extends Model
{
    public $timestamps = false;

    protected $table = 'observaciones';

    protected $fillable = [
        'user_id',
        'tipo_accion',
        'producto_afectado',
        'detalle',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
