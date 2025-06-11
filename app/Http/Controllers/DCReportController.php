<?php

namespace App\Http\Controllers;

use App\Models\DcTransactionDetails;
use App\Models\Supplier;
use App\Models\RawMaterial;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\DCReportExport;

class DCReportController extends Controller
{
    public function index(Request $request)
    {
        $query = DcTransactionDetails::with(['rcmaster', 'dcmaster.supplier', 'dcmaster.invoicepart', 'uom']);

        // Apply filters if they exist
        if ($request->has('supplier_id') && $request->supplier_id) {
            $query->whereHas('dcmaster.supplier', function($q) use ($request) {
                $q->where('id', $request->supplier_id);
            });
        }

        if ($request->has('rm_id') && $request->rm_id) {
            $query->whereHas('dcmaster', function($q) use ($request) {
                $q->where('rm_id', $request->rm_id);
            });
        }

        if ($request->has('part_no') && $request->part_no) {
            $query->whereHas('dcmaster.invoicepart', function($q) use ($request) {
                $q->where('part_no', 'like', '%' . $request->part_no . '%');
            });
        }

        $dcReports = $query->orderBy('issue_date', 'desc')->get();

        // Get suppliers for filter dropdown
        $suppliers = Supplier::where('status', 1)->orderBy('name')->get();
        
        // Get raw materials for filter dropdown
        $rawMaterials = RawMaterial::where('status', 1)->orderBy('name')->get();

        return view('reports.dc-report.index', compact('dcReports', 'suppliers', 'rawMaterials'));
    }

    public function export(Request $request)
    {
        $query = DcTransactionDetails::with(['rcmaster', 'dcmaster.supplier', 'dcmaster.invoicepart', 'uom']);

        // Apply filters if they exist
        if ($request->has('supplier_id') && $request->supplier_id) {
            $query->whereHas('dcmaster.supplier', function($q) use ($request) {
                $q->where('id', $request->supplier_id);
            });
        }

        if ($request->has('rm_id') && $request->rm_id) {
            $query->whereHas('dcmaster', function($q) use ($request) {
                $q->where('rm_id', $request->rm_id);
            });
        }

        if ($request->has('part_no') && $request->part_no) {
            $query->whereHas('dcmaster.invoicepart', function($q) use ($request) {
                $q->where('part_no', 'like', '%' . $request->part_no . '%');
            });
        }

        $dcReports = $query->orderBy('issue_date', 'desc')->get();

        return Excel::download(new DCReportExport($dcReports), 'dc-report.xlsx');
    }
} 