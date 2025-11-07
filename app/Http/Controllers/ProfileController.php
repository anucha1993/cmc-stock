<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * Display the authenticated user's profile
     */
    public function show()
    {
        $user = Auth::user();
        $profile = $user->profile;
        
        // Create profile if doesn't exist
        if (!$profile) {
            $profile = new Profile();
        }
        
        return view('admin.profile.show', compact('user', 'profile'));
    }

    /**
     * Show the form for editing the authenticated user's profile
     */
    public function edit() 
    {
        $user = Auth::user();
        $profile = $user->profile;
        
        // Create profile if doesn't exist
        if (!$profile) {
            $profile = new Profile();
        }
        
        return view('admin.profile.edit', compact('user', 'profile'));
    }

    /**
     * Update the authenticated user's profile
     */
    public function update(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'birth_date' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'address' => 'nullable|string',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Update user data
        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
        ]);

        // Handle avatar upload
        $avatarPath = null;
        if ($request->hasFile('avatar')) {
            // Delete old avatar if exists
            if ($user->profile && $user->profile->avatar) {
                Storage::disk('public')->delete($user->profile->avatar);
            }
            
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
        }

        // Update or create profile
        $profileData = [
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'phone' => $request->phone,
            'birth_date' => $request->birth_date,
            'gender' => $request->gender,
            'address' => $request->address,
        ];

        if ($avatarPath) {
            $profileData['avatar'] = $avatarPath;
        }

        $user->profile()->updateOrCreate(
            ['user_id' => $user->id],
            $profileData
        );

        return redirect()->route('admin.profile')->with('success', 'Profile updated successfully.');
    }

    /**
     * Display a listing of all profiles (Admin only)
     */
    public function index()
    {
        $profiles = Profile::with('user')->paginate(10);
        return view('admin.profile.index', compact('profiles'));
    }

    /**
     * Display the specified profile (Admin only)
     */
    public function showProfile(Profile $profile)
    {
        $profile->load('user');
        return view('admin.profile.show-user', compact('profile'));
    }

    /**
     * Show the form for editing the specified profile (Admin only)
     */
    public function editProfile(Profile $profile)
    {
        $profile->load('user');
        return view('admin.profile.edit-user', compact('profile'));
    }

    /**
     * Update the specified profile (Admin only)
     */
    public function updateProfile(Request $request, Profile $profile)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'birth_date' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'address' => 'nullable|string',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Handle avatar upload
        $avatarPath = $profile->avatar;
        if ($request->hasFile('avatar')) {
            // Delete old avatar if exists
            if ($profile->avatar) {
                Storage::disk('public')->delete($profile->avatar);
            }
            
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
        }

        $profile->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'phone' => $request->phone,
            'birth_date' => $request->birth_date,
            'gender' => $request->gender,
            'address' => $request->address,
            'avatar' => $avatarPath,
        ]);

        return redirect()->route('admin.profiles.index')->with('success', 'Profile updated successfully.');
    }
}
