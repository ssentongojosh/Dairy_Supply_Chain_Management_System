<?php


namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Enums\Role;
// use App\Enums\Role;
class HomeController extends Controller
{
    /**
     * Show the welcome page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('welcome');
    }

    /**
     * Redirect users to the appropriate dashboard based on their role and verification status.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function dashboard()
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Check if user is verified
        if (!$user->verified) {
            if (!$user->business_document_path) {
                return redirect()->route('verification.upload');
            }
            return redirect()->route('verification.pending');
        }

        // User is verified, redirect to appropriate dashboard based on role
        $userRole = $user->role;

        // Ensure $userRole is properly handled
        if ($userRole instanceof Role) {
        if ($userRole instanceof Role) {
            $userRoleValue = $userRole->value;
        } elseif (is_string($userRole)) {
            $userRoleValue = $userRole;
        } else {
            // Fallback to home if role is invalid
            return redirect()->route('home')->with('error', 'Invalid user role configuration.');
        }

        return $this->redirectBasedOnRoleValue($user);
        return $this->redirectBasedOnRoleValue($user);
    }
  }

    /**
     * Redirect to the specific route based on the user's role.
     * Redirect to the specific route based on the user's role.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function redirectBasedOnRoleValue($roleValue){
        switch ($roleValue) {
            case 'admin':
                return redirect()->route('dashboard.analytics');
            case 'retailer':
                return redirect()->route('retailer.dashboard');
            case 'wholesaler':
                return redirect()->route('wholesaler.dashboard');
            case 'farmer':
                return redirect()->route('farmer.dashboard');
            case 'plant_manager':
                return redirect()->route('plant_manager.dashboard');
            case 'driver':
                return redirect()->route('driver.dashboard');
            case 'supplier':
                return redirect()->route('supplier.dashboard');
            case 'executive':
                return redirect()->route('executive.dashboard');
            default:
                return redirect()->route('home')->with('error', 'No dashboard available for your role.');
        }
    }
  }

