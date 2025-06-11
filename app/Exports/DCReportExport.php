<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class DCReportExport implements FromView
{
    protected $dcReports;

    public function __construct($dcReports) {
        $this->dcReports = $dcReports;
    }

    public function view(): View
    {
        return view('exports.dc-report', ['dcReports' => $this->dcReports]);
    }
} 