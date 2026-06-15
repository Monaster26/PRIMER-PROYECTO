<?php

namespace App\Http\Controllers\Admin;
use Inertia\Inertia;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SalePayment;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SaleController extends Controller
{
    public function index()
    {
        $month = (int) (request('month') ?? now()->month);
        $year = (int) (request('year') ?? now()->year);

        $sales = Sale::with('cashier', 'items.product', 'payments')
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->orderBy('date', 'desc')
            ->paginate(30);

        $products = Product::orderBy('name')
            ->where('stock', '>', 0)
            ->get();

        $cashiers = User::role('cashier')->get();

        $saleIds = Sale::whereMonth('date', $month)->whereYear('date', $year)->pluck('id');
        $summary = [
            'total' => (int) round(SaleItem::whereIn('sale_id', $saleIds)->sum('subtotal') * 100),
            'count' => $saleIds->count(),
        ];

        $months = [
            'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
            'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre',
        ];

        return Inertia::render('admin/ventas', [
            'sales' => $sales,
            'products' => $products,
            'cashiers' => $cashiers,
            'month' => $month,
            'year' => $year,
            'month_name' => $months[$month - 1],
            'summary' => $summary,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'cashier_id' => 'required|exists:users,id',
            'payment_method' => 'required|in:cash,card,transfer,mixed',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'payments' => 'required|array|min:1',
            'payments.*.method' => 'required|in:cash,card,transfer,mercadopago',
            'payments.*.amount' => 'required|numeric|min:0',
        ]);

        $sale = Sale::create([
            'date' => $validated['date'],
            'cashier_id' => $validated['cashier_id'],
            'payment_method' => $validated['payment_method'],
        ]);

        foreach ($validated['items'] as $item) {
            $product = Product::findOrFail($item['product_id']);
            $unitPrice = $product->price / 100;
            $costPrice = ($product->cost_price ?? 0) / 100;

            SaleItem::create([
                'sale_id' => $sale->id,
                'product_id' => $product->id,
                'quantity' => $item['quantity'],
                'unit_price' => $unitPrice,
                'cost_price' => $costPrice,
                'subtotal' => $unitPrice * $item['quantity'],
            ]);
        }

        foreach ($validated['payments'] as $payment) {
            SalePayment::create([
                'sale_id' => $sale->id,
                'method' => $payment['method'],
                'amount' => $payment['amount'],
            ]);
        }

        $sale->refresh();

        return redirect()->route('admin.ventas.index')
            ->with('success', "Venta #{$sale->id} registrada.");
    }

    public function destroy(Sale $sale): RedirectResponse
    {
        $sale->delete();

        return redirect()->route('admin.ventas.index')->with('success', 'Venta eliminada y stock restaurado.');
    }
}
