<?php

namespace Database\Seeders;

use App\Models\Promotion;
use Illuminate\Database\Seeder;

class PromotionSeeder extends Seeder
{
    public function run(): void
    {
        $promotions = [
            [
                'name'        => '2x1 en Chocolatina Jet',
                'description' => 'Compra 2 Chocolatinas Jet y lleva 1 gratis.',
                'type'        => 'buy_x_get_y',
                'conditions'  => ['buy_product_id' => null, 'buy_product_sku' => 'SNK-002', 'buy_qty' => 2],
                'rewards'     => ['get_product_sku' => 'SNK-002', 'get_qty' => 1, 'discount_pct' => 100],
                'priority'    => 10,
                'is_active'   => true,
                'is_exclusive'=> false,
                'expires_at'  => now()->addMonths(2),
            ],
            [
                'name'        => 'Pack Hidratación: 6 Aguas al 20%',
                'description' => 'Compra 6 o más Aguas Cristal y obtén el 20% de descuento.',
                'type'        => 'min_qty_discount',
                'conditions'  => ['product_sku' => 'BEB-001', 'min_qty' => 6, 'discount_pct' => 20],
                'rewards'     => [],
                'priority'    => 8,
                'is_active'   => true,
                'is_exclusive'=> false,
                'expires_at'  => now()->addMonths(1),
            ],
            [
                'name'        => 'Combo Desayuno: Leche + Huevos + Avena',
                'description' => 'Lleva los 3 productos del combo y obtén 15% de descuento.',
                'type'        => 'bundle_discount',
                'conditions'  => ['product_skus' => ['LAC-001', 'LAC-005', 'GRA-003'], 'discount_pct' => 15],
                'rewards'     => [],
                'priority'    => 5,
                'is_active'   => true,
                'is_exclusive'=> false,
                'expires_at'  => now()->addMonths(3),
            ],
        ];

        foreach ($promotions as $promo) {
            Promotion::create($promo);
        }

        $this->command->info('✅ ' . Promotion::count() . ' promociones sembradas.');
    }
}
