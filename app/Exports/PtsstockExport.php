<?php

namespace App\Exports;

use App\Models\PtsTransactionSummary;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

// class PtsstockExport implements FromCollection
// {
//     /**
//     * @return \Illuminate\Support\Collection
//     */
//     public function collection()
//     {
//         return PtsTransactionSummary::all();
//     }
// }

class PtsstockExport implements FromView
{
    /**
    * @return \Illuminate\Support\Collection
    */
    protected $d12Datas;
    public function __construct($d12Datas) {
        $this->d12Datas = $d12Datas;
    }
    public function view(): View
    {
        return view('exports.pts_stock',['d12Datas'=>$this->d12Datas]);
    }
}
