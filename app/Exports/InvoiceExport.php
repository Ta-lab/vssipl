<?php

namespace App\Exports;

use App\Models\InvoiceDetails;
// use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class InvoiceExport implements FromView
// class InvoiceExport implements FromCollection, WithHeadings
// FromCollection, WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    // public function collection()
    // {
    //     return InvoiceDetails::all();
    // }
    // public function headings(): array
    // {
    //     return [
    //         'S.NO',
    //         'S.NO',
    //         'S.NO',
    //         'S.NO',
    //         'S.NO',
    //         'S.NO',
    //         'S.NO',
    //         'S.NO',
    //         'S.NO',
    //         'S.NO',
    //         'S.NO',
    //         'S.NO',
    //         'S.NO',
    //         'S.NO',
    //         'S.NO',
    //         'S.NO',
    //         'S.NO',
    //         'S.NO',
    //         'S.NO',
    //         'S.NO',
    //         'S.NO',
    //         'S.NO',
    //         'S.NO',
    //         'S.NO',
    //         'S.NO',
    //         'S.NO',
    //         'S.NO',
    //         'S.NO',
    //         'User',
    //         'Date',
    //     ];
    // }
    protected $invoices;
    public function __construct($invoices) {
        $this->invoices = $invoices;
        // dd($invoices);
    }
    public function view(): View
    {
        $invoicedatas=$this->invoices;
        return view('exports.invoices',compact('invoicedatas'));
    }
}
