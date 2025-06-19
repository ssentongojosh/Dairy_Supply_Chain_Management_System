<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RetailerSupplierController extends Controller
{
    /**
     * Display a list of all wholesalers for retailers.
     */
    public function index()
    {
        // Ensure user is retailer
        if (!Auth::check() || Auth::user()->role !== 'retailer') {
            abort(403);
        }

        $wholesalers = User::where('role', 'wholesaler')
                            ->orderBy('name')
                            ->get();

        return view('retailer.wholesalers', compact('wholesalers'));
    }
}
