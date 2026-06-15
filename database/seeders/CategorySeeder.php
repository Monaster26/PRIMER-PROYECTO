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
            [
                'id' => 1,
                'name' => 'ABARROTES',
                'emoji' => '🥫',
                'icon' => 'Package',
                'subcategories' => ['Aceite y Vinagres', 'Alimento para Niños', 'Arroz', 'Azúcar y Endulzantes', 'Café, Té e Infusiones', 'Enlatados y Granos', 'Esencias y Condimentos', 'Fideos e Instantáneos', 'Harinas y Sal', 'Postres, Cremas y Untables', 'Saborizantes y Leche en Polvo', 'Salsas y Mayonesas'],
            ],
            [
                'id' => 2,
                'name' => 'BEBIDAS',
                'emoji' => '🥤',
                'icon' => 'CupSoda',
                'subcategories' => ['Aguas y Aguas Saborizadas', 'Bebidas en Polvo', 'Bebidas Saborizadas', 'Deportivas', 'Energéticas', 'Gaseosas', 'Té y Néctar', 'Café (Juan Valdez, Marley, Nescafé)'],
            ],
            [
                'id' => 3,
                'name' => 'CECINAS',
                'emoji' => '🥩',
                'icon' => 'Beef',
                'subcategories' => ['Cecina por Kilo', 'Embutidos', 'Envasados'],
            ],
            [
                'id' => 4,
                'name' => 'CONFITES',
                'emoji' => '🍬',
                'icon' => 'Candy',
                'subcategories' => ['Cereales, Avenas y Granolas', 'Chicles y Caramelos', 'Chocolates', 'Galletas', 'Golosinas', 'Gomitas'],
            ],
            [
                'id' => 5,
                'name' => 'CONGELADOS',
                'emoji' => '❄️',
                'icon' => 'Snowflake',
                'subcategories' => ['Frutas y Verduras', 'Hamburguesas y Churrascos', 'Helados', 'Pollo'],
            ],
            [
                'id' => 6,
                'name' => 'HOGAR',
                'emoji' => '🏠',
                'icon' => 'Home',
                'subcategories' => ['Bolsas de Basura', 'Bolsas de Regalo', 'Cocina', 'Desechables', 'Encendedores', 'Pilas', 'Útiles', 'Utilitarios', 'Velas'],
            ],
            [
                'id' => 7,
                'name' => 'LÁCTEOS',
                'emoji' => '🥛',
                'icon' => 'Milk',
                'subcategories' => ['Leches', 'Mantequillas', 'Quesos'],
            ],
            [
                'id' => 8,
                'name' => 'LIMPIEZA',
                'emoji' => '🧼',
                'icon' => 'Sparkles',
                'subcategories' => ['Antigrasas, Baño, Vidrios', 'Aromatizantes', 'Cloros y Gel', 'Insecticidas', 'Lavado', 'Lavalozas', 'Limpiadores Crema', 'Limpia Pisos', 'Papel Higiénico', 'Toallas y Servilletas', 'Utensilios'],
            ],
            [
                'id' => 9,
                'name' => 'MASCOTAS',
                'emoji' => '🐶',
                'icon' => 'Dog',
                'subcategories' => ['Gatos', 'Perros'],
            ],
            [
                'id' => 10,
                'name' => 'MUNDO BEBÉ',
                'emoji' => '👶',
                'icon' => 'Baby',
                'subcategories' => ['Pañales', 'Toallitas'],
            ],
            [
                'id' => 11,
                'name' => 'PANADERÍA',
                'emoji' => '🍞',
                'icon' => 'Croissant',
                'subcategories' => ['Panes', 'Queques', 'Tortillas y Wrap'],
            ],
            [
                'id' => 12,
                'name' => 'PERFUMERÍA',
                'emoji' => '💄',
                'icon' => 'Flower2',
                'subcategories' => ['Accesorios', 'Afeitado', 'Algodones y Alcohol', 'Cosméticos', 'Cremas Corporales', 'Desodorantes', 'Higiene Bucal', 'Jabón de Baño', 'Pañuelos y Toallas Desmaquillantes', 'Perfumes', 'Shampoo y Acondicionador', 'Talco y Zapatos', 'Tinturas', 'Toallas y Protectores', 'Tratamientos Capilares'],
            ],
            [
                'id' => 13,
                'name' => 'SNACKS',
                'emoji' => '🍟',
                'icon' => 'Cookie',
                'subcategories' => ['Aceitunas', 'Cabritas y Ramitas', 'Chicharrón y Tocino', 'De Todito', 'Maní y Frutos Secos', 'Nachos y Tortillas', 'Papas', 'Platanitos', 'Takis'],
            ],
            [
                'id' => 14,
                'name' => 'TABAQUERÍA',
                'emoji' => '🚬',
                'icon' => 'Cigarette',
                'subcategories' => ['Cigarros', 'Papelillo', 'Tabacos', 'Vaporizadores'],
            ],
            [
                'id' => 15,
                'name' => 'TECNOLOGÍA',
                'emoji' => '🔌',
                'icon' => 'Cpu',
                'subcategories' => ['Audífonos', 'Cargadores', 'Chip Telefónico'],
            ],
        ];

        foreach ($categories as $cat) {
            Category::updateOrCreate(
                ['id' => $cat['id']],
                [
                    'name'          => $cat['name'],
                    'slug'          => Str::slug($cat['name']),
                    'emoji'         => $cat['emoji'],
                    'icon'          => $cat['icon'],
                    'subcategories' => $cat['subcategories'],
                    'active'        => true,
                ]
            );
        }

        $this->command->info('✅ ' . Category::count() . ' categorías sembradas.');
    }
}
