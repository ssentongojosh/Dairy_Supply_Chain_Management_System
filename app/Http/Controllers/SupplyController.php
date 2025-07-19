<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RawMaterial;

class SupplyController extends Controller
{
    //to show raw materials
    public function index(Request $request)
    {
        $query = $request->input('search');
        $rawMaterials = RawMaterial::when($query, function($q) use($query){
            return $q->where('name', 'like', '%' . $query . '%');
        })->get();
        return view('inventory.raw_materials', compact('rawMaterials','query'));
    }
}
