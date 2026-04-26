<?php

namespace Database\Seeders;

use App\Models\Coupon;
use Illuminate\Database\Seeder;

class CouponSeeder extends Seeder
{
    public function run(): void
    {
        $coupons = [
            [
                'code'                  => 'BIENVENIDO10',
                'description'           => 'Descuento de bienvenida del 10% para nuevos clientes.',
                'type'                  => 'percentage',
                'value'                 => 10,
                'min_order_amount'      => 500000, // $5.000 COP
                'max_discount_amount'   => 2000000, // máx $20.000
                'max_uses'              => 500,
                'max_uses_per_customer' => 1,
                'is_active'             => true,
                'expires_at'            => now()->addMonths(6),
            ],
            [
                'code'                  => 'AHORRA20',
                'description'           => '20% de descuento en compras mayores a $30.000.',
                'type'                  => 'percentage',
                'value'                 => 20,
                'min_order_amount'      => 3000000, // $30.000 COP
                'max_discount_amount'   => 5000000, // máx $50.000
                'max_uses'              => 200,
                'max_uses_per_customer' => 2,
                'is_active'             => true,
                'expires_at'            => now()->addMonths(3),
            ],
            [
                'code'                  => 'DELIVERY5000',
                'description'           => '$5.000 de descuento fijo en tu próxima compra.',
                'type'                  => 'fixed',
                'value'                 => 500000, // $5.000 en centavos
                'min_order_amount'      => 2000000, // $20.000
                'max_uses'              => 100,
                'max_uses_per_customer' => 1,
                'is_active'             => true,
                'expires_at'            => now()->addMonth(),
            ],
            [
                'code'                  => 'VIP30',
                'description'           => '30% exclusivo para clientes VIP.',
                'type'                  => 'percentage',
                'value'                 => 30,
                'min_order_amount'      => 5000000, // $50.000
                'max_discount_amount'   => 10000000,
                'max_uses'              => null, // ilimitado
                'max_uses_per_customer' => 3,
                'is_active'             => true,
                'expires_at'            => now()->addYear(),
            ],
            [
                'code'                  => 'LIQUIDACION',
                'description'           => 'Cupón de liquidación de fin de mes — 15% de descuento.',
                'type'                  => 'percentage',
                'value'                 => 15,
                'min_order_amount'      => 1000000, // $10.000
                'max_uses'              => 50,
                'max_uses_per_customer' => 1,
                'is_active'             => false, // inactivo hasta activar manualmente
                'expires_at'            => now()->endOfMonth(),
            ],
        ];

        foreach ($coupons as $coupon) {
            Coupon::updateOrCreate(
                ['code' => $coupon['code']],
                $coupon
            );
        }

        $this->command->info('✅ ' . Coupon::count() . ' cupones sembrados.');
    }
}
