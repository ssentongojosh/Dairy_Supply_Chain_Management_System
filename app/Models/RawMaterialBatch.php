<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Supplier;


class RawMaterialBatch extends Model
{
    //
    use HasFactory;

    protected $fillable = [
        'raw_material_id',
        'batch_code',
        'quantity',
        'source',
        'received_date',
    ];

    //relation to te raw material model
    public function rawMaterial()
    {
        return $this->belongsTo(RawMaterial::class);
    }


// ...existing code...

public function supplier()
{
    return $this->belongsTo(Supplier::class);
}

// ...existing code...
}
