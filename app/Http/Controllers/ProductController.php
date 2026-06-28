<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    /**
     * Listado paginado con filtros avanzados.
     */
    public function index(Request $request): Response
    {
        $query = Product::with(['category'])
            ->when($request->search, fn($q) => $q->search($request->search))
            ->when($request->category_id, fn($q) => $q->where('category_id', $request->category_id))
            ->when($request->boolean('low_stock'), fn($q) => $q->lowStock())
            ->when($request->boolean('inactive'), fn($q) => $q->where('is_active', false), fn($q) => $q->active())
            ->orderBy($request->sort_by ?? 'name', $request->sort_dir ?? 'asc');

        return Inertia::render('Products/Index', [
            'products'   => $query->paginate(20)->withQueryString(),
            'categories' => Category::active()->ordered()->get(['id', 'name']),
            'filters'    => $request->only(['search', 'category_id', 'low_stock', 'inactive']),
        ]);
    }

    /**
     * Muestra el formulario de creación.
     */
    public function create(): Response
    {
        return Inertia::render('Products/Form', [
            'categories' => Category::active()->ordered()->get(['id', 'name']),
            'product'    => null,
        ]);
    }

    /**
     * Almacena un nuevo producto.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id'  => 'required|exists:categories,id',
            'name'         => 'required|string|max:255',
            'description'  => 'nullable|string',
            'sku'          => 'required|string|max:100|unique:products,sku',
            'barcode'      => 'nullable|string|max:30|unique:products,barcode',
            'brand'        => 'nullable|string|max:100',
            'unit'         => 'required|in:und,kg,lt,ml,g,lb',
            'cost_price'   => 'nullable|numeric|min:0',
            'price'        => 'required|numeric|min:0',
            'tax_rate'     => 'required|in:0,5,19',
            'stock'        => 'required|integer|min:0',
            'min_stock'    => 'required|integer|min:0',
            'max_discount' => 'required|integer|min:0|max:100',
            'is_active'    => 'boolean',
            'sort_order'   => 'nullable|integer',
            'sub_category' => 'nullable|string|max:100',
            'image'        => 'nullable|image|max:5120',
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('products', 'public');
            $validated['image_path'] = $path;
        }

        $validated['cost_price'] = $validated['cost_price'] ? (int) (round((float) $validated['cost_price'], 2) * 100) : 0;
        $validated['price'] = (int) (round((float) $validated['price'], 2) * 100);
        $validated['slug'] = Str::slug($validated['name']) . '-' . Str::random(4);

        $product = Product::create($validated);

        return redirect()->back()
            ->with('success', 'Producto creado exitosamente.');
    }

    /**
     * Detalle de producto con variantes y movimientos recientes.
     */
    public function show(Product $product): Response
    {
        $product->load(['category', 'variants']);

        return Inertia::render('Products/Show', [
            'product'          => $product,
            'recentMovements'  => $product->stockMovements()
                ->with('employee')
                ->latest()
                ->limit(20)
                ->get(),
        ]);
    }

    /**
     * Formulario de edición.
     */
    public function edit(Product $product): Response
    {
        return Inertia::render('Products/Form', [
            'categories' => Category::active()->ordered()->get(['id', 'name']),
            'product'    => $product->load('variants'),
        ]);
    }

    /**
     * Actualiza el producto.
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'category_id'  => 'required|exists:categories,id',
            'name'         => 'required|string|max:255',
            'description'  => 'nullable|string',
            'sku'          => "required|string|max:100|unique:products,sku,{$product->id}",
            'barcode'      => "nullable|string|max:30|unique:products,barcode,{$product->id}",
            'brand'        => 'nullable|string|max:100',
            'unit'         => 'required|in:und,kg,lt,ml,g,lb',
            'cost_price'   => 'nullable|numeric|min:0',
            'price'        => 'required|numeric|min:0',
            'tax_rate'     => 'required|in:0,5,19',
            'min_stock'    => 'required|integer|min:0',
            'max_discount' => 'required|integer|min:0|max:100',
            'is_active'    => 'boolean',
            'sort_order'   => 'nullable|integer',
            'stock'        => 'required|integer|min:0',
            'sub_category' => 'nullable|string|max:100',
            'image'        => 'nullable|image|max:5120',
        ]);

        if ($request->hasFile('image')) {
            // Delete old image
            if ($product->image_path) {
                Storage::disk('public')->delete($product->image_path);
            }
            $path = $request->file('image')->store('products', 'public');
            $validated['image_path'] = $path;
        }

        $validated['cost_price'] = $validated['cost_price'] ? (int) (round((float) $validated['cost_price'], 2) * 100) : 0;
        $validated['price'] = (int) (round((float) $validated['price'], 2) * 100);

        $oldStock = $product->stock;
        $product->update($validated);

        if ($product->wasChanged('stock')) {
            StockMovement::record(
                product: $product,
                quantityChange: $product->stock - $oldStock,
                type: 'adjustment',
                notes: "Corrección manual: stock {$oldStock} → {$product->stock}",
            );
        }

        return redirect()->route('products.show', $product)
            ->with('success', 'Producto actualizado exitosamente.');
    }

    /**
     * Eliminación lógica (SoftDelete).
     */
    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('products.index')
            ->with('success', 'Producto eliminado.');
    }

    /**
     * Filtrado público de productos para la vitrina de categorías.
     * GET /api/products/filter?category_slug=bebidas&subcategory_slug=gaseosas
     */
    public function filter(Request $request)
    {
        $request->validate([
            'category_slug'    => 'nullable|string|max:100',
            'subcategory_slug' => 'nullable|string|max:100',
            'q'                => 'nullable|string|max:100',
            'sort'             => 'nullable|in:name,price,created_at',
            'dir'              => 'nullable|in:asc,desc',
            'per_page'         => 'nullable|integer|min:4|max:100',
        ]);

        $query = Product::with('category')->active();

        // Prioridad: subcategoría > categoría raíz
        if ($slug = $request->input('subcategory_slug')) {
            $cat = \App\Models\Category::where('slug', $slug)->first();
            if ($cat) $query->where('category_id', $cat->id);
        } elseif ($slug = $request->input('category_slug')) {
            $root = \App\Models\Category::with('children')
                ->whereNull('parent_id')
                ->where('slug', $slug)
                ->first();
            if ($root) {
                $ids = $root->children->pluck('id')->push($root->id)->all();
                $query->whereIn('category_id', $ids);
            }
        }

        if ($term = $request->input('q')) {
            $query->search($term);
        }

        $sort    = in_array($request->input('sort'), ['name', 'price', 'created_at'])
                    ? $request->input('sort') : 'name';
        $sortDir = $request->input('dir', 'asc');
        $perPage = (int) $request->input('per_page', 20);

        $paginated = $query->orderBy($sort, $sortDir)->paginate($perPage)->withQueryString();

        return response()->json([
            'data' => $paginated->getCollection()->map(fn($p) => [
                'id'           => $p->id,
                'name'         => $p->name,
                'slug'         => $p->slug,
                'sku'          => $p->sku,
                'price'        => $p->price,
                'stock'        => $p->effective_stock,
                'is_low_stock' => $p->is_low_stock,
                'image_url'    => $p->image_path ? asset('storage/' . $p->image_path) : null,
                'category'     => $p->category ? ['id' => $p->category->id, 'name' => $p->category->name] : null,
                'description'  => $p->description,
                'brand'        => $p->brand,
                'unit'         => $p->unit,
            ]),
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'last_page'    => $paginated->lastPage(),
                'total'        => $paginated->total(),
                'per_page'     => $paginated->perPage(),
            ],
            'links' => [
                'prev' => $paginated->previousPageUrl(),
                'next' => $paginated->nextPageUrl(),
            ],
        ]);
    }

    /**
     * Búsqueda rápida por SKU o barcode (para el POS).
     */
    public function lookup(Request $request)
    {
        $request->validate(['q' => 'required|string|min:2']);

        $product = Product::with('variants')
            ->active()
            ->where(function ($query) use ($request) {
                $query->where('barcode', $request->q)
                      ->orWhere('sku', $request->q);
            })
            ->first();

        if (!$product) {
            return response()->json(['found' => false, 'message' => 'Producto no encontrado.'], 404);
        }

        return response()->json([
            'found'   => true,
            'product' => array_merge($product->toArray(), [
                'price_formatted'     => $product->price_formatted,
                'effective_stock'     => $product->effective_stock,
                'is_low_stock'        => $product->is_low_stock,
            ]),
        ]);
    }
}
