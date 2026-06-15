<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    /**
     * Muestra las categorías o las retorna como JSON si es una petición API.
     */
    public function index(Request $request)
    {
        if ($request->wantsJson() || $request->is('api/*')) {
            $categories = Category::active()->ordered()->get()->map(function ($cat) {
                return [
                    'id'            => (string) $cat->id,
                    'name'          => $cat->name,
                    'emoji'         => $cat->emoji,
                    'icon'          => $cat->icon,
                    'subcategories' => $cat->subcategories ?? [],
                ];
            });
            return response()->json($categories);
        }

        return Inertia::render('Categories/Index', [
            'categories' => Category::ordered()->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:100',
            'emoji'         => 'nullable|string|max:10',
            'icon'          => 'nullable|string|max:50',
            'active'        => 'boolean',
            'subcategories' => 'nullable|array',
        ]);

        $validated['slug'] = Str::slug($validated['name']) . '-' . Str::random(4);

        Category::create($validated);

        return redirect()->route('categories.index')->with('success', 'Categoría creada.');
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:100',
            'emoji'         => 'nullable|string|max:10',
            'icon'          => 'nullable|string|max:50',
            'active'        => 'boolean',
            'subcategories' => 'nullable|array',
        ]);

        $category->update($validated);

        return back()->with('success', 'Categoría actualizada.');
    }

    public function destroy(Category $category)
    {
        if ($category->products()->exists()) {
            return back()->withErrors(['error' => 'No se puede eliminar una categoría con productos.']);
        }
        $category->delete();
        return redirect()->route('categories.index')->with('success', 'Categoría eliminada.');
    }
}
