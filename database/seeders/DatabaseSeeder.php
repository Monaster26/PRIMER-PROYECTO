<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Orquestador principal de seeders para Monasterios Mini-Market.
     * Orden de ejecución respeta dependencias de FK.
     */
    public function run(): void
    {
        // Usuario administrador web
        User::firstOrCreate(
            ['email' => 'admin@monasterios.co'],
            ['name'  => 'Administrador Monasterios', 'password' => bcrypt('password')],
        );

        $this->call([
            RoleSeeder::class,           // 0. Roles admin, cashier, web_client
            CategorySeeder::class,       // 1. Categorías (sin FK externas)
            ProductSeeder::class,        // 2. Productos (depende de categorías)
            EmployeeSeeder::class,       // 3. Empleados POS
            CouponSeeder::class,         // 4. Cupones de descuento
            PromotionSeeder::class,      // 5. Promociones Buy X Get Y
            SupplierDataSeeder::class,   // 6. Proveedores reales
        ]);

        $this->command->info('');
        $this->command->info('🛒 ¡Monasterios Mini-Market listo!');
        $this->command->info('──────────────────────────────────────────────');
        $this->command->info('👤 Admin web: admin@monasterios.co / password');
        $this->command->info('🖥️  POS Admin PIN: 1234');
        $this->command->info('🖥️  POS Supervisor PIN: 5678');
        $this->command->info('🖥️  POS Cajero1 PIN: 9012');
        $this->command->info('──────────────────────────────────────────────');
        $this->command->warn('⚠️  Cambia todos los PINs y contraseñas en producción.');
    }
}
