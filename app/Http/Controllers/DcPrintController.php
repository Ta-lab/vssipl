<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DcTransactionDetails;
use App\Models\DcMaster;
use App\Models\BomMaster;
Use App\Models\RouteMaster;
Use App\Models\Supplier;
use App\Models\ItemProcesmaster;
use App\Models\ProductMaster;
use App\Models\ProductProcessMaster;
use App\Models\ChildProductMaster;
use App\Models\CustomerProductMaster;
use App\Models\TransDataD11;
use App\Models\TransDataD12;
use App\Models\TransDataD13;
use App\Models\PtsTransactionDetail;
use App\Models\PtsTransactionSummary;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\DcPrint;
use App\Http\Requests\StoreDcPrintRequest;
use App\Http\Requests\UpdateDcPrintRequest;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Auth;

use function Laravel\Prompts\select;

class DcPrintController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $dcprintDatas=DcPrint::with('dctransaction')->where('from_unit','=',1)->where('s_no','!=',0)->where('print_status','!=',0)->groupBy('s_no') ->get();
        return view('dc_print.index',compact('dcprintDatas'));
    }

    public function ptsdcMultiList(){
        $dcprintDatas=DcPrint::with('dctransaction')->where('from_unit','!=',1)->where('s_no','!=',0)->where('print_status','!=',0)->groupBy('s_no') ->get();
        return view('dc_print.pts_multidc_view',compact('dcprintDatas'));
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        $dcprintSnos=DcPrint::with('dctransaction')->where('from_unit','=',1)->where('s_no','!=',0)->where('print_status','!=',0) ->select('s_no')->orderBy('id','DESC')->first();
        $dcsupplierDatas=DcMaster::with('supplier')->where('status','=',1)->where('type_id','!=',1)->where('supplier_id','!=',4)->groupBy('supplier_id')->get();
        $sno=$dcprintSnos->s_no??NULL;
        $dc_sno=$sno+1;
        // dd($dc_sno);
        return view('dc_print.create',compact('dcsupplierDatas','dc_sno'));
    }

    public function ptsdcMultiCreate(){
        $dcprintSnos=DcPrint::with('dctransaction')->where('from_unit','!=',1)->where('s_no','!=',0)->where('print_status','!=',0) ->select('s_no')->orderBy('id','DESC')->first();
        $dcsupplierDatas=DcMaster::with('supplier')->where('status','=',1)->where('type_id','!=',1)->where('supplier_id','=',4)->groupBy('supplier_id')->get();
        $sno=$dcprintSnos->s_no??NULL;
        $dc_sno=$sno+1;
        // dd($dc_sno);
        return view('dc_print.pts_multidc_create',compact('dcsupplierDatas','dc_sno'));
    }

    public function dcMultiPrintData(Request $request){
        // dd($request->all());
        $s_no=$request->s_no;
        $from_unit=$request->from_unit;
        $dc_transactionDatas=DB::table('dc_prints as a')
        ->join('dc_transaction_details AS b', 'a.dc_id', '=', 'b.id')
        ->join('dc_masters as c', 'b.dc_master_id', '=', 'c.id')
        ->join('route_masters as d', 'b.rc_id', '=', 'd.id')
        ->join('mode_of_units as e', 'b.uom_id', '=', 'e.id')
        ->join('product_masters as f', 'c.part_id', '=', 'f.id')
        ->select('a.id as dc_print_id','b.id as dc_id','d.id as rc_id','d.rc_id as dc_no','b.issue_date','f.id as part_id','f.part_no','b.uom_id','e.name as uom','b.issue_qty','b.unit_rate','b.total_rate')
        ->where('a.s_no','=',$s_no)
        ->where('a.from_unit','=',$from_unit)
        ->get();
        // dd($dc_transactionDatas);

        $table="";
        foreach ($dc_transactionDatas as $key => $dc_transactionData) {
            $table.='<tr class="tr_'.$dc_transactionData->dc_print_id.'">'.
            '<td>'.$dc_transactionData->dc_no.'</td>'.
            '<td>'.$dc_transactionData->issue_date.'</td>'.
            '<td>'.$dc_transactionData->part_no.'</td>'.
            '<td>'.$dc_transactionData->issue_qty.'</td>'.
            '<td>'.$dc_transactionData->uom.'</td>'.
            '<td>'.$dc_transactionData->unit_rate.'</td>'.
            '<td>'.$dc_transactionData->total_rate.'</td>'.
            '</tr>';
        }
        return response()->json(['table'=>$table]);

    }

    public function ptsdcMultiReceiveData(Request $request){
        // dd($request->all());
        $s_no=$request->s_no;
        $fifoCheckDatas=DB::table('dc_prints as a')
        ->join('dc_transaction_details AS b', 'a.dc_id', '=', 'b.id')
        ->join('pts_transaction_summaries as c', 'c.rc_id', '=', 'b.rc_id')
        ->select('a.id','a.s_no',DB::raw('((c.u1_dc_issue_qty)-(c.pts_store_dc_receive_qty)) as avl_qty'))
        ->where('a.from_unit','=',1)->where('a.s_no','!=',0)->where('a.print_status','=',1)->whereIn('a.status',[0,1])
        ->havingRaw('avl_qty >?', [0])
        ->orderBy('a.id', 'ASC')
        ->groupBy('a.id')
        ->first();
        // dd($fifoCheckDatas);
        $fifoSNo=$fifoCheckDatas->s_no;
        // dd($fifoSNo);
        if ($fifoSNo==$s_no) {
            $sno_msg=true;
            // dd($sno_msg);
            $dc_transactionDatas=DB::table('dc_prints as a')
            ->join('dc_transaction_details AS b', 'a.dc_id', '=', 'b.id')
            ->join('dc_masters as c', 'b.dc_master_id', '=', 'c.id')
            ->join('route_masters as d', 'b.rc_id', '=', 'd.id')
            ->join('mode_of_units as e', 'b.uom_id', '=', 'e.id')
            ->join('product_masters as f', 'c.part_id', '=', 'f.id')
            ->join('trans_data_d11_s as g', 'd.id', '=', 'g.rc_id')
            ->join('pts_transaction_summaries as h', 'd.id', '=', 'h.rc_id')
            ->join('item_procesmasters as j', 'g.process_id', '=', 'j.id')
            ->join('item_procesmasters as k', 'g.next_process_id', '=', 'k.id')
            ->select('a.id as dc_print_id','b.id as dc_id','d.id as rc_id','d.rc_id as dc_no','b.issue_date','g.part_id','f.part_no','b.uom_id','e.name as uom','b.issue_qty','b.unit_rate','g.process_id','j.operation as process_name','g.next_process_id','k.operation as next_process_name','b.total_rate','h.pts_store_dc_receive_qty as u2_dc_qty',DB::raw('((h.u1_dc_issue_qty)-(h.pts_store_dc_receive_qty)) as avl_qty'))
            ->where('a.s_no','=',$s_no)
            ->where('a.status','=',1)
            ->havingRaw('avl_qty >?', [0])
            ->get();
            // dd($dc_transactionDatas);
        $html = view('dc_print.add_items',compact('dc_transactionDatas'))->render();
        } else {
            $sno_msg=false;
            $html='<svg xmlns="http://www.w3.org/2000/svg" style="display: none;">
            <symbol id="check-circle-fill" fill="currentColor" viewBox="0 0 16 16">
                <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
            </symbol>
            <symbol id="info-fill" fill="currentColor" viewBox="0 0 16 16">
                <path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zm.93-9.412-1 4.705c-.07.34.029.533.304.533.194 0 .487-.07.686-.246l-.088.416c-.287.346-.92.598-1.465.598-.703 0-1.002-.422-.808-1.319l.738-3.468c.064-.293.006-.399-.287-.47l-.451-.081.082-.381 2.29-.287zM8 5.5a1 1 0 1 1 0-2 1 1 0 0 1 0 2z"/>
            </symbol>
            <symbol id="exclamation-triangle-fill" fill="currentColor" viewBox="0 0 16 16">
                <path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
            </symbol>
            </svg><div class="alert alert-danger d-flex align-items-center" role="alert">';
            $html.='<svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Danger:"><use xlink:href="#exclamation-triangle-fill"/></svg><div>Sorry Previous Lot Is Inward Pending ,So Cannot Inward This..Previous Lot DC No Is DC-U1-'.$fifoSNo.'</div></div>';
        }

        // $dc_print_count=DcPrint::where('s_no','=',$s_no)->where('status','=',0)->count();
        // if ($dc_print_count>0) {
            // $dc_transactionDatas=DB::table('dc_prints as a')
            // ->join('dc_transaction_details AS b', 'a.dc_id', '=', 'b.id')
            // ->join('dc_masters as c', 'b.dc_master_id', '=', 'c.id')
            // ->join('route_masters as d', 'b.rc_id', '=', 'd.id')
            // ->join('mode_of_units as e', 'b.uom_id', '=', 'e.id')
            // ->join('product_masters as f', 'c.part_id', '=', 'f.id')
            // ->join('trans_data_d11_s as g', 'd.id', '=', 'g.rc_id')
            // ->join('pts_transaction_summaries as h', 'd.id', '=', 'h.rc_id')
            // ->join('item_procesmasters as j', 'g.process_id', '=', 'j.id')
            // ->join('item_procesmasters as k', 'g.next_process_id', '=', 'k.id')
            // ->select('a.id as dc_print_id','b.id as dc_id','d.id as rc_id','d.rc_id as dc_no','b.issue_date','g.part_id','f.part_no','b.uom_id','e.name as uom','b.issue_qty','b.unit_rate','g.process_id','j.operation as process_name','g.next_process_id','k.operation as next_process_name','b.total_rate','h.pts_store_dc_receive_qty as u2_dc_qty',DB::raw('((h.u1_dc_issue_qty)-(h.pts_store_dc_receive_qty)) as avl_qty'))
            // ->where('a.s_no','=',$s_no)
            // ->where('a.status','=',0)
            // ->havingRaw('avl_qty >?', [0])
            // ->get();
        // } else {
        //     $dc_transactionDatas=DB::table('dc_prints as a')
        //     ->join('dc_transaction_details AS b', 'a.dc_id', '=', 'b.id')
        //     ->join('dc_masters as c', 'b.dc_master_id', '=', 'c.id')
        //     ->join('route_masters as d', 'b.rc_id', '=', 'd.id')
        //     ->join('mode_of_units as e', 'b.uom_id', '=', 'e.id')
        //     ->join('product_masters as f', 'c.part_id', '=', 'f.id')
        //     ->join('trans_data_d11_s as g', 'd.id', '=', 'g.rc_id')
        //     ->join('pts_transaction_summaries as h', 'd.id', '=', 'h.rc_id')
        //     ->join('item_procesmasters as j', 'g.process_id', '=', 'j.id')
        //     ->join('item_procesmasters as k', 'g.next_process_id', '=', 'k.id')
        //     ->select('a.id as dc_print_id','b.id as dc_id','d.id as rc_id','d.rc_id as dc_no','b.issue_date','g.part_id','f.part_no','b.uom_id','e.name as uom','b.issue_qty','b.unit_rate','g.process_id','j.operation as process_name','g.next_process_id','k.operation as next_process_name','b.total_rate','h.pts_store_dc_receive_qty as u2_dc_qty',DB::raw('((h.pts_store_dc_receive_qty)-(h.pts_production_receive_qty)) as avl_qty'))
        //     ->where('a.s_no','=',$s_no)
        //     ->where('a.status','=',1)
        //     ->havingRaw('avl_qty >?', [0])
        //     ->get();
        // }
        // dd($dc_transactionDatas);
        return response()->json(['table'=>$html,'sno_msg'=>$sno_msg]);
    }

    public function ptsMultiDCHandOverData(Request $request){
        // dd($request->all());
        $s_no=$request->s_no;

        $count=DB::table('dc_prints as a')
        ->join('dc_transaction_details AS b', 'a.dc_id', '=', 'b.id')
        ->join('pts_transaction_summaries as c', 'c.rc_id', '=', 'b.rc_id')
        ->selectRaw('a.id,a.s_no,((c.pts_store_dc_receive_qty)-(c.pts_production_receive_qty)) as avl_qty')
        ->havingRaw('avl_qty >?', [0])
        ->orderBy('a.id', 'ASC')
        ->groupBy('a.id')
        ->count();
        // dd($count);
        $productionStatus=DB::table('dc_prints as a')
        ->join('dc_transaction_details AS b', 'a.dc_id', '=', 'b.id')
        ->join('pts_transaction_summaries as c', 'c.rc_id', '=', 'b.rc_id')
        // ->selectRaw('a.id,a.s_no,((c.pts_store_dc_receive_qty)-(c.pts_production_receive_qty)) as avl_qty')
        ->selectRaw('a.id,a.s_no,((c.pts_production_receive_qty)-((c.pts_production_issue_qty)+(c.pts_production_reject_qty)+(c.pts_production_rework_qty))) as avl_qty')
        ->havingRaw('avl_qty >?', [0])
        ->orderBy('a.id', 'ASC')
        ->groupBy('a.id')
        ->first();
        // dd($productionStatus);
        if ($productionStatus!='') {
            $fifoCheck=DB::table('dc_prints as a')
        ->join('dc_transaction_details AS b', 'a.dc_id', '=', 'b.id')
        ->join('pts_transaction_summaries as c', 'c.rc_id', '=', 'b.rc_id')
        ->selectRaw('a.id,a.s_no,((c.pts_store_dc_receive_qty)-(c.pts_production_receive_qty)) as avl_qty')
        // ->selectRaw('a.id,a.s_no,((c.pts_production_receive_qty)-((c.pts_production_issue_qty)+(c.pts_production_reject_qty)+(c.pts_production_rework_qty))) as avl_qty')
        ->havingRaw('avl_qty >?', [0])
        ->orderBy('a.id', 'ASC')
        ->groupBy('a.id')
        ->first();
        // dd($fifoCheck);
        $fifoSno=$fifoCheck->s_no;
        if ($productionStatus->s_no==$s_no) {
                $production_msg=true;
            if ($fifoSno==$s_no) {
                $sno_msg=true;
                $dc_transactionDatas=DB::table('dc_prints as a')
                ->join('dc_transaction_details AS b', 'a.dc_id', '=', 'b.id')
                ->join('dc_masters as c', 'b.dc_master_id', '=', 'c.id')
                ->join('route_masters as d', 'b.rc_id', '=', 'd.id')
                ->join('mode_of_units as e', 'b.uom_id', '=', 'e.id')
                ->join('product_masters as f', 'c.part_id', '=', 'f.id')
                ->join('trans_data_d11_s as g', 'd.id', '=', 'g.rc_id')
                ->join('pts_transaction_summaries as h', 'd.id', '=', 'h.rc_id')
                ->join('item_procesmasters as j', 'g.process_id', '=', 'j.id')
                ->join('item_procesmasters as k', 'g.next_process_id', '=', 'k.id')
                ->select('a.id as dc_print_id','b.id as dc_id','d.id as rc_id','d.rc_id as dc_no','b.issue_date','g.part_id','f.part_no','b.uom_id','e.name as uom','b.issue_qty','b.unit_rate','g.process_id','j.operation as process_name','g.next_process_id','k.operation as next_process_name','b.total_rate','h.pts_store_dc_receive_qty','h.pts_production_receive_qty',DB::raw('((h.pts_store_dc_receive_qty)-(h.pts_production_receive_qty)) as avl_qty'))
                ->where('a.s_no','=',$s_no)
                ->havingRaw('avl_qty >?', [0])
                ->get();
                // dd($dc_transactionDatas);
            $html = view('dc_print.pts_handover_items',compact('dc_transactionDatas'))->render();
            }else{
                $sno_msg=false;
                $html='<svg xmlns="http://www.w3.org/2000/svg" style="display: none;">
                <symbol id="check-circle-fill" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                </symbol>
                <symbol id="info-fill" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zm.93-9.412-1 4.705c-.07.34.029.533.304.533.194 0 .487-.07.686-.246l-.088.416c-.287.346-.92.598-1.465.598-.703 0-1.002-.422-.808-1.319l.738-3.468c.064-.293.006-.399-.287-.47l-.451-.081.082-.381 2.29-.287zM8 5.5a1 1 0 1 1 0-2 1 1 0 0 1 0 2z"/>
                </symbol>
                <symbol id="exclamation-triangle-fill" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
                </symbol>
                </svg><div class="alert alert-danger d-flex align-items-center" role="alert">';
                $html.='<svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Danger:"><use xlink:href="#exclamation-triangle-fill"/></svg><div>Sorry Previous Lot Is Inward Pending ,So Cannot Inward This..Previous Lot DC No Is DC-U1-'.$fifoSno.'</div></div>';
            }
        } else {
            $production_msg=false;
            $sno_msg=false;
            $html='<svg xmlns="http://www.w3.org/2000/svg" style="display: none;">
            <symbol id="check-circle-fill" fill="currentColor" viewBox="0 0 16 16">
                <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
            </symbol>
            <symbol id="info-fill" fill="currentColor" viewBox="0 0 16 16">
                <path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zm.93-9.412-1 4.705c-.07.34.029.533.304.533.194 0 .487-.07.686-.246l-.088.416c-.287.346-.92.598-1.465.598-.703 0-1.002-.422-.808-1.319l.738-3.468c.064-.293.006-.399-.287-.47l-.451-.081.082-.381 2.29-.287zM8 5.5a1 1 0 1 1 0-2 1 1 0 0 1 0 2z"/>
            </symbol>
            <symbol id="exclamation-triangle-fill" fill="currentColor" viewBox="0 0 16 16">
                <path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
            </symbol>
            </svg><div class="alert alert-danger d-flex align-items-center" role="alert">';
            $html.='<svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Danger:"><use xlink:href="#exclamation-triangle-fill"/></svg><div>Sorry Previous Lot Is Production Pending ,So Cannot Handover This..Previous Lot DC No Is DC-U1-'.$productionStatus->s_no.'</div></div>';
        }
        }else {
            $sno_msg=true;
            $production_msg=true;

            $dc_transactionDatas=DB::table('dc_prints as a')
            ->join('dc_transaction_details AS b', 'a.dc_id', '=', 'b.id')
            ->join('dc_masters as c', 'b.dc_master_id', '=', 'c.id')
            ->join('route_masters as d', 'b.rc_id', '=', 'd.id')
            ->join('mode_of_units as e', 'b.uom_id', '=', 'e.id')
            ->join('product_masters as f', 'c.part_id', '=', 'f.id')
            ->join('trans_data_d11_s as g', 'd.id', '=', 'g.rc_id')
            ->join('pts_transaction_summaries as h', 'd.id', '=', 'h.rc_id')
            ->join('item_procesmasters as j', 'g.process_id', '=', 'j.id')
            ->join('item_procesmasters as k', 'g.next_process_id', '=', 'k.id')
            ->select('a.id as dc_print_id','b.id as dc_id','d.id as rc_id','d.rc_id as dc_no','b.issue_date','g.part_id','f.part_no','b.uom_id','e.name as uom','b.issue_qty','b.unit_rate','g.process_id','j.operation as process_name','g.next_process_id','k.operation as next_process_name','b.total_rate','h.pts_store_dc_receive_qty','h.pts_production_receive_qty',DB::raw('((h.pts_store_dc_receive_qty)-(h.pts_production_receive_qty)) as avl_qty'))
            ->where('a.s_no','=',$s_no)
            ->havingRaw('avl_qty >?', [0])
            ->get();
            // dd($dc_transactionDatas);
        $html = view('dc_print.pts_handover_items',compact('dc_transactionDatas'))->render();
        }


        return response()->json(['table'=>$html,'sno_msg'=>$sno_msg,'production_msg'=>$production_msg]);
    }

    public function ptsMultiDCHandOverList(){
        $multiDCDatas=DB::table('dc_prints as a')
        ->join('dc_transaction_details AS b', 'a.dc_id', '=', 'b.id')
        ->join('pts_transaction_summaries as c', 'c.rc_id', '=', 'b.rc_id')
        ->select('a.id','a.s_no',DB::raw('((c.pts_store_dc_receive_qty)-(c.pts_production_receive_qty)) as avl_qty'))
        ->havingRaw('avl_qty >?', [0])
        ->orderBy('a.id', 'ASC')
        ->groupBy('a.s_no')
        ->get();
        // dd($multiDCDatas);
        return view('dc_print.pts_multidc_handover',compact('multiDCDatas'));

    }
    public function ptsdcMultiPdfData(Request $request){
        $s_no=$request->s_no;
        $count=DcPrint::where('s_no','=',$s_no)->where('from_unit','!=',1)->count();
        $dc_trans_datas=DcPrint::with('dctransaction')->where('s_no','=',$s_no)->where('from_unit','!=',1)->first();
        $dc_trans_type=$dc_trans_datas->dctransaction->dcmaster->type_id;
        // $count=21;
        $page_count=ceil($count/10);
        // dd(ceil($page_count));
        // dd($dc_trans_type);
        // dd($s_no);

        if ($dc_trans_type==1) {
            $dc_transactionDatas=DB::table('dc_prints as a')
            ->join('dc_transaction_details AS b', 'a.dc_id', '=', 'b.id')
            ->join('dc_masters as c', 'b.dc_master_id', '=', 'c.id')
            ->join('route_masters as d', 'b.rc_id', '=', 'd.id')
            ->join('mode_of_units as e', 'b.uom_id', '=', 'e.id')
            ->join('raw_materials as f', 'c.rm_id', '=', 'f.id')
            ->join('item_procesmasters as g', 'c.operation_id', '=', 'g.id')
            ->join('suppliers as h', 'c.supplier_id', '=', 'h.id')
            ->select('*','b.no_cover','b.cover_qty','c.operation_desc','c.hsnc','b.vehicle_no','b.trans_mode','a.id as dc_print_id','h.name as supplier_name','h.address as supplier_address','h.address1 as supplier_address1','h.city as supplier_city','h.state as supplier_state','h.pincode as supplier_pincode','h.state_code as supplier_state_code','h.gst_number as supplier_gst_number','a.s_no','b.issue_wt','c.operation_id','g.operation','b.id as dc_id','d.id as rc_id','d.rc_id as dc_no','b.issue_date','f.id as part_id','f.name as part_no','b.uom_id','e.name as uom','b.issue_qty','b.unit_rate','b.total_rate','b.remarks')
            ->where('a.s_no','=',$s_no)
            ->where('a.from_unit','!=',1)
            ->get();
        }else {
            // dd($page_count);
            $dc_transactionDatas=DB::table('dc_prints as a')
            ->join('dc_transaction_details AS b', 'a.dc_id', '=', 'b.id')
            ->join('dc_masters as c', 'b.dc_master_id', '=', 'c.id')
            ->join('route_masters as d', 'b.rc_id', '=', 'd.id')
            ->join('mode_of_units as e', 'b.uom_id', '=', 'e.id')
            ->join('product_masters as f', 'c.part_id', '=', 'f.id')
            ->join('item_procesmasters as g', 'c.operation_id', '=', 'g.id')
            ->join('suppliers as h', 'c.supplier_id', '=', 'h.id')
            ->select('*','b.no_cover','b.cover_qty','c.operation_desc','c.hsnc','b.vehicle_no','b.trans_mode','a.id as dc_print_id','h.name as supplier_name','h.address as supplier_address','h.address1 as supplier_address1','h.city as supplier_city','h.state as supplier_state','h.pincode as supplier_pincode','h.state_code as supplier_state_code','h.gst_number as supplier_gst_number','a.s_no','b.issue_wt','c.operation_id','g.operation','b.id as dc_id','d.id as rc_id','d.rc_id as dc_no','b.issue_date','f.id as part_id','f.part_no','b.uom_id','e.name as uom','b.issue_qty','b.unit_rate','b.total_rate','b.remarks')
            ->where('a.s_no','=',$s_no)
            ->where('a.from_unit','!=',1)
            ->get();

        }
        $totalData=DB::table('dc_prints as a')
        ->join('dc_transaction_details AS b', 'a.dc_id', '=', 'b.id')->where('a.s_no','=',$s_no)->where('a.from_unit','!=',1)->select(DB::raw('(SUM(total_rate)) as sum_rate'),DB::raw('(SUM(issue_qty)) as sum_qty'))->get();
        // dd($dc_transactionDatas);
        $pdf = Pdf::loadView('dc_print.ptsdcmultipdf',compact('dc_transactionDatas','totalData','count','page_count'))->setPaper('a4', 'portrait');
        // $pdf = Pdf::setOption(['dpi' => 150, 'defaultFont' => 'sans-serif']);
        return $pdf->stream();
        // return view('dc_print.dcmultipdf',compact('dc_transactionDatas'));

    }
    public function dcMultiPdfData(Request $request){
        $s_no=$request->s_no;
        $count=DcPrint::where('s_no','=',$s_no)->count();
        $dc_trans_datas=DcPrint::with('dctransaction')->where('s_no','=',$s_no)->first();
        $dc_trans_type=$dc_trans_datas->dctransaction->dcmaster->type_id;
        // $count=21;
        $page_count=ceil($count/10);
        // dd(ceil($page_count));
        // dd($dc_trans_type);
        // dd($s_no);

        if ($dc_trans_type==1) {
            $dc_transactionDatas=DB::table('dc_prints as a')
            ->join('dc_transaction_details AS b', 'a.dc_id', '=', 'b.id')
            ->join('dc_masters as c', 'b.dc_master_id', '=', 'c.id')
            ->join('route_masters as d', 'b.rc_id', '=', 'd.id')
            ->join('mode_of_units as e', 'b.uom_id', '=', 'e.id')
            ->join('raw_materials as f', 'c.rm_id', '=', 'f.id')
            ->join('item_procesmasters as g', 'c.operation_id', '=', 'g.id')
            ->join('suppliers as h', 'c.supplier_id', '=', 'h.id')
            ->select('*','c.operation_desc','c.hsnc','b.vehicle_no','b.trans_mode','a.id as dc_print_id','h.name as supplier_name','h.address as supplier_address','h.address1 as supplier_address1','h.city as supplier_city','h.state as supplier_state','h.pincode as supplier_pincode','h.state_code as supplier_state_code','h.gst_number as supplier_gst_number','a.s_no','b.issue_wt','c.operation_id','g.operation','b.id as dc_id','d.id as rc_id','d.rc_id as dc_no','b.issue_date','f.id as part_id','f.name as part_no','b.uom_id','e.name as uom','b.issue_qty','b.unit_rate','b.total_rate','b.remarks')
            ->where('a.s_no','=',$s_no)
            ->get();
        }else {
            // dd($page_count);
            $dc_transactionDatas=DB::table('dc_prints as a')
            ->join('dc_transaction_details AS b', 'a.dc_id', '=', 'b.id')
            ->join('dc_masters as c', 'b.dc_master_id', '=', 'c.id')
            ->join('route_masters as d', 'b.rc_id', '=', 'd.id')
            ->join('mode_of_units as e', 'b.uom_id', '=', 'e.id')
            ->join('product_masters as f', 'c.part_id', '=', 'f.id')
            ->join('item_procesmasters as g', 'c.operation_id', '=', 'g.id')
            ->join('suppliers as h', 'c.supplier_id', '=', 'h.id')
            ->select('c.operation_desc','c.hsnc','b.vehicle_no','b.trans_mode','a.id as dc_print_id','h.name as supplier_name','h.address as supplier_address','h.address1 as supplier_address1','h.city as supplier_city','h.state as supplier_state','h.pincode as supplier_pincode','h.state_code as supplier_state_code','h.gst_number as supplier_gst_number','a.s_no','b.issue_wt','c.operation_id','g.operation','b.id as dc_id','d.id as rc_id','d.rc_id as dc_no','b.issue_date','f.id as part_id','f.part_no','b.uom_id','e.name as uom','b.issue_qty','b.unit_rate','b.total_rate','b.remarks')
            ->where('a.s_no','=',$s_no)
            ->get();

        }
        $totalData=DB::table('dc_prints as a')
        ->join('dc_transaction_details AS b', 'a.dc_id', '=', 'b.id')->where('a.s_no','=',$s_no)->select(DB::raw('(SUM(total_rate)) as sum_rate'),DB::raw('(SUM(issue_qty)) as sum_qty'))->get();
        // dd($dc_transactionDatas);
        $pdf = Pdf::loadView('dc_print.dcmultipdf',compact('dc_transactionDatas','totalData','count','page_count'))->setPaper('a4', 'portrait');
        // $pdf = Pdf::setOption(['dpi' => 150, 'defaultFont' => 'sans-serif']);
        return $pdf->stream();
        // return view('dc_print.dcmultipdf',compact('dc_transactionDatas'));

    }
    public function dcSupplierPrintData(Request $request){
        // dd($request->all());
        $supplier_id=$request->supplier_id;
        $dc_transactionDatas=DB::table('dc_prints as a')
        ->join('dc_transaction_details AS b', 'a.dc_id', '=', 'b.id')
        ->join('dc_masters as c', 'b.dc_master_id', '=', 'c.id')
        ->join('route_masters as d', 'b.rc_id', '=', 'd.id')
        ->join('mode_of_units as e', 'b.uom_id', '=', 'e.id')
    ->join('product_masters as f', 'c.part_id', '=', 'f.id')
        ->select('a.id as dc_print_id','b.id as dc_id','d.id as rc_id','d.rc_id as dc_no','b.issue_date','f.id as part_id','f.part_no','b.uom_id','e.name as uom','b.issue_qty','b.unit_rate','b.total_rate')
        ->where('a.print_status','=',0)
        ->where('a.s_no','=',0)
        ->where('c.supplier_id','=',$supplier_id)
        ->get();
        // dd($dc_transactionDatas);

        $table="";
        foreach ($dc_transactionDatas as $key => $dc_transactionData) {
            $table.='<tr class="tr_'.$dc_transactionData->dc_print_id.'">'.
            '<td><input type="checkbox" class="form-check-input sub_id" name="sub_id[]" data-id="'.$dc_transactionData->dc_print_id.'" value="'.$dc_transactionData->dc_print_id.'"></td>'.
            '<td><select name="dc_id[]" class="form-control bg-light dc_id" readonly id="dc_id"><option value="'.$dc_transactionData->dc_id.'">'.$dc_transactionData->dc_no.'</option></select></td>'.
            '<td><input type="date" name="issue_date[]"  class="form-control bg-light issue_date" readonly  id="issue_date" value="'.$dc_transactionData->issue_date.'"></td>'.
            '<td><select name="part_id[]" class="form-control bg-light part_id" readonly id="part_id"><option value="'.$dc_transactionData->part_id.'">'.$dc_transactionData->part_no.'</option></select></td>'.
            '<td><input type="number" name="issue_qty[]"  class="form-control bg-light issue_qty" readonly  id="issue_qty" value="'.$dc_transactionData->issue_qty.'"></td>'.
            '<td><select name="uom_id[]" class="form-control bg-light uom_id"  id="uom_id"><option value="'.$dc_transactionData->uom_id.'">'.$dc_transactionData->uom.'</option></select></td>'.
            '<td><input type="number" name="unit_rate[]" readonly class="form-control bg-light unit_rate"   id="unit_rate" value="'.$dc_transactionData->unit_rate.'"></td>'.
            '<td><input type="number" name="total_rate[]" readonly  class="form-control bg-light total_rate"   id="total_rate" value="'.$dc_transactionData->total_rate.'"></td>'.
            '</tr>';
        }
        return response()->json(['table'=>$table]);

    }

    public function rmDcSupplierPrintData(Request $request){
        $supplier_id=$request->supplier_id;
        $dc_transactionDatas=DB::table('dc_prints as a')
        ->join('dc_transaction_details AS b', 'a.dc_id', '=', 'b.id')
        ->join('dc_masters as c', 'b.dc_master_id', '=', 'c.id')
        ->join('route_masters as d', 'b.rc_id', '=', 'd.id')
        ->join('mode_of_units as e', 'b.uom_id', '=', 'e.id')
        ->join('raw_materials as f', 'c.rm_id', '=', 'f.id')
        ->select('a.id as dc_print_id','b.id as dc_id','d.id as rc_id','d.rc_id as dc_no','b.issue_date','f.id as rm_id','f.name as rm','b.uom_id','e.name as uom','b.issue_qty','b.unit_rate','b.total_rate')
        ->where('a.print_status','=',0)
        ->where('a.s_no','=',0)
        ->where('c.supplier_id','=',$supplier_id)
        ->get();
        // dd($dc_transactionDatas);

        $table="";
        foreach ($dc_transactionDatas as $key => $dc_transactionData) {
            $table.='<tr class="tr_'.$dc_transactionData->dc_print_id.'">'.
            '<td><input type="checkbox" class="form-check-input sub_id" name="sub_id[]" data-id="'.$dc_transactionData->dc_print_id.'" value="'.$dc_transactionData->dc_print_id.'"></td>'.
            '<td><select name="dc_id[]" class="form-control bg-light dc_id" readonly id="dc_id"><option value="'.$dc_transactionData->dc_id.'">'.$dc_transactionData->dc_no.'</option></select></td>'.
            '<td><input type="date" name="issue_date[]"  class="form-control bg-light issue_date" readonly  id="issue_date" value="'.$dc_transactionData->issue_date.'"></td>'.
            '<td><select name="rm_id[]" class="form-control bg-light rm_id" readonly id="rm_id"><option value="'.$dc_transactionData->rm_id.'">'.$dc_transactionData->rm.'</option></select></td>'.
            '<td><input type="number" name="issue_qty[]"  class="form-control bg-light issue_qty" readonly  id="issue_qty" value="'.$dc_transactionData->issue_qty.'"></td>'.
            '<td><select name="uom_id[]" class="form-control bg-light uom_id"  id="uom_id"><option value="'.$dc_transactionData->uom_id.'">'.$dc_transactionData->uom.'</option></select></td>'.
            '<td><input type="number" name="unit_rate[]" readonly class="form-control bg-light unit_rate"   id="unit_rate" value="'.$dc_transactionData->unit_rate.'"></td>'.
            '<td><input type="number" name="total_rate[]" readonly  class="form-control bg-light total_rate"   id="total_rate" value="'.$dc_transactionData->total_rate.'"></td>'.
            '</tr>';
        }
        return response()->json(['table'=>$table]);
    }



    public function ptsInwardData(){
    $d12Datas=PtsTransactionSummary::with('rcmaster','partmaster')->select('part_id','open_date','rc_id','u1_dc_issue_qty','pts_store_dc_receive_qty','pts_store_dc_reject_qty','pts_store_dc_rework_qty','pts_production_receive_qty','pts_production_issue_qty','pts_production_reject_qty','pts_production_rework_qty','remarks',DB::raw('((pts_store_dc_receive_qty)+(pts_store_dc_reject_qty)+(pts_store_dc_rework_qty)) as avl_qty'))->havingRaw('avl_qty >?', [0])->orderBy('updated_at','DESC')->get();
            // dd($d12datas);
       return view('dc_print.pts_inwardlist',compact('d12Datas'));
    }

    public function ptsMultiDcStore(Request $request){
        // dd($request->handover);
        // dd($request->all());
        $s_no=$request->s_no;
        $dcprint_datas=$request->sub_id;
        $dc_id=$request->dc_id;
        $issue_date=$request->issue_date;
        $part_id=$request->part_id;
        $issue_qty=$request->issue_qty;
        $received_qty=$request->received_qty;
        $receive_qty=$request->receive_qty;
        $balance_qty=$request->balance_qty;
        $uom_id=$request->uom_id;
        $status=$request->status;
        $reason=$request->reason;

        date_default_timezone_set('Asia/Kolkata');
        $current_date=date('Y-m-d');

        if ($request->handover!='') {
        // dd($request->handover);
        // dd($request->all());

            date_default_timezone_set('Asia/Kolkata');
            $current_date=date('Y-m-d');

            foreach ($dcprint_datas as $key => $dcprint_data) {
                if ($dcprint_data!='') {
                    $current_processDatas=ProductProcessMaster::with('processMaster')->where('part_id','=',$part_id[$dcprint_data])->where('process_master_id','=',$request->next_process_id[$dcprint_data])->first();
                    $current_process=$current_processDatas->processMaster->operation;

                    // dd($current_processDatas);
                    $current_process_order_id=$current_processDatas->process_order_id;

                    $next_processDatas2=ProductProcessMaster::with('processMaster')->where('part_id','=',$part_id[$dcprint_data])->where('process_order_id','>',$current_process_order_id)->where('status','=',1)->first();
                    $next_product_process_id2=$next_processDatas2->id;
                    $next_process_id2=$next_processDatas2->process_master_id;
                    $next_process2=$next_processDatas2->processMaster->operation;
                    $next_process_order_id2=$next_processDatas2->process_order_id;
                    // dd($next_processDatas);
                    // dd($next_process);
                    // $dcPrintDatas=DcPrint::find($dcprint_data);
                    $avlqty=$receive_qty[$dcprint_data];

                    $dcTransactionData=DcTransactionDetails::find($dc_id[$dcprint_data]);
                    // dd($dcTransactionData->rc_id);

                    // update dc receive qty
                    $rc_id=$dcTransactionData->rc_id;

                    $ptsTransactionDetail=new PtsTransactionDetail;
                    $ptsTransactionDetail->open_date=$current_date;
                    $ptsTransactionDetail->part_id=$part_id[$dcprint_data];
                    $ptsTransactionDetail->process_id=$request->next_process_id[$dcprint_data];
                    $ptsTransactionDetail->process=$current_process;
                    $ptsTransactionDetail->rc_id=$rc_id;
                    $ptsTransactionDetail->previous_rc_id=$rc_id;
                    $ptsTransactionDetail->issue_qty=$avlqty;
                    $ptsTransactionDetail->prepared_by = auth()->user()->id;
                    $ptsTransactionDetail->save();

                    $ptsTransactionSummary=PtsTransactionSummary::where('rc_id','=',$rc_id)->first();
                    // dd($ptsTransactionSummary);
                    $old_receive_qty=$ptsTransactionSummary->pts_production_receive_qty;
                    $current_receive_qty=$old_receive_qty+$avlqty;
                    // dd($current_receive_qty);
                    $ptsTransactionSummary->pts_production_receive_qty=$current_receive_qty;
                    $ptsTransactionSummary->updated_by = auth()->user()->id;
                    $ptsTransactionSummary->updated_at = Carbon::now();
                    $ptsTransactionSummary->update();
                }
            }
            return redirect()->route('ptsmultidchandoverlist')->withSuccess('Multi Delivery Challan Part Successfully Handover To Pts Production..!');
        } else {
            // dd($request->handover);
            // dd($request->all());
            // dd('receive');
            foreach ($dcprint_datas as $key => $dcprint_data) {
                if ($dcprint_data!='') {
                        if ($balance_qty[$dcprint_data]==0) {
                            $dcPrintDatas=DcPrint::find($dcprint_data);
                            $dcPrintDatas->status=0;
                            $dcPrintDatas->updated_by = auth()->user()->id;
                            $dcPrintDatas->updated_at = Carbon::now();
                            $dcPrintDatas->update();

                            $avlqty=$receive_qty[$dcprint_data];

                            $dcTransactionData=DcTransactionDetails::find($dc_id[$dcprint_data]);
                            // dd($dcTransactionData->rc_id);
                            // update dc receive qty
                            $rc_id=$dcTransactionData->rc_id;

                            $ptsTransactionSummary=PtsTransactionSummary::where('rc_id','=',$rc_id)->where('status','=',1)->first();
                            // dd($ptsTransactionSummary);
                            $old_receive_qty=$ptsTransactionSummary->pts_store_dc_receive_qty;
                            $current_receive_qty=$old_receive_qty+$avlqty;
                            $ptsTransactionSummary->pts_store_dc_receive_qty=$current_receive_qty;
                            $ptsTransactionSummary->updated_by = auth()->user()->id;
                            $ptsTransactionSummary->updated_at = Carbon::now();
                            $ptsTransactionSummary->update();




                        }elseif ($balance_qty[$dcprint_data]>0) {
                            # code...
                            $dcPrintDatas=DcPrint::find($dcprint_data);

                            $dcTransactionData=DcTransactionDetails::find($dc_id[$dcprint_data]);
                            // dd($dcTransactionData->rc_id);
                            // update dc receive qty
                            $rc_id=$dcTransactionData->rc_id;
                            $shortage=$reason[$dcprint_data].'Shortage'.$balance_qty[$dcprint_data].'Nos';
                            $dcTransactionData->reason=$shortage;
                            $dcTransactionData->updated_by = auth()->user()->id;
                            $dcTransactionData->updated_at = Carbon::now();
                            $dcTransactionData->update();
                            $avlqty=$receive_qty[$dcprint_data];

                            $ptsTransactionSummary=PtsTransactionSummary::where('rc_id','=',$rc_id)->where('status','=',1)->first();
                            // dd($ptsTransactionSummary);
                            $old_receive_qty=$ptsTransactionSummary->pts_store_dc_receive_qty;
                            $current_receive_qty=$old_receive_qty+$avlqty;
                            $ptsTransactionSummary->pts_store_dc_receive_qty=$current_receive_qty;
                            $ptsTransactionSummary->remarks=$shortage;
                            $ptsTransactionSummary->updated_by = auth()->user()->id;
                            $ptsTransactionSummary->updated_at = Carbon::now();
                            $ptsTransactionSummary->update();

                        }elseif ($balance_qty[$dcprint_data]<0) {
                            $dcPrintDatas=DcPrint::find($dcprint_data);
                            $dcTransactionData=DcTransactionDetails::find($dc_id[$dcprint_data]);
                            // dd($dcTransactionData->rc_id);

                            // update dc receive qty
                            $rc_id=$dcTransactionData->rc_id;
                            $excess=$reason[$dcprint_data].'Excess'.$balance_qty[$dcprint_data].'Nos';
                            $dcTransactionData->reason=$excess;
                            $dcTransactionData->updated_by = auth()->user()->id;
                            $dcTransactionData->updated_at = Carbon::now();
                            $dcTransactionData->update();

                            $avlqty=$issue_qty[$dcprint_data]-$received_qty[$dcprint_data];
                        }
                            // update dc print
                    // dd($dcPrintDatas);

                            // update dc transaction

                            // dd($rc_id);
                            $preTransDataD11Datas=TransDataD11::with('nextprocessmaster')->where('rc_id','=',$rc_id)->first();
                            $current_process_id=$preTransDataD11Datas->next_process_id;
                            $current_process=$preTransDataD11Datas->nextprocessmaster->operation;
                            $current_product_process_id=$preTransDataD11Datas->next_product_process_id;
                            // $preTransDataD11Datas->update();

                            $current_processDatas=ProductProcessMaster::where('part_id','=',$part_id[$dcprint_data])->where('process_master_id','=',$current_process_id)->where('id','=',$current_product_process_id)->first();
                            // dd($current_processDatas);
                            $current_process_order_id=$current_processDatas->process_order_id;

                            $next_processDatas=ProductProcessMaster::with('processMaster')->where('part_id','=',$part_id[$dcprint_data])->where('process_order_id','>',$current_process_order_id)->where('status','=',1)->first();
                            $next_product_process_id=$next_processDatas->id;
                            $next_process_id=$next_processDatas->process_master_id;
                            $next_process=$next_processDatas->processMaster->operation;
                            $next_process_order_id=$next_processDatas->process_order_id;
                            // dd($next_processDatas);
                            // dd($next_process);

                            $ptsTransactionDetail=new PtsTransactionDetail;
                            $ptsTransactionDetail->open_date=$current_date;
                            $ptsTransactionDetail->part_id=$part_id[$dcprint_data];
                            $ptsTransactionDetail->process_id=$current_process_id;
                            $ptsTransactionDetail->process=$current_process;
                            $ptsTransactionDetail->rc_id=$rc_id;
                            $ptsTransactionDetail->previous_rc_id=$rc_id;
                            $ptsTransactionDetail->receive_qty=$avlqty;
                            $ptsTransactionDetail->prepared_by = auth()->user()->id;
                            $ptsTransactionDetail->save();



                }
            }
        return redirect()->route('ptsmultidcreceive')->withSuccess('Multi Delivery Challan Part Received Successfully!');

        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDcPrintRequest $request)
    {
        //
        // dd($request->all());
        $sub_datas=$request->sub_id;
        foreach ($sub_datas as $key => $sub_data) {
            $dcprintDatas=DcPrint::find($sub_data);
            $dcprintDatas->s_no=$request->s_no;
            $dcprintDatas->print_status=1;
            $dcprintDatas->updated_by = auth()->user()->id;
            $dcprintDatas->update();
            DB::commit();
        }
        return redirect()->route('ptsmultidcreceivelist')->withSuccess('Multi Delivery Challan Created Successfully!');
    }

    public function ptsMultiDCReceive()
    {
        // $multiDCDatas=DcPrint::where('from_unit','=',1)->where('s_no','!=',0)->where('print_status','=',1)->whereIn('status',[0,1])->where('pts_production_status','=',1)->groupBy('s_no') ->get();
        $multiDCDatas=DB::table('dc_prints as a')
        ->join('dc_transaction_details AS b', 'a.dc_id', '=', 'b.id')
        ->join('pts_transaction_summaries as c', 'c.rc_id', '=', 'b.rc_id')
        ->select('a.id','a.s_no',DB::raw('((c.u1_dc_issue_qty)-(c.pts_store_dc_receive_qty)) as avl_qty'))
        ->where('a.from_unit','=',1)->where('a.s_no','!=',0)->where('a.print_status','=',1)->whereIn('a.status',[0,1])
        ->havingRaw('avl_qty >?', [0])
        ->orderBy('a.id', 'ASC')
        ->groupBy('a.s_no')
        ->get();
        // dd($multiDCDatas);
        return view('dc_print.pts_multidc_receive',compact('multiDCDatas'));
    }

    /**
     * Display the specified resource.
     */
    public function show(DcPrint $dcprint)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DcPrint $dcprint)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDcPrintRequest $request, DcPrint $dcprint)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DcPrint $dcprint)
    {
        //
    }
}
