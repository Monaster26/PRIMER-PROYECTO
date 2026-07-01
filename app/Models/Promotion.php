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
        'max_uses',
        'starts_at',
        'expires_at',
    ];

    protected $casts = [
        'conditions'   => 'array',
        'rewards'      => 'array',
        'is_active'    => 'boolean',
        'is_exclusive' => 'boolean',
        'priority'     => 'integer',
        'max_uses'     => 'integer',
        'used_count'   => 'integer',
        'starts_at'    => 'datetime',
        'expires_at'   => 'datetime',
    ];

    // ─── Scopes ────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                     ->where(fn($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
                     ->where(fn($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>=', now()))
                     ->where(fn($q) => $q->whereNull('max_uses')->orWhereRaw('used_count < max_uses'))
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
            'special_price'      => $this->evaluateSpecialPrice($cartItems),
            'category_discount'  => $this->evaluateCategoryDiscount($cartItems),
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

        $sets = intdiv($buyQty, $cond['buy_qty']);

        $getProduct = Product::find($rwd['get_product_id']);
        if (!$getProduct) {
            return ['applies' => false, 'rewards' => [], 'discount' => 0];
        }

        if (!empty($cond['special_price_total'])) {
            $buyProduct = Product::find($cond['buy_product_id']);
            if (!$buyProduct) {
                return ['applies' => false, 'rewards' => [], 'discount' => 0];
            }
            $normalTotal = ($cond['buy_qty'] * $sets * $buyProduct->price) + ($getProduct->price * $rwd['get_qty'] * $sets);
            $fixedCents = (int) $cond['special_price_total'] * $sets;
            $discount = max(0, $normalTotal - $fixedCents);
            return [
                'applies'  => true,
                'rewards'  => [
                    'product_id' => $rwd['get_product_id'],
                    'qty'        => $rwd['get_qty'] * $sets,
                    'discount_pct' => 100,
                ],
                'discount' => $discount,
            ];
        }

        $discountPct = $rwd['discount_pct'] ?? 100;
        $discount = (int) round($getProduct->price * $rwd['get_qty'] * ($discountPct / 100) * $sets);

        return [
            'applies'  => true,
            'rewards'  => [
                'product_id' => $rwd['get_product_id'],
                'qty'        => $rwd['get_qty'] * $sets,
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
            $groupSpecialCents = (int) $cond['special_price'];
            $groupDiscount = max(0, $groupNormalPrice - $groupSpecialCents);
        }

        $discount = $groups * $groupDiscount;

        return ['applies' => true, 'rewards' => [], 'discount' => $discount];
    }

    private function evaluateBundleDiscount(array $cartItems): array
    {
        $cond    = $this->conditions; // {product_ids: [1,2,3], discount_pct: 15}

        if (empty($cond['product_ids'])) {
            return ['applies' => false, 'rewards' => [], 'discount' => 0];
        }

        if (empty($cond['discount_pct']) && empty($cond['special_price_total'])) {
            return ['applies' => false, 'rewards' => [], 'discount' => 0];
        }

        $cart = collect($cartItems);
        $required = $cond['product_ids'];

        $sets = null;
        foreach ($required as $pid) {
            $qty = $cart->where('product_id', $pid)->sum('qty');
            if ($qty < 1) {
                return ['applies' => false, 'rewards' => [], 'discount' => 0];
            }
            $sets = $sets === null ? $qty : min($sets, $qty);
        }

        // ponytail: one-set normal price uses first-found variant price
        $unitNormal = 0;
        foreach ($required as $pid) {
            $unitNormal += $cart->firstWhere('product_id', $pid)['price'];
        }

        $discountableNormal = $unitNormal * $sets;

        if (!empty($cond['special_price_total'])) {
            $fixedCents = (int) $cond['special_price_total'];
            $discount = max(0, $discountableNormal - $fixedCents * $sets);
        } else {
            $discount = (int) round($discountableNormal * ($cond['discount_pct'] / 100));
        }

        return ['applies' => true, 'rewards' => [], 'discount' => $discount];
    }

    private function evaluateSpecialPrice(array $cartItems): array
    {
        $cond = $this->conditions;

        if (empty($cond['product_id']) || empty($cond['special_price'])) {
            return ['applies' => false, 'rewards' => [], 'discount' => 0];
        }

        $item = collect($cartItems)->firstWhere('product_id', $cond['product_id']);
        if (!$item) {
            return ['applies' => false, 'rewards' => [], 'discount' => 0];
        }

        $normalTotal = $item['price'] * $item['qty'];
        $specialTotal = (int) $cond['special_price'] * $item['qty'];
        $discount = max(0, $normalTotal - $specialTotal);

        return ['applies' => $discount > 0, 'rewards' => [], 'discount' => $discount];
    }

    private function evaluateCategoryDiscount(array $cartItems): array
    {
        $cond = $this->conditions;

        if (empty($cond['category_id']) || empty($cond['discount_pct'])) {
            return ['applies' => false, 'rewards' => [], 'discount' => 0];
        }

        $category = Category::find($cond['category_id']);
        if (!$category) {
            return ['applies' => false, 'rewards' => [], 'discount' => 0];
        }

        $allIds = [$category->id];
        $childIds = [$category->id];
        while (!empty($childIds)) {
            $childIds = Category::whereIn('parent_id', $childIds)->pluck('id')->toArray();
            $allIds = array_merge($allIds, $childIds);
        }
        $productIds = Product::whereIn('category_id', $allIds)->pluck('id')->toArray();

        $discount = 0;
        foreach ($cartItems as $item) {
            if (in_array($item['product_id'], $productIds)) {
                $discount += (int) round($item['price'] * $item['qty'] * $cond['discount_pct'] / 100);
            }
        }

        return ['applies' => $discount > 0, 'rewards' => [], 'discount' => $discount];
    }
}
