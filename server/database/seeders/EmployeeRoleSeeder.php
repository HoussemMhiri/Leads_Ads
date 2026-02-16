<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class EmployeeRoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = ['admin', 'member', 'viewer'];

        foreach ($roles as $role) {
            Role::firstOrCreate([
                'name' => $role,
                'guard_name' => 'employee',
            ]);
        }
    }
}
