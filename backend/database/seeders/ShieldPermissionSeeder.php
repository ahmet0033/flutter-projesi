<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ShieldPermissionSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['ManageSettings', 'ViewReports', 'ExportReports', 'ViewActivityLog', 'ManageStaff'] as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        if (! Permission::query()->exists()) {
            return;
        }

        $admin = Role::findByName('admin', 'web');
        if ($admin) {
            $admin->syncPermissions(Permission::all());
        }

        $staff = Role::findByName('staff', 'web');
        if ($staff) {
            $staff->syncPermissions(
                Permission::query()
                    ->where(function ($query) {
                        $query->where('name', 'like', '%:Order')
                            ->orWhere('name', 'like', '%:Product')
                            ->orWhere('name', 'like', '%:Category')
                            ->orWhere('name', 'like', '%:Review');
                    })
                    ->get(),
            );
        }
    }
}
