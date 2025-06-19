<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Vendor extends Model
{
    use HasFactory;    protected $fillable = [
        'name',
        'user_id',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    // Relationship with products
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    // Relationship with user (if vendors are linked to users)
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
