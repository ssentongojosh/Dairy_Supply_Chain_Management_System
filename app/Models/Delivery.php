<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;


class Delivery extends Model
{
    //table details
    protected $fillable = [
        'item_name',
        'quantity',
        'order_id',
        'sender_id',
        'receiver_id',
        'from',
        'to',
        'status',
        'confirmed',
        'notes',
        'delivery_date',
    ];

    //tracing to the user table
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }
    

}
