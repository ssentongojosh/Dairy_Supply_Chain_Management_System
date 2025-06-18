<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    //
    protected $fillable = [
        'product_id',
        'quantity',
        'order_date',
        'delivery_date',
        'price',
    ];

      public function product()
    {
        return $this->belongsTo(Product::class);
    }
    
}
