<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Production extends Model
{
    //fillable
    protected $fillable = [
        'product_id',
        'order_id',
        'quantity_produced',
        'batch_code',
        'production_date',
    ];

    //relation to products model
    public function products()
    {
        return $this->belongsTo(Products::class);
    }
}
