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
        $from = request('from');
        $to = request('to');

        $query = Sale::with('cashier', 'items.product', 'payments');

        if ($from && $to) {
            $query->whereBetween('created_at', [$from, $to . ' 23:59:59']);
        } elseif ($from) {
            $query->whereDate('created_at', $from);
        }

        $sales = $query->orderBy('created_at', 'desc')->paginate(30);

        $products = Product::orderBy('name')
            ->where('stock', '>', 0)
            ->get();

        $cashiers = User::role('cashier')->get();

        return Inertia::render('admin/ventas', [
            'sales' => $sales,
            'products' => $products,
            'cashiers' => $cashiers,
            'from' => $from,
            'to' => $to,
            'summary' => [
                'total' => (int) (clone $query)->sum('total'),
                'count' => $sales->total(),
            ],
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
        $sale->delete();

        return redirect()->route('admin.ventas.index')->with('success', 'Venta eliminada y stock restaurado.');
    }
}
