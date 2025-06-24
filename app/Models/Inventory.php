<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    //
    protected $fillable = [
        'name',
        'product_id',
        'quantity',
        'unit',
        'location',
        'goods_type',
        'store_id',
        'batch_id',
        'storage_condition',
        'expiry_date',
        //'status',
    ];

    public function getAutoStatusAttribute():string
    {
        if($this->quantity >= 25){
            return 'available';
        }
        elseif($this->quantity >= 10){
            return 'limited';
        }
        else{
            return 'out of stock';
        }
    }

    //public function getIsReservedAttribute(): bool
    //{
    //    return $this->orders()->where('shipped', false)->exists();
    //}

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function product()
    {
      return $this->belongsTo(Product::class);
    }

}
