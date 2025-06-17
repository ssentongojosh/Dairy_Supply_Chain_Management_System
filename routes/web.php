
<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\dashboard\Analytics;
use App\Http\Controllers\layouts\WithoutMenu;
use App\Http\Controllers\layouts\WithoutNavbar;
use App\Http\Controllers\layouts\Fluid;
use App\Http\Controllers\layouts\Container;
use App\Http\Controllers\layouts\Blank;
use App\Http\Controllers\pages\AccountSettingsAccount;
use App\Http\Controllers\pages\AccountSettingsNotifications;
use App\Http\Controllers\pages\AccountSettingsConnections;
use App\Http\Controllers\pages\MiscError;
use App\Http\Controllers\pages\MiscUnderMaintenance;
use App\Http\Controllers\authentications\LoginBasic;
use App\Http\Controllers\authentications\RegisterBasic;
use App\Http\Controllers\authentications\ForgotPasswordBasic;
use App\Http\Controllers\cards\CardBasic;
use App\Http\Controllers\user_interface\Accordion;
use App\Http\Controllers\user_interface\Alerts;
use App\Http\Controllers\user_interface\Badges;
use App\Http\Controllers\user_interface\Buttons;
use App\Http\Controllers\user_interface\Carousel;
use App\Http\Controllers\user_interface\Collapse;
use App\Http\Controllers\user_interface\Dropdowns;
use App\Http\Controllers\user_interface\Footer;
use App\Http\Controllers\user_interface\ListGroups;
use App\Http\Controllers\user_interface\Modals;
use App\Http\Controllers\user_interface\Navbar;
use App\Http\Controllers\user_interface\Offcanvas;
use App\Http\Controllers\user_interface\PaginationBreadcrumbs;
use App\Http\Controllers\user_interface\Progress;
use App\Http\Controllers\user_interface\Spinners;
use App\Http\Controllers\user_interface\TabsPills;
use App\Http\Controllers\user_interface\Toasts;
use App\Http\Controllers\user_interface\TooltipsPopovers;
use App\Http\Controllers\user_interface\Typography;
use App\Http\Controllers\extended_ui\PerfectScrollbar;
use App\Http\Controllers\extended_ui\TextDivider;
use App\Http\Controllers\icons\RiIcons;
use App\Http\Controllers\form_elements\BasicInput;
use App\Http\Controllers\form_elements\InputGroups;
use App\Http\Controllers\form_layouts\VerticalForm;
use App\Http\Controllers\form_layouts\HorizontalForm;
use App\Http\Controllers\tables\Basic as TablesBasic;
use App\Http\Controllers\dashboard\RetailerDashboard;
use App\Http\Controllers\dashboard\WholesalerDashboard;

use App\Http\Controllers\DocumentVerificationController;
use App\Http\Controllers\ChatController;

// Root route - Welcome page
Route::get('/', [HomeController::class, 'index'])->name('home');

// Authentication routes
Route::get('/login', [LoginBasic::class, 'index'])->name('login');
Route::post('/login', [LoginBasic::class, 'authenticate'])->name('login.submit');
Route::post('/logout', [LoginBasic::class, 'logout'])->name('logout');
Route::get('/forgot-password', [ForgotPasswordBasic::class, 'index'])->name('password.request');

// Registration routes
Route::get('/register', [RegisterBasic::class, 'index'])->name('register');
Route::post('/register', [RegisterBasic::class, 'register'])->name('register.submit');

// General dashboard route that redirects based on role and verification status
Route::get('/dashboard', [HomeController::class, 'dashboard'])->name('dashboard');

// Chat routes
Route::get('/app/chat', [ChatController::class, 'index'])->name('app-chat')->middleware('auth');
Route::post('/chat/send', [ChatController::class, 'sendMessage'])->name('chat.send')->middleware('auth');
Route::get('/chat/messages', [ChatController::class, 'getMessages'])->name('chat.messages')->middleware('auth');

// Dashboard routes with role middleware
Route::get('/analytics', [Analytics::class, 'index'])
  ->name('dashboard.analytics')
  ->middleware(['auth', 'role:admin']);

Route::get('/retailer/dashboard', [RetailerDashboard::class, 'index'])
  ->name('retailer.dashboard')
  ->middleware(['auth', 'role:retailer']);

Route::get('/wholesaler/dashboard', [WholesalerDashboard::class, 'index'])
  ->name('wholesaler.dashboard')
  ->middleware(['auth', 'role:wholesaler']);

// Other role dashboard routes
Route::get('/farmer/dashboard', function() {
  if (!auth()->check() || auth()->user()->role !== \App\Enums\Role::FARMER) {
    return redirect()->route('home')->with('error', 'Access denied.');
  }
  return view('dashboard.farmer');
})->name('farmer.dashboard');

