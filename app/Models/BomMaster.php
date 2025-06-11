<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BomMaster extends Model
{
    use HasFactory;

    public function childpart_master()
    {
        return $this->belongsTo(ChildProductMaster::class,'child_part_id');
    }

    public function rm_master()
    {
        return $this->belongsTo(RawMaterial::class,'rm_id');
    }
}
