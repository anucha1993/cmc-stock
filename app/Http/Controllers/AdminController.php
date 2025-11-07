<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;

class AdminController extends Controller
{
    public function dashboard()
    {
        $totalUsers = User::count();
        $totalRoles = Role::count();
        $activeUsers = User::where('is_active', true)->count();
        $inactiveUsers = User::where('is_active', false)->count();
        
        $recentUsers = User::with(['roles', 'profile'])
            ->latest()
            ->limit(5)
            ->get();
            
        $roleStats = Role::withCount('users')
            ->orderBy('level')
            ->get();

        return view('admin.dashboard', compact(
            'totalUsers',
            'totalRoles', 
            'activeUsers',
            'inactiveUsers',
            'recentUsers',
            'roleStats'
        ));
    }
}
