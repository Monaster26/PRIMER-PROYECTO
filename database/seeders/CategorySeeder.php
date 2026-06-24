<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Lácteos y Huevos',     'icon' => '🥛', 'sort_order' => 1,  'slug' => 'lacteos-y-huevos'],
            ['name' => 'Frutas y Verduras',     'icon' => '🥦', 'sort_order' => 2,  'slug' => 'frutas-y-verduras'],
            ['name' => 'Carnes y Embutidos',    'icon' => '🥩', 'sort_order' => 3,  'slug' => 'carnes-y-embutidos'],
            ['name' => 'Panadería y Pastelería','icon' => '🍞', 'sort_order' => 4,  'slug' => 'panaderia-y-pasteleria'],
            ['name' => 'Bebidas',               'icon' => '🧃', 'sort_order' => 5,  'slug' => 'bebidas'],
            ['name' => 'Snacks y Dulces',       'icon' => '🍫', 'sort_order' => 6,  'slug' => 'snacks-y-dulces'],
            ['name' => 'Aseo del Hogar',        'icon' => '🧹', 'sort_order' => 7,  'slug' => 'aseo-del-hogar'],
            ['name' => 'Cuidado Personal',      'icon' => '🧴', 'sort_order' => 8,  'slug' => 'cuidado-personal'],
            ['name' => 'Enlatados y Conservas', 'icon' => '🥫', 'sort_order' => 9,  'slug' => 'enlatados-y-conservas'],
            ['name' => 'Granos y Cereales',     'icon' => '🌾', 'sort_order' => 10, 'slug' => 'granos-y-cereales'],
            ['name' => 'ABARROTES',            'icon' => '🥫', 'sort_order' => 11, 'slug' => 'abarrotes'],
            ['name' => 'CECINAS',              'icon' => '🥩', 'sort_order' => 12, 'slug' => 'cecinas'],
            ['name' => 'CONFITES',             'icon' => '🍬', 'sort_order' => 13, 'slug' => 'confites'],
            ['name' => 'CONGELADOS',           'icon' => '❄️', 'sort_order' => 14, 'slug' => 'congelados'],
            ['name' => 'HOGAR',                'icon' => '🏠', 'sort_order' => 15, 'slug' => 'hogar'],
            ['name' => 'LÁCTEOS',              'icon' => '🥛', 'sort_order' => 16, 'slug' => 'lacteos'],
            ['name' => 'LIMPIEZA',             'icon' => '🧼', 'sort_order' => 17, 'slug' => 'limpieza'],
            ['name' => 'MASCOTAS',             'icon' => '🐶', 'sort_order' => 18, 'slug' => 'mascotas'],
            ['name' => 'MUNDO BEBÉ',           'icon' => '👶', 'sort_order' => 19, 'slug' => 'mundo-bebe'],
            ['name' => 'PANADERÍA',            'icon' => '🍞', 'sort_order' => 20, 'slug' => 'panaderia'],
            ['name' => 'PERFUMERÍA',           'icon' => '💄', 'sort_order' => 21, 'slug' => 'perfumeria'],
            ['name' => 'SNACKS',               'icon' => '🍟', 'sort_order' => 22, 'slug' => 'snacks'],
            ['name' => 'TABAQUERÍA',           'icon' => '🚬', 'sort_order' => 23, 'slug' => 'tabaqueria'],
            ['name' => 'TECNOLOGÍA',           'icon' => '🔌', 'sort_order' => 24, 'slug' => 'tecnologia'],
            ['name' => 'Sin categoría',        'icon' => '🏷️', 'sort_order' => 25, 'slug' => 'sin-categoria'],
        ];

        foreach ($categories as $cat) {
            $slug = $cat['slug'] ?? Str::slug($cat['name']);
            Category::updateOrCreate(
                ['slug' => $slug],
                [
                    'name'       => $cat['name'],
                    'slug'       => $slug,
                    'icon'       => $cat['icon'],
                    'sort_order' => $cat['sort_order'],
                    'is_active'  => true,
                ]
            );
        }

        // Subcategorías de Bebidas
        $bebidas = Category::where('slug', 'bebidas')->first();
        if ($bebidas) {
            $subs = ['Aguas y Hidratantes', 'Jugos y Néctares', 'Gaseosas', 'Energizantes', 'Café y Té'];
            foreach ($subs as $i => $sub) {
                Category::updateOrCreate(
                    ['slug' => Str::slug($sub)],
                    [
                        'name'      => $sub,
                        'slug'      => Str::slug($sub),
                        'parent_id' => $bebidas->id,
                        'sort_order'=> $i + 1,
                        'is_active' => true,
                    ]
                );
            }
        }

        $this->command->info('✅ ' . Category::count() . ' categorías sembradas.');
    }
}
