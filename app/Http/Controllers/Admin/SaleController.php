<?php

namespace App\Http\Controllers\Admin;
use Inertia\Inertia;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SalePayment;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SaleController extends Controller
{
    public function index()
    {
        $query = Sale::with('cashier', 'items.product', 'payments');

        if ($from = request('from')) {
            $query->whereDate('created_at', '>=', $from);
        }
        if ($to = request('to')) {
            $query->whereDate('created_at', '<=', $to);
        }
        if ($cashierId = request('cashier_id')) {
            $query->where('user_id', $cashierId);
        }
        if ($method = request('payment_method')) {
            $query->whereHas('payments', fn($q) => $q->where('method', $method));
        }
        if ($search = request('search')) {
            $query->where('id', (int) $search);
        }

        $sales = $query->orderBy('created_at', 'desc')->paginate(30)->withQueryString();

        $cashiers = User::role('cashier')->get(['id', 'name']);

        return Inertia::render('admin/ventas-historial', [
            'sales'    => $sales,
            'cashiers' => $cashiers,
            'filters'  => request(['from', 'to', 'cashier_id', 'payment_method', 'search']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'cashier_id' => 'required|exists:users,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'payments' => 'required|array|min:1',
            'payments.*.method' => 'required|in:cash,card,transfer',
            'payments.*.amount' => 'required|numeric|min:0',
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

        $totalCents = 0;

        $sale = Sale::create([
            'user_id' => $validated['cashier_id'],
            'type' => 'pos',
            'cash_amount' => $cashAmount,
            'card_amount' => $cardAmount,
            'transfer_amount' => $transferAmount,
            'total' => 0,
        ]);

        foreach ($validated['items'] as $item) {
            $product = Product::findOrFail($item['product_id']);
            $lineTotal = $product->price * $item['quantity'];

            SaleItem::create([
                'sale_id' => $sale->id,
                'product_id' => $product->id,
                'quantity' => $item['quantity'],
                'price' => $product->price,
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
        }

        $sale->update(['total' => $totalCents]);

        foreach ($validated['payments'] as $payment) {
            SalePayment::create([
                'sale_id' => $sale->id,
                'method' => $payment['method'],
                'amount' => $payment['amount'],
            ]);
        }

        return redirect()->route('admin.ventas.index')
            ->with('success', "Venta #{$sale->id} registrada.");
    }

    public function destroy(Sale $sale): RedirectResponse
    {
        $sale->load('items.product');

        foreach ($sale->items as $item) {
            StockMovement::record(
                product: $item->product,
                quantityChange: $item->quantity,
                type: 'return_in',
                reference: $sale,
                notes: "Reversión de venta #{$sale->id}",
            );
        }

        $sale->delete();

        return redirect()->route('admin.ventas.index')->with('success', 'Venta eliminada y stock restaurado.');
    }
}
