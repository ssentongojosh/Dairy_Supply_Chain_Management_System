<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    //columns
    protected $fillable = [
        'name','goods_type','telephone',
    ];
}
