<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RawMaterialOrderItem extends Model
{
    //
    protected $table = 'rawmaterials_order_items';

    protected $fillable = [
        'order_id',
        'raw_material_id',
        'quantity',
        'unit_price',
    ];

    public function order()
    {
        return $this->belongsTo(RawMaterialOrder::class, 'order_id');
    }

    public function rawMaterial()
    {
        return $this->belongsTo(RawMaterial::class, 'raw_material_id');
    }

}
