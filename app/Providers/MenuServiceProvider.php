<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use App\Http\View\Composers\MenuComposer;

class MenuServiceProvider extends ServiceProvider
{
  /**
   * Register services.
   */
  public function register(): void
  {
    //
  }

  /**
   * Bootstrap services.
   */
  public function boot(): void
  {
    // Register the MenuComposer for views that need menu data
    View::composer([
      'layouts.sections.menu.verticalMenu',
      'layouts.*'
    ], MenuComposer::class);
  }
}
