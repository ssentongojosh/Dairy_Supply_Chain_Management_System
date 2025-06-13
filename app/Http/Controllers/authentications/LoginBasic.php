<?php

namespace App\Http\Controllers\authentications;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Enums\Role;

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
      $redirectUrl = $this->redirectBasedOnRole($user);

      return redirect($redirectUrl);
    }

    return back()->withErrors([
      'email' => 'The provided credentials do not match our records.',
    ])->onlyInput('email');
  }

  protected function redirectBasedOnRole($user)
  {
    switch ($user->role) {
      case Role::ADMIN:
        return route('dashboard.analytics');
      case Role::RETAILER:
        return route('retailer.dashboard');
      case Role::WHOLESALER:
        return route('wholesaler.dashboard');
      case Role::FARMER:
        return route('farmer.dashboard');
      case Role::DRIVER:
        return route('driver.dashboard');
      case Role::WAREHOUSE_MANAGER:
        return route('warehouse.dashboard');
      case Role::EXECUTIVE:
        return route('executive.dashboard');
      case Role::INSPECTOR:
        return route('inspector.dashboard');
      case Role::QUALITY_ASSURANCE:
        return route('quality.dashboard');
      default:
        return route('home');
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
