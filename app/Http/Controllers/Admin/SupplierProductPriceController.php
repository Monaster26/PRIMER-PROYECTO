<?php

namespace App\Http\Controllers\Admin;
use Inertia\Inertia;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\SupplierProductPrice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SupplierProductPriceController extends Controller
{
    public function index()
    {
        $prices = SupplierProductPrice::with('product', 'supplier')
            ->orderBy('last_updated_at', 'desc')
            ->paginate(30);

        $products = Product::orderBy('name')->get();
        $suppliers = Supplier::orderBy('company_name')->get();

        return Inertia::render('admin/comparativa-precios', [
            'prices' => $prices,
            'products' => $products,
            'suppliers' => $suppliers,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'offered_price' => 'required|numeric|min:0',
        ]);

        $validated['last_updated_at'] = now();

        SupplierProductPrice::create($validated);

        return redirect()->route('admin.comparativa-precios.index')->with('success', 'Cotización registrada.');
    }

    public function update(Request $request, SupplierProductPrice $supplierProductPrice): RedirectResponse
    {
        $validated = $request->validate([
            'offered_price' => 'required|numeric|min:0',
        ]);

        $validated['last_updated_at'] = now();

        $supplierProductPrice->update($validated);

        return redirect()->route('admin.comparativa-precios.index')->with('success', 'Cotización actualizada.');
    }

    public function destroy(SupplierProductPrice $supplierProductPrice): RedirectResponse
    {
        $supplierProductPrice->delete();

        return redirect()->route('admin.comparativa-precios.index')->with('success', 'Cotización eliminada.');
    }
}
