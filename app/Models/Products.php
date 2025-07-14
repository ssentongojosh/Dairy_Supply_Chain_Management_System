<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Products extends Model
{
    //
    protected $fillable = [
        'name',
        'image',
        'price',
        'quantity',
        'manufacture_date',
        'supplier_id',
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

}
