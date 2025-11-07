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
    const MASTER_ADMIN_LEVEL = 1;
    const ADMIN_LEVEL = 2;
    const MEMBER_LEVEL = 3;

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
     * Check if this role is Member
     */
    public function isMember()
    {
        return $this->level === self::MEMBER_LEVEL;
    }

    /**
     * Get role level name
     */
    public function getLevelNameAttribute()
    {
        switch ($this->level) {
            case self::MASTER_ADMIN_LEVEL:
                return 'Master Admin';
            case self::ADMIN_LEVEL:
                return 'Admin';
            case self::MEMBER_LEVEL:
                return 'Member';
            default:
                return 'Unknown';
        }
    }
}
