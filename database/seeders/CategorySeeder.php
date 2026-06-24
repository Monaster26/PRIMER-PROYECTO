<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $parents = [
            ['name' => 'ABARROTES',    'icon' => '🥫', 'sort_order' => 1],
            ['name' => 'BEBIDAS',      'icon' => '🥤', 'sort_order' => 2],
            ['name' => 'CECINAS',      'icon' => '🥩', 'sort_order' => 3],
            ['name' => 'CONFITES',     'icon' => '🍬', 'sort_order' => 4],
            ['name' => 'CONGELADOS',   'icon' => '❄️', 'sort_order' => 5],
            ['name' => 'HOGAR',        'icon' => '🏠', 'sort_order' => 6],
            ['name' => 'LÁCTEOS',      'icon' => '🥛', 'sort_order' => 7],
            ['name' => 'LIMPIEZA',     'icon' => '🧹', 'sort_order' => 8],
            ['name' => 'MASCOTAS',     'icon' => '🐾', 'sort_order' => 9],
            ['name' => 'MUNDO BEBÉ',   'icon' => '👶', 'sort_order' => 10],
            ['name' => 'PANADERÍA',    'icon' => '🍞', 'sort_order' => 11],
            ['name' => 'PERFUMERÍA',   'icon' => '🧴', 'sort_order' => 12],
            ['name' => 'SNACKS',       'icon' => '🍿', 'sort_order' => 13],
            ['name' => 'TABAQUERÍA',   'icon' => '🚬', 'sort_order' => 14],
            ['name' => 'TECNOLOGÍA',   'icon' => '🔌', 'sort_order' => 15],
        ];

        $children = [
            'ABARROTES' => [
                'Aceite y Vinagres', 'Alimento para Niños', 'Arroz',
                'Azúcar y Endulzantes', 'Café, Té e Infusiones',
                'Enlatados y Granos', 'Esencias y Condimentos',
                'Fideos e Instantáneos', 'Harinas y Sal',
                'Postres, Cremas y Untables', 'Saborizantes y Leche en Polvo',
                'Salsas y Mayonesas',
            ],
            'BEBIDAS' => [
                'Aguas y Aguas Saborizadas', 'Bebidas en Polvo',
                'Bebidas Saborizadas', 'Deportivas', 'Energéticas',
                'Gaseosas', 'Té y Néctar',
                'Café (Juan Valdez, Marley, Nescafé)',
            ],
            'CECINAS' => [
                'Cecina por Kilo', 'Embutidos', 'Envasados',
            ],
            'CONFITES' => [
                'Cereales, Avenas y Granolas', 'Chicles y Caramelos',
                'Chocolates', 'Galletas', 'Golosinas', 'Gomitas',
            ],
            'CONGELADOS' => [
                'Frutas y Verduras', 'Hamburguesas y Churrascos',
                'Helados', 'Pollo',
            ],
            'HOGAR' => [
                'Bolsas de Basura', 'Bolsas de Regalo', 'Cocina',
                'Desechables', 'Encendedores', 'Pilas', 'Útiles',
                'Utilitarios', 'Velas',
            ],
            'LÁCTEOS' => [
                'Leches', 'Mantequillas', 'Quesos',
            ],
            'LIMPIEZA' => [
                'Antigrasas, Baño, Vidrios', 'Aromatizantes',
                'Cloros y Gel', 'Insecticidas', 'Lavado', 'Lavalozas',
                'Limpiadores Crema', 'Limpia Pisos', 'Papel Higiénico',
                'Toallas y Servilletas', 'Utensilios',
            ],
            'MASCOTAS' => [
                'Gatos', 'Perros',
            ],
            'MUNDO BEBÉ' => [
                'Pañales', 'Toallitas',
            ],
            'PANADERÍA' => [
                'Panes', 'Queques', 'Tortillas y Wrap',
            ],
            'PERFUMERÍA' => [
                'Accesorios', 'Afeitado', 'Algodones y Alcohol',
                'Cosméticos', 'Cremas Corporales', 'Desodorantes',
                'Higiene Bucal', 'Jabón de Baño',
                'Pañuelos y Toallas Desmaquillantes', 'Perfumes',
                'Shampoo y Acondicionador', 'Talco y Zapatos',
                'Tinturas', 'Toallas y Protectores',
                'Tratamientos Capilares',
            ],
            'SNACKS' => [
                'Aceitunas', 'Cabritas y Ramitas',
                'Chicharrón y Tocino', 'De Todito',
                'Maní y Frutos Secos', 'Nachos y Tortillas',
                'Papas', 'Platanitos', 'Takis',
            ],
            'TABAQUERÍA' => [
                'Cigarros', 'Papelillo', 'Tabacos', 'Vaporizadores',
            ],
            'TECNOLOGÍA' => [
                'Audífonos', 'Cargadores', 'Chip Telefónico',
            ],
        ];

        foreach ($parents as $i => $parent) {
            $cat = Category::updateOrCreate(
                ['slug' => Str::slug($parent['name'])],
                [
                    'name'       => $parent['name'],
                    'icon'       => $parent['icon'],
                    'sort_order' => $parent['sort_order'],
                    'is_active'  => true,
                ]
            );

            foreach ($children[$parent['name']] ?? [] as $j => $childName) {
                Category::updateOrCreate(
                    ['slug' => Str::slug($childName)],
                    [
                        'name'       => $childName,
                        'parent_id'  => $cat->id,
                        'sort_order' => $j + 1,
                        'is_active'  => true,
                    ]
                );
            }
        }

        if ($this->command) {
            $this->command->info('✅ ' . Category::count() . ' categorías sembradas.');
        }
    }
}
