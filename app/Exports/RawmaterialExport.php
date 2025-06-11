<?php

namespace App\Exports;

use App\Models\RawMaterial;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class RawmaterialExport implements FromView
{

    protected $raw_materials,$total_avl_kg;
    public function __construct($raw_materials,$total_avl_kg) {
        $this->raw_materials = $raw_materials;
        $this->total_avl_kg = $total_avl_kg;
    }
    public function view(): View
    {
        return view('exports.rawmaterial',['raw_materialdatas'=>$this->raw_materials,'total_avl_kg'=>$this->total_avl_kg]);
    }
}