Route::get('/driver/dashboard', function() {
  if (!auth()->check() || auth()->user()->role !== \App\Enums\Role::DRIVER) {
    return redirect()->route('home')->with('error', 'Access denied.');
  }
  return view('dashboard.driver');
})->name('driver.dashboard');

Route::get('/warehouse/dashboard', function() {
  if (!auth()->check() || auth()->user()->role !== \App\Enums\Role::WAREHOUSE_MANAGER) {
    return redirect()->route('home')->with('error', 'Access denied.');
  }
  return view('dashboard.warehouse');
})->name('warehouse.dashboard');

Route::get('/executive/dashboard', function() {
  if (!auth()->check() || auth()->user()->role !== \App\Enums\Role::EXECUTIVE) {
    return redirect()->route('home')->with('error', 'Access denied.');
  }
  return view('dashboard.executive');
})->name('executive.dashboard');

Route::get('/inspector/dashboard', function() {
  if (!auth()->check() || auth()->user()->role !== \App\Enums\Role::INSPECTOR) {
    return redirect()->route('home')->with('error', 'Access denied.');
  }
  return view('dashboard.inspector');
})->name('inspector.dashboard');

Route::get('/quality/dashboard', function() {
  if (!auth()->check() || auth()->user()->role !== \App\Enums\Role::QUALITY_ASSURANCE) {
    return redirect()->route('home')->with('error', 'Access denied.');
  }
  return view('dashboard.quality');
})->name('quality.dashboard');

// Layout routes
Route::get('/layouts/without-menu', [WithoutMenu::class, 'index'])->name('layouts-without-menu');
Route::get('/layouts/without-navbar', [WithoutNavbar::class, 'index'])->name('layouts-without-navbar');
Route::get('/layouts/fluid', [Fluid::class, 'index'])->name('layouts-fluid');
Route::get('/layouts/container', [Container::class, 'index'])->name('layouts-container');
Route::get('/layouts/blank', [Blank::class, 'index'])->name('layouts-blank');

// Page routes
Route::get('/pages/account-settings-account', [AccountSettingsAccount::class, 'index'])->name('pages-account-settings-account');
Route::get('/pages/account-settings-notifications', [AccountSettingsNotifications::class, 'index'])->name('pages-account-settings-notifications');
Route::get('/pages/account-settings-connections', [AccountSettingsConnections::class, 'index'])->name('pages-account-settings-connections');
Route::get('/pages/misc-error', [MiscError::class, 'index'])->name('pages-misc-error');
Route::get('/pages/misc-under-maintenance', [MiscUnderMaintenance::class, 'index'])->name('pages-misc-under-maintenance');

// Authentication page routes (for demo purposes)
Route::get('/auth/login-basic', [LoginBasic::class, 'index'])->name('auth-login-basic');
Route::get('/auth/register-basic', [RegisterBasic::class, 'index'])->name('auth-register-basic');
Route::get('/auth/forgot-password-basic', [ForgotPasswordBasic::class, 'index'])->name('auth-reset-password-basic');

// Card routes
Route::get('/cards/basic', [CardBasic::class, 'index'])->name('cards-basic');

// User Interface routes
Route::get('/ui/accordion', [Accordion::class, 'index'])->name('ui-accordion');
Route::get('/ui/alerts', [Alerts::class, 'index'])->name('ui-alerts');
Route::get('/ui/badges', [Badges::class, 'index'])->name('ui-badges');
Route::get('/ui/buttons', [Buttons::class, 'index'])->name('ui-buttons');
Route::get('/ui/carousel', [Carousel::class, 'index'])->name('ui-carousel');
Route::get('/ui/collapse', [Collapse::class, 'index'])->name('ui-collapse');
Route::get('/ui/dropdowns', [Dropdowns::class, 'index'])->name('ui-dropdowns');
Route::get('/ui/footer', [Footer::class, 'index'])->name('ui-footer');
Route::get('/ui/list-groups', [ListGroups::class, 'index'])->name('ui-list-groups');
Route::get('/ui/modals', [Modals::class, 'index'])->name('ui-modals');
Route::get('/ui/navbar', [Navbar::class, 'index'])->name('ui-navbar');
Route::get('/ui/offcanvas', [Offcanvas::class, 'index'])->name('ui-offcanvas');
Route::get('/ui/pagination-breadcrumbs', [PaginationBreadcrumbs::class, 'index'])->name('ui-pagination-breadcrumbs');
Route::get('/ui/progress', [Progress::class, 'index'])->name('ui-progress');
Route::get('/ui/spinners', [Spinners::class, 'index'])->name('ui-spinners');
Route::get('/ui/tabs-pills', [TabsPills::class, 'index'])->name('ui-tabs-pills');
Route::get('/ui/toasts', [Toasts::class, 'index'])->name('ui-toasts');
Route::get('/ui/tooltips-popovers', [TooltipsPopovers::class, 'index'])->name('ui-tooltips-popovers');
Route::get('/ui/typography', [Typography::class, 'index'])->name('ui-typography');

