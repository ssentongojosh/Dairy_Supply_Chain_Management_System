<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use App\Models\RawMaterial;


class Inventory extends Model
{
    //
    protected $fillable = [
        'name',
        'product_id',
        'quantity',
        'reorder_point',
        'unit_cost',
        'selling_price',
        'unit',
        'location',
        'last_restocked_at',
        'auto_order_quantity'
    ];

    protected $casts = [
        'quantity' => 'integer',
        'reorder_point' => 'integer',
        'unit_cost' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'last_restocked_at' => 'date'
    ];

    // Relationships
    public function user()
    {
    return $this->belongsTo(User::class);
    }
    //[
       // 'goods_type',
       // 'store_id',
     //   'batch_id',
        //'storage_condition',
      //  'expiry_date',
        //'status',
    //];

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
