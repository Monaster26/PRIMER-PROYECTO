<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // Obtener categorías por slug
        $cats = Category::pluck('id', 'slug');

        // Mapeo de slugs viejos a slugs nuevos para mantener compatibilidad
        $slugMapping = [
            'lacteos-y-huevos'      => 'lacteos',
            'snacks-y-dulces'       => 'snacks',
            'aseo-del-hogar'        => 'limpieza',
            'cuidado-personal'      => 'perfumeria',
            'granos-y-cereales'     => 'abarrotes',
            'enlatados-y-conservas' => 'abarrotes',
        ];

        $products = [
            // ── Lácteos ──────────────────────────────────────────────
            ['category' => 'lacteos-y-huevos', 'name' => 'Leche Entera Alpina 1L',        'sku' => 'LAC-001', 'barcode' => '7702080005012', 'brand' => 'Alpina',   'unit' => 'und', 'cost' => 350000,  'price' => 480000,  'stock' => 80,  'tax' => 0],
            ['category' => 'lacteos-y-huevos', 'name' => 'Leche Deslactosada Colanta 1L', 'sku' => 'LAC-002', 'barcode' => '7704218100512', 'brand' => 'Colanta',  'unit' => 'und', 'cost' => 380000,  'price' => 510000,  'stock' => 60,  'tax' => 0],
            ['category' => 'lacteos-y-huevos', 'name' => 'Yogurt Fresa Alpina 200g',      'sku' => 'LAC-003', 'barcode' => '7702080011013', 'brand' => 'Alpina',   'unit' => 'und', 'cost' => 150000,  'price' => 230000,  'stock' => 100, 'tax' => 0],
            ['category' => 'lacteos-y-huevos', 'name' => 'Queso Campesino Colanta 500g',  'sku' => 'LAC-004', 'barcode' => '7704218200614', 'brand' => 'Colanta',  'unit' => 'und', 'cost' => 620000,  'price' => 890000,  'stock' => 30,  'tax' => 0],
            ['category' => 'lacteos-y-huevos', 'name' => 'Huevos AA x12 und',            'sku' => 'LAC-005', 'barcode' => '7701035001012', 'brand' => 'La Granja','unit' => 'und', 'cost' => 650000,  'price' => 850000,  'stock' => 50,  'tax' => 0],
            ['category' => 'lacteos-y-huevos', 'name' => 'Mantequilla sin sal 250g',      'sku' => 'LAC-006', 'barcode' => '7702080300515', 'brand' => 'Alpina',   'unit' => 'und', 'cost' => 420000,  'price' => 580000,  'stock' => 40,  'tax' => 0],

            // ── Bebidas ───────────────────────────────────────────────
            ['category' => 'bebidas',           'name' => 'Agua Cristal 600ml',            'sku' => 'BEB-001', 'barcode' => '7702048310016', 'brand' => 'Cristal',   'unit' => 'und', 'cost' => 70000,   'price' => 150000,  'stock' => 200, 'tax' => 19],
            ['category' => 'bebidas',           'name' => 'Coca-Cola Original 350ml',      'sku' => 'BEB-002', 'barcode' => '7702177440017', 'brand' => 'Coca-Cola', 'unit' => 'und', 'cost' => 150000,  'price' => 270000,  'stock' => 150, 'tax' => 19],
            ['category' => 'bebidas',           'name' => 'Jugo Hit Naranja 1L',           'sku' => 'BEB-003', 'barcode' => '7702177440018', 'brand' => 'Hit',       'unit' => 'und', 'cost' => 250000,  'price' => 380000,  'stock' => 90,  'tax' => 0],
            ['category' => 'bebidas',           'name' => 'Postobón Manzana Lata 330ml',   'sku' => 'BEB-004', 'barcode' => '7702038710019', 'brand' => 'Postobón',  'unit' => 'und', 'cost' => 180000,  'price' => 290000,  'stock' => 120, 'tax' => 19],
            ['category' => 'bebidas',           'name' => 'Red Bull Energizante 250ml',    'sku' => 'BEB-005', 'barcode' => '9002490100070', 'brand' => 'Red Bull',  'unit' => 'und', 'cost' => 500000,  'price' => 750000,  'stock' => 60,  'tax' => 19],
            ['category' => 'bebidas',           'name' => 'Café Juan Valdez Colecciónx250g','sku'=> 'BEB-006', 'barcode' => '7702018100031', 'brand' => 'Juan Valdez','unit'=> 'und', 'cost' => 1500000, 'price' => 2200000, 'stock' => 25,  'tax' => 0],

            // ── Snacks ────────────────────────────────────────────────
            ['category' => 'snacks-y-dulces',   'name' => 'Papas Margarita Sal x105g',    'sku' => 'SNK-001', 'barcode' => '7702178110022', 'brand' => 'Margarita', 'unit' => 'und', 'cost' => 180000,  'price' => 290000,  'stock' => 150, 'tax' => 19],
            ['category' => 'snacks-y-dulces',   'name' => 'Chocolatina Jet x16g',         'sku' => 'SNK-002', 'barcode' => '7702178530023', 'brand' => 'Jet',       'unit' => 'und', 'cost' => 80000,   'price' => 150000,  'stock' => 300, 'tax' => 19],
            ['category' => 'snacks-y-dulces',   'name' => 'Gomitas Trolli Sour 200g',     'sku' => 'SNK-003', 'barcode' => '4000512124024', 'brand' => 'Trolli',    'unit' => 'und', 'cost' => 280000,  'price' => 430000,  'stock' => 80,  'tax' => 19],
            ['category' => 'snacks-y-dulces',   'name' => 'Galletas Oreo x154g',          'sku' => 'SNK-004', 'barcode' => '7622300408025', 'brand' => 'Oreo',      'unit' => 'und', 'cost' => 380000,  'price' => 560000,  'stock' => 90,  'tax' => 19],
            ['category' => 'snacks-y-dulces',   'name' => 'Maní Salado x200g',            'sku' => 'SNK-005', 'barcode' => '7702090500026', 'brand' => 'Maicao',    'unit' => 'und', 'cost' => 200000,  'price' => 320000,  'stock' => 70,  'tax' => 19],

            // ── Aseo del Hogar ────────────────────────────────────────
            ['category' => 'aseo-del-hogar',    'name' => 'Detergente Fab x1kg',          'sku' => 'ASE-001', 'barcode' => '7702177100027', 'brand' => 'Fab',       'unit' => 'und', 'cost' => 650000,  'price' => 990000,  'stock' => 60,  'tax' => 19],
            ['category' => 'aseo-del-hogar',    'name' => 'Suavizante Downy 1L',          'sku' => 'ASE-002', 'barcode' => '7702018200028', 'brand' => 'Downy',     'unit' => 'und', 'cost' => 750000,  'price' => 1100000, 'stock' => 45,  'tax' => 19],
            ['category' => 'aseo-del-hogar',    'name' => 'Limpiador Multiusos Fabuloso 1L','sku'=> 'ASE-003','barcode'=> '7501032910039', 'brand' => 'Fabuloso',  'unit' => 'und', 'cost' => 380000,  'price' => 590000,  'stock' => 80,  'tax' => 19],
            ['category' => 'aseo-del-hogar',    'name' => 'Papel Higiénico Scott x4 und', 'sku' => 'ASE-004', 'barcode' => '7702018500030', 'brand' => 'Scott',     'unit' => 'und', 'cost' => 420000,  'price' => 650000,  'stock' => 100, 'tax' => 19],
            ['category' => 'aseo-del-hogar',    'name' => 'Esponja Scotia Doble x2 und',  'sku' => 'ASE-005', 'barcode' => '7702018600031', 'brand' => '3M',        'unit' => 'und', 'cost' => 150000,  'price' => 250000,  'stock' => 120, 'tax' => 19],

            // ── Cuidado Personal ──────────────────────────────────────
            ['category' => 'cuidado-personal',  'name' => 'Shampoo Head & Shoulders 375ml','sku'=> 'CUI-001', 'barcode' => '7501032930041', 'brand' => 'H&S',       'unit' => 'und', 'cost' => 750000,  'price' => 1100000, 'stock' => 50,  'tax' => 19],
            ['category' => 'cuidado-personal',  'name' => 'Jabón Protex Clásico x3 und',  'sku' => 'CUI-002', 'barcode' => '7501032920042', 'brand' => 'Protex',    'unit' => 'und', 'cost' => 380000,  'price' => 580000,  'stock' => 90,  'tax' => 19],
            ['category' => 'cuidado-personal',  'name' => 'Desodorante Axe Phoenix 150ml', 'sku' => 'CUI-003', 'barcode' => '7791293001043', 'brand' => 'Axe',       'unit' => 'und', 'cost' => 550000,  'price' => 800000,  'stock' => 70,  'tax' => 19],
            ['category' => 'cuidado-personal',  'name' => 'Crema Dental Colgate Triple x75ml','sku'=> 'CUI-004','barcode'=> '7501032910044','brand' => 'Colgate',   'unit' => 'und', 'cost' => 320000,  'price' => 500000,  'stock' => 80,  'tax' => 19],

            // ── Granos y Cereales ─────────────────────────────────────
            ['category' => 'granos-y-cereales', 'name' => 'Arroz Roa Diana x1kg',         'sku' => 'GRA-001', 'barcode' => '7702178800045', 'brand' => 'Diana',     'unit' => 'kg',  'cost' => 250000,  'price' => 380000,  'stock' => 200, 'tax' => 0],
            ['category' => 'granos-y-cereales', 'name' => 'Frijol Bola Roja x500g',       'sku' => 'GRA-002', 'barcode' => '7702178800046', 'brand' => 'Suprema',   'unit' => 'und', 'cost' => 280000,  'price' => 420000,  'stock' => 120, 'tax' => 0],
            ['category' => 'granos-y-cereales', 'name' => 'Avena Quaker x400g',           'sku' => 'GRA-003', 'barcode' => '7702014800047', 'brand' => 'Quaker',    'unit' => 'und', 'cost' => 300000,  'price' => 450000,  'stock' => 90,  'tax' => 0],
            ['category' => 'granos-y-cereales', 'name' => 'Azúcar Blanca x1kg',           'sku' => 'GRA-004', 'barcode' => '7702178800048', 'brand' => 'Riopaila',  'unit' => 'kg',  'cost' => 280000,  'price' => 400000,  'stock' => 150, 'tax' => 0],

            // ── Enlatados ─────────────────────────────────────────────
            ['category' => 'enlatados-y-conservas', 'name' => 'Atún en Agua Van Camps x170g','sku'=> 'ENL-001','barcode'=> '7702178900049','brand' => 'Van Camps','unit' => 'und', 'cost' => 250000,  'price' => 380000,  'stock' => 100, 'tax' => 0],
            ['category' => 'enlatados-y-conservas', 'name' => 'Sardinas en Tomate x155g',   'sku'=> 'ENL-002', 'barcode'=> '7702178900050','brand' => 'Conservas','unit' => 'und','cost' => 200000,  'price' => 310000,  'stock' => 80,  'tax' => 0],
        ];

        foreach ($products as $p) {
            $mappedSlug = $slugMapping[$p['category']] ?? $p['category'];
            $catId = $cats[$mappedSlug] ?? null;
            if (!$catId) {
                $this->command->warn("⚠️  Categoría no encontrada: {$p['category']} (mapeada a {$mappedSlug})");
                continue;
            }

            $product = Product::updateOrCreate(
                ['sku' => $p['sku']],
                [
                    'category_id' => $catId,
                    'name'        => $p['name'],
                    'slug'        => Str::slug($p['name']) . '-' . strtolower($p['sku']),
                    'barcode'     => $p['barcode'],
                    'brand'       => $p['brand'],
                    'unit'        => $p['unit'],
                    'cost_price'  => $p['cost'],
                    'price'       => $p['price'],
                    'tax_rate'    => $p['tax'],
                    'stock'       => $p['stock'],
                    'min_stock'   => 10,
                    'max_discount'=> 15,
                    'is_active'   => true,
                ]
            );

            // Registrar stock inicial si es un producto nuevo
            if ($product->wasRecentlyCreated && $p['stock'] > 0) {
                StockMovement::record(
                    product: $product,
                    quantityChange: $p['stock'],
                    type: 'adjustment',
                    notes: 'Stock inicial — Seeder'
                );
            }
        }

        $this->command->info('✅ ' . Product::count() . ' productos sembrados.');
    }
}
