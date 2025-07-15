<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RawMaterial;

class SupplyController extends Controller
{
    //to show raw materials
    public function index()
    {
        $rawMaterials = RawMaterial::all();
        return view('inventory.raw_materials', compact('rawMaterials'));
    }
}
