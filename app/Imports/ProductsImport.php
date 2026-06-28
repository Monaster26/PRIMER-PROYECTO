<?php

namespace App\Imports;

use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Imports\HeadingRowFormatter;
use Maatwebsite\Excel\Validators\Failure;

HeadingRowFormatter::default('none');

class ProductsImport implements ToModel, WithHeadingRow, SkipsEmptyRows, SkipsOnFailure, WithValidation
{
    use Importable;

    public int $created = 0;
    public int $updated = 0;
    public array $failures = [];
    public array $stockChanges = [];

    public function rules(): array
    {
        return [
            'Codigo'       => 'required|string',
            'Descripcion'  => 'required|string|min:1',
            'Precio Venta' => 'required|numeric|min:0',
            'Precio Costo' => 'required|numeric|min:0',
            'Inventario'   => 'nullable|integer|min:0',
            'Inv. Minimo'  => 'nullable|integer|min:0',
            'Categoria'    => 'nullable|string',
            'Subcategoria' => 'nullable|string',
        ];
    }

    public function model(array $row)
    {
        $sku = trim($row['Codigo']);

        $trashed = Product::onlyTrashed()->where('sku', $sku)->first();
        if ($trashed) {
            $this->failures[] = "El producto con SKU {$sku} está eliminado/archivado y no puede ser alterado por Excel.";
            return null;
        }

        $product = Product::firstOrNew(['sku' => $sku]);
        $oldStock = $product->exists ? $product->stock : 0;

        $product->name = trim($row['Descripcion']);
        $product->cost_price = (int) round((float) $row['Precio Costo'] * 100);
        $product->price = (int) round((float) $row['Precio Venta'] * 100);
        $product->stock = (int) ($row['Inventario'] ?? 0);
        $product->min_stock = (int) ($row['Inv. Minimo'] ?? 5);
        $product->sub_category = trim($row['Subcategoria'] ?? $row['Subcategoría'] ?? '');

        $categoryId = $this->resolveCategory(
            trim($row['Categoria'] ?? $row['Categoría'] ?? ''),
            $product->sub_category
        );
        if ($categoryId) {
            $product->category_id = $categoryId;
        }

        if (!$product->exists) {
            $product->sku = $sku;
            $product->barcode = null;
            $product->is_active = true;
            $product->unit = 'und';
            $product->tax_rate = 0;
            $product->sort_order = 0;
            $product->slug = Str::slug($product->name) . '-' . Str::random(6);
            $this->created++;
        } else {
            $this->updated++;
        }

        $diff = $product->stock - $oldStock;
        if ($diff !== 0) {
            $this->stockChanges[] = [
                'product' => $product,
                'diff'    => $diff,
            ];
        }

        return $product;
    }

    public function onFailure(Failure ...$failures)
    {
        foreach ($failures as $failure) {
            $this->failures[] = 'Fila ' . $failure->row() . ': ' . implode(', ', $failure->errors());
        }
    }

    private function resolveCategory(string $categoryName, string $subcategoryName): ?int
    {
        if (empty($categoryName)) return null;

        $normalized = $this->normalizeName($categoryName);
        $parent = Category::whereNull('parent_id')->get()
            ->first(fn($c) => $this->normalizeName($c->name) === $normalized);

        if (!$parent) {
            $parent = Category::create([
                'name' => mb_strtoupper(trim($categoryName)),
                'slug' => Str::slug(trim($categoryName)),
                'is_active' => true,
            ]);
        }

        if (empty($subcategoryName)) return $parent->id;

        $subNormalized = $this->normalizeName($subcategoryName);
        $child = Category::where('parent_id', $parent->id)->get()
            ->first(fn($c) => $this->normalizeName($c->name) === $subNormalized);

        if (!$child) {
            $child = Category::create([
                'name' => trim($subcategoryName),
                'slug' => Str::slug(trim($subcategoryName)),
                'parent_id' => $parent->id,
                'is_active' => true,
            ]);
        }

        return $child->id;
    }

    private function normalizeName(string $name): string
    {
        return mb_strtolower(preg_replace('/\s+/', ' ', trim($name)));
    }
}
