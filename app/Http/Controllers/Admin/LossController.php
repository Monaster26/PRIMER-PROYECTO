<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Loss;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LossController extends Controller
{
    public function index()
    {
        $month = (int) (request('month') ?? now()->month);
        $year = (int) (request('year') ?? now()->year);

        $losses = Loss::with('product')
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->orderBy('date', 'desc')
            ->paginate(30);

        $products = Product::orderBy('name')->get();

        $summary = [
            'total_value'  => (float) Loss::whereMonth('date', $month)->whereYear('date', $year)->sum('total_loss_value'),
            'total_quantity' => (int) Loss::whereMonth('date', $month)->whereYear('date', $year)->sum('quantity'),
            'count'        => Loss::whereMonth('date', $month)->whereYear('date', $year)->count(),
        ];

        $months = [
            'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
            'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre',
        ];

        return Inertia::render('admin/perdida', [
            'losses'   => $losses,
            'products' => $products,
            'month'    => $month,
            'year'     => $year,
            'month_name' => $months[$month - 1],
            'summary'  => $summary,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'date'       => 'required|date',
            'product_id' => 'required|exists:products,id',
            'quantity'   => 'required|integer|min:1',
            'reason'     => 'nullable|string|max:255',
        ]);

        $loss = Loss::create($validated);

        $product = Product::findOrFail($validated['product_id']);
        $product->decrement('stock', $validated['quantity']);

        return redirect()->route('admin.perdida.index', [
            'month' => now()->month,
            'year'  => now()->year,
        ])->with('success', "Pérdida registrada. Stock de {$product->name} actualizado.");
    }

    public function destroy(Loss $loss): RedirectResponse
    {
        $product = $loss->product;

        if ($product) {
            $product->increment('stock', $loss->quantity);
        }

        $loss->delete();

        return redirect()->route('admin.perdida.index', [
            'month' => request('month', now()->month),
            'year'  => request('year', now()->year),
        ])->with('success', 'Pérdida eliminada y stock restaurado.');
    }
}
