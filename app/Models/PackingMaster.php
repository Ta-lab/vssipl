<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackingMaster extends Model
{
    use HasFactory;

    public function covermaster()
    {
        return $this->belongsTo(PackingCoverDetails::class,'cover_id');
    }
}
