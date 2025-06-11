<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CoverStrickerDetails extends Model
{
    use HasFactory;

    public function stickermaster()
    {
        return $this->belongsTo(PackingStrickerDetails::class,'stricker_id');
    }

    public function rcmaster()
    {
        return $this->belongsTo(RouteMaster::class,'rc_id');
    }
    public function fgreceivedby()
    {
        return $this->belongsTo(User::class,'fg_receive_by');
    }



}
