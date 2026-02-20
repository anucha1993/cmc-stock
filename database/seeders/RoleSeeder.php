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
                'description' => 'ผู้ดูแลระบบ - สิทธิ์สูงสุด จัดการทุกอย่างรวมถึงบทบาทและผู้ใช้',
                'level' => Role::MASTER_ADMIN_LEVEL,
                'is_active' => true,
            ],
            [
                'name' => 'admin',
                'display_name' => 'ผู้จัดการคลัง',
                'description' => 'จัดการสินค้า/คลัง, อนุมัติ/ปฏิเสธคำขอทั้งหมด, จัดการผู้ใช้',
                'level' => Role::ADMIN_LEVEL,
                'is_active' => true,
            ],
            [
                'name' => 'supervisor',
                'display_name' => 'หัวหน้างาน',
                'description' => 'สร้าง/แก้ไขข้อมูล, สร้างใบงานต่างๆ, ดูรายงาน (ไม่สามารถอนุมัติ/ลบ)',
                'level' => Role::SUPERVISOR_LEVEL,
                'is_active' => true,
            ],
            [
                'name' => 'staff',
                'display_name' => 'พนักงานคลัง',
                'description' => 'สแกนบาร์โค้ด, ตรวจนับสต็อก, สร้างคำขอปรับสต็อก, ดูข้อมูล',
                'level' => Role::STAFF_LEVEL,
                'is_active' => true,
            ],
            [
                'name' => 'viewer',
                'display_name' => 'ผู้ดูข้อมูล',
                'description' => 'ดูข้อมูลและรายงานเท่านั้น ไม่สามารถสร้าง/แก้ไข/ลบ',
                'level' => Role::VIEWER_LEVEL,
                'is_active' => true,
            ],
            [
                'name' => 'driver',
                'display_name' => 'คนรถ',
                'description' => 'สแกนบาร์โค้ดตอนขนของออกจากคลัง, ดูใบตัดสต็อก',
                'level' => Role::DRIVER_LEVEL,
                'is_active' => true,
            ],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(
                ['name' => $role['name']],
                $role
            );
        }

        // Migrate old 'member' users to 'supervisor' and delete 'member' role
        $memberRole = Role::where('name', 'member')->first();
        if ($memberRole) {
            $supervisorRole = Role::where('name', 'supervisor')->first();
            if ($supervisorRole) {
                // Move all users from 'member' to 'supervisor'
                \DB::table('user_roles')
                    ->where('role_id', $memberRole->id)
                    ->update(['role_id' => $supervisorRole->id]);
                // Delete the old member role
                $memberRole->delete();
            }
        }
    }
}
