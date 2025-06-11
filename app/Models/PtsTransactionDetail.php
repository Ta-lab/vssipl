<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PtsTransactionDetail extends Model
{
    use HasFactory;

    public function rcmaster()
    {
        return $this->belongsTo(RouteMaster::class,'rc_id');
    }

    public function previous_rcmaster()
    {
        return $this->belongsTo(RouteMaster::class,'previous_rc_id');
    }

    public function partmaster()
    {
        return $this->belongsTo(ChildProductMaster::class,'part_id');
    }
    public function strickermaster()
    {
        return $this->belongsTo(PackingStrickerDetails::class,'stricker_id');
    }

    public function currentprocessmaster()
    {
        return $this->belongsTo(ItemProcesmaster::class,'process_id');
    }

    public function prepareuserdetails()
    {
        return $this->belongsTo(User::class,'prepared_by');
    }

}