// Extended UI routes
Route::get('/extended/ui-perfect-scrollbar', [PerfectScrollbar::class, 'index'])->name('extended-ui-perfect-scrollbar');
Route::get('/extended/ui-text-divider', [TextDivider::class, 'index'])->name('extended-ui-text-divider');

// Icon routes
Route::get('/icons/icons-ri', [RiIcons::class, 'index'])->name('icons-ri');

// Form element routes
Route::get('/forms/basic-inputs', [BasicInput::class, 'index'])->name('forms-basic-inputs');
Route::get('/forms/input-groups', [InputGroups::class, 'index'])->name('forms-input-groups');

// Form layout routes
Route::get('/form/layouts-vertical', [VerticalForm::class, 'index'])->name('form-layouts-vertical');
Route::get('/form/layouts-horizontal', [HorizontalForm::class, 'index'])->name('form-layouts-horizontal');

// Table routes
Route::get('/tables/basic', [TablesBasic::class, 'index'])->name('tables-basic');




// Add these routes to your web.php file
Route::middleware(['auth'])->group(function () {
    Route::get('/verification/upload', [DocumentVerificationController::class, 'showUploadForm'])
        ->name('verification.upload');
    Route::post('/verification/upload', [DocumentVerificationController::class, 'uploadDocument'])
        ->name('verification.upload.submit');
    Route::get('/verification/pending', [DocumentVerificationController::class, 'pendingVerification'])
        ->name('verification.pending');
});

use App\Http\Controllers\SupplierOrderController;

Route::prefix('supplier')->name('supplier.')->group(function () {
    Route::get('/orders', [SupplierOrderController::class, 'index'])->name('orders.index');

    Route::post('/orders/{order}/approve', [SupplierOrderController::class, 'approve'])->name('orders.approve');

    Route::post('/orders/{order}/ship', [SupplierOrderController::class, 'ship'])->name('orders.ship');
});


Route::middleware(['auth'])->group(function () {
    // Payment routes
    Route::get('/orders/{order}/pay', [PaymentController::class, 'initiatePayment'])
        ->name('payments.initiate');
    Route::post('/orders/{order}/pay', [PaymentController::class, 'processPayment'])
        ->name('payments.process');
});

Route::middleware(['auth', 'order.paid'])->group(function () {
    Route::post('/orders/{order}/approve', [SupplierController::class, 'approveOrder']);
    Route::post('/orders/{order}/ship', [SupplierController::class, 'markShipped']);
});

Route::prefix('wholesaler')->middleware(['auth', 'role:wholesaler'])->group(function () {
    // Retailer orders
    Route::get('/dashboard', [WholesalerController::class, 'index'])->name('wholesaler.dashboard');
    Route::post('/orders/{order}/approve', [WholesalerController::class, 'approveOrder'])->name('wholesaler.orders.approve');
    Route::post('/orders/{order}/ship', [WholesalerController::class, 'markShipped'])->name('wholesaler.orders.ship');
    
    // Factory orders
    Route::get('/factory-orders', [WholesalerController::class, 'factoryOrders'])->name('wholesaler.factory.orders');
    Route::post('/factory-orders', [WholesalerController::class, 'storeFactoryOrder'])->name('wholesaler.factory.orders.store');
});
Route::prefix('factory')->middleware(['auth', 'role:factory'])->group(function () {
    // Wholesaler orders
    Route::get('/dashboard', [FactoryController::class, 'index'])->name('factory.dashboard');
    Route::post('/orders/{order}/approve', [FactoryController::class, 'approveOrder'])->name('factory.orders.approve');
    Route::post('/orders/{order}/ship', [FactoryController::class, 'markShipped'])->name('factory.orders.ship');
    
    // Supplier orders
    Route::get('/supplier-orders', [FactoryController::class, 'supplierOrders'])->name('factory.supplier.orders');
    Route::post('/supplier-orders', [FactoryController::class, 'storeSupplierOrder'])->name('factory.supplier.orders.store');
});

//retailer orders
Route::prefix('retailer')->middleware(['auth', 'role:retailer'])->group(function () {
    Route::get('/dashboard', [RetailerController::class, 'index'])->name('retailer.dashboard');
    Route::post('/orders', [RetailerController::class, 'storeOrder'])->name('retailer.orders.store');
    Route::get('/orders/{order}', [RetailerController::class, 'showOrder'])->name('retailer.orders.show');
    Route::post('/orders/{order}/receive', [RetailerController::class, 'markReceived'])->name('retailer.orders.receive');
});

// For all roles
Route::middleware('auth')->group(function () {
    Route::get('/orders/{order}/verify', [PaymentController::class, 'showVerificationForm'])
         ->name('payments.verify.form');
    Route::post('/orders/{order}/verify', [PaymentController::class, 'verifyPayment'])
         ->name('payments.verify');
});
