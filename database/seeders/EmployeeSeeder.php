<?php

namespace Database\Seeders;

use App\Models\Employee;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        $employees = [
            [
                'name'  => 'Carlos Monasterios',
                'email' => 'admin@monasterios.co',
                'pin'   => '1234',   // PIN de prueba — cambiar en producción
                'role'  => 'admin',
            ],
            [
                'name'  => 'Ana Martínez',
                'email' => 'supervisor@monasterios.co',
                'pin'   => '5678',
                'role'  => 'supervisor',
            ],
            [
                'name'  => 'Luis García',
                'email' => 'cajero1@monasterios.co',
                'pin'   => '9012',
                'role'  => 'cashier',
            ],
            [
                'name'  => 'María López',
                'email' => 'cajera2@monasterios.co',
                'pin'   => '3456',
                'role'  => 'cashier',
            ],
        ];

        foreach ($employees as $emp) {
            Employee::updateOrCreate(
                ['email' => $emp['email']],
                [
                    'name'      => $emp['name'],
                    'email'     => $emp['email'],
                    'pin'       => $emp['pin'], // Se hashea automáticamente por el mutator del modelo
                    'role'      => $emp['role'],
                    'is_active' => true,
                ]
            );
        }

        $this->command->info('✅ ' . Employee::count() . ' empleados sembrados.');
        $this->command->warn('⚠️  PIPs de prueba: Admin=1234 | Supervisor=5678 | Cajero1=9012 | Cajera2=3456');
    }
}
