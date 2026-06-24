<?php

namespace App\Imports;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Imports\HeadingRowFormatter;
use Maatwebsite\Excel\Validators\Failure;

HeadingRowFormatter::default('none');

class ProductsImport implements ToModel, WithHeadingRow, SkipsEmptyRows, SkipsOnFailure
{
    use Importable;

    public int $created = 0;
    public int $updated = 0;
    public array $failures = [];

    public function model(array $row)
    {
        $sku = trim($row['Codigo'] ?? '');
        if (empty($sku)) {
            $this->failures[] = 'SKU vacío (columna Codigo) en fila';
            return null;
        }

        $costPrice = $this->parseNumeric($row['Precio Costo'] ?? 0);
        $price = $this->parseNumeric($row['Precio Venta'] ?? 0);

        if ($costPrice === null || $price === null) {
            $this->failures[] = "SKU {$sku}: Precio Costo o Precio Venta no numérico";
            return null;
        }

        $product = Product::firstOrNew(['sku' => $sku]);

        $product->name = trim($row['Descripcion'] ?? '');
        $product->cost_price = (int) round($costPrice * 100);
        $product->price = (int) round($price * 100);
        $product->stock = (int) ($row['Inventario'] ?? 0);
        $product->min_stock = (int) ($row['Inv. Minimo'] ?? 5);

        $deptName = trim($row['Departamento'] ?? '');
        if (empty($deptName)) {
            $deptName = 'Sin categoría';
        }
        $dept = $this->findOrCreateCategory($deptName);
        if ($dept['id']) {
            $product->category_id = $dept['id'];
            $product->category_slug = $dept['slug'];
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

        return $product;
    }

    public function onFailure(Failure ...$failures)
    {
        foreach ($failures as $failure) {
            $this->failures[] = 'Fila ' . $failure->row() . ': ' . implode(', ', $failure->errors());
        }
    }

    private function parseNumeric(mixed $value): ?float
    {
        if (is_numeric($value)) {
            return (float) $value;
        }
        $cleaned = preg_replace('/[^0-9.,\-]/', '', (string) $value);
        $cleaned = str_replace(',', '.', $cleaned);
        if (is_numeric($cleaned)) {
            return (float) $cleaned;
        }
        return null;
    }

    private function findOrCreateCategory(string $name): array
    {
        $slug = Str::slug($name);
        if (empty($slug)) {
            return ['id' => null, 'slug' => null];
        }
        $category = Category::where('slug', $slug)->first();
        if ($category) {
            return ['id' => $category->id, 'slug' => $category->slug];
        }
        $category = Category::create([
            'name' => $name,
            'slug' => $slug,
            'is_active' => true,
        ]);
        return ['id' => $category->id, 'slug' => $category->slug];
    }
}
