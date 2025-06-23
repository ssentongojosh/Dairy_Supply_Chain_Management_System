<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    use HasFactory;    protected $fillable = [
        'user_id',
        'product_id',
        'quantity',
        'reorder_point',
        'unit_cost',
        'selling_price',
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

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Accessors
    public function getIsLowStockAttribute()
    {
        return $this->quantity <= $this->reorder_point;
    }

    public function getIsOutOfStockAttribute()
    {
        return $this->quantity === 0;
    }
}
