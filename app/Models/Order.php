<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'buyer_id',
        'seller_id',
        'status',
        'total_amount',
        'payment_status',
        'approved_at',
        'payment_due_date',
        'notes'
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'approved_at' => 'datetime',
        'payment_due_date' => 'datetime',
    ];

    // Relationship with buyer (user who placed the order)
    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    // Relationship with seller (user who sells the products)
    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    // Legacy relationship for backward compatibility
    public function user()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    // Order items
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    // Alternative name for order items (for backward compatibility)
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    // Calculate total amount from items
    public function getTotalAmountAttribute()
    {
        if ($this->relationLoaded('items')) {
            return $this->items->reduce(function ($total, $item) {
                return $total + ($item->quantity * $item->unit_price);
            }, 0);
        }

        return $this->attributes['total_amount'] ?? 0;
    }

    // Payments relationship
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    // Get the latest payment
    public function latestPayment()
    {
        return $this->hasOne(Payment::class)->latest();
    }

    // Check if order can be approved automatically
    public function canBeAutoApproved()
    {
        foreach ($this->items as $item) {
            $inventory = $this->seller->inventory()
                ->where('product_id', $item->product_id)
                ->first();

            if (!$inventory || $inventory->quantity < $item->quantity) {
                return false;
            }
        }
        return true;
    }

    // Check if payment is required
    public function requiresPayment()
    {
        return in_array($this->status, ['approved', 'processing']) &&
               $this->payment_status === 'unpaid';
    }
}
