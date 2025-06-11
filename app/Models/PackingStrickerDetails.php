<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackingStrickerDetails extends Model
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

    public function inspectedby()
    {
        return $this->belongsTo(FirewallInspectionDetails::class,'inspect_by');
    }

    public function prepareuserdetails()
    {
        return $this->belongsTo(User::class,'prepared_by');
    }

    public function covermaster()
    {
        return $this->belongsTo(PackingCoverDetails::class,'cover_id');
    }

}
