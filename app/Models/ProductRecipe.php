<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductRecipe extends Model
{
    //
   //table
   protected $table = 'product_raw_material';

    protected $fillable = [
        'product_id',
        'raw_material_id',
        'quantity_required',
    ];

    //relationships
    public function product()
    {
        return $this->belongsTo(Products::class);
    }

    public function rawMaterial()
    {
        return $this->belongsTo(RawMaterial::class, 'raw_material_id');
    }
}
