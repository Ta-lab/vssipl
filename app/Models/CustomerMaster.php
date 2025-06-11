<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerMaster extends Model
{
    use HasFactory;

    protected $guarded =['cus_type','cus_address1','delivery_cus_address1'];

}
