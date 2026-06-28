<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryAdjustment;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class InventoryAdjustmentController extends Controller
{
    public function index(Request $request)
    {
        $query = InventoryAdjustment::with(['user', 'items.product']);

        if ($request->has('date')) {
            $query->whereDate('created_at', $request->date);
        }

        $adjustments = $query->latest()->paginate(10)->withQueryString();

        return Inertia::render('admin/inventory/Index', [
            'adjustments' => $adjustments,
            'filters'     => $request->only(['date']),
        ]);
    }

    public function create()
    {
        return Inertia::render('admin/inventory/Create');
    }

    public function scanProduct(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'sku'     => 'required_without:barcode|string',
            'barcode' => 'required_without:sku|string',
        ]);

        $product = Product::where('sku', $validated['sku'] ?? null)
            ->orWhere('barcode', $validated['barcode'] ?? null)
            ->first();

        if (!$product) {
            return response()->json(['error' => 'Producto no encontrado'], 404);
        }

        return response()->json([
            'id'           => $product->id,
            'name'         => $product->name,
            'sku'          => $product->sku,
            'barcode'      => $product->barcode,
            'system_stock' => $product->stock,
            'cost_price'   => $product->cost_price,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'items'                     => 'required|array|min:1',
            'items.*.product_id'        => 'required|exists:products,id',
            'items.*.counted_stock'     => 'required|integer',
        ]);

        try {
            $adjustment = DB::transaction(function () use ($validated) {
                $adjustment = InventoryAdjustment::create([
                    'user_id'              => auth()->id(),
                    'total_cost_difference' => 0,
                    'status'               => 'draft',
                ]);

                $totalDiff = 0;

                foreach ($validated['items'] as $item) {
                    $product = Product::lockForUpdate()->findOrFail($item['product_id']);

                    $systemStock  = $product->stock;
                    $countedStock = $item['counted_stock'];
                    $difference   = $countedStock - $systemStock;

                    $adjustment->items()->create([
                        'product_id'    => $item['product_id'],
                        'system_stock'  => $systemStock,
                        'counted_stock' => $countedStock,
                        'difference'    => $difference,
                        'cost_price'    => $product->cost_price,
                    ]);

                    StockMovement::record(
                        product: $product,
                        quantityChange: $difference,
                        type: 'adjustment',
                        notes: "Conteo cíclico: sistema {$systemStock}, conteo {$countedStock}",
                    );

                    $totalDiff += $difference * $product->cost_price;
                }

                $adjustment->update([
                    'total_cost_difference' => $totalDiff,
                    'status'               => 'completed',
                ]);

                return $adjustment->load('items.product');
            });

            return redirect()->back()->with('success', '¡Auditoría de inventario procesada con éxito!');
        } catch (\Throwable $e) {
            report($e);
            return redirect()->back()->with('error', 'Error al procesar el inventario: ' . $e->getMessage());
        }
    }
}
