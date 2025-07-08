<?php


namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Enums\Role;
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
            $userRoleValue = $userRole->value;
        } elseif (is_string($userRole)) {
            $userRoleValue = $userRole;
        } else {
            // Fallback to home if role is invalid
            return redirect()->route('home')->with('error', 'Invalid user role configuration.');
        }

        return $this->redirectBasedOnRoleValue($user);
    }

    /**
     * Redirect to the specific route based on the user's role.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function redirectBasedOnRoleValue($user)
  {
    switch ($user->role) {
      case Role::ADMIN:
        return redirect()->route('dashboard.analytics');
      case Role::RETAILER:
        return redirect()->route('dashboard.retailer');
      case Role::WHOLESALER:
        return redirect()->route('wholesaler.dashboard');
      case Role::FARMER:
        return redirect()->route('farmer.dashboard');
      case Role::DRIVER:
        return redirect()->route('driver.dashboard');
      case Role::WAREHOUSE_MANAGER:
        return redirect()->route('warehouse.dashboard');
      case Role::EXECUTIVE:
        return redirect()->route('executive.dashboard');
      case Role::INSPECTOR:
        return redirect()->route('inspector.dashboard');
      case Role::QUALITY_ASSURANCE:
        return redirect()->route('quality.dashboard');
      default:
        return redirect()->route('home');
    }
  }
}
