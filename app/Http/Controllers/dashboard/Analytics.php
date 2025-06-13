<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use App\Enums\Role;
use App\Models\User; // Import the User model
use Illuminate\Support\Facades\Auth; // Import Auth facade
use Illuminate\Support\Facades\Log;

class Analytics extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = Auth::user(); // Use Auth facade or auth() helper

        // Robust check for User object
        if (!$user instanceof User) {
            Log::critical('Analytics Error: Auth::user() did not return a User instance.', [
                'returned_type' => gettype($user),
                'returned_value' => $user
            ]);
            // Redirect to login or show an error, as user is not properly authenticated
            return redirect()->route('login')->withErrors(['session_error' => 'Your session is invalid. Please log in again.']);
        }

        $userRole = $user->role;
        $userRoleValue = null;

        if ($userRole instanceof Role) {
            $userRoleValue = $userRole->value;
        } elseif (is_string($userRole)) {
            $userRoleValue = $userRole;
        } else {
            Log::error('User role in Analytics is not a valid Role enum instance or string.', ['user_id' => $user->id, 'role_data' => $userRole]);
            return redirect()->route('home')->with('error', 'Invalid user role.');
        }

        Log::info('Analytics dashboard accessed', [
            'user_id' => $user->id,
            'role_value' => $userRoleValue
        ]);

        // Check role using string value (admin and retailer allowed as per previous setup)
        if (!in_array($userRoleValue, ['admin', 'retailer'])) {
            return redirect()->route('home')->with('error', 'You do not have permission to access this page.');
        }

        return view('content.dashboard.dashboards-analytics');
    }
}
