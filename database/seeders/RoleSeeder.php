<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $admin = Role::create(['name' => 'admin', 'guard_name' => 'web']);
        Role::create(['name' => 'cashier', 'guard_name' => 'web']);
        Role::create(['name' => 'web_client', 'guard_name' => 'web']);

        $adminUser = User::where('email', 'admin@monasterios.co')->first();
        if ($adminUser) {
            $adminUser->assignRole($admin);
        }
    }
}
