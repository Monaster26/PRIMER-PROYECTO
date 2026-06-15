<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use App\Models\Coupon;
use App\Models\Promotion;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class PosController extends Controller
{
    // ─── Vista principal del POS ───────────────────────────

    public function index(): Response
    {
        return Inertia::render('Pos/Terminal', [
            'categories' => \App\Models\Category::active()->with('products:id,name,sku,barcode,price,stock,image_path,category_id')->ordered()->get(),
        ]);
    }

    // ─── Autenticación por PIN ─────────────────────────────

    /**
     * Inicia sesión de empleado en el POS con su PIN.
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'pin'         => 'required|string|min:4|max:8',
        ]);

        $employee = Employee::active()->findOrFail($request->employee_id);
        $token    = $employee->authenticateWithPin($request->pin);

        if (!$token) {
            return response()->json(['success' => false, 'message' => 'PIN incorrecto.'], 401);
        }

        return response()->json([
            'success'  => true,
            'token'    => $token,
            'employee' => [
                'id'        => $employee->id,
                'name'      => $employee->name,
                'role'      => $employee->role,
                'role_name' => $employee->role_name,
            ],
        ]);
    }

    /**
     * Cierra la sesión POS del empleado.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->validate(['employee_id' => 'required|exists:employees,id']);

        $employee = Employee::findOrFail($request->employee_id);
        $employee->closeSession();

        return response()->json(['success' => true]);
    }

    /**
     * Lista los empleados activos para selección en el login.
     */
    public function employees(): JsonResponse
    {
        return response()->json(
            Employee::active()->orderBy('name')->get(['id', 'name', 'role'])
        );
    }

    // ─── Checkout transaccional ────────────────────────────

    /**
     * Procesa la venta completa con integridad garantizada por DB::transaction.
     * Descuenta stock, registra movimientos y aplica cupones/promociones.
     */
    public function checkout(Request $request): JsonResponse
    {
        $request->validate([
            'employee_id'    => 'required|exists:employees,id',
            'session_token'  => 'required|string',
            'items'          => 'required|array|min:1',
            'items.*.product_id'         => 'required|exists:products,id',
            'items.*.product_variant_id' => 'nullable|exists:product_variants,id',
            'items.*.quantity'           => 'required|integer|min:1',
            'payment_method' => 'required|in:cash,card,transfer,mixed',
            'cash_tendered'  => 'nullable|integer|min:0',
            'customer_id'    => 'nullable|exists:customers,id',
            'coupon_code'    => 'nullable|string',
        ]);

        // Verificar sesión del empleado
        $employee = Employee::findOrFail($request->employee_id);
        if (!$employee->validateSessionToken($request->session_token)) {
            return response()->json(['success' => false, 'message' => 'Sesión inválida.'], 401);
        }

        try {
            $order = DB::transaction(function () use ($request, $employee) {

                $subtotal      = 0;
                $discountTotal = 0;
                $taxTotal      = 0;
                $orderItemsData = [];

                // ─── 1. Validar y calcular ítems ───────────
                foreach ($request->items as $item) {
                    $product = Product::active()->lockForUpdate()->findOrFail($item['product_id']);
                    $variant = isset($item['product_variant_id'])
                        ? ProductVariant::lockForUpdate()->findOrFail($item['product_variant_id'])
                        : null;

                    $target = $variant ?? $product;
                    $qty    = $item['quantity'];

                    // Verificar stock suficiente
                    if ($target->stock < $qty) {
                        throw new \RuntimeException(
                            "Stock insuficiente para \"{$product->name}\". Disponible: {$target->stock}."
                        );
                    }

                    $unitPrice   = $variant ? $variant->effective_price : $product->price;
                    $lineTotal   = $unitPrice * $qty;
                    $taxAmount   = (int) round($lineTotal * ($product->tax_rate / 100));
                    $subtotal   += $lineTotal;
                    $taxTotal   += $taxAmount;

                    $orderItemsData[] = [
                        'product'    => $product,
                        'variant'    => $variant,
                        'qty'        => $qty,
                        'unit_price' => $unitPrice,
                        'tax_amount' => $taxAmount,
                        'line_total' => $lineTotal,
                    ];
                }

                // ─── 2. Aplicar cupón ──────────────────────
                $coupon   = null;
                $couponDiscount = 0;

                if ($request->coupon_code) {
                    $coupon = Coupon::active()->where('code', $request->coupon_code)->first();
                    if ($coupon) {
                        $validation = $coupon->validate($subtotal, $request->customer_id);
                        if ($validation['valid']) {
                            $couponDiscount = $validation['discount'];
                            $discountTotal += $couponDiscount;
                        }
                    }
                }

                // ─── 3. Aplicar promociones ────────────────
                $promoIds = [];
                $cartForPromo = array_map(fn($i) => [
                    'product_id' => $i['product']->id,
                    'qty'        => $i['qty'],
                    'price'      => $i['unit_price'],
                ], $orderItemsData);

                $promotions = Promotion::active()->get();
                foreach ($promotions as $promo) {
                    $result = $promo->evaluateCart($cartForPromo);
                    if ($result['applies']) {
                        $discountTotal += $result['discount'];
                        $promoIds[]     = $promo->id;
                        if ($promo->is_exclusive) break;
                    }
                }

                $total = max(0, $subtotal + $taxTotal - $discountTotal);

                // ─── 4. Crear la Orden ─────────────────────
                $order = Order::create([
                    'customer_id'    => $request->customer_id,
                    'employee_id'    => $employee->id,
                    'coupon_id'      => $coupon?->id,
                    'subtotal'       => $subtotal,
                    'discount_total' => $discountTotal,
                    'tax_total'      => $taxTotal,
                    'total'          => $total,
                    'payment_method' => $request->payment_method,
                    'cash_tendered'  => $request->cash_tendered,
                    'change_due'     => $request->cash_tendered
                        ? max(0, $request->cash_tendered - $total)
                        : null,
                    'status'         => 'completed',
                    'sale_channel'   => 'pos',
                    'promotion_ids'  => $promoIds ?: null,
                    'completed_at'   => now(),
                ]);

                // ─── 5. Crear ítems y descontar stock ──────
                foreach ($orderItemsData as $itemData) {
                    $order->items()->create([
                        'product_id'         => $itemData['product']->id,
                        'product_variant_id' => $itemData['variant']?->id,
                        'product_name'       => $itemData['product']->name,
                        'product_sku'        => $itemData['variant']?->sku ?? $itemData['product']->sku,
                        'quantity'           => $itemData['qty'],
                        'unit_price'         => $itemData['unit_price'],
                        'cost_price'         => $itemData['variant']?->cost_price ?? $itemData['product']->cost_price,
                        'tax_amount'         => $itemData['tax_amount'],
                        'line_total'         => $itemData['line_total'],
                    ]);

                    // Descuento de stock con auditoría
                    StockMovement::record(
                        product: $itemData['product'],
                        quantityChange: -$itemData['qty'],
                        type: 'sale',
                        reference: $order,
                        variantId: $itemData['variant']?->id,
                        unitCost: $itemData['variant']?->cost_price ?? $itemData['product']->cost_price,
                        employeeId: $employee->id
                    );
                }

                // ─── 6. Actualizar cupón ───────────────────
                if ($coupon && $couponDiscount > 0) {
                    $coupon->increment('used_count');
                    \App\Models\CouponUsage::create([
                        'coupon_id'        => $coupon->id,
                        'order_id'         => $order->id,
                        'customer_id'      => $request->customer_id,
                        'discount_applied' => $couponDiscount,
                    ]);
                }

                // ─── 7. Actualizar puntos de fidelidad ─────
                if ($request->customer_id) {
                    $customer = Customer::find($request->customer_id);
                    $customer?->addLoyaltyPoints($total);
                }

                return $order;
            });

            return response()->json([
                'success'      => true,
                'order_number' => $order->order_number,
                'total'        => $order->total,
                'change_due'   => $order->change_due,
            ]);

        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            report($e);
            return response()->json(['success' => false, 'message' => 'Error interno al procesar la venta.'], 500);
        }
    }

    /**
     * Valida un cupón en tiempo real (sin aplicarlo todavía).
     */
    public function validateCoupon(Request $request): JsonResponse
    {
        $request->validate([
            'code'        => 'required|string',
            'order_total' => 'required|integer|min:0',
            'customer_id' => 'nullable|integer',
        ]);

        $coupon = Coupon::active()->where('code', $request->code)->first();

        if (!$coupon) {
            return response()->json(['valid' => false, 'message' => 'Cupón no encontrado.']);
        }

        $result = $coupon->validate($request->order_total, $request->customer_id);

        return response()->json(array_merge($result, ['coupon' => $coupon->only(['code', 'type', 'value'])]));
    }
}
