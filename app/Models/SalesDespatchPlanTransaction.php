<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesDespatchPlanTransaction extends Model
{
    use HasFactory;

    public function packingstrickerdetails()
    {
        return $this->belongsTo(PackingStrickerDetails::class,'stricker_id');
    }

    public function productmaster()
    {
        return $this->belongsTo(ProductMaster::class,'part_id');
    }

    public function customermaster()
    {
        return $this->belongsTo(CustomerMaster::class,'cus_id');
    }

    public function salesplanmaster()
    {
        return $this->belongsTo(SalesDespatchPlanSummary::class,'plan_id');
    }

    public function manufacturingpartmaster()
    {
        return $this->belongsTo(ChildProductMaster::class,'manufacturing_part_id');
    }

    public function rcmaster()
    {
        return $this->belongsTo(RouteMaster::class,'prc_id');
    }

    public function prepared_user()
    {
        return $this->belongsTo(User::class,'prepared_by');
    }


}
