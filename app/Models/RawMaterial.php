<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RawMaterial extends Model
{
    //filled in from table
    protected $fillable = [
        'name',
        'quantity',
        'expiry',
        'reorder_threshold',
    ];

    //relation to batch model
    public function batches()
    {
        return $this->hasMany(RawMaterialBatch::class);
    }

    //relation to usuage model
    public function usages()
    {
        return $this->hasMany(RawMaterialUsage::class);
    }

}
