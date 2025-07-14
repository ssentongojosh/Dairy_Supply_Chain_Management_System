<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Enums\Role;

class CheckRole
{
  /**
   * Handle an incoming request.
   */
  public function handle(Request $request, Closure $next, ...$roles)
  {
    // Get the authenticated user
    $user = Auth::user();

    // Convert single role to array for consistency
    if (!is_array($roles)) {
      $roles = [$roles];
    }

    // Convert string roles to Enum cases for comparison
    $enumRoles = [];
    foreach ($roles as $role) {
      try {
        $enumRoles[] = Role::from($role);
      } catch (\ValueError $e) {
        // If the role string doesn't match any enum case, log and continue
        Log::warning('Invalid role in middleware', ['role' => $role]);
      }
    }

    // Log for debugging
    Log::info('Role check', [
      'required_roles' => $roles,
      'required_enum_roles' => $enumRoles,
      'user_role' => $user ? $user->role : 'not authenticated',
      'user_role_value' => $user && $user->role ? $user->role->value : null,
      'user_id' => $user ? $user->id : null,
      'session_id' => session()->getId()
    ]);

    // Check if user has any of the required roles
    $hasRole = false;
    
    if ($user && $user->role) {
      // Check if user role matches any of the required roles (both enum and string comparison)
      foreach ($enumRoles as $requiredRole) {
        if ($user->role === $requiredRole || $user->role->value === $requiredRole->value) {
          $hasRole = true;
          break;
        }
      }
      
      // Also check string values directly in case of enum conversion issues
      foreach ($roles as $roleString) {
        if ($user->role->value === $roleString) {
          $hasRole = true;
          break;
        }
      }
    }
    
    if (!$hasRole) {
      Log::warning('Access denied', [
        'user_role' => $user ? $user->role : 'not authenticated',
        'user_role_value' => $user && $user->role ? $user->role->value : null,
        'required_roles' => $roles,
        'required_enum_roles' => $enumRoles,
        'request_url' => $request->url()
      ]);
      return redirect()->route('home')->with('error', 'You do not have permission to access that page.');
    }

    return $next($request);
  }
}
