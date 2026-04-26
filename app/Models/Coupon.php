<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coupon extends Model
{
    protected $fillable = [
        'code',
        'description',
        'type',
        'value',
        'min_order_amount',
        'max_discount_amount',
        'max_uses',
        'max_uses_per_customer',
        'used_count',
        'is_active',
        'starts_at',
        'expires_at',
        'applicable_category_ids',
    ];

    protected $casts = [
        'value'                  => 'integer',
        'min_order_amount'       => 'integer',
        'max_discount_amount'    => 'integer',
        'max_uses'               => 'integer',
        'max_uses_per_customer'  => 'integer',
        'used_count'             => 'integer',
        'is_active'              => 'boolean',
        'starts_at'              => 'datetime',
        'expires_at'             => 'datetime',
        'applicable_category_ids' => 'array',
    ];

    // ─── Relaciones ────────────────────────────────────────

    public function usages(): HasMany
    {
        return $this->hasMany(CouponUsage::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    // ─── Scopes ────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                     ->where(fn($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
                     ->where(fn($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>=', now()));
    }

    // ─── Lógica de negocio ─────────────────────────────────

    /**
     * Valida si el cupón es aplicable a un pedido dado.
     *
     * @param  int          $orderTotal     Total del pedido en centavos
     * @param  int|null     $customerId     ID del cliente
     * @return array{valid: bool, message: string, discount: int}
     */
    public function validate(int $orderTotal, ?int $customerId = null): array
    {
        if (!$this->is_active) {
            return ['valid' => false, 'message' => 'El cupón no está activo.', 'discount' => 0];
        }

        if ($this->starts_at && $this->starts_at->isFuture()) {
            return ['valid' => false, 'message' => 'El cupón aún no está vigente.', 'discount' => 0];
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return ['valid' => false, 'message' => 'El cupón ha expirado.', 'discount' => 0];
        }

        if ($this->max_uses !== null && $this->used_count >= $this->max_uses) {
            return ['valid' => false, 'message' => 'El cupón ha alcanzado su límite de usos.', 'discount' => 0];
        }

        if ($orderTotal < $this->min_order_amount) {
            $min = '$' . number_format($this->min_order_amount / 100, 0, ',', '.');
            return ['valid' => false, 'message' => "Compra mínima requerida: {$min}.", 'discount' => 0];
        }

        if ($customerId && $this->max_uses_per_customer > 0) {
            $customerUses = $this->usages()->where('customer_id', $customerId)->count();
            if ($customerUses >= $this->max_uses_per_customer) {
                return ['valid' => false, 'message' => 'Ya has usado este cupón el máximo permitido.', 'discount' => 0];
            }
        }

        $discount = $this->calculateDiscount($orderTotal);

        return ['valid' => true, 'message' => 'Cupón válido.', 'discount' => $discount];
    }

    /**
     * Calcula el descuento en centavos.
     */
    public function calculateDiscount(int $orderTotal): int
    {
        $discount = match ($this->type) {
            'percentage' => (int) round($orderTotal * ($this->value / 100)),
            'fixed'      => $this->value,
            default      => 0,
        };

        // Aplicar límite de descuento máximo si existe
        if ($this->max_discount_amount !== null) {
            $discount = min($discount, $this->max_discount_amount);
        }

        return min($discount, $orderTotal);
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }
}
