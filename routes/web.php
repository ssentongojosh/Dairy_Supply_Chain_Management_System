<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
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
// use App\Http\Controllers\dashboard\FarmerDashboard;
use App\Http\Controllers\RetailInventoryController;
use App\Http\Controllers\ReportHistoryController;
// use App\Http\Controllers\SupplierDashboardController;
// use App\Http\Controllers\PlantManagerDashboard;
use App\Http\Controllers\PlantManagerOrderController;
use App\Http\Controllers\PlantManagerInventoryController;
use App\Http\Controllers\MarketplaceController;
use App\Http\Controllers\RawMaterialInventoryController;
use App\Http\Controllers\OrderController;
Use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\FarmerInventoryController;
use App\Http\Controllers\SupplierOrderController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\SupplyController;

use App\Http\Controllers\DocumentVerificationController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\dashboard\UserController;
use App\Models\User;
use App\Http\Controllers\dashboard\SupplierDashboard;
use App\Http\Controllers\SupplierInventoryController;
use App\Http\Controllers\RetailerSupplierController;
use App\Http\Controllers\dashboard\FarmerDashboard;
use App\Http\Controllers\dashboard\PlantManagerDashboard;
// Root route - Welcome page
use App\Http\Controllers\PrInventoryController;

// index page
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
Route::post('/messages/mark-as-read', [ChatController::class, 'markMessageAsRead'])->name('messages.mark-read')->middleware('auth');

// Order routes
Route::get('/app/order', [OrderController::class, 'index'])->name('app.order');

// Inventory routes
Route::get('/app/inventory', [InventoryController::class, 'index'])->name('app-inventory')->middleware('auth');

// Dashboard routes with role middleware
Route::get('/analytics', [Analytics::class, 'index'])
  ->name('dashboard.analytics')
  ->middleware(['auth', 'role:admin']);

Route::get('/retailer/dashboard', [RetailerDashboard::class, 'index'])
  ->name('dashboard.retailer')
  ->middleware(['auth', 'role:retailer']);

// Wholesaler dashboard is defined in the prefix group below
Route::get('/wholesaler/dashboard', [WholesalerDashboard::class, 'index'])
  ->name('wholesaler.dashboard')
  ->middleware(['auth', 'role:wholesaler']);

// Other role dashboard routes
// Wholesaler order routes (for dashboard quick actions)
Route::get('/wholesaler/orders', [OrderController::class, 'index'])
  ->name('wholesaler.orders')
  ->middleware(['auth', 'role:wholesaler']);


Route::get('/wholesaler/orders/create', [OrderController::class, 'createOrder'])->name('wholesaler.orders.create');

// Wholesaler order history route
Route::get('/wholesaler/orders/history', [OrderController::class, 'orderHistory'])
  ->name('wholesaler.orders.history')
  ->middleware(['auth', 'role:wholesaler']);

// Wholesaler order payment show route
Route::get('/wholesaler/orders/{order}/payment', [PaymentController::class, 'showVerificationForm'])
  ->name('wholesaler.orders.payment.show')
  ->middleware(['auth', 'role:wholesaler']);

// Add update status route for orders
Route::put('/orders/{order}/update-status', [OrderController::class, 'updateStatus'])->name('orders.updateStatus');

// Other role dashboard routes controller doesnot exist
Route::get('/farmer/dashboard', [FarmerDashboard::class, 'index'])
  ->name('farmer.dashboard')
  ->middleware(['auth', 'role:farmer']);

Route::get('/driver/dashboard', function() {
  if (!Auth::check() || Auth::user()->role !== \App\Enums\Role::DRIVER) {
    return redirect()->route('home')->with('error', 'Access denied.');
  }
  return view('dashboard.driver');
})->name('driver.dashboard');

Route::get('/warehouse/dashboard', function() {
  if (!Auth::check() || Auth::user()->role !== \App\Enums\Role::WAREHOUSE_MANAGER) {
    return redirect()->route('home')->with('error', 'Access denied.');
  }
  return view('dashboard.warehouse');
})->name('warehouse.dashboard');

