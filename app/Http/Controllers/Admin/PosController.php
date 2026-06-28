<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CashSession;
use App\Models\ControlZeta;
use App\Models\Observacion;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SalePayment;
use App\Models\Coupon;
use App\Models\Promotion;
use App\Models\StockMovement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PosController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::active()
            ->orderBy('name')
            ->get(['id', 'name', 'sku', 'barcode', 'price', 'stock', 'unit', 'image_path']);

        $user = auth()->user();

        // Admin siempre pasa sin bloqueo; cajero debe tener caja abierta
        $hasOpenSession = $user->hasRole('admin') ? true :
            CashSession::where('user_id', $user->id)
                ->whereNull('closed_at')
                ->latest('opened_at')
                ->exists();

        $ultimaSession = CashSession::whereNotNull('closed_at')
            ->with('user:id,name')
            ->latest('closed_at')
            ->first(['cierre_desglose', 'total_efectivo_cierre', 'closed_at', 'user_id']);

        $response = [
            'products' => $products,
            'hasOpenSession' => $hasOpenSession,
            'ultimaSesion' => $ultimaSession ? [
                'cierre_desglose' => $ultimaSession->cierre_desglose,
                'total_efectivo_cierre' => $ultimaSession->total_efectivo_cierre,
                'cerrado_por' => $ultimaSession->user?->name,
                'cerrado_at' => $ultimaSession->closed_at?->format('d/m/Y H:i'),
            ] : null,
        ];

        // Partial reload: solo cuando el modal solicita todaySales
        $partial = $request->header('X-Inertia-Partial-Data');
        $partialKeys = $partial ? explode(',', $partial) : [];

        if (in_array('todaySales', $partialKeys)) {
            $sales = Sale::with(['items.product', 'payments', 'cashier'])
                ->where('user_id', $user->id)
                ->whereDate('created_at', today())
                ->orderBy('created_at', 'desc')
                ->get();

            $response['todaySales'] = $sales->map(fn($s) => [
                'id'              => $s->id,
                'folio'           => $s->id,
                'time'            => $s->created_at->format('H:i'),
                'total'           => $s->total,
                'items'           => $s->items->map(fn($i) => [
                    'name'     => $i->product?->name ?? 'Producto sin nombre',
                    'quantity' => $i->quantity,
                    'price'    => $i->price,
                    'total'    => $i->total_line,
                ]),
                'payments'        => $s->payments->map(fn($p) => [
                    'method' => $p->method,
                    'amount' => $p->amount,
                ]),
                'cash_amount'     => $s->cash_amount,
                'card_amount'     => $s->card_amount,
                'transfer_amount' => $s->transfer_amount,
                'net_total'       => $s->net_total,
                'tax_total'       => $s->tax_total,
                'discount_total'  => $s->discount_total,
                'cashier_name'    => $s->cashier?->name,
                'created_at'      => $s->created_at->toDateTimeString(),
            ]);
        }

        return Inertia::render('admin/pos', $response);
    }

    public function reprint(Sale $sale)
    {
        $sale->load(['items.product', 'payments', 'cashier']);

        return Inertia::render('admin/pos-ticket-reprint', [
            'sale' => [
                'id'        => $sale->id,
                'folio'     => $sale->id,
                'items'     => $sale->items->map(fn($i) => [
                    'name'     => $i->product?->name ?? 'Producto sin nombre',
                    'quantity' => $i->quantity,
                    'price'    => $i->price,
                    'total'    => $i->total_line,
                ]),
                'payments'  => $sale->payments->map(fn($p) => [
                    'method' => $p->method,
                    'amount' => $p->amount,
                ]),
                'total'           => $sale->total,
                'cash_amount'     => $sale->cash_amount,
                'card_amount'     => $sale->card_amount,
                'transfer_amount' => $sale->transfer_amount,
                'net_total'       => $sale->net_total,
                'tax_total'       => $sale->tax_total,
                'discount_total'  => $sale->discount_total,
                'cashier_name'    => $sale->cashier?->name,
                'created_at'      => $sale->created_at->toDateTimeString(),
            ],
        ]);
    }

    public function openSession(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'cant_20k_apertura' => 'required|integer|min:0',
            'cant_10k_apertura' => 'required|integer|min:0',
            'cant_5k_apertura' => 'required|integer|min:0',
            'cant_2k_apertura' => 'required|integer|min:0',
            'cant_1k_apertura' => 'required|integer|min:0',
            'cant_500_apertura' => 'required|integer|min:0',
            'cant_100_apertura' => 'required|integer|min:0',
            'cant_50_apertura' => 'required|integer|min:0',
            'cant_10_apertura' => 'required|integer|min:0',
        ]);

        // Calcular total en servidor (seguridad: no confiar en el cliente)
        $total =
            ($validated['cant_20k_apertura'] * 20000) +
            ($validated['cant_10k_apertura'] * 10000) +
            ($validated['cant_5k_apertura'] * 5000) +
            ($validated['cant_2k_apertura'] * 2000) +
            ($validated['cant_1k_apertura'] * 1000) +
            ($validated['cant_500_apertura'] * 500) +
            ($validated['cant_100_apertura'] * 100) +
            ($validated['cant_50_apertura'] * 50) +
            ($validated['cant_10_apertura'] * 10);

        $aperturaDesglose = [
            '20k' => $validated['cant_20k_apertura'],
            '10k' => $validated['cant_10k_apertura'],
            '5k'  => $validated['cant_5k_apertura'],
            '2k'  => $validated['cant_2k_apertura'],
            '1k'  => $validated['cant_1k_apertura'],
            '500' => $validated['cant_500_apertura'],
            '100' => $validated['cant_100_apertura'],
            '50'  => $validated['cant_50_apertura'],
            '10'  => $validated['cant_10_apertura'],
        ];

        // Discrepancia contra última sesión cerrada (tolerancia $500)
        $ultimaSession = CashSession::whereNotNull('closed_at')
            ->latest('closed_at')
            ->first();

        if ($ultimaSession) {
            $diferencia = $total - $ultimaSession->total_efectivo_cierre;

            if (abs($diferencia) > 500 && !$request->has('observacion')) {
                return response()->json([
                    'message' => 'Diferencia detectada en gaveta',
                    'requiere_justificacion' => true,
                    'diferencia' => $diferencia,
                    'ultimo_cierre_monto' => (int) $ultimaSession->total_efectivo_cierre,
                    'ultimo_cierre_desglose' => $ultimaSession->cierre_desglose,
                    'nuevo_apertura_monto' => $total,
                ], 422);
            }

            if (abs($diferencia) > 500 && $request->has('observacion')) {
                Observacion::create([
                    'user_id' => $request->user()->id,
                    'tipo_accion' => 'descuadre_apertura',
                    'detalle' => $request->input('observacion'),
                    'monto_diferencia' => $diferencia,
                    'read_at' => null,
                    'metadata' => [
                        'old_desglose' => $ultimaSession->cierre_desglose,
                        'new_desglose' => $aperturaDesglose,
                        'old_total' => (int) $ultimaSession->total_efectivo_cierre,
                        'new_total' => $total,
                    ],
                ]);
            }
        }

        $session = CashSession::create([
            'user_id'          => $request->user()->id,
            'opened_at'        => now(),
            'opening_balance'  => $total,
            'apertura_desglose'=> $aperturaDesglose,
            ...$validated,
        ]);

        // total_efectivo_apertura se calcula automáticamente en el modelo (saving event)

        ControlZeta::create([
            'cash_session_id' => $session->id,
            'fecha_apertura'  => now(),
            'cajero'          => $request->user()->name,
            'esperado_caja'   => 0,
            'efectivo_neto'   => 0,
            'red_compra_neto' => 0,
            'transferencia'   => 0,
            'red_compra_total' => 0,
            'porcentaje_banco' => 0,
        ]);

        return response()->json([
            'success' => true,
            'session' => [
                'id' => $session->id,
                'opening_balance' => $session->opening_balance,
                'opened_at' => $session->opened_at,
            ],
        ]);
    }

    public function lookup(string $code): JsonResponse
    {
        $product = Product::active()
            ->where(function ($q) use ($code) {
                $q->where('barcode', $code)
                  ->orWhere('sku', $code);
            })
            ->first(['id', 'name', 'sku', 'barcode', 'price', 'stock', 'unit', 'image_path']);

        if (!$product) {
            return response()->json(['error' => 'Producto no encontrado'], 404);
        }

        return response()->json(['product' => $product]);
    }

    public function checkout(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'payments' => 'required|array|min:1',
            'payments.*.method' => 'required|in:cash,card,transfer',
            'payments.*.amount' => 'required|numeric|min:0',
            'coupon_code' => 'nullable|string',
        ]);

        $cashAmount = 0;
        $cardAmount = 0;
        $transferAmount = 0;

        foreach ($validated['payments'] as $payment) {
            $amountCents = (int) round($payment['amount'] * 100);
            match ($payment['method']) {
                'cash' => $cashAmount += $amountCents,
                'card' => $cardAmount += $amountCents,
                'transfer' => $transferAmount += $amountCents,
            };
        }

        try {
            return DB::transaction(function () use ($validated, $cashAmount, $cardAmount, $transferAmount) {
                $totalCents = 0;
                $netTotal = 0;
                $taxTotal = 0;

                $sale = Sale::create([
                    'user_id' => auth()->id(),
                    'type' => 'pos',
                    'cash_amount' => $cashAmount,
                    'card_amount' => $cardAmount,
                    'transfer_amount' => $transferAmount,
                    'total' => 0,
                ]);

                foreach ($validated['items'] as $item) {
                    $product = Product::lockForUpdate()->findOrFail($item['product_id']);

                    if ($product->stock < $item['quantity']) {
                        throw new \Exception("Stock insuficiente para {$product->name} (disponible: {$product->stock})");
                    }

                    $taxRate = $product->tax_rate > 0 ? $product->tax_rate : 19;
                    $unitPrice = $product->price;
                    $netPrice = $taxRate > 0 ? (int) round($unitPrice / (1 + $taxRate / 100)) : $unitPrice;
                    $taxAmount = $unitPrice - $netPrice;
                    $lineTotal = $unitPrice * $item['quantity'];

                    SaleItem::create([
                        'sale_id'    => $sale->id,
                        'product_id' => $product->id,
                        'quantity'   => $item['quantity'],
                        'price'      => $unitPrice,
                        'net_price'  => $netPrice,
                        'tax_amount' => $taxAmount,
                        'tax_rate'   => $taxRate,
                        'total_line' => $lineTotal,
                    ]);

                    StockMovement::record(
                        product: $product,
                        quantityChange: -$item['quantity'],
                        type: 'sale',
                        reference: $sale,
                        unitCost: $product->cost_price,
                        notes: "Venta POS #{$sale->id}",
                    );
                    $totalCents += $lineTotal;
                    $netTotal += $netPrice * $item['quantity'];
                    $taxTotal += $taxAmount * $item['quantity'];
                }

                $cartForPromo = collect($validated['items'])->map(fn($item) => [
                    'product_id' => $item['product_id'],
                    'variant_id' => null,
                    'qty'        => $item['quantity'],
                    'price'      => Product::find($item['product_id'])->price,
                ])->toArray();

                $promoDiscount = 0;
                $appliedPromotions = [];

                foreach (Promotion::active()->get() as $promo) {
                    $result = $promo->evaluateCart($cartForPromo);
                    if ($result['applies']) {
                        $promoDiscount += $result['discount'];
                        $appliedPromotions[] = $promo->name;
                        if ($promo->is_exclusive) break;
                    }
                }

                $couponDiscount = 0;
                $coupon = null;

                if (!empty($validated['coupon_code'])) {
                    $coupon = Coupon::active()->where('code', $validated['coupon_code'])->first();
                    if (!$coupon) {
                        throw new \Exception('Cupón inválido o expirado.');
                    }
                    $result = $coupon->validate($totalCents);
                    if (!$result['valid']) {
                        throw new \Exception($result['message']);
                    }
                    $couponDiscount = $result['discount'];
                }

                $totalDiscount = min($promoDiscount + $couponDiscount, $totalCents);
                $finalTotal = $totalCents - $totalDiscount;

                $netTotal = (int) $netTotal;
                $taxTotal = (int) $taxTotal;

                $sale->update([
                    'total'           => $finalTotal,
                    'net_total'       => $netTotal,
                    'tax_total'       => $taxTotal,
                    'discount_total'  => $totalDiscount,
                    'coupon_id'       => $coupon?->id,
                    'promo_discount'  => $promoDiscount,
                    'coupon_discount' => $couponDiscount,
                ]);

                if ($coupon) {
                    $coupon->increment('used_count');
                }

                foreach ($validated['payments'] as $payment) {
                    SalePayment::create([
                        'sale_id' => $sale->id,
                        'method' => $payment['method'],
                        'amount' => $payment['amount'],
                    ]);
                }

                return response()->json([
                    'success'             => "Venta #{$sale->id} registrada correctamente.",
                    'sale_id'             => $sale->id,
                    'discount_total'      => $totalDiscount,
                    'applied_promotions'  => $appliedPromotions,
                    'coupon_discount'     => $couponDiscount,
                ]);
            });
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }
}
