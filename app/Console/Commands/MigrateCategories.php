<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Product;
use Database\Seeders\CategorySeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class MigrateCategories extends Command
{
    protected $signature = 'categories:migrate';
    protected $description = 'Migrate old categories to new structure, preserving all products';

    private array $officialSlugs = [
        'abarrotes', 'bebidas', 'cecinas', 'confites', 'congelados',
        'hogar', 'lacteos', 'limpieza', 'mascotas', 'mundo-bebe',
        'panaderia', 'perfumeria', 'snacks', 'tabaqueria', 'tecnologia',
    ];

    public function handle(): int
    {
        // ── 1. Snapshot old categories before seeding ──────────────
        $this->info('📸 Snapshotting current categories...');
        $oldCats = Category::all(['id', 'name', 'slug', 'parent_id'])->keyBy('id');
        $this->line("   Found {$oldCats->count()} existing categories.");

        // ── 2. Run the seeder ──────────────────────────────────────
        $this->info('🌱 Running CategorySeeder...');
        $this->call(CategorySeeder::class);

        // ── 3. Lookup new categories ──────────────────────────────
        $this->info('🔍 Indexing new categories...');
        $newParents = Category::whereIn('slug', $this->officialSlugs)->get()->keyBy('slug');

        $childId = fn (string $parentSlug, string $childName): ?int => (
            ($parent = $newParents[$parentSlug] ?? null)
                ? Category::where('parent_id', $parent->id)->where('slug', Str::slug($childName))->value('id')
                : null
        );

        // ── 4. Build mapping: old name → new category ID ──────────
        $mapping = [];

        // Viejas raíces que se actualizaron in-place → no necesitan mudanza
        // Pero incluimos todas por claridad (productos ya apuntan bien)

        // Viejas raíces → nuevas raíces
        $mapping['Lácteos y Huevos']    = $newParents['lacteos']->id;
        $mapping['Carnes y Embutidos']  = $newParents['cecinas']->id;
        $mapping['Panadería y Pastelería'] = $newParents['panaderia']->id;
        $mapping['Snacks y Dulces']     = $newParents['snacks']->id;
        $mapping['Aseo del Hogar']      = $newParents['limpieza']->id;
        $mapping['Cuidado Personal']    = $newParents['perfumeria']->id;
        $mapping['EDWAR']               = $newParents['abarrotes']->id;
        $mapping['Sin categoría']       = $newParents['abarrotes']->id;

        // Viejas raíces → nuevas subcategorías
        $mapping['Frutas y Verduras']   = $childId('congelados', 'Frutas y Verduras') ?? $newParents['congelados']->id;
        $mapping['Enlatados y Conservas'] = $childId('abarrotes', 'Enlatados y Granos') ?? $newParents['abarrotes']->id;
        $mapping['Granos y Cereales']   = $childId('abarrotes', 'Enlatados y Granos') ?? $newParents['abarrotes']->id;

        // Viejas subcategorías de Bebidas → nuevas subcategorías
        $mapping['Aguas y Hidratantes'] = $childId('bebidas', 'Aguas y Aguas Saborizadas') ?? $newParents['bebidas']->id;
        $mapping['Jugos y Néctares']    = $childId('bebidas', 'Té y Néctar') ?? $newParents['bebidas']->id;
        $mapping['Energizantes']        = $childId('bebidas', 'Energéticas') ?? $newParents['bebidas']->id;
        $mapping['Café y Té']          = $childId('bebidas', 'Café (Juan Valdez, Marley, Nescafé)') ?? $newParents['bebidas']->id;

        // ── 5. Migrate products ──────────────────────────────────
        $this->info('📦 Migrating products...');
        $totalMigrated = 0;

        foreach ($mapping as $oldName => $newId) {
            $oldCat = $oldCats->firstWhere('name', $oldName);
            if (!$oldCat) {
                $this->warn("   ⚠ Old category '{$oldName}' not found, skipping.");
                continue;
            }

            if ($oldCat->id === $newId) {
                continue; // misma categoría actualizada in-place
            }

            $count = Product::where('category_id', $oldCat->id)->update(['category_id' => $newId]);
            if ($count > 0) {
                $this->line("   ✓ {$count} products: '{$oldName}' → ID {$newId}");
                $totalMigrated += $count;
            }
        }

        $this->info("   Total migrated: {$totalMigrated} products.");

        // ── 6. Cleanup orphan categories ──────────────────────────
        $this->info('🧹 Cleaning up orphan categories...');

        $expectedChildSlugs = [
            'abarrotes'  => ['aceite-y-vinagres','alimento-para-ninos','arroz','azucar-y-endulzantes','cafe-te-e-infusiones','enlatados-y-granos','esencias-y-condimentos','fideos-e-instantaneos','harinas-y-sal','postres-cremas-y-untables','saborizantes-y-leche-en-polvo','salsas-y-mayonesas'],
            'bebidas'    => ['aguas-y-aguas-saborizadas','bebidas-en-polvo','bebidas-saborizadas','deportivas','energeticas','gaseosas','te-y-nectar','cafe-juan-valdez-marley-nescafe'],
            'cecinas'    => ['cecina-por-kilo','embutidos','envasados'],
            'confites'   => ['cereales-avenas-y-granolas','chicles-y-caramelos','chocolates','galletas','golosinas','gomitas'],
            'congelados' => ['frutas-y-verduras','hamburguesas-y-churrascos','helados','pollo'],
            'hogar'      => ['bolsas-de-basura','bolsas-de-regalo','cocina','desechables','encendedores','pilas','utiles','utilitarios','velas'],
            'lacteos'    => ['leches','mantequillas','quesos'],
            'limpieza'   => ['antigrasas-bano-vidrios','aromatizantes','cloros-y-gel','insecticidas','lavado','lavalozas','limpiadores-crema','limpia-pisos','papel-higienico','toallas-y-servilletas','utensilios'],
            'mascotas'   => ['gatos','perros'],
            'mundo-bebe' => ['panales','toallitas'],
            'panaderia'  => ['panes','queques','tortillas-y-wrap'],
            'perfumeria' => ['accesorios','afeitado','algodones-y-alcohol','cosmeticos','cremas-corporales','desodorantes','higiene-bucal','jabon-de-bano','panuelos-y-toallas-desmaquillantes','perfumes','shampoo-y-acondicionador','talco-y-zapatos','tinturas','toallas-y-protectores','tratamientos-capilares'],
            'snacks'     => ['aceitunas','cabritas-y-ramitas','chicharron-y-tocino','de-todito','mani-y-frutos-secos','nachos-y-tortillas','papas','platanitos','takis'],
            'tabaqueria' => ['cigarros','papelillo','tabacos','vaporizadores'],
            'tecnologia' => ['audifonos','cargadores','chip-telefonico'],
        ];

        $allSlugsToKeep = $this->officialSlugs;
        foreach ($expectedChildSlugs as $slugs) {
            $allSlugsToKeep = array_merge($allSlugsToKeep, $slugs);
        }

        $deleted = Category::whereNotIn('slug', $allSlugsToKeep)->get();

        foreach ($deleted as $cat) {
            $remaining = Product::where('category_id', $cat->id)->count();
            if ($remaining > 0) {
                $this->warn("   ⚠ '{$cat->name}' (slug: {$cat->slug}) still has {$remaining} products — skipping delete.");
                continue;
            }
            $cat->delete();
            $this->line("   ✗ Deleted '{$cat->name}' (slug: {$cat->slug})");
        }

        $this->info('✅ Done! ' . Category::count() . ' categories remain.');

        return self::SUCCESS;
    }
}
