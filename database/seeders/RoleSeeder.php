<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'master-admin',
                'display_name' => 'Master Admin',
                'description' => 'Master Administrator with full system access',
                'level' => 1, // MASTER_ADMIN_LEVEL
                'is_active' => true,
            ],
            [
                'name' => 'admin',
                'display_name' => 'Admin',
                'description' => 'Administrator with limited system access',
                'level' => 2, // ADMIN_LEVEL
                'is_active' => true,
            ],
            [
                'name' => 'member',
                'display_name' => 'Member',
                'description' => 'Regular member with basic access',
                'level' => 3, // MEMBER_LEVEL
                'is_active' => true,
            ],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(
                ['name' => $role['name']],
                $role
            );
        }
    }
}
