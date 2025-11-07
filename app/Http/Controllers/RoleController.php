<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $roles = Role::withCount('users')->paginate(10);
        return view('admin.roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.roles.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:roles',
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'level' => 'required|integer|in:1,2,3',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        Role::create([
            'name' => $request->name,
            'display_name' => $request->display_name,  
            'description' => $request->description,
            'level' => $request->level,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.roles.index')->with('success', 'Role created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Role $role)
    {
        $role->load('users.profile');
        return view('admin.roles.show', compact('role'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Role $role)
    {
        return view('admin.roles.edit', compact('role'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Role $role)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'level' => 'required|integer|in:1,2,3',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $role->update([
            'name' => $request->name,
            'display_name' => $request->display_name,
            'description' => $request->description,
            'level' => $request->level,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.roles.index')->with('success', 'Role updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role)
    {
        // Detach all users from this role before deleting
        $role->users()->detach();
        $role->delete();

        return redirect()->route('admin.roles.index')->with('success', 'Role deleted successfully.');
    }

    /**
     * Manage users for a specific role
     */
    public function manageUsers(Role $role)
    {
        $role->load('users.profile');
        
        // Get users that don't have this role
        $availableUsers = \App\Models\User::whereDoesntHave('roles', function($query) use ($role) {
            $query->where('role_id', $role->id);
        })->get();
        
        return view('admin.roles.manage-users', compact('role', 'availableUsers'));
    }
    
    /**
     * Add user to role
     */
    public function addUser(Request $request, Role $role)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);
        
        $user = \App\Models\User::findOrFail($request->user_id);
        
        // Check if user already has this role
        if (!$user->hasRole($role->name)) {
            $user->roles()->attach($role->id);
            return redirect()->back()->with('success', 'เพิ่มผู้ใช้เข้าสู่บทบาทเรียบร้อยแล้ว');
        }
        
        return redirect()->back()->with('warning', 'ผู้ใช้นี้มีบทบาทนี้อยู่แล้ว');
    }
    
    /**
     * Remove user from role
     */
    public function removeUser(Role $role, \App\Models\User $user)
    {
        if ($user->hasRole($role->name)) {
            $user->roles()->detach($role->id);
            return redirect()->back()->with('success', 'ลบผู้ใช้ออกจากบทบาทเรียบร้อยแล้ว');
        }
        
        return redirect()->back()->with('warning', 'ผู้ใช้นี้ไม่มีบทบาทนี้');
    }
}
