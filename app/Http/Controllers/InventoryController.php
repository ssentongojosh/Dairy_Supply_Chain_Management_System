<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InventoryController extends Controller
{
    /**
     * Route users to appropriate inventory view based on their role
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
                // Redirect retailers to their inventory page
                return redirect()->route('retailer.inventory');

            case 'wholesaler':
                // Redirect wholesalers to their inventory page
                return redirect()->route('wholesaler.inventory');

            case 'farmer':
                // Redirect farmers to their inventory page
                return redirect()->route('farmer.inventory');

            case 'plant_manager':
                // Redirect plant managers to their inventory page
                return redirect()->route('plant_manager.inventory');

            case 'factory':
                // TODO: Create factory inventory controller
                return redirect()->route('dashboard')->with('info', 'Factory inventory management coming soon!');

            case 'supplier':
                // TODO: Create supplier inventory controller
                return redirect()->route('dashboard')->with('info', 'Supplier inventory management coming soon!');

            case 'admin':
                // TODO: Create admin inventory overview
                return redirect()->route('dashboard')->with('info', 'Admin inventory overview coming soon!');

            default:
                return redirect()->route('dashboard')->with('error', 'Access denied.');
        }
    }
}
