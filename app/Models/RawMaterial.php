<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RawMaterial extends Model
{
    //
    protected $fillable = [
        'name',
        'quantity',
        'expiry',
        'user_id',
    ];

    /**
     * Get the user that owns the raw material
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
