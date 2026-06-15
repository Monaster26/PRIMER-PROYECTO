<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class Employee extends Model
{
    protected $fillable = [
        'name',
        'email',
        'pin',
        'role',
        'phone',
        'is_active',
        'last_login_at',
        'current_session_token',
    ];

    protected $casts = [
        'is_active'      => 'boolean',
        'last_login_at'  => 'datetime',
    ];

    protected $hidden = ['pin', 'current_session_token'];

    // ─── Hashing del PIN con Argon2id ───────────────────────

    public function setPinAttribute(string $value): void
    {
        // Solo hashear si no está ya hasheado (evitar doble hash)
        $this->attributes['pin'] = strlen($value) < 20
            ? Hash::make($value, ['driver' => 'argon2id'])
            : $value;
    }

    // ─── Relaciones ────────────────────────────────────────

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function returns(): HasMany
    {
        return $this->hasMany(OrderReturn::class);
    }

    // ─── Autenticación POS ─────────────────────────────────

    /**
     * Verifica el PIN y genera un token de sesión POS.
     *
     * @return string|null Token de sesión o null si PIN incorrecto
     */
    public function authenticateWithPin(string $pin): ?string
    {
        if (!$this->is_active) return null;

        if (!Hash::check($pin, $this->pin)) return null;

        $token = Str::random(64);
        $this->update([
            'current_session_token' => hash('sha256', $token),
            'last_login_at'         => now(),
        ]);

        return $token;
    }

    /**
     * Verifica si un token de sesión es válido.
     */
    public function validateSessionToken(string $token): bool
    {
        return $this->current_session_token === hash('sha256', $token);
    }

    public function closeSession(): void
    {
        $this->update(['current_session_token' => null]);
    }

    // ─── Scopes ────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByRole($query, string $role)
    {
        return $query->where('role', $role);
    }

    // ─── Helpers ───────────────────────────────────────────

    public function isSupervisorOrAdmin(): bool
    {
        return in_array($this->role, ['supervisor', 'admin']);
    }

    public function getRoleNameAttribute(): string
    {
        return match ($this->role) {
            'cashier'    => 'Cajero',
            'supervisor' => 'Supervisor',
            'admin'      => 'Administrador',
            default      => $this->role,
        };
    }
}
