<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the roles that belong to the user.
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles');
    }

    /**
     * Get the user's profile.
     */
    public function profile()
    {
        return $this->hasOne(Profile::class);
    }

    /**
     * Check if user has a specific role.
     */
    public function hasRole($role)
    {
        if (is_string($role)) {
            return $this->roles->contains('name', $role);
        }
        
        return $this->roles->contains($role);
    }

    /**
     * Check if user has any of the given roles.
     */
    public function hasAnyRole($roles)
    {
        if (is_array($roles)) {
            foreach ($roles as $role) {
                if ($this->hasRole($role)) {
                    return true;
                }
            }
            return false;
        }
        
        return $this->hasRole($roles);
    }

    /**
     * Check if user is Master Admin
     */
    public function isMasterAdmin()
    {
        return $this->roles()->where('level', Role::MASTER_ADMIN_LEVEL)->exists();
    }

    /**
     * Check if user is Admin (Admin or Master Admin)
     */
    public function isAdmin()
    {
        return $this->roles()->whereIn('level', [Role::MASTER_ADMIN_LEVEL, Role::ADMIN_LEVEL])->exists();
    }

    /**
     * Check if user is at least Supervisor (level 1-3)
     */
    public function isSupervisor()
    {
        return $this->roles()->where('level', '<=', Role::SUPERVISOR_LEVEL)->exists();
    }

    /**
     * Check if user is at least Staff (level 1-4)
     */
    public function isStaff()
    {
        return $this->roles()->where('level', '<=', Role::STAFF_LEVEL)->exists();
    }

    /**
     * Check if user is Viewer (level 5)
     */
    public function isViewer()
    {
        return $this->roles()->where('level', Role::VIEWER_LEVEL)->exists();
    }

    /**
     * Check if user is Driver (level 6)
     */
    public function isDriver()
    {
        return $this->roles()->where('level', Role::DRIVER_LEVEL)->exists();
    }

    /**
     * Check if user is Member (kept for backward compat)
     */
    public function isMember()
    {
        return $this->roles()->where('level', '>=', Role::SUPERVISOR_LEVEL)->exists();
    }

    /**
     * Check if user has at least the given level (lower number = higher rank)
     */
    public function hasMinLevel(int $level): bool
    {
        return $this->roles()->where('level', '<=', $level)->exists();
    }

    /**
     * Get user's highest role level (lower = more permissions)
     */
    public function getHighestRoleLevel()
    {
        return $this->roles()->min('level') ?? Role::DRIVER_LEVEL;
    }

    /**
     * Get user's role names
     */
    public function getRoleNamesAttribute()
    {
        return $this->roles->pluck('display_name')->implode(', ');
    }
}
