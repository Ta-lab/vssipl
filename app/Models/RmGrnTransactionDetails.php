<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RmGrnTransactionDetails extends Model
{
    use HasFactory;

    public function rm_requistion_master()
    {
        return $this->belongsTo(RmRequistion::class,'requistion_id');
    }

    public function partmaster()
    {
        return $this->belongsTo(ChildProductMaster::class,'part_id');
    }

    public function rm_master()
    {
        return $this->belongsTo(RawMaterial::class,'rm_id');
    }

    public function rc_master()
    {
        return $this->belongsTo(RouteMaster::class,'rc_id');
    }
}
