<?php

namespace App\Http\Controllers;

use App\Models\TransDataD12;
use App\Http\Requests\StoreTransDataD12Request;
use App\Http\Requests\UpdateTransDataD12Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\OpenRouteCardReportExport;

class TransDataD12Controller extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTransDataD12Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(TransDataD12 $transDataD12)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TransDataD12 $transDataD12)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTransDataD12Request $request, TransDataD12 $transDataD12)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TransDataD12 $transDataD12)
    {
        //
    }

    /**
     * Display the open route card report.
     */
    public function openRouteCardReport()
    {
        $routeCards = \App\Models\TransDataD12::with([
            'partmaster',
            'rm_master',
            'currentprocessmaster',
            'currentproductprocessmaster',
            'current_rcmaster',
            'previous_rcmaster',
            'heat_nomaster',
            'grndata',
            'receiver',
        ])->paginate(20);
        return view('open_route_card.report', compact('routeCards'));
    }

    /**
     * Export the open route card report as an Excel file.
     */
    public function exportOpenRouteCardReport()
    {
         $query = \App\Models\TransDataD12::with([
             'partmaster',
             'rm_master',
             'currentprocessmaster',
             'currentproductprocessmaster',
             'current_rcmaster',
             'previous_rcmaster',
             'heat_nomaster',
             'grndata',
             'receiver',
         ]);
         return Excel::download(new OpenRouteCardReportExport($query), 'open_route_card_report.xlsx');
    }
}
