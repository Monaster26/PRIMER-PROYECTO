<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\ProductsImport;
use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;

class ProductController extends Controller
{
    public function index(Request $request): Response
    {
        $products = Product::with('category')
            ->when($request->search, fn($q) => $q->search($request->search))
            ->orderBy('name')
            ->paginate(30);

        return Inertia::render('admin/codigos', [
            'products' => $products,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'sku'           => 'required|string|max:255|unique:products,sku',
            'barcode'       => 'nullable|string|max:255|unique:products,barcode',
            'category_slug' => 'nullable|string|max:50',
            'sub_category'  => 'nullable|string|max:255',
            'unit'          => 'nullable|string|max:50',
            'cost_price'    => 'nullable|numeric|min:0',
            'price'         => 'required|numeric|min:0',
            'tax_rate'      => 'nullable|integer|min:0|max:100',
            'stock'         => 'required|integer|min:0',
            'min_stock'     => 'nullable|integer|min:0',
            'is_active'     => 'boolean',
            'sort_order'    => 'nullable|integer|min:0',
            'description'   => 'nullable|string',
            'image'         => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        if (!empty($validated['category_slug'])) {
            $category = Category::where('slug', $validated['category_slug'])->first();
            $validated['category_id'] = $category?->id;
        }

        $validated['slug'] = Str::slug($validated['name']) . '-' . Str::random(6);
        $validated['is_active'] = $request->boolean('is_active');
        $validated['cost_price'] = $validated['cost_price'] ? (int) (round((float) $validated['cost_price'], 2) * 100) : 0;
        $validated['price'] = (int) (round((float) $validated['price'], 2) * 100);
        $validated['tax_rate'] = $validated['tax_rate'] ?? 0;
        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        $validated['min_stock'] = $validated['min_stock'] ?? 5;

        if ($request->hasFile('image')) {
            $validated['image_path'] = $request->file('image')->store('products', 'public');
        }

        Product::create($validated);

        return redirect()->route('admin.codigos.index')->with('success', 'Producto registrado.');
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'sku'           => 'required|string|max:255|unique:products,sku,' . $product->id,
            'barcode'       => 'nullable|string|max:255|unique:products,barcode,' . $product->id,
            'category_slug' => 'nullable|string|max:50',
            'sub_category'  => 'nullable|string|max:255',
            'unit'          => 'nullable|string|max:50',
            'cost_price'    => 'nullable|numeric|min:0',
            'price'         => 'required|numeric|min:0',
            'tax_rate'      => 'nullable|integer|min:0|max:100',
            'stock'         => 'required|integer|min:0',
            'min_stock'     => 'nullable|integer|min:0',
            'is_active'     => 'boolean',
            'sort_order'    => 'nullable|integer|min:0',
            'description'   => 'nullable|string',
            'image'         => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        if (!empty($validated['category_slug'])) {
            $category = Category::where('slug', $validated['category_slug'])->first();
            $validated['category_id'] = $category?->id;
        }

        $validated['is_active'] = $request->boolean('is_active');
        $validated['cost_price'] = $validated['cost_price'] ? (int) (round((float) $validated['cost_price'], 2) * 100) : 0;
        $validated['price'] = (int) (round((float) $validated['price'], 2) * 100);
        $validated['tax_rate'] = $validated['tax_rate'] ?? 0;
        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        $validated['min_stock'] = $validated['min_stock'] ?? 5;

        if ($request->hasFile('image')) {
            if ($product->image_path) {
                Storage::disk('public')->delete($product->image_path);
            }
            $validated['image_path'] = $request->file('image')->store('products', 'public');
        }

        $product->update($validated);

        return redirect()->route('admin.codigos.index')->with('success', 'Producto actualizado.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        if ($product->image_path) {
            Storage::disk('public')->delete($product->image_path);
        }
        $product->delete();

        return redirect()->route('admin.codigos.index')->with('success', 'Producto eliminado.');
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        $import = new ProductsImport();
        Excel::import($import, $request->file('file'));

        $message = 'Importación completada: ';
        $message .= "{$import->created} creados, {$import->updated} actualizados.";

        if (count($import->failures) > 0) {
            $message .= ' Fallos: ' . implode(' | ', array_slice($import->failures, 0, 10));
            if (count($import->failures) > 10) {
                $message .= ' (+' . (count($import->failures) - 10) . ' más)';
            }
        }

        return redirect()->route('admin.codigos.index')
            ->with('success', $message);
    }

    public function addStock(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'quantity'  => 'required|integer|min:1',
            'unit_cost' => 'nullable|numeric|min:0',
            'notes'     => 'nullable|string|max:500',
        ]);

        StockMovement::record(
            $product,
            (int) $validated['quantity'],
            'purchase',
            reference: null,
            variantId: null,
            unitCost: $validated['unit_cost'] !== null ? (int) ((float) $validated['unit_cost'] * 100) : null,
            notes: $validated['notes'] ?? null,
            employeeId: auth()->id(),
        );

        return redirect()->route('admin.codigos.index')
            ->with('success', "Stock añadido: {$validated['quantity']} unidades.");
    }
}
