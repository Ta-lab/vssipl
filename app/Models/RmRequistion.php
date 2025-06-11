<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RmRequistion extends Model
{
    use HasFactory;



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

    public function machine_master()
    {
        return $this->belongsTo(MachineMaster::class,'machine_id');
    }

    public function group_master()
    {
        return $this->belongsTo(GroupMaster::class,'group_id');
    }

    public function request_user()
    {
        return $this->belongsTo(User::class,'request_by');
    }

    public function approved_user()
    {
        return $this->belongsTo(User::class,'approve_by');
    }
}
