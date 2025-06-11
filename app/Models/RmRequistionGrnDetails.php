<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RmRequistionGrnDetails extends Model
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

    public function machine_master()
    {
        return $this->belongsTo(MachineMaster::class,'machine_id');
    }

    public function group_master()
    {
        return $this->belongsTo(GroupMaster::class,'group_id');
    }

    public function req_master()
    {
        return $this->belongsTo(RmRequistion::class,'req_rc_id');
    }

    public function rc_master()
    {
        return $this->belongsTo(RouteMaster::class,'issue_rc_id');
    }

    public function grn_master()
    {
        return $this->belongsTo(GRNInwardRegister::class,'grn_id');
    }

    public function heatno_master()
    {
        return $this->belongsTo(HeatNumber::class,'heat_id');
    }

    public function grnqc_master()
    {
        return $this->belongsTo(GrnQuality::class,'grn_qc_id');
    }


    public function rmissuedby()
    {
        return $this->belongsTo(User::class,'updated_by');
    }

}
