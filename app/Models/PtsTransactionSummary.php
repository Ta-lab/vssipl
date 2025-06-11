<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PtsTransactionSummary extends Model
{
    use HasFactory;

    public function rcmaster()
    {
        return $this->belongsTo(RouteMaster::class,'rc_id');
    }

    public function partmaster()
    {
        return $this->belongsTo(ChildProductMaster::class,'part_id');
    }

    public function currentprocessmaster()
    {
        return $this->belongsTo(ItemProcesmaster::class,'process_id');
    }

    public function nextprocessmaster()
    {
        return $this->belongsTo(ItemProcesmaster::class,'next_process_id');
    }

    public function prepareuserdetails()
    {
        return $this->belongsTo(User::class,'prepared_by');
    }
}
