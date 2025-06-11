<?php

namespace App\Exports;

use App\Models\Supplier;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class SupplierExport implements FromView
{
    /**
    * @return \Illuminate\Support\Collection
    */
    protected $suppliers;
    public function __construct($suppliers) {
        $this->suppliers = $suppliers;
    }
    public function view(): View
    {
        return view('exports.supplier',['suppliers'=>$this->suppliers]);
    }
}
