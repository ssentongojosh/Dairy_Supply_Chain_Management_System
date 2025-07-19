<?php

namespace App\Http\Controllers\pages;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;

class AccountSettingsAccount extends Controller
{
  public function index()
  {
    $user = Auth::user();
    return view('content.pages.pages-account-settings-account', compact('user'));
  }

  /**
   * Update user account settings
   */
  public function update(Request $request): JsonResponse
  {
    try {
      $user = Auth::user();
      
      $validator = Validator::make($request->all(), [
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email,' . $user->id,
        'address' => 'nullable|string|max:500',
      ]);

      if ($validator->fails()) {
        return response()->json([
          'success' => false,
          'message' => 'Validation failed',
          'errors' => $validator->errors()
        ], 422);
      }

      // Update user data
      $user->update([
        'name' => $request->name,
        'email' => $request->email,
        'address' => $request->address,
      ]);

      return response()->json([
        'success' => true,
        'message' => 'Account settings updated successfully'
      ]);

    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Failed to update account settings: ' . $e->getMessage()
      ], 500);
    }
  }

  /**
   * Upload user profile photo
   */
  public function uploadPhoto(Request $request): JsonResponse
  {
    try {
      $validator = Validator::make($request->all(), [
        'photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:800', // max 800KB
      ]);

      if ($validator->fails()) {
        return response()->json([
          'success' => false,
          'message' => 'Invalid file. Please upload JPG, PNG, or GIF under 800KB.',
          'errors' => $validator->errors()
        ], 422);
      }

      $user = Auth::user();
      
      // Delete old photo if exists
      if ($user->profile_photo && Storage::disk('public')->exists($user->profile_photo)) {
        Storage::disk('public')->delete($user->profile_photo);
      }

      // Store new photo
      $path = $request->file('photo')->store('profile-photos', 'public');
      
      // Update user profile photo path
      $user->update(['profile_photo' => $path]);

      return response()->json([
        'success' => true,
        'message' => 'Profile photo updated successfully',
        'photo_url' => Storage::url($path)
      ]);

    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Failed to upload photo: ' . $e->getMessage()
      ], 500);
    }
  }

  /**
   * Reset profile photo to default
   */
  public function resetPhoto(): JsonResponse
  {
    try {
      $user = Auth::user();
      
      // Delete existing photo if exists
      if ($user->profile_photo && Storage::disk('public')->exists($user->profile_photo)) {
        Storage::disk('public')->delete($user->profile_photo);
      }

      // Reset to null (will use default)
      $user->update(['profile_photo' => null]);

      return response()->json([
        'success' => true,
        'message' => 'Profile photo reset to default',
        'photo_url' => asset('assets/img/avatars/1.png') // default photo
      ]);

    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Failed to reset photo: ' . $e->getMessage()
      ], 500);
    }
  }

  /**
   * Deactivate user account
   */
  public function deactivate(Request $request): JsonResponse
  {
    try {
      $validator = Validator::make($request->all(), [
        'confirmation' => 'required|accepted', // Must be true/1/"yes"/"on"
      ]);

      if ($validator->fails()) {
        return response()->json([
          'success' => false,
          'message' => 'Please confirm account deactivation'
        ], 422);
      }

      $user = Auth::user();
      
      // Delete profile photo if exists
      if ($user->profile_photo && Storage::disk('public')->exists($user->profile_photo)) {
        Storage::disk('public')->delete($user->profile_photo);
      }

      // Log out the user first
      Auth::logout();
      
      // Delete the user account
      $user->delete();

      return response()->json([
        'success' => true,
        'message' => 'Account deactivated successfully',
        'redirect' => route('login') // or wherever you want to redirect
      ]);

    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Failed to deactivate account: ' . $e->getMessage()
      ], 500);
    }
  }
}
