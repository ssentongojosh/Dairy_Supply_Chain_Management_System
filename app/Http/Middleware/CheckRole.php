<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
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
    $user = auth()->user();

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
    if (!$user || !in_array($user->role, $enumRoles)) {
      return redirect()->route('home')->with('error', 'You do not have permission to access that page.');
    }

    return $next($request);
  }
}
