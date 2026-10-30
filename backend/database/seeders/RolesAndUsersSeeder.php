<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolesAndUsersSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['super_admin', 'admin', 'staff', 'customer'] as $role) {
            Role::firstOrCreate([
                'name' => $role,
                'guard_name' => 'web',
            ]);
        }

        $admin = User::updateOrCreate(
            ['email' => 'admin@redeast.agency'],
            [
                'name' => 'RedEast Admin',
                'password' => 'password',
                'email_verified_at' => now(),
            ],
        );
        $admin->syncRoles(['super_admin']);

        $customer = User::updateOrCreate(
            ['email' => 'customer@redeast.agency'],
            [
                'name' => 'RedEast Customer',
                'password' => 'password',
                'email_verified_at' => now(),
            ],
        );
        $customer->syncRoles(['customer']);
    }
}
