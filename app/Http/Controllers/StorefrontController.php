<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StorefrontController extends Controller
{
    /**
     * Página pública de vitrina por categoría o subcategoría.
     *
     * Rutas:
     *   GET /categoria/{categorySlug}
     *   GET /categoria/{categorySlug}/{subcategorySlug}
     */
    public function byCategory(
        Request $request,
        string $categorySlug,
        ?string $subcategorySlug = null
    ): Response {

        // ── 1. Resolver la categoría raíz ─────────────────────────────
        $rootCategory = Category::with('children')
            ->whereNull('parent_id')
            ->where('slug', $categorySlug)
            ->firstOrFail();

        // ── 2. Resolver subcategoría activa (opcional) ────────────────
        $activeSubcategory = null;
        if ($subcategorySlug) {
            $activeSubcategory = Category::where('slug', $subcategorySlug)
                ->where('parent_id', $rootCategory->id)
                ->firstOrFail();
        }

        // ── 3. Determinar el category_id al que filtrar ───────────────
        //    Si hay subcategoría → filtramos por ella,
        //    si no → filtramos por todos los hijos de la raíz + la raíz.
        $filterIds = $activeSubcategory
            ? [$activeSubcategory->id]
            : $rootCategory->children->pluck('id')->push($rootCategory->id)->all();

        // ── 4. Query de productos ─────────────────────────────────────
        $sort    = $request->input('sort', 'name');
        $sortDir = in_array($request->input('dir'), ['asc', 'desc']) ? $request->input('dir') : 'asc';

        $allowedSorts = ['name', 'price', 'stock', 'created_at'];
        if (!in_array($sort, $allowedSorts)) {
            $sort = 'name';
        }

        $products = Product::with(['category'])
            ->active()
            ->whereIn('category_id', $filterIds)
            ->when($request->input('q'), fn($q, $term) => $q->search($term))
            ->orderBy($sort, $sortDir)
            ->paginate(20)
            ->withQueryString()
            ->through(fn($p) => [
                'id'          => $p->id,
                'name'        => $p->name,
                'slug'        => $p->slug,
                'sku'         => $p->sku,
                'price'       => $p->price,
                'price_cents' => $p->price,
                'stock'       => $p->effective_stock,
                'is_low_stock'=> $p->is_low_stock,
                'image_url'   => $p->image_path
                                    ? asset('storage/' . $p->image_path)
                                    : null,
                'category'    => $p->category ? ['id' => $p->category->id, 'name' => $p->category->name] : null,
                'description' => $p->description,
                'brand'       => $p->brand,
                'unit'        => $p->unit,
            ]);

        // ── 5. Árbol completo de categorías para el sidebar ───────────
        $allCategories = Category::with('children')
            ->whereNull('parent_id')
            ->ordered()
            ->get(['id', 'name', 'slug', 'icon', 'parent_id']);

        return Inertia::render('CategoryView', [
            'rootCategory'      => [
                'id'       => $rootCategory->id,
                'name'     => $rootCategory->name,
                'slug'     => $rootCategory->slug,
                'icon'     => $rootCategory->icon,
                'children' => $rootCategory->children->map(fn($c) => [
                    'id'   => $c->id,
                    'name' => $c->name,
                    'slug' => $c->slug,
                ]),
            ],
            'activeSubcategory' => $activeSubcategory ? [
                'id'   => $activeSubcategory->id,
                'name' => $activeSubcategory->name,
                'slug' => $activeSubcategory->slug,
            ] : null,
            'products'          => $products,
            'allCategories'     => $allCategories,
            'filters'           => $request->only(['q', 'sort', 'dir']),
        ]);
    }
}
