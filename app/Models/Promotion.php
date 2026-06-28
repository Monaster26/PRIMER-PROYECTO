<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    protected $fillable = [
        'name',
        'description',
        'type',
        'conditions',
        'rewards',
        'is_active',
        'priority',
        'is_exclusive',
        'starts_at',
        'expires_at',
    ];

    protected $casts = [
        'conditions'   => 'array',
        'rewards'      => 'array',
        'is_active'    => 'boolean',
        'is_exclusive' => 'boolean',
        'priority'     => 'integer',
        'starts_at'    => 'datetime',
        'expires_at'   => 'datetime',
    ];

    // ─── Scopes ────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                     ->where(fn($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
                     ->where(fn($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>=', now()))
                     ->orderByDesc('priority');
    }

    // ─── Motor de aplicación de promoción ──────────────────

    /**
     * Evalúa si esta promoción aplica a un carrito de compras.
     *
     * @param  array $cartItems [['product_id'=>1,'variant_id'=>null,'qty'=>3,'price'=>5000], ...]
     * @return array{applies: bool, rewards: array, discount: int}
     */
    public function evaluateCart(array $cartItems): array
    {
        return match ($this->type) {
            'buy_x_get_y'        => $this->evaluateBuyXGetY($cartItems),
            'min_qty_discount'   => $this->evaluateMinQtyDiscount($cartItems),
            'bundle_discount'    => $this->evaluateBundleDiscount($cartItems),
            default              => ['applies' => false, 'rewards' => [], 'discount' => 0],
        };
    }

    private function evaluateBuyXGetY(array $cartItems): array
    {
        $cond = $this->conditions; // {buy_product_id, buy_qty}
        $rwd  = $this->rewards;   // {get_product_id, get_qty, discount_pct}

        if (empty($cond['buy_product_id']) || empty($cond['buy_qty']) || empty($rwd['get_product_id'])) {
            return ['applies' => false, 'rewards' => [], 'discount' => 0];
        }

        $buyQty = collect($cartItems)
            ->where('product_id', $cond['buy_product_id'])
            ->sum('qty');

        if ($buyQty < $cond['buy_qty']) {
            return ['applies' => false, 'rewards' => [], 'discount' => 0];
        }

        $getProduct = Product::find($rwd['get_product_id']);
        if (!$getProduct) {
            return ['applies' => false, 'rewards' => [], 'discount' => 0];
        }

        $discountPct = $rwd['discount_pct'] ?? 100;
        $discount = (int) round($getProduct->price * $rwd['get_qty'] * ($discountPct / 100));

        return [
            'applies'  => true,
            'rewards'  => [
                'product_id' => $rwd['get_product_id'],
                'qty'        => $rwd['get_qty'],
                'discount_pct' => $discountPct,
            ],
            'discount' => $discount,
        ];
    }

    private function evaluateMinQtyDiscount(array $cartItems): array
    {
        $cond = $this->conditions; // {product_id, min_qty, discount_pct} or {product_id, min_qty, special_price}

        if (empty($cond['product_id']) || empty($cond['min_qty'])) {
            return ['applies' => false, 'rewards' => [], 'discount' => 0];
        }

        if (empty($cond['discount_pct']) && empty($cond['special_price'])) {
            return ['applies' => false, 'rewards' => [], 'discount' => 0];
        }

        $qty = collect($cartItems)
            ->where('product_id', $cond['product_id'])
            ->sum('qty');

        if ($qty < $cond['min_qty']) {
            return ['applies' => false, 'rewards' => [], 'discount' => 0];
        }

        $unitPrice = collect($cartItems)
            ->where('product_id', $cond['product_id'])
            ->first()['price']
            ?? 0;

        $groups = intdiv($qty, $cond['min_qty']);
        $groupNormalPrice = $cond['min_qty'] * (int) $unitPrice;

        if (!empty($cond['discount_pct'])) {
            $groupDiscount = (int) round($groupNormalPrice * ($cond['discount_pct'] / 100));
        } else {
            $groupSpecialCents = (int) $cond['special_price'] * 100;
            $groupDiscount = max(0, $groupNormalPrice - $groupSpecialCents);
        }

        $discount = $groups * $groupDiscount;

        return ['applies' => true, 'rewards' => [], 'discount' => $discount];
    }

    private function evaluateBundleDiscount(array $cartItems): array
    {
        $cond    = $this->conditions; // {product_ids: [1,2,3], discount_pct: 15}

        if (empty($cond['product_ids']) || empty($cond['discount_pct'])) {
            return ['applies' => false, 'rewards' => [], 'discount' => 0];
        }

        $cartIds  = collect($cartItems)->pluck('product_id')->toArray();
        $required = $cond['product_ids'];

        foreach ($required as $pid) {
            if (!in_array($pid, $cartIds)) {
                return ['applies' => false, 'rewards' => [], 'discount' => 0];
            }
        }

        $bundleTotal = collect($cartItems)
            ->whereIn('product_id', $required)
            ->sum(fn($item) => $item['qty'] * $item['price']);

        $discount = (int) round($bundleTotal * ($cond['discount_pct'] / 100));

        return ['applies' => true, 'rewards' => [], 'discount' => $discount];
    }
}
