<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Products extends Model
{
    //table to use in db
    protected $table = 'products';
    //
    protected $fillable = [
        'name',
        'image',
        'price',
        'quantity',
        'manufacture_date',
        'supplier_id',
        'sale_unit',
        'threshold',
    ];

    protected $casts = [
        'manufacture_date' => 'date',
    ];

    //relationship to the productions and product_usuage table
    public function productions()
    {
        return $this->hasMany(Production::class);
    }

    public function usages()
    {
        return $this->hasMany(ProductUsage::class);
    }

    //for production
    //public function recipeItems()
    //{
    //return $this->hasMany(ProductRecipe::class);
    //}
    //another sample
    public function recipeItems() 
    {
    return $this->hasMany(ProductRecipe::class, 'product_id');
    }

}
