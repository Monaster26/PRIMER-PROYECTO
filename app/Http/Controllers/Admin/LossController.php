<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Loss;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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
        StockMovement::record(
            product: $product,
            quantityChange: -$validated['quantity'],
            type: 'loss',
            notes: $validated['reason'] ?? 'Pérdida registrada',
        );

        return redirect()->route('admin.perdida.index', [
            'month' => now()->month,
            'year'  => now()->year,
        ])->with('success', "Pérdida registrada. Stock de {$product->name} actualizado.");
    }

    public function destroy(Loss $loss): RedirectResponse
    {
        $product = $loss->product;

        if ($product) {
            StockMovement::record(
                product: $product,
                quantityChange: $loss->quantity,
                type: 'return_in',
                notes: "Reversión de pérdida #{$loss->id}",
            );
        }

        $month = (int) (request('month') ?? now()->month);
        $year  = (int) (request('year') ?? now()->year);

        try {
            $loss->delete();
            session()->flash('success', 'Pérdida eliminada y stock restaurado.');
        } catch (\Throwable $e) {
            Log::error('Error al eliminar pérdida #{id}: {msg}', [
                'id'  => $loss->id,
                'msg' => $e->getMessage(),
            ]);
            if ($product) {
                StockMovement::record(
                    product: $product,
                    quantityChange: -$loss->quantity,
                    type: 'loss',
                    notes: "Rollback reversión de pérdida #{$loss->id}",
                );
            }
            session()->flash('error', 'No se pudo eliminar la pérdida.');
        }

        return redirect()->route('admin.perdida.index', [
            'month' => $month,
            'year'  => $year,
        ]);
    }

    public function update(Request $request, Loss $loss): RedirectResponse
    {
        $validated = $request->validate([
            'date'     => 'required|date',
            'quantity' => 'required|integer|min:1',
            'reason'   => 'nullable|string|max:255',
        ]);

        $oldQty = $loss->quantity;
        $loss->update($validated);

        $diff = $loss->quantity - $oldQty;
        if ($diff !== 0 && ($product = $loss->product)) {
            StockMovement::record(
                product: $product,
                quantityChange: -$diff,
                type: 'adjustment',
                notes: "Corrección de pérdida #{$loss->id}: cantidad {$oldQty} → {$loss->quantity}",
            );
        }

        return redirect()->route('admin.perdida.index', [
            'month' => request('month', now()->month),
            'year'  => request('year', now()->year),
        ])->with('success', 'Pérdida actualizada.');
    }
}
