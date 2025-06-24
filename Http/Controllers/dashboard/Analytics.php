<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use App\Enums\Role;

class Analytics extends Controller
{
  public function __construct()
  {
    $this->middleware('auth');
  }

  public function index()
  {
    // Check if user has admin role only
    $user = auth()->user();

    if (!$user || $user->role !== Role::ADMIN) {
      return redirect()->route('home')->with('error', 'You do not have permission to access that page.');
    }

    return view('content.dashboard.dashboards-analytics');
  }
}
