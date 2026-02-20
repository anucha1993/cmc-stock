<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Use Bootstrap 5 pagination styling (compatible with AdminLTE)
        Paginator::useBootstrapFive();

        // =====================================================
        // Gate definitions for 6-level role system
        // Level 1: Master Admin  — ทุกอย่าง + จัดการ roles
        // Level 2: Admin         — จัดการ + อนุมัติ + จัดการผู้ใช้
        // Level 3: Supervisor    — สร้าง/แก้ไข + ดูรายงาน (ไม่อนุมัติ/ลบ)
        // Level 4: Staff         — สแกน + ตรวจนับ + คำขอปรับสต็อก
        // Level 5: Viewer        — ดูข้อมูลอย่างเดียว
        // Level 6: Driver        — สแกนบาร์โค้ดขนของ + ดูใบตัดสต็อก
        // =====================================================

        // Master Admin only
        Gate::define('manage-roles', fn ($user) => $user->isMasterAdmin());

        // Admin+ (level 1-2): จัดการผู้ใช้, อนุมัติ, ลบ, CRUD หลัก
        Gate::define('manage-users', fn ($user) => $user->isAdmin());
        Gate::define('approve', fn ($user) => $user->isAdmin());
        Gate::define('delete', fn ($user) => $user->isAdmin());
        Gate::define('manage-master-data', fn ($user) => $user->isAdmin());
        Gate::define('manage-warehouses', fn ($user) => $user->isAdmin());

        // Supervisor+ (level 1-3): สร้าง/แก้ไข ข้อมูล
        Gate::define('create-edit', fn ($user) => $user->isSupervisor());
        Gate::define('manage-products', fn ($user) => $user->isSupervisor());
        Gate::define('manage-delivery-notes', fn ($user) => $user->isSupervisor());
        Gate::define('manage-transfers', fn ($user) => $user->isSupervisor());
        Gate::define('manage-production', fn ($user) => $user->isSupervisor());
        Gate::define('manage-claims', fn ($user) => $user->isSupervisor());
        Gate::define('manage-stock-checks', fn ($user) => $user->isSupervisor());

        // Staff+ (level 1-4): สแกน, ตรวจนับ, คำขอปรับสต็อก
        Gate::define('scan-barcode', fn ($user) => $user->isStaff());
        Gate::define('stock-operations', fn ($user) => $user->isStaff());
        Gate::define('print-barcode', fn ($user) => $user->isStaff());

        // Viewer+ (level 1-5): ดูข้อมูล
        Gate::define('view-data', fn ($user) => $user->hasMinLevel(5));
        Gate::define('view-reports', fn ($user) => $user->hasMinLevel(5));

        // Driver (level 6): เฉพาะสแกนขนของ + ดูใบตัดสต็อก
        Gate::define('driver-scan', fn ($user) => $user->isDriver() || $user->isStaff());
        Gate::define('view-delivery-notes', fn ($user) => $user->isDriver() || $user->hasMinLevel(5));

        // Legacy alias
        Gate::define('admin', fn ($user) => $user->isAdmin());
        Gate::define('manage-profiles', fn ($user) => $user->isAdmin());
    }
}
