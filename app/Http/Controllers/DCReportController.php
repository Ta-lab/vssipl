<?php

namespace App\Http\Controllers;

use App\Models\DcTransactionDetails;
use Illuminate\Http\Request;

class DCReportController extends Controller
{
    public function index()
    {
        $dcReports = DcTransactionDetails::with(['rcmaster', 'dcmaster.supplier', 'dcmaster.invoicepart', 'uom'])
            ->orderBy('issue_date', 'desc')
            ->get();

        return view('reports.dc-report.index', compact('dcReports'));
    }
} 