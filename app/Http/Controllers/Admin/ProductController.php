<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\ProductsImport;
use App\Models\Category;
use App\Models\Loss;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\StockMovement;
use Illuminate\Http\JsonResponse;
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
            ->withCount(['batches as batches_expiring_count' => fn($q) => $q->active()->expiringSoon()])
            ->withCount(['batches as batches_expired_count' => fn($q) => $q->active()->expired()])
            ->when($request->search, fn($q) => $q->search($request->search))
            ->when($request->category_id, function ($q, $id) {
                $category = \App\Models\Category::find($id);
                $ids = $category?->children()->pluck('id')->prepend($category->id) ?? [$id];
                $q->whereIn('category_id', $ids);
            })
            ->orderBy('name')
            ->paginate(30);

        return Inertia::render('admin/codigos', [
            'products' => $products,
            'categories' => Category::ordered()->get(['id', 'name', 'slug', 'parent_id']),
            'categoryTree' => Category::with('children')->roots()->ordered()->get(['id', 'name', 'slug']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'sku'           => 'required|string|max:255|unique:products,sku',
            'barcode'       => 'nullable|string|max:255|unique:products,barcode',
            'category_slug' => 'required|string|max:50|exists:categories,slug',
            'sub_category'  => 'nullable|string|max:255',
            'unit'          => 'nullable|string|max:50',
            'cost_price'    => 'nullable|numeric|min:0',
            'price'         => 'required|numeric|min:0',
            'tax_rate'      => 'nullable|integer|min:0|max:100',
            'stock'         => 'required|integer|min:0',
            'min_stock'     => 'nullable|integer|min:0',
            'is_active'     => 'boolean',
            'is_featured'     => 'boolean',
            'expiration_date' => 'nullable|date',
            'sort_order'    => 'nullable|integer|min:0',
            'description'   => 'nullable|string',
            'image'         => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $category = Category::where('slug', $validated['category_slug'])->firstOrFail();

        if (!empty($validated['sub_category'])) {
            $child = Category::where('parent_id', $category->id)
                ->where('name', $validated['sub_category'])
                ->first();
            $validated['category_id'] = $child?->id ?? $category->id;
        } else {
            $validated['category_id'] = $category->id;
        }
        $validated['category_slug'] = $category->slug;

        $validated['slug'] = Str::slug($validated['name']) . '-' . Str::random(6);
        $validated['is_active'] = $request->boolean('is_active');
        $validated['is_featured'] = $request->boolean('is_featured');
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
            'category_slug' => 'required|string|max:50|exists:categories,slug',
            'sub_category'  => 'nullable|string|max:255',
            'unit'          => 'nullable|string|max:50',
            'cost_price'    => 'nullable|numeric|min:0',
            'price'         => 'required|numeric|min:0',
            'tax_rate'      => 'nullable|integer|min:0|max:100',
            'stock'         => 'required|integer|min:0',
            'min_stock'     => 'nullable|integer|min:0',
            'is_active'     => 'boolean',
            'is_featured'     => 'boolean',
            'expiration_date' => 'nullable|date',
            'sort_order'    => 'nullable|integer|min:0',
            'description'   => 'nullable|string',
            'image'         => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $category = Category::where('slug', $validated['category_slug'])->firstOrFail();

        if (!empty($validated['sub_category'])) {
            $child = Category::where('parent_id', $category->id)
                ->where('name', $validated['sub_category'])
                ->first();
            $validated['category_id'] = $child?->id ?? $category->id;
        } else {
            $validated['category_id'] = $category->id;
        }
        $validated['category_slug'] = $category->slug;

        $validated['is_active'] = $request->boolean('is_active');
        $validated['is_featured'] = $request->boolean('is_featured');
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
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
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

    public function searchSkuAjax(Request $request): JsonResponse
    {
        $search = $request->get('query');

        $product = Product::where('is_active', true)
            ->where(function ($q) use ($search) {
                $q->where('sku', $search)
                  ->orWhere('barcode', $search)
                  ->orWhere('id', $search);
            })
            ->first(['id', 'name', 'sku', 'barcode', 'stock', 'cost_price', 'price', 'image_path']);

        return response()->json($product);
    }

    public function searchNameAjax(Request $request): JsonResponse
    {
        $search = $request->get('query');

        $products = Product::where('is_active', true)
            ->where('name', 'LIKE', "%{$search}%")
            ->limit(5)
            ->get(['id', 'name', 'sku', 'barcode', 'stock', 'cost_price', 'price', 'image_path']);

        return response()->json($products);
    }

    public function addStock(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'quantity'        => 'required|integer|min:1',
            'unit_cost'       => 'nullable|numeric|min:0',
            'notes'           => 'nullable|string|max:500',
            'expiration_date' => 'nullable|date',
        ]);

        if (!empty($validated['expiration_date'])) {
            $product->update(['expiration_date' => $validated['expiration_date']]);
        }

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

        if (!empty($validated['expiration_date'])) {
            $product->batches()->create([
                'quantity'        => (int) $validated['quantity'],
                'cost_price'      => $product->cost_price,
                'expiration_date' => $validated['expiration_date'],
                'received_at'     => now(),
                'notes'           => $validated['notes'] ?? null,
            ]);
        }

        if ($validated['unit_cost'] !== null) {
            $newCost = (int) ((float) $validated['unit_cost'] * 100);
            $product->update(['cost_price' => $newCost]);
        }

        return redirect()->route('admin.codigos.index')
            ->with('success', "Stock añadido: {$validated['quantity']} unidades.");
    }

    public function batches(Product $product): JsonResponse
    {
        return response()->json(
            $product->batches()->active()->get()->map(fn($b) => $this->formatBatch($b))
        );
    }

    public function updateBatch(Request $request, Product $product, ProductBatch $batch): JsonResponse
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:0',
        ]);

        $oldQty = $batch->quantity;
        $newQty = (int) $validated['quantity'];

        if ($newQty === $oldQty) {
            return response()->json($product->batches()->active()->get()->map(fn($b) => $this->formatBatch($b)));
        }

        $diff = $newQty - $oldQty;

        StockMovement::record(
            $product,
            $diff,
            'adjustment',
            reference: $batch,
            unitCost: $batch->cost_price,
            notes: "Ajuste manual de lote #{$batch->id}",
        );

        $batch->update(['quantity' => $newQty]);

        return response()->json($product->batches()->active()->get()->map(fn($b) => $this->formatBatch($b)));
    }

    public function destroyBatch(Product $product, ProductBatch $batch): JsonResponse
    {
        if ($batch->quantity <= 0) {
            return response()->json(['error' => 'El lote ya está inactivo.'], 422);
        }

        $qty = $batch->quantity;

        Loss::create([
            'date'         => now(),
            'product_id'   => $product->id,
            'quantity'     => $qty,
            'cost_at_loss' => $batch->cost_price / 100,
            'reason'       => 'Vencimiento de lote',
        ]);

        StockMovement::record(
            $product,
            -$qty,
            'loss',
            reference: $batch,
            unitCost: $batch->cost_price,
            notes: "Baja por vencimiento de lote #{$batch->id}",
        );

        $batch->update(['quantity' => 0]);

        return response()->json($product->batches()->active()->get()->map(fn($b) => $this->formatBatch($b)));
    }

    private function formatBatch(ProductBatch $batch): array
    {
        return [
            'id'              => $batch->id,
            'quantity'        => $batch->quantity,
            'cost_price'      => $batch->cost_price,
            'expiration_date' => $batch->expiration_date?->format('Y-m-d'),
            'received_at'     => $batch->received_at->format('Y-m-d'),
            'status'          => $batch->status,
            'notes'           => $batch->notes,
        ];
    }
}
