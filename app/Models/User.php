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
        return $this->roles()->where('level', 1)->exists(); // Role::MASTER_ADMIN_LEVEL
    }

    /**
     * Check if user is Admin (Admin or Master Admin)
     */
    public function isAdmin()
    {
        return $this->roles()->whereIn('level', [1, 2])->exists(); // Role::MASTER_ADMIN_LEVEL, Role::ADMIN_LEVEL
    }

    /**
     * Check if user is Member
     */
    public function isMember()
    {
        return $this->roles()->where('level', 3)->exists(); // Role::MEMBER_LEVEL
    }

    /**
     * Get user's highest role level
     */
    public function getHighestRoleLevel()
    {
        return $this->roles()->min('level') ?? 3; // Role::MEMBER_LEVEL
    }

    /**
     * Get user's role names
     */
    public function getRoleNamesAttribute()
    {
        return $this->roles->pluck('display_name')->implode(', ');
    }
}
