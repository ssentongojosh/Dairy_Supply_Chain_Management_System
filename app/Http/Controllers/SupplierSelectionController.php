<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RawMaterial;
use App\Models\Supplier;

class SupplierSelectionController extends Controller
{
    //select and keep raw materials
    public function index()
    {
        $supplier = auth()->user(); // assuming supplier is logged in
        $rawMaterials = RawMaterial::all();
        $selectedMaterials = $supplier->rawMaterials->pluck('id');

        return view('supplier.select_materials', compact('rawMaterials', 'selectedMaterials'));
    }

    public function store(Request $request)
    {
        $supplier = auth()->user();

        $request->validate([
            'raw_materials' => 'array',
            'raw_materials.*' => 'exists:raw_materials,id',
        ]);

        // Sync selections
        $supplier->rawMaterials()->sync($request->raw_materials);

        return redirect()->route('supplier.dashboard')->with('success', 'Your supply list has been updated!');
    }
}
