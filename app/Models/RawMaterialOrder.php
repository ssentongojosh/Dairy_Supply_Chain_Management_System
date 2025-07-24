<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RawMaterialOrder extends Model
{
    //

    protected $table = 'rawmaterials_orders';

    protected $fillable = [
        'buyer_id',
        'seller_id',
        'status',
        'payment_status',
        'total_amount',
    ];

    public function items()
    {
        return $this->hasMany(RawMaterialOrderItem::class, 'order_id');
    }

    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }
}
