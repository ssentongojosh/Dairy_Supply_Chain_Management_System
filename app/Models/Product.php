<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    //
    protected $primaryKey = 'product_id';

    protected $fillable = [
        'name',
        'quantity',
        'unit_price',
        'manufacture_date',
        'expiry_date',
    ];

    protected $dates = [
        'manufacture_date',
        'expiry_date',
    ];

    public function inventories()
{
    return $this->hasMany(Inventory::class);
}

    public function orders()
{
    return $this->hasMany(Order::class);
}

}
