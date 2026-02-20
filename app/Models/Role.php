<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'display_name',
        'description',
        'level',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'level' => 'integer',
    ];

    // Role levels constants
    const MASTER_ADMIN_LEVEL = 1;  // ผู้ดูแลระบบ
    const ADMIN_LEVEL = 2;         // ผู้จัดการคลัง
    const SUPERVISOR_LEVEL = 3;    // หัวหน้างาน
    const STAFF_LEVEL = 4;         // พนักงานคลัง
    const VIEWER_LEVEL = 5;        // ผู้ดูข้อมูล
    const DRIVER_LEVEL = 6;        // คนรถ (สแกนบาร์โค้ดตอนขนของ)

    /**
     * Get the users that belong to the role.
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_roles'); 
    }

    /**
     * Check if this role is Master Admin
     */
    public function isMasterAdmin()
    {
        return $this->level === self::MASTER_ADMIN_LEVEL;
    }

    /**
     * Check if this role is Admin
     */
    public function isAdmin()
    {
        return $this->level === self::ADMIN_LEVEL;
    }

    /**
     * Check if this role is Supervisor
     */
    public function isSupervisor()
    {
        return $this->level === self::SUPERVISOR_LEVEL;
    }

    /**
     * Check if this role is Staff
     */
    public function isStaff()
    {
        return $this->level === self::STAFF_LEVEL;
    }

    /**
     * Check if this role is Viewer
     */
    public function isViewer()
    {
        return $this->level === self::VIEWER_LEVEL;
    }

    /**
     * Check if this role is Driver
     */
    public function isDriver()
    {
        return $this->level === self::DRIVER_LEVEL;
    }

    /**
     * Get role level name
     */
    public function getLevelNameAttribute()
    {
        return match ($this->level) {
            self::MASTER_ADMIN_LEVEL => 'Master Admin',
            self::ADMIN_LEVEL       => 'ผู้จัดการคลัง',
            self::SUPERVISOR_LEVEL  => 'หัวหน้างาน',
            self::STAFF_LEVEL       => 'พนักงานคลัง',
            self::VIEWER_LEVEL      => 'ผู้ดูข้อมูล',
            self::DRIVER_LEVEL      => 'คนรถ',
            default                 => 'Unknown',
        };
    }
}
