<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductUsage extends Model
{
    //fillable
    protected $fillable = [
        'product_id',
        'order_id',
        'quantity_used',
        'used_on',
    ];

    //relation to products model
    public function products()
    {
        return $this->belongsTo(Products::class);
    }
}
