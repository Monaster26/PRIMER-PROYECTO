<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\StockMovement;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PurchaseOrderController extends Controller
{
    public function index(Request $request): Response
    {
        $orders = PurchaseOrder::with('supplier', 'creator', 'items.product')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $suppliers = Supplier::orderBy('company_name')->get(['id', 'company_name']);
        $products = Product::active()->orderBy('name')->get(['id', 'name', 'sku', 'cost_price', 'stock']);

        return Inertia::render('admin/pedidos', [
            'orders'    => $orders,
            'suppliers' => $suppliers,
            'products'  => $products,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'supplier_id'          => 'required|exists:suppliers,id',
            'ordered_at'           => 'required|date',
            'notes'                => 'nullable|string|max:500',
            'items'                => 'required|array|min:1',
            'items.*.product_id'   => 'required|exists:products,id',
            'items.*.quantity'     => 'required|integer|min:1',
            'items.*.unit_cost'    => 'required|numeric|min:0',
        ]);

        $lastId = PurchaseOrder::max('id') ?? 0;
        $orderNumber = 'PO-' . str_pad($lastId + 1, 4, '0', STR_PAD_LEFT);

        $totalCost = 0;
        $items = [];

        foreach ($validated['items'] as $item) {
            $unitCost = (int) (round((float) $item['unit_cost'], 2) * 100);
            $subtotal = $unitCost * (int) $item['quantity'];
            $totalCost += $subtotal;

            $items[] = new PurchaseOrderItem([
                'product_id' => $item['product_id'],
                'quantity'   => (int) $item['quantity'],
                'unit_cost'  => $unitCost,
                'subtotal'   => $subtotal,
            ]);
        }

        $order = PurchaseOrder::create([
            'order_number' => $orderNumber,
            'supplier_id'  => $validated['supplier_id'],
            'total_cost'   => $totalCost,
            'status'       => 'pending',
            'notes'        => $validated['notes'] ?? null,
            'ordered_at'   => $validated['ordered_at'],
            'created_by'   => auth()->id(),
        ]);

        $order->items()->saveMany($items);

        return redirect()->route('admin.pedidos.index')
            ->with('success', "Pedido {$orderNumber} registrado.");
    }

    public function receive(Request $request, PurchaseOrder $purchaseOrder): RedirectResponse
    {
        if ($purchaseOrder->status !== 'pending') {
            return back()->with('error', 'El pedido ya fue ' . $purchaseOrder->status . '.');
        }

        $validated = $request->validate([
            'items'              => 'required|array',
            'items.*.id'         => 'required|exists:purchase_order_items,id',
            'items.*.received_quantity' => 'required|integer|min:0',
        ]);

        $purchaseOrder->load('items.product');

        $receivedIds = collect($validated['items'])->pluck('id');

        foreach ($purchaseOrder->items as $item) {
            $incoming = $validated['items'][$receivedIds->search($item->id)] ?? null;
            $receivedQty = $incoming ? (int) $incoming['received_quantity'] : $item->quantity;

            if ($receivedQty > 0) {
                StockMovement::record(
                    product: $item->product,
                    quantityChange: $receivedQty,
                    type: 'purchase',
                    reference: $purchaseOrder,
                    variantId: null,
                    unitCost: $item->unit_cost,
                    notes: "PO: {$purchaseOrder->order_number}",
                    employeeId: auth()->id(),
                );
            }

            $item->update(['received_quantity' => $receivedQty]);
        }

        $purchaseOrder->update([
            'status'      => 'received',
            'received_at' => now(),
        ]);

        return redirect()->route('admin.pedidos.index')
            ->with('success', "Pedido {$purchaseOrder->order_number} recibido. Stock actualizado.");
    }

    public function destroy(PurchaseOrder $purchaseOrder): RedirectResponse
    {
        if ($purchaseOrder->status === 'received') {
            return back()->with('error', 'No se puede eliminar un pedido recibido.');
        }

        $purchaseOrder->delete();

        return redirect()->route('admin.pedidos.index')
            ->with('success', 'Pedido cancelado.');
    }
}
