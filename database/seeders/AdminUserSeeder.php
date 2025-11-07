<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\Profile;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Master Admin User
        $masterAdminUser = User::firstOrCreate(
            ['email' => 'masteradmin@example.com'],
            [
                'name' => 'Master Admin',
                'email' => 'masteradmin@example.com',
                'phone' => '0812345678',
                'password' => Hash::make('password'),
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // Create Admin User
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'phone' => '0823456789',
                'password' => Hash::make('password'),
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // Create Member User
        $memberUser = User::firstOrCreate(
            ['email' => 'member@example.com'],
            [
                'name' => 'Member User',
                'email' => 'member@example.com',
                'phone' => '0834567890',
                'password' => Hash::make('password'),
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // Get roles
        $masterAdminRole = Role::where('name', 'master-admin')->first();
        $adminRole = Role::where('name', 'admin')->first();
        $memberRole = Role::where('name', 'member')->first();

        // Assign roles to users
        if ($masterAdminRole) {
            $masterAdminUser->roles()->syncWithoutDetaching([$masterAdminRole->id]);
        }

        if ($adminRole) {
            $adminUser->roles()->syncWithoutDetaching([$adminRole->id]);
        }

        if ($memberRole) {
            $memberUser->roles()->syncWithoutDetaching([$memberRole->id]);
        }

        // Create profiles for users
        Profile::firstOrCreate(
            ['user_id' => $masterAdminUser->id],
            [
                'first_name' => 'Master',
                'last_name' => 'Admin',
                'phone' => '0812345678',
                'gender' => 'male',
                'address' => 'Admin Office, Bangkok',
            ]
        );

        Profile::firstOrCreate(
            ['user_id' => $adminUser->id],
            [
                'first_name' => 'Admin',
                'last_name' => 'User',
                'phone' => '0823456789',
                'gender' => 'female',
                'address' => 'Office Building, Bangkok',
            ]
        );

        Profile::firstOrCreate(
            ['user_id' => $memberUser->id],
            [
                'first_name' => 'Member',
                'last_name' => 'User',
                'phone' => '0834567890',
                'gender' => 'male',
                'address' => 'Home Address, Bangkok',
            ]
        );
    }
}
