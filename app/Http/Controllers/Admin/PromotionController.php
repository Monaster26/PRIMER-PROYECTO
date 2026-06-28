<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Promotion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PromotionController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Promotion::orderByDesc('priority');

        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $now = now();
            match ($request->status) {
                'active'    => $query->where('is_active', true)
                    ->where(fn($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>=', $now)),
                'expired'   => $query->where(fn($q) => $q->where('expires_at', '<', $now)->orWhere('is_active', false)),
                'scheduled' => $query->where('is_active', true)
                    ->where('starts_at', '>', $now),
                default     => null,
            };
        }

        return Inertia::render('admin/promociones', [
            'promotions' => $query->paginate(15)->withQueryString(),
            'filters'    => $request->only(['search', 'type', 'status']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatePromotion($request);

        $data = [
            'name'         => $validated['name'],
            'description'  => $validated['description'] ?? null,
            'type'         => $validated['type'],
            'conditions'   => $this->buildConditions($validated),
            'rewards'      => $this->buildRewards($validated),
            'is_active'    => $validated['is_active'] ?? true,
            'is_exclusive' => $validated['is_exclusive'] ?? false,
            'priority'     => $validated['priority'] ?? 0,
            'starts_at'    => $validated['starts_at'] ?? null,
            'expires_at'   => $validated['expires_at'] ?? null,
        ];

        Promotion::create($data);

        return redirect()->route('admin.promociones.index')
            ->with('success', 'Promoción creada.');
    }

    public function update(Request $request, Promotion $promotion): RedirectResponse
    {
        $validated = $this->validatePromotion($request, $promotion);

        $data = [
            'name'         => $validated['name'],
            'description'  => $validated['description'] ?? null,
            'type'         => $validated['type'],
            'conditions'   => $this->buildConditions($validated),
            'rewards'      => $this->buildRewards($validated),
            'is_active'    => $validated['is_active'] ?? true,
            'is_exclusive' => $validated['is_exclusive'] ?? false,
            'priority'     => $validated['priority'] ?? 0,
            'starts_at'    => $validated['starts_at'] ?? null,
            'expires_at'   => $validated['expires_at'] ?? null,
        ];

        $promotion->update($data);

        return redirect()->route('admin.promociones.index')
            ->with('success', 'Promoción actualizada.');
    }

    public function destroy(Promotion $promotion): RedirectResponse
    {
        $promotion->delete();

        return redirect()->route('admin.promociones.index')
            ->with('success', 'Promoción eliminada.');
    }

    public function toggleActive(Promotion $promotion): JsonResponse
    {
        $promotion->update(['is_active' => !$promotion->is_active]);

        return response()->json([
            'is_active' => $promotion->fresh()->is_active,
        ]);
    }

    public function evaluate(Request $request, Promotion $promotion): JsonResponse
    {
        $request->validate([
            'items'            => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.qty'      => 'required|integer|min:1',
            'items.*.price'    => 'required|integer|min:0',
        ]);

        $cartForPromo = collect($request->items)->map(fn($item) => [
            'product_id' => $item['product_id'],
            'variant_id' => null,
            'qty'        => $item['qty'],
            'price'      => $item['price'],
        ])->toArray();

        $result = $promotion->evaluateCart($cartForPromo);

        return response()->json([
            'promotion'     => $promotion->only(['id', 'name', 'type', 'is_active']),
            'conditions'    => $promotion->conditions,
            'cart_for_promo' => $cartForPromo,
            'applies'       => $result['applies'],
            'discount'      => $result['discount'],
            'discount_pesos' => $result['discount'] / 100,
        ]);
    }

    private function validatePromotion(Request $request, ?Promotion $promotion = null): array
    {
        $rules = [
            'name'          => 'required|string|max:255',
            'description'   => 'nullable|string|max:1000',
            'type'          => 'required|in:buy_x_get_y,min_qty_discount,bundle_discount',
            'is_active'     => 'boolean',
            'is_exclusive'  => 'boolean',
            'priority'      => 'nullable|integer|min:0',
            'starts_at'     => 'nullable|date',
            'expires_at'    => 'nullable|date|after_or_equal:starts_at',
        ];

        $conditional = match ($request->type) {
            'buy_x_get_y' => [
                'conditions.buy_product_id' => 'required|integer|exists:products,id',
                'conditions.buy_qty'        => 'required|integer|min:1',
                'rewards.get_product_id'    => 'required|integer|exists:products,id',
                'rewards.get_qty'           => 'required|integer|min:1',
                'rewards.discount_pct'      => 'nullable|integer|min:0|max:100',
            ],
            'min_qty_discount' => [
                'conditions.product_id'    => 'required|integer|exists:products,id',
                'conditions.min_qty'       => 'required|integer|min:1',
                'conditions.discount_pct'  => 'required_without:conditions.special_price|nullable|integer|min:1|max:100',
                'conditions.special_price' => 'required_without:conditions.discount_pct|nullable|numeric|min:1',
            ],
            'bundle_discount' => [
                'conditions.product_ids'   => 'required|array|min:2',
                'conditions.product_ids.*' => 'integer|exists:products,id',
                'conditions.discount_pct'  => 'required|integer|min:1|max:100',
            ],
            default => [],
        };

        return $request->validate(array_merge($rules, $conditional));
    }

    private function buildConditions(array $validated): array
    {
        return match ($validated['type']) {
            'buy_x_get_y' => [
                'buy_product_id' => $validated['conditions']['buy_product_id'],
                'buy_qty'        => (int) $validated['conditions']['buy_qty'],
            ],
            'min_qty_discount' => [
                'product_id'    => $validated['conditions']['product_id'],
                'min_qty'       => (int) $validated['conditions']['min_qty'],
                'discount_pct'  => isset($validated['conditions']['discount_pct']) ? (int) $validated['conditions']['discount_pct'] : null,
                'special_price' => isset($validated['conditions']['special_price']) ? (int) $validated['conditions']['special_price'] : null,
            ],
            'bundle_discount' => [
                'product_ids'  => $validated['conditions']['product_ids'],
                'discount_pct' => (int) $validated['conditions']['discount_pct'],
            ],
            default => [],
        };
    }

    private function buildRewards(array $validated): array
    {
        if ($validated['type'] !== 'buy_x_get_y') return [];

        return [
            'get_product_id' => $validated['rewards']['get_product_id'],
            'get_qty'        => (int) $validated['rewards']['get_qty'],
            'discount_pct'   => (int) ($validated['rewards']['discount_pct'] ?? 100),
        ];
    }
}
