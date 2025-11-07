<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\Profile;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get roles
        $masterAdminRole = Role::where('name', 'master-admin')->first();
        $adminRole = Role::where('name', 'admin')->first();
        $memberRole = Role::where('name', 'member')->first();

        // Create Master Admin User
        $masterAdmin = User::firstOrCreate(
            ['email' => 'master@cmc-stock.com'],
            [
                'name' => 'Master Administrator',
                'email' => 'master@cmc-stock.com',
                'phone' => '0800000001',
                'password' => Hash::make('password123'),
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // Assign Master Admin role
        if ($masterAdminRole && !$masterAdmin->hasRole('master-admin')) {
            $masterAdmin->roles()->attach($masterAdminRole->id);
        }

        // Create Master Admin Profile
        Profile::firstOrCreate(
            ['user_id' => $masterAdmin->id],
            [
                'first_name' => 'Master',
                'last_name' => 'Administrator',
                'phone' => '0800000001',
                'birth_date' => '1990-01-01',
                'gender' => 'other',
                'address' => 'System Administrator',
            ]
        );

        // Create Admin User
        $admin = User::firstOrCreate(
            ['email' => 'admin@cmc-stock.com'],
            [
                'name' => 'System Admin',
                'email' => 'admin@cmc-stock.com',
                'phone' => '0800000002',
                'password' => Hash::make('password123'),
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // Assign Admin role
        if ($adminRole && !$admin->hasRole('admin')) {
            $admin->roles()->attach($adminRole->id);
        }

        // Create Admin Profile
        Profile::firstOrCreate(
            ['user_id' => $admin->id],
            [
                'first_name' => 'System',
                'last_name' => 'Admin',
                'phone' => '0800000002',
                'birth_date' => '1992-05-15',
                'gender' => 'male',
                'address' => 'Bangkok, Thailand',
            ]
        );

        // Create Member User
        $member = User::firstOrCreate(
            ['email' => 'member@cmc-stock.com'],
            [
                'name' => 'Test Member',
                'email' => 'member@cmc-stock.com',
                'phone' => '0800000003',
                'password' => Hash::make('password123'),
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // Assign Member role
        if ($memberRole && !$member->hasRole('member')) {
            $member->roles()->attach($memberRole->id);
        }

        // Create Member Profile
        Profile::firstOrCreate(
            ['user_id' => $member->id],
            [
                'first_name' => 'Test',
                'last_name' => 'Member',
                'phone' => '0800000003',
                'birth_date' => '1995-08-20',
                'gender' => 'female',
                'address' => 'Chiang Mai, Thailand',
            ]
        );

        // Create additional test users
        for ($i = 1; $i <= 5; $i++) {
            $testUser = User::firstOrCreate(
                ['email' => "user{$i}@cmc-stock.com"],
                [
                    'name' => "Test User {$i}",
                    'email' => "user{$i}@cmc-stock.com",
                    'phone' => '080000000' . (3 + $i),
                    'password' => Hash::make('password123'),
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]
            );

            // Assign random role
            $randomRole = collect([$adminRole, $memberRole])->random();
            if ($randomRole && !$testUser->roles->contains($randomRole)) {
                $testUser->roles()->attach($randomRole->id);
            }

            // Create test profile
            Profile::firstOrCreate(
                ['user_id' => $testUser->id],
                [
                    'first_name' => "Test{$i}",
                    'last_name' => "User{$i}",
                    'phone' => '080000000' . (3 + $i),
                    'birth_date' => now()->subYears(rand(20, 50))->format('Y-m-d'),
                    'gender' => collect(['male', 'female'])->random(),
                    'address' => "Test Address {$i}, Thailand",
                ]
            );
        }
    }
}
