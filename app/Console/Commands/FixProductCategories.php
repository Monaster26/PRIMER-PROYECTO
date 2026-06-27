<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Console\Command;

class FixProductCategories extends Command
{
    protected $signature = 'products:fix-categories';
    protected $description = 'Fix category_id to point to subcategory when sub_category is set';

    public function handle(): int
    {
        $updated = 0;
        $failed = [];

        $products = Product::whereNotNull('sub_category')
            ->where('sub_category', '!=', '')
            ->get();

        $bar = $this->output->createProgressBar($products->count());
        $bar->start();

        foreach ($products as $product) {
            $current = Category::find($product->category_id);
            if ($current && $current->parent_id !== null) {
                $bar->advance();
                continue;
            }

            $child = Category::where('parent_id', $product->category_id)
                ->where('name', $product->sub_category)
                ->first();

            if ($child) {
                $product->update(['category_id' => $child->id]);
                $updated++;
            } else {
                $failed[] = $product->name;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newline(2);

        $this->info("✅ {$updated} productos actualizados.");

        if (count($failed) > 0) {
            $this->warn("⚠️  " . count($failed) . " productos sin coincidencia (revisión manual):");
            foreach ($failed as $name) {
                $this->line("   - {$name}");
            }
        } else {
            $this->info("✅ 0 fallos — todos encontraron su subcategoría.");
        }

        return self::SUCCESS;
    }
}
