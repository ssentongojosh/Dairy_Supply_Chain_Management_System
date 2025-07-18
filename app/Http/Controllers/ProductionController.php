<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProductRecipe;
use App\Models\Products;
use App\Models\Production;
use App\Models\RawMaterial;
use App\Models\RawMaterialUsage;

class ProductionController extends Controller
{
    //to show the recipe
    public function index()
    {
        //$allProducts = Products::all();products = Products::with('recipeItems.rawMaterial')->get();

        $products = Products::with('recipeItems.rawMaterial')->get();
        //dd($products->first()->toArray());
        return view('recipe.index', compact('products'));
    }

    //to store values for production 
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        //how much to produce
        $product = Products::findOrFail($request->product_id);
        $quantityToProduce = $request->quantity;

        //raw materials selection
        $recipeItems = ProductRecipe::where('product_id', $product->id)->get();

        foreach ($recipeItems as $item) {
            $rawMaterial = RawMaterial::findOrFail($item->raw_material_id);

            $totalNeeded = $item->quantity_required * $quantityToProduce;

            if ($rawMaterial->quantity < $totalNeeded) {
                return back()->with('error', "Not enough {$rawMaterial->name} in stock.");
            }

            // Deduct stock
            $rawMaterial->quantity -= $totalNeeded;
            $rawMaterial->save();

            // Log usage
            RawMaterialUsage::create([
                'raw_material_id' => $rawMaterial->id,
                'quantity_used' => $totalNeeded,
                'product_id' => $product->id,
                'used_on' => now(),
            ]);
        }

        // Update product stock
        $product->quantity += $quantityToProduce;
        $product->save();

        // Log production batch
        Production::create([
            'product_id' => $product->id,
            'quantity_produced' => $quantityToProduce,
            'production_date' => now(),
            'batch_code' => 'BATCH-' . strtoupper(uniqid()),
        ]);

        return back()->with('success', 'Production successful.');
    }

    //logic for creating a recipe
    public function edit(Products $product)
    {
        // Get all raw materials to select from
        $rawMaterials = RawMaterial::all();

        // Load existing recipe items if any
        $recipeItems = $product->recipeItems()->get();

        return view('recipe.create', compact('product', 'rawMaterials', 'recipeItems'));
    }

    //edits for the recipe
    public function update(Request $request, Products $product)
    {
        //$product = Products::findOrFail($id);
        
        $data = $request->validate([
            'raw_materials' => 'required|array',
            'raw_materials.*' => 'exists:raw_materials,id',
            'quantities' => 'required|array',
            'quantities.*' => 'numeric|min:0',
        ]);

        $rawMaterials = $data['raw_materials'];
        $quantities = $data['quantities'];

        // Delete old recipe items for this product first
        $product->recipeItems()->delete();

        //to insert the recipe
        if ($product->recipeItems()->exists()) {
        return back()->with('error', 'This product already has a recipe.');
        }

        // Insert new recipe items
        foreach ($rawMaterials as $index => $rawMaterialId) {
            if (isset($quantities[$index]) && $quantities[$index] > 0) {
                ProductRecipe::create([
                    'product_id' => $product->id,
                    'raw_material_id' => $rawMaterialId,
                    'quantity_required' => $quantities[$index],
                ]);
            }
        }

        return redirect()->route('recipe.index', $product->id)
                         ->with('success', 'Recipe updated successfully.');
    }

    //for checkout for production
    public function checkProduction($productId)
    {
    $recipe = ProductRecipe::where('product_id', $productId)->get();

    if ($recipe->isEmpty()) {
        return response()->json([
            'has_recipe' => false,
            'has_enough_materials' => false
        ]);
    }

    // Simulate checking enough stock
    $enough = true;
    foreach ($recipe as $item) {
        $material = RawMaterial::find($item->raw_material_id);
        if (!$material || $material->quantity < $item->quantity_quantity) {
            $enough = false;
            break;
        }
    }

    return response()->json([
        'has_recipe' => true,
        'has_enough_materials' => $enough
    ]);
    }


}
