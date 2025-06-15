<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class VendorController extends Controller
{
    public function showApplicationForm()
    {
        return view('vendor.apply');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'vendor_type' => 'required|in:retailer,wholesaler,supplier,factory',
        ]);
        if ($request->vendor_type === 'retailer') {
    return redirect()->route('retailer.dashboard');
}


        switch ($request->vendor_type) {
            case 'retailer':
                return redirect()->away('https://retailer.example.com');
            case 'wholesaler':
                return redirect()->away('https://wholesaler.example.com');
            case 'supplier':
                return redirect()->away('https://supplier.example.com');
            case 'factory':
                return redirect()->away('https://factory.example.com');
            default:
                return redirect()->route('vendor.apply')
                    ->withErrors(['vendor_type' => 'Invalid vendor type selected']);
        }
    }
}

