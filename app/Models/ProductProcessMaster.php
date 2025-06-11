<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductProcessMaster extends Model
{
    use HasFactory;

    public function processMaster()
    {
        return $this->belongsTo(ItemProcesmaster::class,'process_master_id');
    }

    public function productMaster()
    {
        return $this->belongsTo(ProductMaster::class,'part_id');
    }

    public function childProductMaster()
    {
        return $this->belongsTo(ChildProductMaster::class,'part_id');
    }

    public function foremanMaster()
    {
        return $this->belongsTo(ForemanMaster::class,'foreman_id');
    }

    public function machine_master()
    {
        return $this->belongsTo(MachineMaster::class,'machine_id');
    }

    public function group_master()
    {
        return $this->belongsTo(GroupMaster::class,'group_id');
    }
}
