<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    //
    protected $fillable = [
        'product_id',
        'quantity',
        'location',
        'goods_type',
        'store_id',
        'batch_id',
        'storage_condition',
        'expiry_date',
        'status',
    ];

    public function product()
{
    return $this->belongsTo(Product::class);
}

}
