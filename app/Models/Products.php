<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Products extends Model
{
    //
    protected $fillable = [
        'name',
        'price',
        'quantity',
        'manufacture_date',
        'supplier_id',
    ];

    protected $casts = [
        'manufacture_date' => 'date',
    ];
}
