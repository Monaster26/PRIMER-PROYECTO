<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderReturn;
use App\Models\StockMovement;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Order::with(['customer', 'employee'])
            ->when($request->search, fn($q) => $q->where('order_number', 'like', "%{$request->search}%"))
            ->when($request->channel, fn($q) => $q->byChannel($request->channel))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->date_from, fn($q) => $q->whereDate('created_at', '>=', $request->date_from))
            ->when($request->date_to, fn($q) => $q->whereDate('created_at', '<=', $request->date_to))
            ->latest();

        return Inertia::render('Orders/Index', [
            'orders'  => $query->paginate(25)->withQueryString(),
            'filters' => $request->only(['search', 'channel', 'status', 'date_from', 'date_to']),
            'summary' => [
                'today_total' => Order::completed()->today()->sum('total'),
                'today_count' => Order::completed()->today()->count(),
            ],
        ]);
    }

    public function show(Order $order): Response
    {
        return Inertia::render('Orders/Show', [
            'order' => $order->load(['customer', 'employee', 'coupon', 'items.product', 'items.variant', 'returns']),
        ]);
    }

    /**
     * Procesa una devolución con reintegro de stock dentro de una transacción.
     */
    public function processReturn(Request $request, Order $order)
    {
        $request->validate([
            'items'         => 'required|array|min:1',
            'items.*.order_item_id' => 'required|exists:order_items,id',
            'items.*.qty'   => 'required|integer|min:1',
            'items.*.reason'=> 'required|string|max:255',
            'refund_method' => 'required|in:cash,card,store_credit',
            'employee_id'   => 'required|exists:employees,id',
        ]);

        DB::transaction(function () use ($request, $order) {
            $refundAmount = 0;
            $itemsData    = [];

            foreach ($request->items as $returnItem) {
                $orderItem = $order->items()->findOrFail($returnItem['order_item_id']);
                $qty       = min($returnItem['qty'], $orderItem->quantity);

                $itemRefund    = (int) round(($orderItem->unit_price * $qty));
                $refundAmount += $itemRefund;

                $itemsData[] = [
                    'order_item_id' => $orderItem->id,
                    'qty'           => $qty,
                    'reason'        => $returnItem['reason'],
                    'refund'        => $itemRefund,
                ];

                // Reintegrar stock
                StockMovement::record(
                    product: $orderItem->product,
                    quantityChange: +$qty,
                    type: 'return_in',
                    reference: $order,
                    variantId: $orderItem->product_variant_id,
                    employeeId: $request->employee_id
                );
            }

            OrderReturn::create([
                'order_id'      => $order->id,
                'employee_id'   => $request->employee_id,
                'items'         => $itemsData,
                'refund_amount' => $refundAmount,
                'refund_method' => $request->refund_method,
                'status'        => 'completed',
                'stock_restored'=> true,
                'processed_at'  => now(),
            ]);

            // Si toda la orden fue devuelta, marcarla como refunded
            $order->update(['status' => 'refunded']);
        });

        return back()->with('success', 'Devolución procesada exitosamente.');
    }
}
