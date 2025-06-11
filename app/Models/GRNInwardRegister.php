<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GRNInwardRegister extends Model
{
    use HasFactory;

    public function podata()
    {
        return $this->belongsTo(PODetail::class,'po_id');
    }

    public function poproduct()
    {
        return $this->belongsTo(POProductDetail::class,'p_o_product_id');
    }

    public function rmmaster()
    {
        return $this->belongsTo(RawMaterial::class,'rm_id');
    }
    public function rcmaster()
    {
        return $this->belongsTo(RouteMaster::class,'grnnumber');
    }

}
