<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RawMaterialUsage extends Model
{
    //
    use HasFactory;

    protected $fillable = [
        'raw_material_id',
        'quantity_used',
        'used_for',
        'used_date',
    ];

    //relation to the raw material model
    public function rawMaterial()
    {
        return $this->belongsTo(RawMaterial::class);
    }
}
