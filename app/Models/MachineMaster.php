<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MachineMaster extends Model
{
    use HasFactory;

    public function groupmaster()
    {
        return $this->belongsTo(GroupMaster::class,'group_id');
    }

    public function cellmaster()
    {
        return $this->belongsTo(CellMaster::class,'cell_id');
    }
}
