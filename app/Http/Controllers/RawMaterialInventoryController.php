<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RawMaterialInventoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $items = RawMaterial::all();
        return view('raw-material.index', compact('items'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:15',
            'quantity' => 'required|integer|min:1',
            'unit' => 'required|string|max:10',
        ]);
        
        //save and store new item
        $newItem = RawMaterial::create($data);

        //new item exists so can now be stored
        return redirect()->route('raw-material.index')->with('success', 'Item added successfully!')->with('newItem', $newItem);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $item = RawMaterial::findOrFail($id);

        $data = $request->validate([
            'name' => 'required|string|max:15',
            'quantity' => 'required|integer|min:1',
            'unit' => 'required|string|max:10',
        ]);

        $item->update($data);

        return redirect()->route('raw-material.index')->with('success', 'Raw material updated!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $item = RawMaterial::findOrFail($id);
        $item->delete();

        return redirect()->route('raw-material.index')->with('success', 'Raw material deleted.');
    }
}
