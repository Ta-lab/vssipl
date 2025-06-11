<?php

namespace App\Exports;

use App\Models\RawMaterialCategory;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class RawmaterialCategoryExport implements FromView
{
    /**
    * @return \Illuminate\Support\Collection
    */
    protected $categories;
    public function __construct($categories) {
        $this->categories = $categories;
    }
    public function view(): View
    {
        return view('exports.rawmaterialcategory',['categorydatas'=>$this->categories]);
    }
}