Route::get('/executive/dashboard', function() {
  if (!Auth::check() || Auth::user()->role !== \App\Enums\Role::EXECUTIVE) {
    return redirect()->route('home')->with('error', 'Access denied.');
  }
  return view('dashboard.executive');
})->name('executive.dashboard');

Route::get('/inspector/dashboard', function() {
  if (!Auth::check() || Auth::user()->role !== \App\Enums\Role::INSPECTOR) {
    return redirect()->route('home')->with('error', 'Access denied.');
  }
  return view('dashboard.inspector');
})->name('inspector.dashboard');

Route::get('/quality/dashboard', function() {
  if (!Auth::check() || Auth::user()->role !== \App\Enums\Role::QUALITY_ASSURANCE) {
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



//route for inventory
Route::resource('inventoriess', \App\Http\Controllers\ProductInventoryController::class);
//Route::get('/inventory', [PrInventoryController::class, 'index']);
//tryout for supplier inventory
Route::get('/supplier/inventory', function () {
    return view('supplier.inventory');
})->name('supplier.inventory');
Route::get('/supplier/inventory', [SupplierInventoryController::class, 'index'])->name('supplier.inventory');


//route to create a new inventory item
// Route::get('/inventory', [PrInventoryController::class, 'index'])->name('inventory.index');
// Route::get('/inventory/create', [PrInventoryController::class, 'create'])->name('inventory.create');
// Route::post('/inventory', [PrInventoryController::class, 'store'])->name('inventory.store');

//route for search
// Route::get('/inventory/search',[PrInventoryController::class, 'search'])->name('inventory.search');
// Route::get('/inventory/{id}/edit',[PrInventoryController::class, 'edit'])->name('inventory.edit');
// ;

//supply to show
Route::get('/inventory/raw-materials', [SupplyController::class, 'index'])->name('inventory.raw_materials');

//tryout

Route::middleware(['auth'])->group(function () {
    Route::resource('raw_materials', RawMaterialInventoryController::class);
});

//delete inventory
// Route::delete('/inventory/{id}', [PrInventoryController::class, 'destroy'])->name('inventory.destroy');

//raw materials inventory
//route for inventory
Route::resource('raw_materials', \App\Http\Controllers\RawMaterialInventoryController::class);
Route::resource('inventory', RawMaterialInventoryController::class);
Route::get('raw-material', [RawMaterialInventoryController::class, 'index']);

//route to create a new inventory item
Route::get('/plant_manager/dashboard', [RawMaterialInventoryController::class, 'index'])->name('plant_manager.dashboard');
Route::get('/raw-material/create', [RawMaterialInventoryController::class, 'create'])->name('raw-material.create');
Route::post('/raw-material', [RawMaterialInventoryController::class, 'store'])->name('raw-material.store');

//route for search
Route::get('/raw-material/search',[RawMaterialInventoryController::class, 'search'])->name('raw-material.search');
Route::get('/raw-material/{id}/edit',[RawMaterialInventoryController::class, 'edit'])->name('raw-material.edit');
Route::put('/raw-material/{id}',[RawMaterialInventoryController::class, 'update'])->name('raw-material.update');

//update item
Route::put('/inventory/{id}',[RawMaterialInventoryController::class, 'update'])->name('inventory.update');

//delete item
Route::delete('/raw-material/{id}', [RawMaterialInventoryController::class, 'destroy'])->name('raw-material.destroy');


//delivery routes
Route::resource('delivery',DeliveryController::class);
Route::get('/delivery', [DeliveryController::class, 'index'])->name('delivery.index');
Route::get('/delivery/create', [DeliveryController::class, 'create'])->name('delivery.create');
//confirm route for arrival
Route::put('/delivery/{id}/confirm', [DeliveryController::class, 'confirm'])->name('delivery.confirm');
//update the status
Route::put('/delivery/{id}/status', [DeliveryController::class, 'updateStatus'])->name('delivery.updateStatus');
//check for delivery id confirmation
// In routes/api.php
Route::get('/delivery/{id}/status', [DeliveryController::class, 'checkStatus']);
Route::get('/delivery/{delivery}/status', function (App\Models\Delivery $delivery) {
    return response()->json(['status' => $delivery->status]);
});
Route::get('/delivery/{id}/status-page', [DeliveryController::class, 'statusPage'])->name('delivery.statusPage');
Route::post('/delivery/{id}/terminate', [DeliveryController::class, 'terminate'])->name('delivery.terminate');
Route::get('/my-deliveries', [DeliveryController::class, 'myDeliveries'])->name('delivery.mine');


//catalog for inventory
Route::get('/catalog', [CatalogController::class, 'index'])->name('catalog.index');


// User CRUD routes for admin
Route::resource('users', UserController::class)
    ->except(['show'])
    ->middleware(['auth', 'role:admin']);

// Add user detail route
Route::get('/users/{user}', function (User $user) {
    return view('content.dashboard.user-view', compact('user'));
})->name('users.show')->middleware('auth');


// Supplier routes group - CORRECTED VERSION
Route::prefix('supplier')->middleware(['auth', 'role:supplier'])->name('supplier.')->group(function () {
    // Dashboard - using the dedicated dashboard controller
    Route::get('supplier/dashboard', [SupplierDashboard::class, 'index'])->name('dashboard');

    // Order management
    Route::get('/orders', [SupplierOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/history', [SupplierOrderController::class, 'orderHistory'])->name('orders.history');
    Route::get('/orders/{order}', [SupplierOrderController::class, 'showOrder'])->name('orders.show');
    Route::post('/orders/{order}/approve', [SupplierOrderController::class, 'approveOrder'])->name('orders.approve');
    Route::post('/orders/{order}/reject', [SupplierOrderController::class, 'rejectOrder'])->name('orders.reject');
    Route::post('/orders/{order}/ship', [SupplierOrderController::class, 'markShipped'])->name('orders.ship');

    // Inventory management
    Route::get('/inventory', [SupplierInventoryController::class, 'index'])->name('inventory');
    Route::post('/inventory', [SupplierInventoryController::class, 'store'])->name('inventory.store');
    Route::get('/inventory/{inventory}', [SupplierInventoryController::class, 'show'])->name('inventory.show');
    Route::put('/inventory/{inventory}', [SupplierInventoryController::class, 'update'])->name('inventory.update');
    Route::put('/inventory/{inventory}/update-quantity', [SupplierInventoryController::class, 'updateQuantity'])->name('inventory.update-quantity');
    Route::put('/inventory/{inventory}/threshold', [SupplierInventoryController::class, 'updateThreshold'])->name('inventory.threshold');
    Route::post('/inventory/{inventory}/adjust', [SupplierInventoryController::class, 'adjustStock'])->name('inventory.adjust');
    Route::delete('/inventory/{inventory}', [SupplierInventoryController::class, 'destroy'])->name('inventory.destroy');
    Route::get('/inventory/products', [SupplierInventoryController::class, 'getAvailableProducts'])->name('inventory.products');
    Route::post('/inventory/bulk-threshold', [SupplierInventoryController::class, 'bulkUpdateThreshold'])->name('inventory.bulk-threshold');
    Route::post('/inventory/bulk-import', [SupplierInventoryController::class, 'bulkImport'])->name('inventory.bulk-import');
    Route::get('/inventory/template', [SupplierInventoryController::class, 'downloadTemplate'])->name('inventory.template');
    Route::get('/inventory/stats', [SupplierInventoryController::class, 'getStats'])->name('inventory.stats');
});




Route::middleware(['auth'])->group(function () {

    // Universal Payment Routes
    Route::get('/orders/{order}/pay', [PaymentController::class, 'initiatePayment'])
        ->name('payments.initiate');

    Route::post('/orders/{order}/pay', [PaymentController::class, 'processPayment'])
        ->name('payments.process');

    Route::get('/orders/{order}/verify', [PaymentController::class, 'showVerificationForm'])
        ->name('payments.verify.form');

    Route::post('/orders/{order}/verify', [PaymentController::class, 'verifyPayment'])
        ->name('payments.verify.process');
});

//retailer orders - CORRECTED SECTION
Route::get('/retailer/dashboard', [OrderController::class, 'index'])->name('dashboard.retailer'); // Fixed: was '/dashboard'
Route::get('/retailer/orders', [OrderController::class, 'outgoingOrders'])->name('retailer.orders');
Route::post('/retailer/orders', [OrderController::class, 'storeOrder'])->name('retailer.orders.store');
Route::get('/retailer/orders/create', [OrderController::class, 'createOrder'])->name('retailer.orders.create');
Route::get('/retailer/orders/{order}', function (\App\Models\Order $order) {
    return view('retailer.order-show', compact('order'));
})->middleware('auth')->name('retailer.orders.show');
Route::patch('/retailer/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('retailer.orders.updateStatus');
Route::post('/retailer/orders/{order}/cancel', [OrderController::class, 'cancelOrder'])->name('retailer.orders.cancel');
Route::get('/retailer/orders/{order}/pay', [OrderController::class, 'showPayment'])->name('retailer.orders.payment.show');
Route::post('/retailer/orders/{order}/pay', [OrderController::class, 'processPayment'])->name('retailer.orders.payment.process');
// Route::post('/retailer/orders/{order}/payment', [OrderController::class, 'processPayment'])->name('retailer.orders.payment.process'); // duplicate route
Route::get('/retailer/orders/history', [OrderController::class, 'orderHistory'])->name('retailer.orders.history');

// Inventory routes
Route::get('/inventory', [RetailInventoryController::class, 'index'])->name('retailer.inventory');
Route::post('/inventory', [RetailInventoryController::class, 'store'])->name('retailer.inventory.store');
Route::patch('/inventory/{inventory}', [RetailInventoryController::class, 'updateQuantity'])->name('retailer.inventory.update');
Route::delete('/inventory/{inventory}', [RetailInventoryController::class, 'destroy'])->name('retailer.inventory.destroy');
Route::get('/inventory/products', [RetailInventoryController::class, 'getAvailableProducts'])->name('retailer.inventory.products');

// Threshold management routes
Route::patch('/inventory/{inventory}/threshold', [RetailInventoryController::class, 'updateThreshold'])->name('retailer.inventory.threshold');
Route::post('/inventory/bulk-threshold', [RetailInventoryController::class, 'bulkUpdateThreshold'])->name('retailer.inventory.bulk-threshold');

// Auto-reorder routes
Route::post('/inventory/{inventory}/reorder', [RetailInventoryController::class, 'createReorder'])->name('retailer.inventory.reorder');
Route::post('/inventory/auto-reorder', [RetailInventoryController::class, 'autoReorder'])->name('retailer.inventory.auto-reorder');

// Suppliers listing
Route::get('/suppliers', [RetailerSupplierController::class, 'index'])->name('retailer.suppliers');

// Retailer vendor browse


// Vendor actions for retailer




// For all roles
Route::middleware('auth')->group(function () {
    Route::get('/orders/{order}/verify', [PaymentController::class, 'showVerificationForm'])
         ->name('payments.verify.form');
    Route::post('/orders/{order}/verify', [PaymentController::class, 'verifyPayment'])
         ->name('payments.verify');
});

// Marketplace routes (browse products and sellers)
Route::middleware(['auth'])->group(function () {
    Route::get('/marketplace', [MarketplaceController::class, 'index'])
         ->name('marketplace.index');
    Route::get('/marketplace/product/{product}', [MarketplaceController::class, 'showProduct'])
         ->name('marketplace.product');
    Route::get('/marketplace/add-product', [MarketplaceController::class, 'showAddForm'])
         ->name('marketplace.add-product');
    Route::post('/marketplace/add-product', [MarketplaceController::class, 'addToInventory'])
         ->name('marketplace.add-to-inventory');
    Route::post('/marketplace/create-product', [MarketplaceController::class, 'createProduct'])
         ->name('marketplace.create-product');
});

//supplier order
Route::prefix('supplier')->middleware(['auth', 'role:supplier'])->group(function () {
    Route::get('supplier/dashboard', [SupplierDashboard::class, 'index'])->name('supplier.dashboard');
    Route::get('/orders', [OrderController::class, 'orderHistory'])->name('supplier.orders');
    Route::get('/orders/dashboard', [OrderController::class, 'index'])->name('supplier.order.dashboard');
    Route::get('/orders/{order}', [OrderController::class, 'showOrder'])->name('supplier.orders.show');
    Route::post('/orders/{order}/approve', [OrderController::class, 'approveOrder'])->name('supplier.orders.approve');
    Route::post('/orders/{order}/reject', [OrderController::class, 'rejectOrder'])->name('supplier.orders.reject');
    Route::post('/orders/{order}/ship', [OrderController::class, 'markShipped'])->name('supplier.orders.ship');
    Route::get('/orders/history', [OrderController::class, 'history'])->name('supplier.orders.history');
    Route::get('/orders/history', [OrderController::class, 'orderHistory'])->name('supplier.orders.history');
    Route::patch('/orders/{order}/approve', [OrderController::class, 'approveOrder'])->name('supplier.orders.approve');
    Route::patch('/orders/{order}/reject', [OrderController::class, 'rejectOrder'])->name('supplier.orders.reject');
    Route::patch('/orders/{order}/ship', [OrderController::class, 'markShipped'])->name('supplier.orders.ship');
    Route::patch('/orders/{order}/update', [OrderController::class, 'updateStatus'])->name('supplier.orders.updateStatus');
    Route::get('/orders/index', [OrderController::class, 'index'])->name('supplier.orders.index');
    Route::get('/inventory', [SupplierInventoryController::class, 'index'])->name('supplier.inventory');
});
Route::get('/plant_manager/orders/create', [App\Http\Controllers\OrderController::class, 'createOrder'])->name('plant_manager.orders.create');
// Route::get('/plant_manager/standalone-test', function() { return 'Standalone works!'; });
//plant_manager order
Route::prefix('plant_manager')->middleware(['auth', 'role:plant_manager'])->group(function () {
    Route::get('/dashboard', [PlantManagerDashboard::class, 'index'])->name('plant_manager.dashboard');
    Route::get('/orders/history', [OrderController::class, 'orderHistory'])->name('plant_manager.orders');

    Route::get('/orders/dashboard', [OrderController::class, 'index'])->name('plant_manager.order.dashboard');

    Route::get('/orders/history', [OrderController::class, 'orderHistory'])->name('plant_manager.orders_history');
    Route::get('/orders/{order}', [OrderController::class, 'showOrder'])->name('plant_manager.orders.show');
    Route::get('/orders/create', [OrderController::class, 'createOrder'])->name('plant_manager.orders.create');
    Route::post('/orders', [OrderController::class, 'storeOrder'])->name('plant_manager.orders.store');
    Route::patch('/orders/{order}/approve', [OrderController::class, 'approveOrder'])->name('plant_manager.orders.approve');
    Route::patch('/orders/{order}/reject', [OrderController::class, 'rejectOrder'])->name('plant_manager.orders.reject');
    Route::patch('/orders/{order}/ship', [OrderController::class, 'markShipped'])->name('plant_manager.orders.ship');
    Route::get('/orders/history', [OrderController::class, 'history'])->name('plant_manager.orders.history');
     Route::patch('/orders/{order}/update', [OrderController::class, 'updateStatus'])->name('plant_manager.orders.updateStatus');

    Route::patch('/orders/{order}/update', [OrderController::class, 'updateStatus'])->name('plant_manager.orders.updateStatus');
    Route::get('/inventory', [PlantManagerInventoryController::class, 'index'])->name('plant_manager.inventory');
});

Route::get('plant_manager/orders', [OrderController::class, 'orderHistory'])->name('plant_manager.orders')->middleware(['auth', 'role:plant_manager']);


//wholesaler order
Route::prefix('wholesaler')->middleware(['auth', 'role:wholesaler'])->group(function () {
    // Place specific routes before wildcard routes
    Route::get('/orders/history', [OrderController::class, 'history'])->name('wholesaler.orders.history');
    // Route::get('wholesaler/dashboard', [WholesalerDashboardController::class, 'index'])->name('wholesaler.dashboard');
    Route::get('/orders', [OrderController::class, 'index'])->name('wholesaler.orders');
    Route::get('/orders/dashboard', [OrderController::class, 'index'])->name('wholesaler.order.dashboard');
    Route::get('/orders', [OrderController::class, 'orderHistory'])->name('wholesaler.orders');
    Route::get('/orders/create', [OrderController::class, 'createOrder'])->name('wholesaler.orders.create');
    Route::post('/orders', [OrderController::class, 'storeOrder'])->name('wholesaler.orders.store');

    // Wildcard routes should come after specific routes
    Route::post('/orders/store', [OrderController::class, 'storeOrder'])->name('wholesaler.orders.store');
    Route::get('/orders/{order}', [OrderController::class, 'showOrder'])->name('wholesaler.orders.show');
    Route::get('/orders/create', [OrderController::class, 'createOrder'])->name('wholesaler.orders.create');
        Route::post('/orders', [OrderController::class, 'storeOrder'])->name('wholesaler.orders.store');
    Route::post('/orders/{order}/approve', [OrderController::class, 'approveOrder'])->name('wholesaler.orders.approve');
    Route::post('/orders/{order}/reject', [OrderController::class, 'rejectOrder'])->name('wholesaler.orders.reject');    Route::post('/orders/{order}/ship', [OrderController::class, 'markShipped'])->name('wholesaler.orders.ship');

    // Inventory management for wholesaler
    Route::get('/inventory', [InventoryController::class, 'wholesalerInventory'])->name('wholesaler.inventory');
    Route::get('/inventory/products', [InventoryController::class, 'wholesalerGetProducts'])->name('wholesaler.inventory.products');
    Route::post('/inventory', [InventoryController::class, 'wholesalerStore'])->name('wholesaler.inventory.store');
    Route::patch('/inventory/{inventory}', [InventoryController::class, 'wholesalerUpdateQuantity'])->name('wholesaler.inventory.update');
    Route::patch('/inventory/{inventory}/threshold', [InventoryController::class, 'wholesalerUpdateThreshold'])->name('wholesaler.inventory.threshold');
    Route::delete('/inventory/{inventory}', [InventoryController::class, 'wholesalerDestroy'])->name('wholesaler.inventory.destroy');
});

    Route::post('/orders/{order}/reject', [OrderController::class, 'rejectOrder'])->name('wholesaler.orders.reject');
    Route::post('/orders/{order}/ship', [OrderController::class, 'markShipped'])->name('wholesaler.orders.ship');
    Route::get('/orders/history', [OrderController::class, 'orderHistory'])->name('wholesaler.orders.history');
    // Route::get('/inventory', [wholesalerInventoryController::class, 'index'])->name('wholesaler.inventory');


// Inventory management contrller doesnot exist
    Route::get('/inventory', [FarmerInventoryController::class, 'index'])->name('farmer.inventory');
    Route::post('/inventory', [FarmerInventoryController::class, 'store'])->name('farmer.inventory.store');
    Route::put('/inventory/{inventory}/update-quantity', [FarmerInventoryController::class, 'updateQuantity'])->name('farmer.inventory.update-quantity');
    Route::put('/inventory/{inventory}/threshold', [FarmerInventoryController::class, 'updateThreshold'])->name('farmer.inventory.threshold');
    Route::delete('/inventory/{inventory}', [FarmerInventoryController::class, 'destroy'])->name('farmer.inventory.destroy');
    Route::get('/inventory/products', [FarmerInventoryController::class, 'getAvailableProducts'])->name('farmer.inventory.products');

// Plant Manager routes group

Route::prefix('plant_manager')->middleware(['auth', 'role:plant_manager'])->group(function () {
    // Dashboard - using the dedicated dashboard controller
    Route::get('plant_manager/dashboard', [PlantManagerDashboard::class, 'index'])->name('plant_manager.dashboard');
// Farmer Order Management
Route::prefix('farmer')->middleware(['auth', 'verified'])->group(function () {
    Route::get('/orders', [OrderController::class, 'index'])->name('farmer.orders.dashboard');

    // Change this name to avoid conflict
    Route::get('/orders/history', [OrderController::class, 'orderHistory'])->name('farmer.orders.history');

    Route::get('/orders/{order}', [OrderController::class, 'showOrder'])->name('farmer.orders.show');
    Route::post('/orders/{order}/approve', [OrderController::class, 'approveOrder'])->name('farmer.orders.approve');
    Route::post('/orders/{order}/reject', [OrderController::class, 'rejectOrder'])->name('farmer.orders.reject');
    Route::post('/orders/{order}/ship', [OrderController::class, 'markShipped'])->name('farmer.orders.ship');


});



    // Inventory management
    Route::get('/inventory', [PlantManagerInventoryController::class, 'index'])->name('plant_manager.inventory');
    Route::post('/inventory', [PlantManagerInventoryController::class, 'store'])->name('plant_manager.inventory.store');
    Route::put('/inventory/{inventory}/update-quantity', [PlantManagerInventoryController::class, 'updateQuantity'])->name('plant_manager.inventory.update-quantity');
    Route::put('/inventory/{inventory}/threshold', [PlantManagerInventoryController::class, 'updateThreshold'])->name('plant_manager.inventory.threshold');
    Route::delete('/inventory/{inventory}', [PlantManagerInventoryController::class, 'destroy'])->name('plant_manager.inventory.destroy');
    Route::get('/inventory/products', [PlantManagerInventoryController::class, 'getAvailableProducts'])->name('plant_manager.inventory.products');
    Route::post('/inventory/process', [PlantManagerInventoryController::class, 'processProduction'])->name('plant_manager.inventory.process');
});


Route::middleware(['auth'])->group(function () {
    // Report routes
   Route::get('/report/settings',[ReportController::class, 'index'])->name('report-settings');

   Route::get('/reports/history', [ReportHistoryController::class, 'index'])->name('reports-history');
    // This route uses route model binding: {report} will automatically load the Report model by ID
    Route::get('/reports/history/{report}/download', [ReportHistoryController::class, 'download'])->name('reports.history.download');
    Route::get('/reports/history/{report}/preview', [ReportHistoryController::class, 'preview'])->name('reports.history.preview');

    // Report configuration routes
    Route::get('/reports/download-on-demand', [ReportConfigurationController::class, 'downloadOnDemand'])->name('reports.download-on-demand');
});

// Farmer Order Management
Route::prefix('farmer')->middleware(['auth', 'verified'])->group(function () {
    Route::get('/orders', [OrderController::class, 'index'])->name('farmer.orders.dashboard');
    Route::get('/orders', [OrderController::class, 'orderHistory'])->name('farmer.orders');
    // Change this name to avoid conflict
    Route::get('/orders/history', [OrderController::class, 'orderHistory'])->name('farmer.orders.history');
    Route::get('/orders/{order}', [OrderController::class, 'showOrder'])->name('farmer.orders.show');
    Route::post('/orders/{order}/approve', [OrderController::class, 'approveOrder'])->name('farmer.orders.approve');
    Route::post('/orders/{order}/reject', [OrderController::class, 'rejectOrder'])->name('farmer.orders.reject');
    Route::post('/orders/{order}/ship', [OrderController::class, 'markShipped'])->name('farmer.orders.ship');
  });

    // Route::get('/inventory', [FarmerInventoryController::class, 'index'])->name('farmer.inventory');


Route::post('/products', [ProductController::class, 'store'])->name('product.store')->middleware(['auth', 'role:plant_manager']);
Route::post('/raw-materials', [RawMaterialInventoryController::class, 'store'])->name('raw_materials.store')->middleware(['auth', 'role:plant_manager']);
// ... existing code ...
Route::get('/wholesaler/orders/{order}/pay', [PaymentController::class, 'initiatePayment'])->name('wholesaler.orders.pay');
Route::get('/seller/{seller}/products', [OrderController::class, 'getProductsForSeller'])->name('seller.products');
Route::get('/plant_manager/orders', [App\Http\Controllers\OrderController::class, 'outgoingOrders'])->name('plant_manager.orders');
Route::get('/farmer/inventory', [App\Http\Controllers\InventoryController::class, 'index'])->name('farmer.inventory');

// Retailer order history route
Route::get('/retailer/orders/history', [OrderController::class, 'orderHistory'])
    ->name('retailer.orders.history')
    ->middleware(['auth', 'role:retailer']);

// Plant manager order history route
Route::get('/plant_manager/orders/history', [OrderController::class, 'orderHistory'])
    ->name('plant_manager.orders.history')
    ->middleware(['auth', 'role:plant_manager']);

// Supplier order history route
Route::get('/supplier/orders/history', [OrderController::class, 'orderHistory'])
    ->name('supplier.orders.history')
    ->middleware(['auth', 'role:supplier']);
