<?php

namespace App\Http\Controllers\authentications;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Enums\Role;
use App\Models\User; // Import the User model

class LoginBasic extends Controller
{
    public function index()
    {
        return view('content.authentications.auth-login-basic');
    }

    public function authenticate(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $request->filled('remember-me'))) {
            $request->session()->regenerate();
            $user = Auth::user();

            // Robust check for User object
            if (!$user instanceof User) {
                Log::critical('Login Error: Auth::user() did not return a User instance.', [
                    'returned_type' => gettype($user),
                    'returned_value' => $user
                ]);
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return back()->withErrors([
                    'email' => 'A critical authentication error occurred. Please try again.',
                ])->onlyInput('email');
            }

            Log::info('Login successful', [
                'user_id' => $user->id,
                'user_role_object' => $user->role, // Log the role object
                'session_id' => session()->getId()
            ]);

            // Check if user is verified
            if (!$user->verified) {
                if (!$user->business_document_path) {
                    return redirect()->route('verification.upload');
                }
                return redirect()->route('verification.pending');
            }

            // User is verified, proceed to dashboard
            $userRole = $user->role; // This should be an App\Enums\Role instance or null
            
            // Ensure $userRole is an instance of Role before accessing ->value
            if ($userRole instanceof Role) {
                $userRoleValue = $userRole->value;
            } elseif (is_string($userRole)) {
                // If it's already a string (e.g., from a different source or if casting failed silently)
                $userRoleValue = $userRole;
            } else {
                Log::error('User role is not a valid Role enum instance or string.', ['user_id' => $user->id, 'role_data' => $userRole]);
                // Fallback or error handling
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return back()->withErrors(['email' => 'Invalid user role configuration.'])->onlyInput('email');
            }
            
            return $this->redirectBasedOnRoleValue($userRoleValue);
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    protected function redirectBasedOnRoleValue($roleValue)
    {
        Log::info('Redirecting based on role value', ['role_value' => $roleValue]);
        switch ($roleValue) {
            case 'admin':
                Log::info('Admin role detected, redirecting to dashboard.analytics');
                return redirect()->route('dashboard.analytics');
            case 'retailer':
                Log::info('Retailer role detected, redirecting to retailer.dashboard');
                return redirect()->route('retailer.dashboard');
            case 'wholesaler':
                Log::info('Wholesaler role detected, redirecting to wholesaler.dashboard');
                return redirect()->route('wholesaler.dashboard');
            // case 'farmer':
            //     return redirect()->route('farmer.dashboard');
            default:
                Log::info('Default role or unknown role, redirecting to home', ['role' => $roleValue]);
                return redirect()->route('home');
        }
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
