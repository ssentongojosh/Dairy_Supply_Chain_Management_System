<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;    protected $fillable = [
        'name',
        'sku',
        'description',
        'price',
        'cost',
        'category',
        'supplier_id', // Using supplier_id as per the migration
        'image_url',
        'is_active'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'cost' => 'decimal:2',
        'is_active' => 'boolean'
    ];    // Relationships
    public function supplier()
    {
        return $this->belongsTo(User::class, 'supplier_id');
    }

    // Alias for backward compatibility
    public function user()
    {
        return $this->belongsTo(User::class, 'supplier_id');
    }

    // Alias for backward compatibility
    public function vendor()
    {
        return $this->belongsTo(User::class, 'supplier_id');
    }

    public function inventory()
    {
        return $this->hasMany(Inventory::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}