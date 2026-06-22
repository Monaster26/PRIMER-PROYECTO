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
        'monto_diferencia',
        'read_at',
        'estado',
        'nota_admin',
        'revisado_at',
        'metadata',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'read_at' => 'datetime',
        'revisado_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    public function scopePendiente($query)
    {
        return $query->where('estado', 'pendiente');
    }

    public function scopeRevisado($query)
    {
        return $query->where('estado', 'revisado');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
