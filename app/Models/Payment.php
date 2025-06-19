<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'amount',
        'method',
        'transaction_id',
        'status',
        'paid_at',
        'verification_code',
        'verification_status'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    // Relationships
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    // Check if payment is completed
    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    // Check if payment is pending
    public function isPending()
    {
        return $this->status === 'pending';
    }

    // Check if payment failed
    public function isFailed()
    {
        return $this->status === 'failed';
    }
}
