<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DcTransactionDetails;
use App\Models\DcMaster;
use App\Models\DcPrint;
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
use App\Models\PtsTransactionSummary;
use App\Http\Requests\StoreDcTransactionDetailsRequest;
use App\Http\Requests\UpdateDcTransactionDetailsRequest;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Auth;

class DcTransactionDetailsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $value=0;
        $dcDatas=DcTransactionDetails::with('dcmaster','rcmaster','uom')->WhereHas('dcmaster', function ($q) use ($value) {
            $q->where('type_id', '=', $value)->where('supplier_id','!=',4);
        })->orderBy('id', 'DESC')->get();
        // dd($dcDatas);
        return view('dc.index',compact('dcDatas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        date_default_timezone_set('Asia/Kolkata');
        $current_date=date('Y-m-d');
        $current_year=date('Y');
        if ( date('m') > 3 ) {
            $year = date('y');
            $next_year=date('y')+1;
            $finacial_year=$year."-".$next_year;
        }
        else {
            $year = date('y') - 1;
            $next_year=date('y');
            $finacial_year=$year."-".$next_year;
        }
        // dd($finacial_year);
            $rc="DC-U1D";
		$current_rcno=$rc.$finacial_year;
        $count1=RouteMaster::whereIn('process_id',[16,17])->where('rc_id','LIKE','%'.$current_rcno.'%')->orderBy('rc_id', 'DESC')->get()->count();
        if ($count1 > 0) {
            $rc_data=RouteMaster::whereIn('process_id',[16,17])->where('rc_id','LIKE','%'.$current_rcno.'%')->orderBy('rc_id', 'DESC')->first();
            $rcnumber=$rc_data['rc_id']??NULL;
            $old_rcnumber=str_replace($current_rcno,"",$rcnumber);
            $old_rcnumber_data=str_pad($old_rcnumber+1,5,0,STR_PAD_LEFT);
            $new_rcnumber=$current_rcno.$old_rcnumber_data;
        }else{
            $str='00001';
            $new_rcnumber=$current_rcno.$str;
        }
        // dd($new_rcnumber);
            $dcmasterDatas=DcMaster::with('supplier')->where('status','=',1)->where('type_id','=',0)->groupBy('supplier_id')->get();
            // dd($dcmasterDatas);
            // return view('dc.create2',compact('dcmasterDatas','new_rcnumber','current_date'));
            return view('dc.insert',compact('dcmasterDatas','new_rcnumber','current_date'));
            // return view('dc.index');
    }

    public function dcPartData(Request $request){
        // dd($request->supplier_id);
        $supplier_id=$request->supplier_id;
        // dd($supplier_id);
        $count=DcMaster::with('invoicepart')->where('status','=',1)->where('type_id','=',0)->where('supplier_id','=',$supplier_id)->get()->count();
        // dd($count);
        if ($count > 0) {
            $dcmasterDatas=DcMaster::with('invoicepart')->where('status','=',1)->where('type_id','=',0)->where('supplier_id','=',$supplier_id)->get();
            $part_id='<option value="" selected>Select The Part Number</option>';
            foreach ($dcmasterDatas as $key => $dcmasterData) {
                $part_id.='<option value="'.$dcmasterData->invoicepart->id.'">'.$dcmasterData->invoicepart->part_no.'</option>';
            }
        return response()->json(['count'=>$count,'part_id'=>$part_id]);
        }else{
            return response()->json(['count'=>$count]);
        }
    }

    public function dcItemRc(Request $request){
        // dd($request->all());
        $part_id=$request->part_id;
        $supplier_id=$request->supplier_id;
        $check=ChildProductMaster::where('status','=',1)->where('part_id','=',$part_id)->count();
        $check1=ChildProductMaster::where('status','=',1)->where('part_id','=',$part_id)->where('item_type','=',1)->count();
        $check2=ChildProductMaster::where('status','=',1)->where('part_id','=',$part_id)->where('item_type','=',0)->count();
        $manufacturingPartDatas=ChildProductMaster::where('status','=',1)->where('part_id','=',$part_id)->get();

        $count=DB::table('child_product_masters as a')
        ->join('bom_masters AS b', 'a.id', '=', 'b.child_part_id')
        ->select('b.manual_usage')
        ->where('a.part_id','=',$part_id)
        ->where('a.status','=',1)
        ->where('b.status','=',1)
        ->count();
        if ($check==0) {
            $t_avl_qty=0;
            $bom=0;
            $dc_kg=0;
            $table="<div class='row clearfix mt-3'>";
            $table.="<div class='col-md-12'>
                        <div class='table-responsive'>
                            <table class='table table-bordered table-striped table-responsive'>
                                <thead>
                                    <tr>
                                        <th>Part No</th>
                                        <th>Order</th>
                                        <th>Route Card</th>
                                        <th>Route Card Available Quantity</th>
                                        <th>DC Quantity</th>
                                        <th>Balance</th>
                                    </tr>
                                </thead>
                                <tbody  id='table_logic'>
                                    <tr><td colspan='6' class='mx-auto'><b>No Result Found</b></td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div></div>
                    <div class='row mb-3 d-flex justify-content-end clearfix' style='background-color: aliceblue'><div class='col-2'><h6>Grand Total:</h6></div>
                        <div class='col-2'><input type='number' name='grand_total[$part_id]' class='form-control bg-light' id='grand_total_$part_id' value='$t_avl_qty'  readonly>
                        </div>
                        <div class='col-2'><h6>Balance:</h6></div>
                        <div class='col-2'><input type='number' name='total_diff[$part_id]' class='form-control bg-light' id='total_diff_$part_id' value=0  readonly></div>
                    </div>";
            return response()->json(['t_avl_qty'=>$t_avl_qty,'table'=>$table,'regular'=>$check1,'alter'=>$check2,'bom'=>$bom]);
        }elseif ($check1==1) {
            $manufacturingPartDatas=ChildProductMaster::where('status','=',1)->where('part_id','=',$part_id)->first();
                // foreach ($manufacturingPartDatas as $key => $manufacturingPartData) {
                    $manufacturingPart=$manufacturingPartDatas->id;
                    $itemType=$manufacturingPartDatas->item_type;
                // }
                // dd($itemType);
                $bom=BomMaster::where('child_part_id','=',$manufacturingPart)->where('status','=',1)->sum('manual_usage');
                // dd($bom);
                    if ($itemType==1) {
                        $dcmasterOperationDatas=DcMaster::with('childpart','procesmaster','supplier')->where('status','=',1)->where('supplier_id','=',$supplier_id)->where('part_id','=',$part_id)->first();
                        // dd($dcmasterOperationDatas);
                        $operation_id=$dcmasterOperationDatas->operation_id;
                        $operation_name=$dcmasterOperationDatas->procesmaster->operation;
                        $operation='<option value="'.$operation_id.'" selected>'.$operation_name.'</option>';
                        $count1=TransDataD11::where('next_process_id','=',$operation_id)->where('part_id','=',$manufacturingPart)->select(DB::raw('(SUM(receive_qty)-SUM(issue_qty)) as t_avl_qty'))
                        ->havingRaw('t_avl_qty >?', [0])->first();

                        if ($count1!=NULL) {
                            $t_avl_qty=$count1->t_avl_qty;
                            $dc_quantity=$t_avl_qty;
                            $dcmasterDatas=TransDataD11::with('rcmaster','partmaster')->where('next_process_id','=',$operation_id)->where('part_id','=',$manufacturingPart)->select('rc_id','part_id',DB::raw('((receive_qty)-(issue_qty)) as avl_qty'))
                            ->havingRaw('avl_qty >?', [0])->get();
                            $invoiceRcDatas=TransDataD11::with('rcmaster','partmaster')->where('next_process_id','=',$operation_id)->where('part_id','=',$manufacturingPart)->select('rc_id','part_id',DB::raw('((receive_qty)-(issue_qty)) as avl_qty'))
                            ->havingRaw('avl_qty >?', [0])->get();
                            $invoicerc_count=TransDataD11::with('rcmaster','partmaster')->where('next_process_id','=',$operation_id)->where('part_id','=',$manufacturingPart)->select('rc_id','part_id',DB::raw('((receive_qty)-(issue_qty)) as avl_qty'))
                            ->havingRaw('avl_qty >?', [0])->get()->count();
                            $m_part_id=$manufacturingPart;
                            $table2=view('dc.dc_rcqty',compact('invoiceRcDatas','dc_quantity','m_part_id','invoicerc_count'))->render();
                        }else {
                            $t_avl_qty=0;
                            $table="";
                        }
                        $regular=$check1-1;
                        if ($count>0) {
                            $bomDatas=DB::table('child_product_masters as a')
                            ->join('bom_masters AS b', 'a.id', '=', 'b.child_part_id')
                            ->select('b.manual_usage')
                            ->where('a.part_id','=',$part_id)
                            ->where('a.stocking_point','=',22)
                            ->where('a.status','=',1)
                            ->where('b.status','=',1)
                            ->first();
                            // dd($bomDatas);
                            $bom=$bomDatas->manual_usage;
                            $dc_kg=round(($bom*$t_avl_qty),3);
                        } else {
                            $bom=0;
                            $dc_kg=0;
                        }
                        return response()->json(['t_avl_qty'=>$t_avl_qty,'table'=>$table2,'bom'=>$bom,'dc_kg'=>$dc_kg,'operation'=>$operation,'regular'=>$check1,'alter'=>$check2]);
                    }else{
                        $dcmasterOperationDatas=DcMaster::with('childpart','procesmaster','supplier')->where('status','=',1)->where('supplier_id','=',$supplier_id)->where('part_id','=',$manufacturingPart)->first();
                        $operation_id=$dcmasterOperationDatas->operation_id;
                        $operation_name=$dcmasterOperationDatas->procesmaster->operation;
                        $operation='<option value="'.$operation_id.'" selected>'.$operation_name.'</option>';
                        $dcmasterDatas=TransDataD11::with('rcmaster')->where('next_process_id','=',$operation_id)->where('part_id','=',$manufacturingPart)->select('rc_id',DB::raw('((receive_qty)-(issue_qty)) as avl_qty'))
                        ->havingRaw('avl_qty >?', [0])->get();
                        $count1=TransDataD11::where('next_process_id','=',$operation_id)->where('part_id','=',$manufacturingPart)->select(DB::raw('(SUM(receive_qty)-SUM(issue_qty)) as t_avl_qty'))
                        ->havingRaw('t_avl_qty >?', [0])->first();
                        $t_avl_qty=$count1->t_avl_qty;
                        // dd($dcmasterDatas);
                        $table="";
                        foreach ($dcmasterDatas as $key => $dcmasterData) {
                            $table.='<tr>'.
                            '<td><select name="route_card_id[]" class="form-control bg-light route_card_id" readonly id="route_card_id"><option value="'.$dcmasterData->rcmaster->id.'">'.$dcmasterData->rcmaster->child_part_no.'</option></select></td>'.
                            '<td><input type="number" name="available_quantity[]"  class="form-control bg-light available_quantity" readonly  id="available_quantity" value="'.$dcmasterData->avl_qty.'"></td>'.
                            '<td><input type="number" name="issue_quantity[]"  class="form-control bg-light issue_quantity" readonly id="issue_quantity" min="0" max="'.$dcmasterData->avl_qty.'"></td>'.
                            '<td><input type="number" name="balance[]"  class="form-control bg-light balance" readonly id="balance" min="0" max="'.$dcmasterData->avl_qty.'"></td>'.
                            '</tr>';
                        }
                        if ($count>0) {
                            $bomDatas=DB::table('child_product_masters as a')
                            ->join('bom_masters AS b', 'a.id', '=', 'b.child_part_id')
                            ->select('b.manual_usage')
                            ->where('a.part_id','=',$part_id)
                            ->where('a.stocking_point','=',22)
                            ->where('a.status','=',1)
                            ->where('b.status','=',1)
                            ->first();
                            // dd($bomDatas);
                            $bom=$bomDatas->manual_usage;
                            $dc_kg=round(($bom*$t_avl_qty),3);
                        } else {
                            $bom=0;
                            $dc_kg=0;
                        }
                        return response()->json(['t_avl_qty'=>$t_avl_qty,'table'=>$table,'operation'=>$operation,'regular'=>$check1,'alter'=>$check2]);
                    }
                }else{
                    // dd('ok');
                    $dcmasterOperationDatas=DcMaster::with('procesmaster','supplier')->where('status','=',1)->where('supplier_id','=',$supplier_id)->where('part_id','=',$part_id)->where('type_id','=',0)->first();
                    // dd($dcmasterOperationDatas);
                    $operation_id=$dcmasterOperationDatas->procesmaster->id;
                    $operation_name=$dcmasterOperationDatas->procesmaster->operation;
                    $operation='<option value="'.$operation_id.'" selected>'.$operation_name.'</option>';

                    $dcmasterDatas2=DB::table('product_masters as b')
                    ->join('child_product_masters AS c', 'c.part_id', '=', 'b.id')
                    ->join('trans_data_d11_s AS d', 'd.part_id', '=', 'c.id')
                    ->join('route_masters AS e', 'd.rc_id', '=', 'e.id')
                    // ->select(DB::raw('((receive_qty)-(issue_qty)) as t_avl_qty'))
                    ->select(DB::raw('(SUM(receive_qty)-SUM(issue_qty)) as t_avl_qty'))
                    ->where('b.id','=',$part_id)
                    ->where('c.stocking_point','=',$operation_id)
                    ->where('d.next_process_id','=',$operation_id)
                    ->where('c.item_type','=',1)
                    ->where('c.status','=',1)
                    ->havingRaw('t_avl_qty >?', [0])
                    ->orderBy('c.no_item_id', 'ASC')
                    ->orderBy('e.id', 'ASC')
                    ->groupBy('c.id')
                    ->min('t_avl_qty');
                    // ->get();
                    // dd($dcmasterDatas2);
                    $t_avl_qty=$dcmasterDatas2;

                    $childPartDatas=ChildProductMaster::where('status','=',1)->where('part_id','=',$part_id)->where('item_type','=',1)->where('stocking_point','=',$operation_id)->get();
                    // dd($childPartDatas);
                    $table2="";
                    foreach ($childPartDatas as $key => $childPartData) {
                        $m_part_id=$childPartData->id;
                        $invoiceRcDatas=DB::table('product_masters AS b')
                        ->join('child_product_masters AS c', 'c.part_id', '=', 'b.id')
                        ->join('trans_data_d11_s AS d', 'd.part_id', '=', 'c.id')
                        ->join('route_masters AS e', 'd.rc_id', '=', 'e.id')
                        ->select('e.id as rcId','e.rc_id','c.id as partId','c.child_part_no','c.no_item_id',DB::raw('((receive_qty)-(issue_qty)) as avl_qty'))
                        ->where('b.id','=',$part_id)
                        ->where('c.stocking_point','=',$operation_id)
                        ->where('d.next_process_id','=',$operation_id)
                        ->where('c.item_type','=',1)
                        ->where('c.status','=',1)
                        ->where('c.id','=',$m_part_id)
                        ->havingRaw('avl_qty >?', [0])
                        ->orderBy('c.no_item_id', 'ASC')
                        ->orderBy('e.id', 'ASC')
                        ->groupBy('e.id')
                        ->get();
                        $table2.=view('dc.dc_multi_rcqty',compact('invoiceRcDatas','t_avl_qty','m_part_id'))->render();
                    }// $bom=0;
                        $regular=$check1-1;
                        if ($count>0) {
                            $bomDatas=DB::table('child_product_masters as a')
                            ->join('bom_masters AS b', 'a.id', '=', 'b.child_part_id')
                            ->select('b.manual_usage')
                            ->where('a.part_id','=',$part_id)
                            ->where('a.stocking_point','=',22)
                            ->where('a.status','=',1)
                            ->where('b.status','=',1)
                            ->first();
                            // dd($bomDatas);
                            $bom=$bomDatas->manual_usage;
                            $dc_kg=round(($bom*$t_avl_qty),2);
                        } else {
                            $bom=0;
                            $dc_kg=0;
                        }
                        // return response()->json(['t_avl_qty'=>$t_avl_qty,'operation'=>$operation,'bom'=>$bom,'regular'=>$regular,'alter'=>$check2]);
                        return response()->json(['t_avl_qty'=>$t_avl_qty,'table'=>$table2,'bom'=>$bom,'dc_kg'=>$dc_kg,'operation'=>$operation,'regular'=>$check1,'alter'=>$check2]);
                        // return response()->json(['t_avl_qty'=>$t_avl_qty,'table'=>$table2,'operation'=>$operation,'regular'=>$check1,'alter'=>$check2]);
                }

    }

    public function dcItemRcQuantity(Request $request){
        // dd($request->all());
        $part_id=$request->part_id;
        // dd($part_id);
        $operation_id=$request->operation_id;
        $supplier_id=$request->supplier_id;
        $dc_quantity=$request->dc_quantity;
        $check=ChildProductMaster::where('status','=',1)->where('part_id','=',$part_id)->count();
        $check1=ChildProductMaster::where('status','=',1)->where('part_id','=',$part_id)->where('item_type','=',1)->count();
        $check2=ChildProductMaster::where('status','=',1)->where('part_id','=',$part_id)->where('item_type','=',0)->count();
        $manufacturingPartDatas=ChildProductMaster::where('status','=',1)->where('part_id','=',$part_id)->get();

        $count=DB::table('child_product_masters as a')
        ->join('bom_masters AS b', 'a.id', '=', 'b.child_part_id')
        ->select('b.manual_usage')
        ->where('a.part_id','=',$part_id)
        ->where('a.status','=',1)
        ->where('b.status','=',1)
        ->count();
        if ($count>0) {
            $bomDatas=DB::table('child_product_masters as a')
            ->join('bom_masters AS b', 'a.id', '=', 'b.child_part_id')
            ->select('b.manual_usage')
            ->where('a.part_id','=',$part_id)
            ->where('a.stocking_point','=',22)
            ->where('a.status','=',1)
            ->where('b.status','=',1)
            ->first();
            // dd($bomDatas);
            $bom=$bomDatas->manual_usage;
            $dc_kg=round(($bom*$dc_quantity),2);
        } else {
            $bom=0;
            $dc_kg=0;
        }

        if ($check==0) {
            $table="<div class='row clearfix mt-3'>";
            $table.="<div class='col-md-12'>
                        <div class='table-responsive'>
                            <table class='table table-bordered table-striped table-responsive'>
                                <thead>
                                <tr>
                                    <th>Part No</th>
                                    <th>Order</th>
                                    <th>Route Card</th>
                                    <th>Route Card Available Quantity</th>
                                    <th>DC Quantity</th>
                                    <th>Balance</th>
                                </tr>
                                </thead>
                                <tbody  id='table_logic'>
                                    <tr><td colspan='6' class='mx-auto'><b>No Result Found</b></td></tr></tbody>
                            </table>
                        </div>
                    </div></div><div class='row mb-3 d-flex justify-content-end clearfix' style='background-color: aliceblue'><div class='col-2'><h6>Grand Total:</h6></div>
                    <div class='col-2'><input type='number' name='grand_total[$part_id]' class='form-control bg-light' id='grand_total_$part_id' value='$dc_quantity'  readonly>
                    </div>
                    <div class='col-2'><h6>Balance:</h6></div>
                    <div class='col-2'><input type='number' name='total_diff[$part_id]' class='form-control bg-light' id='total_diff_$part_id' value=0  readonly></div></div>";
            return response()->json(['table'=>$table]);
        }elseif ($check1==1) {
            foreach ($manufacturingPartDatas as $key => $manufacturingPartData) {
                $manufacturingPart=$manufacturingPartData->id;
                $m_part_id=$manufacturingPartData->id;
                $itemType=$manufacturingPartData->item_type;
            }
            if ($itemType==1) {
                $invoiceRcDatas=TransDataD11::with('rcmaster','partmaster')->where('next_process_id','=',$operation_id)->where('part_id','=',$manufacturingPart)->select('rc_id','part_id',DB::raw('((receive_qty)-(issue_qty)) as avl_qty'))
                    ->havingRaw('avl_qty >?', [0])->get();
                    $invoicerc_count=TransDataD11::with('rcmaster','partmaster')->where('next_process_id','=',$operation_id)->where('part_id','=',$manufacturingPart)->select('rc_id','part_id',DB::raw('((receive_qty)-(issue_qty)) as avl_qty'))
                    ->havingRaw('avl_qty >?', [0])->get()->count();
                $table2=view('dc.dc_rcqty',compact('invoiceRcDatas','dc_quantity','m_part_id','invoicerc_count'))->render();
                return response()->json(['table'=>$table2,'bom'=>$bom,'dc_kg'=>$dc_kg]);
            }
        }elseif ($check1>=1) {
            $childPartDatas=ChildProductMaster::where('status','=',1)->where('part_id','=',$part_id)->where('item_type','=',1)->where('stocking_point','=',$operation_id)->get();
            // dd($childPartDatas);
            $table2="";
            foreach ($childPartDatas as $key => $childPartData) {
                $m_part_id=$childPartData->id;
                $invoiceRcDatas=DB::table('customer_product_masters as a')
                ->join('product_masters AS b', 'a.part_id', '=', 'b.id')
                ->join('child_product_masters AS c', 'c.part_id', '=', 'b.id')
                ->join('trans_data_d11_s AS d', 'd.part_id', '=', 'c.id')
                ->join('route_masters AS e', 'd.rc_id', '=', 'e.id')
                ->select('e.id as rcId','e.rc_id','c.id as partId','c.child_part_no','c.no_item_id',DB::raw('((receive_qty)-(issue_qty)) as avl_qty'))
                ->where('a.part_id','=',$part_id)
                ->where('c.stocking_point','=',$operation_id)
                ->where('d.next_process_id','=',$operation_id)
                ->where('c.item_type','=',1)
                ->where('c.status','=',1)
                ->where('c.id','=',$m_part_id)
                ->havingRaw('avl_qty >?', [0])
                ->orderBy('c.no_item_id', 'ASC')
                ->orderBy('e.id', 'ASC')
                ->groupBy('e.id')
                ->get();
                $table2.=view('dc.dc_multi_rcqty',compact('invoiceRcDatas','dc_quantity','m_part_id'))->render();
            }
        return response()->json(['table'=>$table2,'bom'=>$bom,'dc_kg'=>$dc_kg]);

        }elseif ($check2==1) {
            # code...
        }elseif ($check2>=1) {
            # code...
        }
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDcTransactionDetailsRequest $request)
    {
        //
        // dd($request->all());
        $regular=$request->regular;
        $dc_quantity=$request->dc_quantity;
        $grand_total=$request->grand_total;
        $dc_number=$request->dc_number;
        $dc_date=$request->dc_date;
        $supplier_id=$request->supplier_id;
        $part_id=$request->part_id;
        $operation_id=$request->operation_id;
        $dc_avl_quantity=$request->avl_quantity;
        $trans_mode=$request->trans_mode;
        $vehicle_no=$request->vehicle_no;
        $alter=$request->alter;
        $route_part_id=$request->route_part_id;
        $order_no=$request->order_no;
        $route_card_id=$request->route_card_id;
        $rc_available_quantity=$request->available_quantity;
        $rc_issue_quantity=$request->issue_quantity;
        $issue_wt=$request->issue_wt;
        $remarks=($request->remarks)??NULL;
        // dd($grand_total);
        if ($regular==1) {
            foreach ($grand_total as $key => $value) {
            if ($dc_quantity!=$value) {
                # code...
                // dd('not ok');
                return redirect()->route('delivery_challan.index')->withMessage('Sorry...Route Card Quantity is not matching DC Quantity..!');
            }else {
                // dd('ok');
                $dcMasterData=DcMaster::with('procesmaster','supplier')->where('part_id','=',$part_id)->where('operation_id','=',$operation_id)->where('supplier_id','=',$supplier_id)->first();
                $valuation_rate=(($dcMasterData->procesmaster->valuation_rate)/100);
                $dcMaster_id=$dcMasterData->id;
                $process=$dcMasterData->procesmaster->operation;

                $customerProductData=CustomerProductMaster::where('part_id','=',$part_id)->where('status','=',1)->min('part_rate');
                // dd($customerProductData);
                $part_rate=$customerProductData;
                $unit_rate=$part_rate*$valuation_rate;
                $basic_value=$unit_rate*$dc_quantity;

                $rcMaster=new RouteMaster;
                $rcMaster->create_date=$dc_date;
                $rcMaster->process_id=$operation_id;
                $rcMaster->rc_id=$dc_number;
                $rcMaster->prepared_by=auth()->user()->id;
                $rcMaster->save();

                $rcMasterData=RouteMaster::where('rc_id','=',$dc_number)->where('process_id','=',$operation_id)->first();
                $rc_id=$rcMasterData->id;

                $dcTransData=new DcTransactionDetails;
                $dcTransData->rc_id=$rc_id;
                $dcTransData->issue_date=$dc_date;
                $dcTransData->dc_master_id=$dcMaster_id;
                $dcTransData->issue_qty=$dc_quantity;
                $dcTransData->unit_rate=$part_rate;
                $dcTransData->basic_rate=$basic_value;
                $dcTransData->total_rate=$basic_value;
                $dcTransData->issue_wt=$issue_wt;
                $dcTransData->trans_mode=$trans_mode;
                $dcTransData->vehicle_no=$vehicle_no;
                $dcTransData->remarks=$remarks;
                $dcTransData->prepared_by = auth()->user()->id;
                $dcTransData->save();

                $dc_id=$dcTransData->id;

                $dcPrintData=new DcPrint;
                $dcPrintData->s_no=0;
                $dcPrintData->dc_id=$dc_id;
                $dcPrintData->from_unit=1;
                $dcPrintData->print_status=0;
                $dcPrintData->prepared_by = auth()->user()->id;
                $dcPrintData->save();

                $currentProcess=ProductProcessMaster::where('part_id','=',$route_part_id[0])->where('process_master_id','=',$operation_id)->first();
                // dd($currentProcess);
                $current_order_id=$currentProcess->process_order_id;
                $current_product_process_id=$currentProcess->id;

                $nextProcess=ProductProcessMaster::where('part_id','=',$route_part_id[0])->where('process_order_id','>',$current_order_id)->where('status','=',1)->first();
                $next_product_order_id=$nextProcess->process_order_id;
                $next_product_process_id=$nextProcess->id;
                $next_process_id=$nextProcess->process_master_id;
                // dd($nextProcess);

                $ptsTransactionSummary=new PtsTransactionSummary;
                $ptsTransactionSummary->open_date=$dc_date;
                $ptsTransactionSummary->part_id=$route_part_id[0];
                $ptsTransactionSummary->process_id=$operation_id;
                $ptsTransactionSummary->process=$process;
                $ptsTransactionSummary->rc_id=$rc_id;
                $ptsTransactionSummary->u1_dc_issue_qty=$dc_quantity;
                $ptsTransactionSummary->prepared_by = auth()->user()->id;
                $ptsTransactionSummary->save();

                $d11Datas=new TransDataD11;
                $d11Datas->open_date=$dc_date;
                $d11Datas->rc_id=$rc_id;
                $d11Datas->part_id=$route_part_id[0];
                $d11Datas->process_id=$operation_id;
                $d11Datas->product_process_id=$current_product_process_id;
                $d11Datas->next_process_id=$next_process_id;
                $d11Datas->next_product_process_id=$next_product_process_id;
                $d11Datas->process_issue_qty=$dc_quantity;
                $d11Datas->prepared_by = auth()->user()->id;
                $d11Datas->save();

                foreach ($route_card_id as $key => $card_id) {
                    if ($rc_issue_quantity[$key]!=0) {
                        $previousD11Datas=TransDataD11::where('rc_id','=',$card_id)->where('next_process_id','=',$operation_id)->first();
                        // dd($previousD11Datas);
                        $old_issueqty=$previousD11Datas->issue_qty;
                        $total_issue_qty=$old_issueqty+$rc_issue_quantity[$key];
                        $previousD11Datas->issue_qty=$total_issue_qty;
                        $previousD11Datas->updated_by = auth()->user()->id;
                        $previousD11Datas->updated_at = Carbon::now();
                        $previousD11Datas->update();

                        $currentProcess=ProductProcessMaster::where('part_id','=',$route_part_id[$key])->where('process_master_id','=',$operation_id)->first();
                        $current_order_id=$currentProcess->process_order_id;
                        $current_product_process_id=$currentProcess->id;

                        $nextProcess=ProductProcessMaster::where('part_id','=',$route_part_id[$key])->where('process_order_id','>',$current_order_id)->where('status','=',1)->first();
                        $next_product_order_id=$nextProcess->process_order_id;
                        $next_product_process_id=$nextProcess->id;
                        $next_process_id=$nextProcess->process_master_id;

                        $d12Datas=new TransDataD12;
                        $d12Datas->open_date=$dc_date;
                        $d12Datas->rc_id=$rc_id;
                        $d12Datas->previous_rc_id=$card_id;
                        $d12Datas->part_id=$route_part_id[$key];
                        $d12Datas->process_id=$operation_id;
                        $d12Datas->product_process_id=$current_product_process_id;
                        $d12Datas->issue_qty=$rc_issue_quantity[$key];
                        $d12Datas->prepared_by = auth()->user()->id;
                        $d12Datas->save();

                        $d13Datas=new TransDataD13;
                        $d13Datas->rc_id=$rc_id;
                        $d13Datas->previous_rc_id=$card_id;
                        $d13Datas->prepared_by = auth()->user()->id;
                        $d13Datas->save();
                        DB::commit();
                    }
                }
                return redirect()->route('delivery_challan.index')->withSuccess('Delivery Challan Created Successfully!');
            }
        }
        }elseif ($regular>1) {
            foreach ($grand_total as $key => $value) {
                if ($dc_quantity!=$value) {
                return redirect()->route('delivery_challan.index')->withMessage('Sorry...Route Card Quantity is not matching DC Quantity..!');
                }else {
                    $parentPartDatas=ChildProductMaster::where('status','=',1)->where('part_id','=',$part_id)->where('item_type','=',1)->where('stocking_point','=',22)->first();
                    $parentPartNo=$parentPartDatas->id;
                    // dd($parentPartNo);
                    // $childPartDatas=ChildProductMaster::where('status','=',1)->where('part_id','=',$part_id)->where('item_type','=',1)->where('stocking_point','=',$operation_id)->get();
                    // dd($childPartDatas);

                    $dcMasterData=DcMaster::with('procesmaster','supplier')->where('part_id','=',$part_id)->where('operation_id','=',$operation_id)->where('supplier_id','=',$supplier_id)->first();
                    $valuation_rate=(($dcMasterData->procesmaster->valuation_rate)/100);
                    $dcMaster_id=$dcMasterData->id;
                    $process=$dcMasterData->procesmaster->operation;

                    $customerProductData=CustomerProductMaster::where('part_id','=',$part_id)->where('status','=',1)->min('part_rate');
                    // dd($customerProductData);
                    $part_rate=$customerProductData;
                    $unit_rate=$part_rate*$valuation_rate;
                    $basic_value=$unit_rate*$dc_quantity;

                    $rcMaster=new RouteMaster;
                    $rcMaster->create_date=$dc_date;
                    $rcMaster->process_id=$operation_id;
                    $rcMaster->rc_id=$dc_number;
                    $rcMaster->prepared_by=auth()->user()->id;
                    $rcMaster->save();

                    $rcMasterData=RouteMaster::where('rc_id','=',$dc_number)->where('process_id','=',$operation_id)->first();
                    $rc_id=$rcMasterData->id;

                    $dcTransData=new DcTransactionDetails;
                    $dcTransData->rc_id=$rc_id;
                    $dcTransData->issue_date=$dc_date;
                    $dcTransData->dc_master_id=$dcMaster_id;
                    $dcTransData->issue_qty=$dc_quantity;
                    $dcTransData->unit_rate=$part_rate;
                    $dcTransData->basic_rate=$basic_value;
                    $dcTransData->total_rate=$basic_value;
                    $dcTransData->issue_wt=$issue_wt;
                    $dcTransData->trans_mode=$trans_mode;
                    $dcTransData->vehicle_no=$vehicle_no;
                    $dcTransData->remarks=$remarks;
                    $dcTransData->prepared_by = auth()->user()->id;
                    $dcTransData->save();

                    $ptsTransactionSummary=new PtsTransactionSummary;
                    $ptsTransactionSummary->open_date=$dc_date;
                    $ptsTransactionSummary->part_id=$parentPartNo;
                    $ptsTransactionSummary->process_id=$operation_id;
                    $ptsTransactionSummary->process=$process;
                    $ptsTransactionSummary->rc_id=$rc_id;
                    $ptsTransactionSummary->u1_dc_issue_qty=$dc_quantity;
                    $ptsTransactionSummary->prepared_by = auth()->user()->id;
                    $ptsTransactionSummary->save();

                    $dc_id=$dcTransData->id;

                    $dcPrintData=new DcPrint;
                    $dcPrintData->s_no=0;
                    $dcPrintData->dc_id=$dc_id;
                    $dcPrintData->from_unit=1;
                    $dcPrintData->print_status=0;
                    $dcPrintData->prepared_by = auth()->user()->id;
                    $dcPrintData->save();

                    $currentProcess=ProductProcessMaster::where('part_id','=',$parentPartNo)->where('process_master_id','=',$operation_id)->first();
                    // dd($parentPartNo);
                    // dd($operation_id);
                    // dd($currentProcess);
                    $current_order_id=$currentProcess->process_order_id;
                    $current_product_process_id=$currentProcess->id;

                    $nextProcess=ProductProcessMaster::where('part_id','=',$parentPartNo)->where('process_order_id','>',$current_order_id)->where('status','=',1)->first();
                    $next_product_order_id=$nextProcess->process_order_id;
                    $next_product_process_id=$nextProcess->id;
                    $next_process_id=$nextProcess->process_master_id;
                    // dd($nextProcess);
                    $d11Datas=new TransDataD11;
                    $d11Datas->open_date=$dc_date;
                    $d11Datas->rc_id=$rc_id;
                    $d11Datas->part_id=$parentPartNo;
                    $d11Datas->process_id=$operation_id;
                    $d11Datas->product_process_id=$current_product_process_id;
                    $d11Datas->next_process_id=$next_process_id;
                    $d11Datas->next_product_process_id=$next_product_process_id;
                    $d11Datas->process_issue_qty=$dc_quantity;
                    $d11Datas->prepared_by = auth()->user()->id;
                    $d11Datas->save();

                    foreach ($route_card_id as $key => $card_id) {

                        if ($order_no[$key]==1) {
                            if ($rc_issue_quantity[$key]!=0) {
                                $previousD11Datas=TransDataD11::where('rc_id','=',$card_id)->where('next_process_id','=',$operation_id)->first();
                                // dd($previousD11Datas);
                                $old_issueqty=$previousD11Datas->issue_qty;
                                $total_issue_qty=$old_issueqty+$rc_issue_quantity[$key];
                                $previousD11Datas->issue_qty=$total_issue_qty;
                                $previousD11Datas->updated_by = auth()->user()->id;
                                $previousD11Datas->updated_at = Carbon::now();
                                $previousD11Datas->update();

                                $currentProcess=ProductProcessMaster::where('part_id','=',$parentPartNo)->where('process_master_id','=',$operation_id)->first();
                                $current_order_id=$currentProcess->process_order_id;
                                $current_product_process_id=$currentProcess->id;

                                $nextProcess=ProductProcessMaster::where('part_id','=',$parentPartNo)->where('process_order_id','>',$current_order_id)->where('status','=',1)->first();
                                $next_product_order_id=$nextProcess->process_order_id;
                                $next_product_process_id=$nextProcess->id;
                                $next_process_id=$nextProcess->process_master_id;

                                $d12Datas=new TransDataD12;
                                $d12Datas->open_date=$dc_date;
                                $d12Datas->rc_id=$rc_id;
                                $d12Datas->previous_rc_id=$card_id;
                                $d12Datas->part_id=$parentPartNo;
                                $d12Datas->process_id=$operation_id;
                                $d12Datas->product_process_id=$current_product_process_id;
                                $d12Datas->issue_qty=$rc_issue_quantity[$key];
                                $d12Datas->prepared_by = auth()->user()->id;
                                $d12Datas->save();

                                $d13Datas=new TransDataD13;
                                $d13Datas->rc_id=$rc_id;
                                $d13Datas->previous_rc_id=$card_id;
                                $d13Datas->prepared_by = auth()->user()->id;
                                $d13Datas->save();
                                DB::commit();
                            }
                        } elseif ($order_no[$key]==2) {
                            if ($rc_issue_quantity[$key]!=0) {
                                $previousD11Datas=TransDataD11::where('rc_id','=',$card_id)->where('next_process_id','=',$operation_id)->first();
                                // dd($previousD11Datas);
                                $old_issueqty=$previousD11Datas->issue_qty;
                                $total_issue_qty=$old_issueqty+$rc_issue_quantity[$key];
                                $previousD11Datas->issue_qty=$total_issue_qty;
                                $previousD11Datas->updated_by = auth()->user()->id;
                                $previousD11Datas->updated_at = Carbon::now();
                                $previousD11Datas->update();

                                $currentProcess=ProductProcessMaster::where('part_id','=',$route_part_id[$key])->where('process_master_id','=',$operation_id)->first();
                                $current_order_id=$currentProcess->process_order_id;
                                $current_product_process_id=$currentProcess->id;

                                // $nextProcess=ProductProcessMaster::where('part_id','=',$route_part_id[$key])->where('process_order_id','>',$current_order_id)->where('status','=',1)->first();
                                // $next_product_order_id=$nextProcess->process_order_id;
                                // $next_product_process_id=$nextProcess->id;
                                // $next_process_id=$nextProcess->process_master_id;

                                $d12Datas=new TransDataD12;
                                $d11Datas->open_date=$dc_date;
                                $d11Datas->rc_id=$rc_id;
                                $d12Datas->previous_rc_id=$card_id;
                                $d12Datas->part_id=$route_part_id[$key];
                                $d12Datas->process_id=$operation_id;
                                $d12Datas->product_process_id=$current_product_process_id;
                                $d12Datas->issue_qty=$rc_issue_quantity[$key];
                                $d12Datas->prepared_by = auth()->user()->id;
                                $d12Datas->save();

                                $d13Datas=new TransDataD13;
                                $d13Datas->rc_id=$rc_id;
                                $d13Datas->previous_rc_id=$card_id;
                                $d13Datas->prepared_by = auth()->user()->id;
                                $d13Datas->save();
                                DB::commit();

                                $dummyProcessData=ItemProcesmaster::where('operation','=','Dummy')->where('status','=',1)->first();
                                $dummy_process_id=$dummyProcessData->id;
                                $dummy_rc='AF'.$dc_number;

                                $rcCount=RouteMaster::where('rc_id','=',$dummy_rc)->get()->count();
                                if ($rcCount==0) {
                                    $rcMaster=new RouteMaster;
                                    $rcMaster->create_date=$dc_date;
                                    $rcMaster->process_id=$dummy_process_id;
                                    $rcMaster->rc_id=$dummy_rc;
                                    $rcMaster->prepared_by=auth()->user()->id;
                                    $rcMaster->save();
                                }
                                $rcMasterData=RouteMaster::where('rc_id','=',$dummy_rc)->where('process_id','=',$dummy_process_id)->first();
                            //    dd($rcMasterData);
                                $dummy_rc_id=$rcMasterData->id;

                                $d12Datas=new TransDataD12;
                                $d11Datas->open_date=$dc_date;
                                $d11Datas->rc_id=$dummy_rc_id;
                                $d12Datas->previous_rc_id=$rc_id;
                                $d12Datas->part_id=$route_part_id[$key];
                                $d12Datas->process_id=$dummy_process_id;
                                $d12Datas->product_process_id=$current_product_process_id;
                                $d12Datas->receive_qty=$rc_issue_quantity[$key];
                                $d12Datas->issue_qty=$rc_issue_quantity[$key];
                                $d12Datas->prepared_by = auth()->user()->id;
                                $d12Datas->save();

                                $d13Datas=new TransDataD13;
                                $d13Datas->rc_id=$dummy_rc_id;
                                $d13Datas->previous_rc_id=$rc_id;
                                $d13Datas->prepared_by = auth()->user()->id;
                                $d13Datas->save();
                                DB::commit();
                            }
                        }
                    }
                    return redirect()->route('delivery_challan.index')->withSuccess('Delivery Challan Created Successfully!');
                }
            }
        }elseif ($regular==0) {
            # code...
            return redirect()->route('delivery_challan.index')->withMessage('Sorry...This Part Number is linked Parent Child Master.Please Contact to ERP Team.!');
        }




        // $dcMasterData=DcMaster::with('procesmaster','supplier')->where('part_id','=',$part_id)->where('operation_id','=',$operation_id)->where('supplier_id','=',$supplier_id)->first();
        // $valuation_rate=(($dcMasterData->procesmaster->valuation_rate)/100);
        // $dcMaster_id=$dcMasterData->id;

        // $customerProductData=CustomerProductMaster::where('part_id','=',$part_id)->where('status','=',1)->sum('part_rate');
        // // dd($customerProductData);
        // $part_rate=$customerProductData;
        // $unit_rate=$part_rate*$valuation_rate;
        // $basic_value=$unit_rate*$dc_quantity;

        // $rcMaster=new RouteMaster;
        // $rcMaster->create_date=$dc_date;
        // $rcMaster->process_id=$operation_id;
        // $rcMaster->rc_id=$dc_number;
        // $rcMaster->prepared_by=auth()->user()->id;
        // $rcMaster->save();

        // $rcMasterData=RouteMaster::where('rc_id','=',$dc_number)->where('process_id','=',$operation_id)->first();
        // $rc_id=$rcMasterData->id;

        // $dcTransData=new DcTransactionDetails;
        // $dcTransData->rc_id=$rc_id;
        // $dcTransData->issue_date=$dc_date;
        // $dcTransData->dc_master_id=$dcMaster_id;
        // $dcTransData->issue_qty=$dc_quantity;
        // $dcTransData->unit_rate=$part_rate;
        // $dcTransData->basic_rate=$basic_value;
        // $dcTransData->total_rate=$basic_value;
        // $dcTransData->issue_wt=$issue_wt;
        // $dcTransData->trans_mode=$trans_mode;
        // $dcTransData->vehicle_no=$vehicle_no;
        // $dcTransData->remarks=$remarks;
        // $dcTransData->prepared_by = auth()->user()->id;
        // $dcTransData->save();

        // $dc_id=$dcTransData->id;

        // $dcPrintData=new DcPrint;
        // $dcPrintData->s_no=0;
        // $dcPrintData->dc_id=$dc_id;
        // $dcPrintData->from_unit=1;
        // $dcPrintData->print_status=0;
        // $dcPrintData->prepared_by = auth()->user()->id;
        // $dcPrintData->save();

        // $currentProcess=ProductProcessMaster::where('part_id','=',$part_id)->where('process_master_id','=',$operation_id)->first();
        // $current_order_id=$currentProcess->process_order_id;
        // $current_product_process_id=$currentProcess->id;

        // $nextProcess=ProductProcessMaster::where('part_id','=',$part_id)->where('process_order_id','>',$current_order_id)->where('status','=',1)->first();
        // $next_product_order_id=$nextProcess->process_order_id;
        // $next_product_process_id=$nextProcess->id;
        // $next_process_id=$nextProcess->process_master_id;

        // $d11Datas=new TransDataD11;
        // $d11Datas->open_date=$dc_date;
        // $d11Datas->rc_id=$rc_id;
        // $d11Datas->part_id=$part_id;
        // $d11Datas->process_id=$operation_id;
        // $d11Datas->product_process_id=$current_product_process_id;
        // $d11Datas->next_process_id=$next_process_id;
        // $d11Datas->next_product_process_id=$next_product_process_id;
        // $d11Datas->process_issue_qty=$dc_quantity;
        // $d11Datas->prepared_by = auth()->user()->id;
        // $d11Datas->save();

        // if ($regular==1) {
        //     foreach ($route_card_id as $key => $card_id) {
        //         if ($rc_issue_quantity[$key]!=0) {
        //             $previousD11Datas=TransDataD11::where('rc_id','=',$card_id)->where('next_process_id','=',$operation_id)->first();
        //             // dd($previousD11Datas);
        //             $old_issueqty=$previousD11Datas->issue_qty;
        //             $total_issue_qty=$old_issueqty+$rc_issue_quantity[$key];
        //             $previousD11Datas->issue_qty=$total_issue_qty;
        //             $previousD11Datas->updated_by = auth()->user()->id;
        //             $previousD11Datas->updated_at = Carbon::now();
        //             $previousD11Datas->update();

        //             $currentProcess=ProductProcessMaster::where('part_id','=',$part_id)->where('process_master_id','=',$operation_id)->first();
        //             $current_order_id=$currentProcess->process_order_id;
        //             $current_product_process_id=$currentProcess->id;

        //             $nextProcess=ProductProcessMaster::where('part_id','=',$part_id)->where('process_order_id','>',$current_order_id)->where('status','=',1)->first();
        //             $next_product_order_id=$nextProcess->process_order_id;
        //             $next_product_process_id=$nextProcess->id;
        //             $next_process_id=$nextProcess->process_master_id;

        //             $d12Datas=new TransDataD12;
        //             $d12Datas->open_date=$dc_date;
        //             $d12Datas->rc_id=$rc_id;
        //             $d12Datas->previous_rc_id=$card_id;
        //             $d12Datas->part_id=$part_id;
        //             $d12Datas->process_id=$operation_id;
        //             $d12Datas->product_process_id=$current_product_process_id;
        //             $d12Datas->issue_qty=$rc_issue_quantity[$key];
        //             $d12Datas->prepared_by = auth()->user()->id;
        //             $d12Datas->save();

        //             $d13Datas=new TransDataD13;
        //             $d13Datas->rc_id=$rc_id;
        //             $d13Datas->previous_rc_id=$card_id;
        //             $d13Datas->prepared_by = auth()->user()->id;
        //             $d13Datas->save();
        //             DB::commit();
        //         }
        //     }
        // }elseif ($regular>1) {
        //     foreach ($route_card_id as $key => $card_id) {
        //         if ($order_no[$key]==1) {
        //             if ($rc_issue_quantity[$key]!=0) {
        //                 $previousD11Datas=TransDataD11::where('rc_id','=',$card_id)->where('next_process_id','=',$operation_id)->first();
        //                 // dd($previousD11Datas);
        //                 $old_issueqty=$previousD11Datas->issue_qty;
        //                 $total_issue_qty=$old_issueqty+$rc_issue_quantity[$key];
        //                 $previousD11Datas->issue_qty=$total_issue_qty;
        //                 $previousD11Datas->updated_by = auth()->user()->id;
        //                 $previousD11Datas->updated_at = Carbon::now();
        //                 $previousD11Datas->update();

        //                 $currentProcess=ProductProcessMaster::where('part_id','=',$part_id)->where('process_master_id','=',$operation_id)->first();
        //                 $current_order_id=$currentProcess->process_order_id;
        //                 $current_product_process_id=$currentProcess->id;

        //                 $nextProcess=ProductProcessMaster::where('part_id','=',$part_id)->where('process_order_id','>',$current_order_id)->where('status','=',1)->first();
        //                 $next_product_order_id=$nextProcess->process_order_id;
        //                 $next_product_process_id=$nextProcess->id;
        //                 $next_process_id=$nextProcess->process_master_id;

        //                 $d12Datas=new TransDataD12;
        //                 $d11Datas->open_date=$dc_date;
        //                 $d11Datas->rc_id=$rc_id;
        //                 $d12Datas->previous_rc_id=$card_id;
        //                 $d12Datas->part_id=$part_id;
        //                 $d12Datas->process_id=$operation_id;
        //                 $d12Datas->product_process_id=$current_product_process_id;
        //                 $d12Datas->issue_qty=$rc_issue_quantity[$key];
        //                 $d12Datas->prepared_by = auth()->user()->id;
        //                 $d12Datas->save();

        //                 $d13Datas=new TransDataD13;
        //                 $d13Datas->rc_id=$rc_id;
        //                 $d13Datas->previous_rc_id=$card_id;
        //                 $d13Datas->prepared_by = auth()->user()->id;
        //                 $d13Datas->save();
        //                 DB::commit();
        //             }

        //         } elseif ($order_no[$key]==2) {
        //             if ($rc_issue_quantity[$key]!=0) {
        //                 $previousD11Datas=TransDataD11::where('rc_id','=',$card_id)->where('next_process_id','=',$operation_id)->first();
        //                 // dd($previousD11Datas);
        //                 $old_issueqty=$previousD11Datas->issue_qty;
        //                 $total_issue_qty=$old_issueqty+$rc_issue_quantity[$key];
        //                 $previousD11Datas->issue_qty=$total_issue_qty;
        //                 $previousD11Datas->updated_by = auth()->user()->id;
        //                 $previousD11Datas->updated_at = Carbon::now();
        //                 $previousD11Datas->update();

        //                 $currentProcess=ProductProcessMaster::where('part_id','=',$part_id)->where('process_master_id','=',$operation_id)->first();
        //                 $current_order_id=$currentProcess->process_order_id;
        //                 $current_product_process_id=$currentProcess->id;

        //                 $nextProcess=ProductProcessMaster::where('part_id','=',$part_id)->where('process_order_id','>',$current_order_id)->where('status','=',1)->first();
        //                 $next_product_order_id=$nextProcess->process_order_id;
        //                 $next_product_process_id=$nextProcess->id;
        //                 $next_process_id=$nextProcess->process_master_id;

        //                 $d12Datas=new TransDataD12;
        //                 $d11Datas->open_date=$dc_date;
        //                 $d11Datas->rc_id=$rc_id;
        //                 $d12Datas->previous_rc_id=$card_id;
        //                 $d12Datas->part_id=$part_id;
        //                 $d12Datas->process_id=$operation_id;
        //                 $d12Datas->product_process_id=$current_product_process_id;
        //                 $d12Datas->issue_qty=$rc_issue_quantity[$key];
        //                 $d12Datas->prepared_by = auth()->user()->id;
        //                 $d12Datas->save();

        //                 $d13Datas=new TransDataD13;
        //                 $d13Datas->rc_id=$rc_id;
        //                 $d13Datas->previous_rc_id=$card_id;
        //                 $d13Datas->prepared_by = auth()->user()->id;
        //                 $d13Datas->save();
        //                 DB::commit();

        //                 $dummyProcessData=ItemProcesmaster::where('operation','=','Dummy')->where('status','=',1)->first();
        //                 $dummy_process_id=$dummyProcessData->id;

        //                 $dummy_rc='AF'.$dc_number;
        //                 $rcMaster=new RouteMaster;
        //                 $rcMaster->create_date=$dc_date;
        //                 $rcMaster->process_id=$dummy_process_id;
        //                 $rcMaster->rc_id=$dummy_rc;
        //                 $rcMaster->prepared_by=auth()->user()->id;
        //                 $rcMaster->save();

        //                 $rcMasterData=RouteMaster::where('rc_id','=',$dummy_rc)->where('process_id','=',$dummy_process_id)->first();
        //                 $dummy_rc_id=$rcMasterData->id;

        //                 $d12Datas=new TransDataD12;
        //                 $d11Datas->open_date=$dc_date;
        //                 $d11Datas->rc_id=$dummy_rc_id;
        //                 $d12Datas->previous_rc_id=$rc_id;
        //                 $d12Datas->part_id=$part_id;
        //                 $d12Datas->process_id=$dummy_process_id;
        //                 $d12Datas->product_process_id=$current_product_process_id;
        //                 $d12Datas->receive_qty=$rc_issue_quantity[$key];
        //                 $d12Datas->issue_qty=$rc_issue_quantity[$key];
        //                 $d12Datas->prepared_by = auth()->user()->id;
        //                 $d12Datas->save();

        //                 $d13Datas=new TransDataD13;
        //                 $d13Datas->rc_id=$dummy_rc_id;
        //                 $d13Datas->previous_rc_id=$rc_id;
        //                 $d13Datas->prepared_by = auth()->user()->id;
        //                 $d13Datas->save();
        //                 DB::commit();
        //             }
        //         }
        //     }
        // }
        // return redirect()->route('delivery_challan.index')->withSuccess('Delivery Challan Created Successfully!');
    }



    /**
     * Display the specified resource.
     */
    public function show(DcTransactionDetails $dcTransactionDetails)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DcTransactionDetails $dcTransactionDetails)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDcTransactionDetailsRequest $request, DcTransactionDetails $dcTransactionDetails)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DcTransactionDetails $dcTransactionDetails)
    {
        //
    }
}
