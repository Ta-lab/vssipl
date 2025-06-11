<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RmDcPartPickUp extends Model
{
    use HasFactory;

    public function supplier()
    {
        return $this->belongsTo(Supplier::class,'supplier_id');
    }

    public function rmdetails(){
        return $this->belongsTo(RawMaterial::class,'rm_id');
    }

    public function childpart()
    {
        return $this->belongsTo(ChildProductMaster::class,'part_id');
    }
}
