<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesDespatchPlanSummary extends Model
{
    use HasFactory;

    public function packingmaster()
    {
        return $this->belongsTo(PackingMaster::class,'packing_master_id');
    }

    public function productmaster()
    {
        return $this->belongsTo(ProductMaster::class,'part_id');
    }

    public function customermaster()
    {
        return $this->belongsTo(CustomerMaster::class,'cus_id');
    }

}
