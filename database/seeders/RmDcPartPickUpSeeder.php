<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\RmDcPartPickUp;


class RmDcPartPickUpSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $rmDcPartDatas1=RmDcPartPickUp::create([
            'supplier_id'=>'2',
            'rm_id'=>'1',
            'part_id' => '1',
            'prepared_by'=>'1'
        ]);
    }
}
