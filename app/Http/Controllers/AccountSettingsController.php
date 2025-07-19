<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class AccountSettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the account settings page
     */
    public function account()
    {
        $user = Auth::user();
        return view('content.pages.pages-account-settings-account', compact('user'));
    }

    /**
     * Update account information
     */
    public function updateAccount(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'address' => 'nullable|string|max:500',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:800'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Handle avatar upload
            if ($request->hasFile('avatar')) {
                // Delete old avatar if exists
                if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                    Storage::disk('public')->delete($user->avatar);
                }

                // Store new avatar
                $avatarPath = $request->file('avatar')->store('avatars', 'public');
                $user->avatar = $avatarPath;
            }

            // Update user data
            $user->name = $request->name;
            $user->email = $request->email;
            $user->address = $request->address;
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Account updated successfully',
                'avatar_url' => $user->avatar ? Storage::url($user->avatar) : null
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating your account'
            ], 500);
        }
    }

    /**
     * Upload avatar only
     */
    public function uploadAvatar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:800'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = Auth::user();

            // Delete old avatar if exists
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }

            // Store new avatar
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = $avatarPath;
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Avatar uploaded successfully',
                'avatar_url' => Storage::url($avatarPath)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while uploading the avatar'
            ], 500);
        }
    }

    /**
     * Reset avatar to default
     */
    public function resetAvatar()
    {
        try {
            $user = Auth::user();

            // Delete current avatar if exists
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }

            $user->avatar = null;
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Avatar reset successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while resetting the avatar'
            ], 500);
        }
    }

    /**
     * Deactivate user account
     */
    public function deactivateAccount(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'confirmation' => 'required|boolean|accepted'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Please confirm account deactivation'
            ], 422);
        }

        try {
            $user = Auth::user();

            // Delete avatar if exists
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }

            // Log out the user before deletion
            Auth::logout();

            // Delete the user account
            $user->delete();

            return response()->json([
                'success' => true,
                'message' => 'Account deactivated successfully',
                'redirect' => route('auth-login-basic')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deactivating the account'
            ], 500);
        }
    }
}
