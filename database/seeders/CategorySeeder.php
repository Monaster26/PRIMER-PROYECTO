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
            ['name' => 'Lácteos y Huevos',     'icon' => '🥛', 'sort_order' => 1],
            ['name' => 'Frutas y Verduras',     'icon' => '🥦', 'sort_order' => 2],
            ['name' => 'Carnes y Embutidos',    'icon' => '🥩', 'sort_order' => 3],
            ['name' => 'Panadería y Pastelería','icon' => '🍞', 'sort_order' => 4],
            ['name' => 'Bebidas',               'icon' => '🧃', 'sort_order' => 5],
            ['name' => 'Snacks y Dulces',       'icon' => '🍫', 'sort_order' => 6],
            ['name' => 'Aseo del Hogar',        'icon' => '🧹', 'sort_order' => 7],
            ['name' => 'Cuidado Personal',      'icon' => '🧴', 'sort_order' => 8],
            ['name' => 'Enlatados y Conservas', 'icon' => '🥫', 'sort_order' => 9],
            ['name' => 'Granos y Cereales',     'icon' => '🌾', 'sort_order' => 10],
        ];

        foreach ($categories as $cat) {
            Category::updateOrCreate(
                ['slug' => Str::slug($cat['name'])],
                [
                    'name'       => $cat['name'],
                    'slug'       => Str::slug($cat['name']),
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
