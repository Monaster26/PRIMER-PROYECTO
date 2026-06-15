<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Crypt;

class Customer extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'email_hash',
        'phone',
        'document_number',
        'birth_date',
        'loyalty_points',
        'is_vip',
        'preferences',
        'acquisition_channel',
        'total_spent',
        'order_count',
    ];

    protected $casts = [
        'is_vip'         => 'boolean',
        'birth_date'     => 'date',
        'loyalty_points' => 'integer',
        'total_spent'    => 'integer',
        'order_count'    => 'integer',
    ];

    protected $hidden = ['email', 'preferences'];

    // ─── Encriptación AES-256 de datos sensibles ────────────

    public function setEmailAttribute(?string $value): void
    {
        if ($value) {
            $this->attributes['email'] = Crypt::encryptString($value);
            $this->attributes['email_hash'] = hash('sha256', strtolower(trim($value)));
        }
    }

    public function getEmailAttribute(?string $value): ?string
    {
        if ($value) {
            try {
                return Crypt::decryptString($value);
            } catch (\Exception) {
                return null;
            }
        }
        return null;
    }

    public function setPreferencesAttribute(?array $value): void
    {
        $this->attributes['preferences'] = $value
            ? Crypt::encryptString(json_encode($value))
            : null;
    }

    public function getPreferencesAttribute(?string $value): ?array
    {
        if ($value) {
            try {
                return json_decode(Crypt::decryptString($value), true);
            } catch (\Exception) {
                return null;
            }
        }
        return null;
    }

    // ─── Relaciones ────────────────────────────────────────

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function couponUsages(): HasMany
    {
        return $this->hasMany(CouponUsage::class);
    }

    // ─── Scopes ────────────────────────────────────────────

    public function scopeVip($query)
    {
        return $query->where('is_vip', true);
    }

    public function scopeByPhone($query, string $phone)
    {
        return $query->where('phone', $phone);
    }

    public function scopeByEmail($query, string $email)
    {
        return $query->where('email_hash', hash('sha256', strtolower(trim($email))));
    }

    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
              ->orWhere('phone', 'like', "%{$term}%")
              ->orWhere('document_number', 'like', "%{$term}%");
        });
    }

    // ─── Helpers ───────────────────────────────────────────

    /**
     * Añade puntos de fidelidad (1 punto por cada $1.000 COP gastados).
     */
    public function addLoyaltyPoints(int $amountInCents): void
    {
        $points = (int) floor($amountInCents / 100000); // 1 punto por $1.000
        $this->increment('loyalty_points', $points);
        $this->increment('total_spent', $amountInCents);
        $this->increment('order_count');

        // Promover a VIP si supera 1.000 puntos
        if ($this->loyalty_points >= 1000 && !$this->is_vip) {
            $this->update(['is_vip' => true]);
        }
    }

    public function getTotalSpentFormattedAttribute(): string
    {
        return '$' . number_format($this->total_spent / 100, 0, ',', '.');
    }
}
