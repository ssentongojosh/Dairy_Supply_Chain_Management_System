<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WholesaleDelivery extends Model
{
    //colummns
    protected $fillable = [
        'order_id', 'product_id', 'delivered_by', 'quantity', 'delivery_date', 'status',
    ];

    //relation
    public function product() {
        return $this->belongsTo(Products::class);
    }

    public function order() {
        return $this->belongsTo(Order::class);
    }

    public function user() {
        return $this->belongsTo(User::class, 'delivered_by');
    }
}
