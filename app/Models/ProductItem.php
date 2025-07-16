<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'user_id',
        'quantity',
        'cost_price',
        'selling_price',
        'minimum_stock',
        'maximum_stock',
        'expiry_date',
        'manufacture_date',
        'batch_number',
        'status'
    ];

    protected $casts = [
        'cost_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'expiry_date' => 'date',
        'manufacture_date' => 'date'
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Check if product is low stock
    public function isLowStock(): bool
    {
        return $this->quantity <= $this->minimum_stock;
    }

    // Check if product is expired
    public function isExpired(): bool
    {
        return $this->expiry_date && $this->expiry_date < now();
    }

    // Get status badge color
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'active' => 'success',
            'expired' => 'danger',
            'damaged' => 'warning',
            'sold' => 'info',
            default => 'secondary'
        };
    }
}
