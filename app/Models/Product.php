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
        'added_on', //inventory table for updates on final processing
        'supplier_id', // Using supplier_id as per the migration
        'image_url',
        'is_active',
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

    // Many-to-many relationship with users through product_items
    public function users()
    {
        return $this->belongsToMany(User::class, 'product_items')
                    ->withPivot([
                        'quantity', 
                        'cost_price', 
                        'selling_price', 
                        'minimum_stock',
                        'maximum_stock',
                        'expiry_date',
                        'manufacture_date',
                        'batch_number',
                        'status'
                    ])
                    ->withTimestamps();
    }

    // Direct relationship to product items
    public function productItems()
    {
        return $this->hasMany(ProductItem::class);
    }

    // Get available stock across all users
    public function getTotalStockAttribute()
    {
        return $this->productItems()->where('status', 'active')->sum('quantity');
    }

    // Get users who have this product in stock
    public function usersWithStock()
    {
        return $this->users()->wherePivot('quantity', '>', 0)
                            ->wherePivot('status', 'active');
    }
}

