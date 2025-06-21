<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    /**
     * Route users to appropriate order view based on their role
     */
    public function index()
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Normalize role if enum
        $role = $user->role;
        if ($role instanceof \App\Enums\Role) {
            $role = $role->value;
        }

        // Route users based on their role
        switch ($role) {
            case 'retailer':
                // Redirect retailers to their order history route
                return redirect()->route('retailer.orders');

            case 'wholesaler':
                // Redirect wholesalers to their order history route
                return redirect()->route('wholesaler.orders');

            case 'farmer':
                // Redirect farmers to their order history route
                return redirect()->route('farmer.orders');

            case 'plant_manager':
                // Redirect plant managers to their order dashboard
                return redirect()->route('plant_manager.orders.dashboard');

            case 'factory':
                // TODO: Create factory order controller
                return redirect()->route('dashboard')->with('info', 'Factory order management coming soon!');

            case 'supplier':
                // TODO: Create supplier order controller
                return redirect()->route('dashboard')->with('info', 'Supplier order management coming soon!');

            case 'admin':
                // TODO: Create admin order overview
                return redirect()->route('dashboard')->with('info', 'Admin order overview coming soon!');

            default:
                return redirect()->route('dashboard')->with('error', 'Access denied.');
        }
    }
}
