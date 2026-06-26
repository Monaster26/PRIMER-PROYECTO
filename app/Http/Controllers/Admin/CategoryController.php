<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = Category::with('children')
            ->active()
            ->roots()
            ->ordered()
            ->get(['id', 'name', 'slug', 'icon', 'parent_id']);

        return response()->json($categories);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'parent_id' => 'nullable|integer|exists:categories,id',
        ]);

        $slug = Str::slug($validated['name']);
        $base = $slug;
        $i = 1;
        while (Category::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }

        $category = Category::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'parent_id' => $validated['parent_id'] ?? null,
            'is_active' => true,
        ]);

        return response()->json($category->load('children'));
    }

    public function update(Request $request, Category $category): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
        ]);

        $slug = Str::slug($validated['name']);
        $base = $slug;
        $i = 1;
        while (Category::where('slug', $slug)->where('id', '!=', $category->id)->exists()) {
            $slug = $base . '-' . $i++;
        }

        $category->update([
            'name' => $validated['name'],
            'slug' => $slug,
        ]);

        return response()->json($category);
    }

    public function destroy(Category $category): JsonResponse
    {
        try {
            $category->delete();
            return response()->json(['message' => 'Categoría eliminada.']);
        } catch (QueryException $e) {
            if (str_contains($e->getMessage(), 'restrict') || $e->getCode() === '23000') {
                return response()->json([
                    'message' => 'No puedes eliminar una categoría que contiene productos asignados.',
                ], 409);
            }
            throw $e;
        }
    }
}
