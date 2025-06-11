<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Http\Request;
use App\Models\Supplier;
use App\Models\SupplierProduct;
use App\Models\RmGrnTransactionDetails;
use App\Models\RawMaterial;
use App\Models\RmRequistion;
use App\Models\RmRequistionGrnDetails;
use App\Models\Rackmaster;
use App\Models\ModeOfUnit;
use App\Models\StageQrCodeLock;
use App\Models\GRNInwardRegister;
use App\Models\GrnQuality;
use App\Models\PODetail;
use App\Models\POProductDetail;
use App\Models\ProductProcessMaster;
use App\Models\HeatNumber;
use App\Models\BomMaster;
use App\Models\TransDataD11;
use App\Models\TransDataD12;
use App\Models\TransDataD13;
use App\Models\ItemProcesmaster;
use App\Models\RouteMaster;
use App\Http\Requests\StoreGRNInwardRegisterRequest;
use App\Http\Requests\UpdateGRNInwardRegisterRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Number;
use Illuminate\Support\Facades\DB;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Http\Response;
use Spatie\Browsershot\Browsershot;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Calculation\MathTrig\Sum;

class GRNInwardRegisterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // $inward_datas = DB::table('g_r_n_inward_registers as a')
        //     ->join('p_o_details AS b', 'a.po_id', '=', 'b.id')
        //     ->join('route_masters AS r', 'b.ponumber', '=', 'r.id')
        //     ->join('route_masters AS h', 'a.grnnumber', '=', 'h.id')
        //     ->join('p_o_product_details AS c', 'a.p_o_product_id', '=', 'c.id')
        //     ->join('suppliers AS d', 'c.supplier_id', '=', 'd.id')
        //     ->join('supplier_products AS e', 'c.supplier_product_id', '=', 'e.id')
        //     ->join('raw_material_categories AS f', 'e.raw_material_category_id', '=', 'f.id')
        //     ->join('raw_materials AS g', 'e.raw_material_id', '=', 'g.id')
        //     ->select('a.id as id','h.rc_id as grnnumber','r.rc_id as ponumber', 'a.grndate','d.name AS sc_name','d.supplier_code AS sc_code','f.name AS rm_category','g.name AS rm_desc','a.inward_qty','a.approved_qty', 'a.onhold_qty', 'a.rejected_qty', 'a.issued_qty', 'a.return_qty', 'a.return_dc_qty', 'a.avl_qty', 'a.grn_close_date', 'a.approved_status', 'a.status')
        //     ->orderBy('a.id','ASC')
        //     ->get();
        // dd($inward_datas);

        $inward_datas=GRNInwardRegister::get();
        // return view('grn_inward.index',compact('inward_datas'));
        return view('grn_inward.index2',compact('inward_datas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        date_default_timezone_set('Asia/Kolkata');
        $current_date=date('Y-m-d');
        $current_year=date('Y');
		$rc="G";
		$current_rcno=$rc.$current_year;
        $process=ItemProcesmaster::where('operation','=','RM Inward')->where('status','=',1)->first();
        $process_id=$process->id;
        // dd($process_id);
        $count1=RouteMaster::where('process_id','=',$process_id)->where('rc_id','LIKE','%'.$current_rcno.'%')->orderBy('rc_id', 'DESC')->get()->count();
        // dd($count1);
        // $count=GRNInwardRegister::where('grnnumber','LIKE','%'.$current_rcno.'%')->orderBy('grnnumber', 'DESC')->get()->count();
        if ($count1 > 0) {
            // $po_data=GRNInwardRegister::where('grnnumber','LIKE','%'.$current_rcno.'%')->orderBy('grnnumber', 'DESC')->first();
            $po_data=RouteMaster::where('process_id','=',$process_id)->where('rc_id','LIKE','%'.$current_rcno.'%')->orderBy('rc_id', 'DESC')->first();
            $grnnumber=$po_data['rc_id']??NULL;
            $old_grnnumber=str_replace("G","",$grnnumber);
            $old_grnnumber_data=str_pad($old_grnnumber+1,9,0,STR_PAD_LEFT);
            $new_grnnumber='G'.$old_grnnumber_data;
        }else{
            $str='000001';
            $new_grnnumber=$current_rcno.$str;
        }
        // dd($new_grnnumber);
        $po_datas=PODetail::where('status','=',1)->get();
        // dd($po_datas);
        return view('grn_inward.create',compact('po_datas','new_grnnumber','current_date'));
    }

    public function grn_rmfetchdata(Request $request){
        $id=$request->id;
        $count = POProductDetail::where('id',$id)->get()->count();
        if ($count>0) {
            $po_product_datas = POProductDetail::with('supplier_products')->find($id);
                $sc_product_id=$po_product_datas->supplier_products->material->id;
                // $sc_product_id=$po_product_datas->supplier_product_id;
                $po_product_qty=round($po_product_datas->qty,2);
                $poproduct_inward_qty=round((GRNInwardRegister::where('p_o_product_id','=',$id)->sum('inward_qty')),2);
                $po_qty=round((($po_product_qty)-($poproduct_inward_qty)),2);
                $rm_datas=RawMaterial::find($sc_product_id);
                $max_qty=round($rm_datas->maximum_stock,2);
                $min_qty=round($rm_datas->minimum_stock,2);
                $inspect_qty=round((GRNInwardRegister::where('rm_id','=',$sc_product_id)->where('approved_status','=',0)->sum('inward_qty')),2);
                $inward_qty=round((GRNInwardRegister::where('rm_id','=',$sc_product_id)->sum('avl_qty')),2);
                $avl_qty=round((($inspect_qty)+($inward_qty)),2);
                $to_be_inward_qty=round((($max_qty)-($avl_qty)),2);
                $racks=Rackmaster::where('raw_material_id',$sc_product_id)->get();
                $uom_data_id=$po_product_datas->supplier_products->uom_id;
                $uom_data=ModeOfUnit::find($uom_data_id);
                $html='';
            foreach ($racks as $key => $rack) {
                $html .= '<option value="'.$rack->id.'">'.$rack->rack_name.'</option>';
            }
            $uom='';
            $uom .= '<option value="'.$uom_data->id.'">'.$uom_data->name.'</option>';
            return response()->json(['max_qty'=>$max_qty,'min_qty'=>$min_qty,'to_be_inward_qty'=>$to_be_inward_qty,'html'=>$html,'count'=>$count,'uom'=>$uom,'po_qty'=>$po_qty,'avl_qty'=>$avl_qty]);
        }
    }

    public function addGRNItem(Request $request)
    {
        if($request->rm_id)
        {
            $id=$request->rm_id;
            $count = POProductDetail::where('id',$id)->get()->count();
            if ($count>0) {
                $po_product_datas = POProductDetail::with('supplier_products')->find($id);
                $sc_product_id=$po_product_datas->supplier_products->material->id;
                // $sc_product_id=$po_product_datas->supplier_product_id;
                $rm_datas=RawMaterial::find($sc_product_id);
                $racks=Rackmaster::where('raw_material_id',$sc_product_id)->get();
                $uom_data=ModeOfUnit::find($po_product_datas->supplier_products->uom_id);
                $html = view('grn_inward.add_items',compact('uom_data','racks'))->render();
                return response()->json(['category'=>$html]);
            }
        }
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreGRNInwardRegisterRequest $request)
    {
        //SELECT `id`, `grnnumber_id`, `rack_id`, `heat_no_id`, `inspected_by`, `inspected_date`, `inspected_qty`, `approved_qty`, `onhold_qty`, `rejected_qty`, `status`, `prepared_by`, `updated_by`, `created_at`, `updated_at` FROM `grn_qualities` WHERE 1

        // dd($request->all());

        DB::beginTransaction();
        try {
            $validator = Validator::make($request->all(), [
                'grand_total' => 'required|min:0|max:'.$request->grand_total
            ]);

            // if ($validator->fails()) {
            //     dd('not ok');
            //     return back()->withErrors($validator->messages()->all()[0]);
            //     // return back()->with('toast_error', $validator->messages()->all()[0])->withInput();
            // }else{
                if (($request->to_be_inward_qty)>=($request->grand_total)) {
                    if (($request->po_qty)>=($request->grand_total)) {
                        $process=ItemProcesmaster::where('operation','=','RM Inward')->where('status','=',1)->first();
                        $process_id=$process->id;
                        $po_product_id=$request->rm_id;
                        $po_product_datas = POProductDetail::with('supplier_products')->find($po_product_id);
                        $rm_id=$po_product_datas->supplier_products->material->id;

                        $rm_datas=RawMaterial::find($rm_id);
                        $old_rm_avl=$rm_datas->avl_stock;
                        $avl_stock=(($old_rm_avl)+($request->grand_total));
                        $rm_datas->avl_stock=$avl_stock;
                        $rm_datas->updated_by = auth()->user()->id;
                        $rm_datas->updated_at = Carbon::now();
                        $rm_datas->update();

                        $poDatas=PODetail::find($request->po_id);


                        $pre_rc_id=$poDatas->ponumber;

                        $rcMaster=new RouteMaster;
                        $rcMaster->create_date=$request->grndate;
                        $rcMaster->process_id=$process_id;
                        $rcMaster->rc_id=$request->grnnumber;
                        $rcMaster->prepared_by=auth()->user()->id;
                        $rcMaster->save();

                        $rcMasterData=RouteMaster::where('rc_id','=',$request->grnnumber)->where('process_id','=',$process_id)->first();
                        $rc_id=$rcMasterData->id;

                        $d13Datas=new TransDataD13;
                        $d13Datas->rc_id=$rc_id;
                        $d13Datas->previous_rc_id=$pre_rc_id;
                        $d13Datas->prepared_by = auth()->user()->id;
                        $d13Datas->save();

                        $grn_datas = new GRNInwardRegister;
                        $grn_datas->grnnumber = $rc_id;
                        $grn_datas->grndate = $request->grndate;
                        $grn_datas->po_id = $request->po_id;
                        $grn_datas->rm_id = $rm_id;
                        $grn_datas->p_o_product_id = $po_product_id;
                        $grn_datas->invoice_number = $request->invoice_number??NULL;
                        $grn_datas->invoice_date = $request->invoice_date??NULL;
                        $grn_datas->dc_number = $request->dc_number??NULL;
                        $grn_datas->dc_date = $request->dc_date??NULL;
                        $grn_datas->inward_qty = $request->grand_total;
                        $grn_datas->prepared_by = auth()->user()->id;
                        $grn_datas->save();

                        $poproduct_inward_qty=GRNInwardRegister::where('p_o_product_id','=',$po_product_id)->sum('inward_qty');
                        $po_inward_qty=GRNInwardRegister::where('po_id','=',$request->po_id)->sum('inward_qty');

                        if (($poproduct_inward_qty)==($request->po_qty)) {
                            $po_product_datas->po_close_date=$request->grndate;
                            $po_product_datas->status=1;
                            $po_product_datas->updated_by = auth()->user()->id;
                            $po_product_datas->updated_at = Carbon::now();
                            $po_product_datas->update();
                        }
                        $poqtydatas=POProductDetail::where('po_id','=',$request->po_id)->sum('qty');
                        if ($poqtydatas==$po_inward_qty) {
                            $poDatas->status=0;
                        }
                        $poDatas->correction_status=2;
                        $poDatas->updated_by = auth()->user()->id;
                        $poDatas->updated_at = Carbon::now();
                        $poDatas->update();

                        $grn_id=$grn_datas->id;

                        $rack_ids=$request->rack_id;

                        foreach ($rack_ids as $key => $rack_id) {
                            $grn_heat_nos = new HeatNumber;
                            $grn_heat_nos->grnnumber_id =$grn_id;
                            $grn_heat_nos->heatnumber =$request->heatnumber[$key];
                            $grn_heat_nos->tc_no =$request->tc_no[$key];
                            $grn_heat_nos->rack_id =$rack_id;
                            $grn_heat_nos->lot_no =$request->lot_no[$key];
                            $grn_heat_nos->coil_no =$request->coil_no[$key];
                            $grn_heat_nos->coil_inward_qty =$request->coil_inward_qty[$key];
                            $grn_heat_nos->prepared_by = auth()->user()->id;
                            $grn_heat_nos->save();

                            $heat_no_id=$grn_heat_nos->id;

                            $grn_qc=new GrnQuality;
                            $grn_qc->grnnumber_id =$grn_id;
                            $grn_qc->heat_no_id =$heat_no_id;
                            $grn_qc->rack_id =$rack_id;
                            $grn_qc->inspected_qty =$request->coil_inward_qty[$key];
                            $grn_qc->prepared_by = auth()->user()->id;
                            $grn_qc->save();
                            DB::commit();
                        }
                        return response()->json(['status'=>'success','message'=>'GRN is Created Successfully...!','symbol'=>'Added!']);
                    }else {
                        // dd('not ok1');
                        return response()->json(['status'=>'error','message'=>'You Cannot Inward More than PO Quantity Limit...!','symbol'=>'Cancelled!']);
                    }
                } else {
                    // dd('not ok2');
                    return response()->json(['status'=>'error','message'=>'You Cannot Inward More than Maximum Stock Limit...!','symbol'=>'Cancelled!']);
                }
            // }
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollback();
            //dd($th->getMessage());
            return response()->json(['errors' => $th->getMessage()]);
            // return redirect()->back()->withErrors($th->getMessage());

        }
    }


    public function rmReturnData(){

    }

    public function rmReturnReceipt(){
        date_default_timezone_set('Asia/Kolkata');
        $current_date=date('Y-m-d');
        $d11Datas=TransDataD11::with('rcmaster')->where('rc_status','=',1)->get();
        $activity='Return RM Receipt';
        $stage='Store';
        $qrCodes_count=StageQrCodeLock::where('stage','=',$stage)->where('activity','=',$activity)->where('status','=',1)->count();
        return view('rm_issuance.return_rm',compact('d11Datas','qrCodes_count','current_date'));
    }

    public function rmIssuanceData(){
        // $d12Datas=TransDataD12::with(['partmaster','currentprocessmaster','currentproductprocessmaster','current_rcmaster','previous_rcmaster','heat_nomaster','grndata','rm_master'])->where('process_id','=',3)->orderBy('open_date', 'DESC')->get();
        $d12Datas=TransDataD12::with(['partmaster','currentprocessmaster','currentproductprocessmaster','current_rcmaster','previous_rcmaster','heat_nomaster','grndata','rm_master'])->where('process_id','=',3)->get();
        // dd($d12Datas);
        // $d11Datas=DB::table('trans_data_d12_s as a')
        // ->join('heat_numbers AS b', 'a.heat_id', '=', 'b.id')
        // ->join('raw_materials AS c', 'a.rm_id', '=', 'c.id')
        // ->join('route_masters AS d', 'a.rc_id', '=', 'd.id')
        // ->join('route_masters AS e', 'a.previous_rc_id', '=', 'e.id')
        // ->select('d.rc_id as rc_no','a.open_date','a.rm_issue_qty','e.rc_id as previous_rc_no','a.status','b.id as heat_id','b.heatnumber','b.tc_no','b.coil_no','c.name as rm_desc')
        // ->where('a.process_id','=',3)
        // ->orderBy('a.id', 'DESC')
        // ->get();
        // dd($d11Datas);
        return view('rm_issuance.index',compact('d12Datas'));
    }

    public function rmIssuance(){
        date_default_timezone_set('Asia/Kolkata');
        $current_date=date('Y-m-d');
        $current_year=date('Y');
		$rc="A";
		$current_rcno=$rc.$current_year;
        $process=ItemProcesmaster::where('operation','=','Store')->where('status','=',1)->first();
        $process_id=$process->id;
        $count1=RouteMaster::where('process_id','=',$process_id)->where('rc_id','LIKE','%'.$current_rcno.'%')->orderBy('rc_id', 'DESC')->get()->count();
        // $count=TransDataD11::where('rc_no','LIKE','%'.$current_rcno.'%')->orderBy('rc_no', 'DESC')->get()->count();
        if ($count1 > 0) {
            // $rc_data=TransDataD11::where('rc_no','LIKE','%'.$current_rcno.'%')->orderBy('rc_no', 'DESC')->first();
            $rc_data=RouteMaster::where('process_id','=',$process_id)->where('rc_id','LIKE','%'.$current_rcno.'%')->orderBy('rc_id', 'DESC')->first();
            $rcnumber=$rc_data['rc_id']??NULL;
            $old_rcnumber=str_replace("A","",$rcnumber);
            $old_rcnumber_data=str_pad($old_rcnumber+1,9,0,STR_PAD_LEFT);
            $new_rcnumber='A'.$old_rcnumber_data;
        }else{
            $str='000001';
            $new_rcnumber=$current_rcno.$str;
        }
        // $grnDatas=GRNInwardRegister::where('status','=',0)->select('id','grnnumber')->get();
        $grnDatas=DB::table('g_r_n_inward_registers as a')
        ->join('heat_numbers AS b', 'a.id', '=', 'b.grnnumber_id')
        ->join('route_masters AS n', 'a.grnnumber', '=', 'n.id')
        ->select('a.id as id','n.rc_id as grnnumber')
        ->where('b.status','=',1)
        ->where('a.status','=',0)
        ->groupBy('a.id')
        ->get();
        // dd($grnDatas);
        // dd($new_rcnumber);
        $activity='RM Issuance';
        $stage='Store';
        $qrCodes_count=StageQrCodeLock::where('stage','=',$stage)->where('activity','=',$activity)->where('status','=',1)->count();
        if ($qrCodes_count>0) {
            # code...
            // dd('QR Code Entry...');
            return view('rm_issuance.qr_create',compact('new_rcnumber','current_date'));
        } else {
            return view('rm_issuance.create',compact('grnDatas','new_rcnumber','current_date'));
        }

    }

    public function grnRmFetchData(Request $request){
        // dd($request->all());
        $grn_id=$request->grn_id;

        $heatnNoDatas=DB::table('g_r_n_inward_registers as a')
        ->join('heat_numbers AS b', 'a.id', '=', 'b.grnnumber_id')
        ->select('a.id as grn_id','a.grnnumber','b.id as heat_id','b.heatnumber')
        ->where('b.status','=',1)
        ->where('a.status','=',0)
        ->groupBy('b.heatnumber')
        ->where('a.id','=',$grn_id)
        ->get();

        $rmDatas=DB::table('g_r_n_inward_registers as a')
        ->join('p_o_product_details AS b', 'a.p_o_product_id', '=', 'b.id')
        ->join('supplier_products as c', 'b.supplier_product_id', '=', 'c.id')
        ->join('raw_materials as d', 'c.raw_material_id', '=', 'd.id')
        ->join('bom_masters as e', 'e.rm_id', '=', 'd.id')
        ->join('child_product_masters as f', 'e.child_part_id', '=', 'f.id')
        ->join('mode_of_units as g', 'c.uom_id', '=', 'g.id')
        ->join('product_process_masters as l','l.part_id','=','f.id')
        ->select('a.grnnumber','d.id as rm_id','d.name as rm_desc','f.id as part_id','f.child_part_no as part_no','c.uom_id','g.name as uom_name')
        ->where('a.status','=',0)
        ->where('l.process_master_id','=',3)
        ->where('f.item_type','=',1)
        ->where('a.id','=',$grn_id)
        ->get();

        $fifoCheck=DB::table('g_r_n_inward_registers as a')
        ->join('p_o_product_details AS b', 'a.p_o_product_id', '=', 'b.id')
        ->join('supplier_products as c', 'b.supplier_product_id', '=', 'c.id')
        ->join('raw_materials as d', 'c.raw_material_id', '=', 'd.id')
        ->join('bom_masters as e', 'e.rm_id', '=', 'd.id')
        ->join('child_product_masters as f', 'e.child_part_id', '=', 'f.id')
        ->join('mode_of_units as g', 'c.uom_id', '=', 'g.id')
        ->join('product_process_masters as l','l.part_id','=','f.id')
        ->join('route_masters as k','k.id','=','a.grnnumber')
        ->select('a.grnnumber','d.id as rm_id','d.name as rm_desc','a.id as grn_no','k.rc_id')
        ->where('a.status','=',0)
        ->where('l.process_master_id','=',3)
        ->where('f.item_type','=',1)
        ->where('d.id','=',$rmDatas[0]->rm_id)
        ->first();
        $fifoGrn=$fifoCheck->grn_no;
        $fifoGrnCard=$fifoCheck->rc_id;
        if ($fifoGrn==$grn_id) {
            $success = true;
        }else {
            $success = false;
        }

        $heat_no='<option value="" selected>Select The Heat No</option>';
        foreach ($heatnNoDatas as $key => $heatnNoData) {
            $heat_no.='<option value="'.$heatnNoData->heatnumber.'">'.$heatnNoData->heatnumber.'</option>';
        }
        $uom='<option value="'.$rmDatas[0]->uom_id.'">'.$rmDatas[0]->uom_name.'</option>';
        $rm='<option value="'.$rmDatas[0]->rm_id.'">'.$rmDatas[0]->rm_desc.'</option>';
        $part='<option value="" selected>Select The RM</option>';
        foreach ($rmDatas as $key => $rmData) {
            $part.='<option value="'.$rmData->part_id.'">'.$rmData->part_no.'</option>';
        }
        return response()->json(['rm'=>$rm,'part'=>$part,'heat_no'=>$heat_no,'uom'=>$uom,'fifoGrn'=>$fifoGrnCard,'success'=>$success]);
    }

    public function grnQcFetchData(Request $request){
        // dd($request->all());
        $grn_qc_id=$request->rm_qc_id;
        $grn_qc_datas=GrnQuality::with(['grn_data','heat_no_data','rack_data'])->where('id','=',$grn_qc_id)->first();
        $grnnumber_id=$grn_qc_datas->grnnumber_id;
        if ($grn_qc_datas->status==1) {
            $count=1;
            $grn_no='<option value="'.$grn_qc_datas->grn_data->id.'">'.$grn_qc_datas->grn_data->rcmaster->rc_id.'</option>';
            $rm_id='<option value="'.$grn_qc_datas->grn_data->poproduct->supplier_products->material->id.'">'.$grn_qc_datas->grn_data->poproduct->supplier_products->material->name.'</option>';
            $uom='<option value="'.$grn_qc_datas->grn_data->poproduct->uom_datas->id.'">'.$grn_qc_datas->grn_data->poproduct->uom_datas->name.'</option>';
            $heat_id=$grn_qc_datas->heat_no_data->id;
            $heat_no='<option value="'.$grn_qc_datas->heat_no_data->heatnumber.'">'.$grn_qc_datas->heat_no_data->heatnumber.'</option>';
            $coil_no='<option value="'.$grn_qc_datas->heat_no_data->coil_no.'">'.$grn_qc_datas->heat_no_data->coil_no.'</option>';
            $tc_no=$grn_qc_datas->heat_no_data->tc_no;
            $lot_no=$grn_qc_datas->heat_no_data->lot_no;

            $fifoCheck=DB::table('g_r_n_inward_registers as a')
            ->join('p_o_product_details AS b', 'a.p_o_product_id', '=', 'b.id')
            ->join('supplier_products as c', 'b.supplier_product_id', '=', 'c.id')
            ->join('raw_materials as d', 'c.raw_material_id', '=', 'd.id')
            ->join('bom_masters as e', 'e.rm_id', '=', 'd.id')
            ->join('child_product_masters as f', 'e.child_part_id', '=', 'f.id')
            ->join('mode_of_units as g', 'c.uom_id', '=', 'g.id')
            ->join('product_process_masters as l','l.part_id','=','f.id')
            ->join('route_masters as k','k.id','=','a.grnnumber')
            ->select('a.grnnumber','d.id as rm_id','d.name as rm_desc','a.id as grn_no','k.rc_id','a.avl_qty')
            ->where('l.process_master_id','=',3)
            ->where('f.item_type','=',1)
            ->where('d.id','=',$grn_qc_datas->grn_data->poproduct->supplier_products->material->id)
            ->havingRaw('a.avl_qty >?', [0])
            ->first();
            $fifoGrn=$fifoCheck->grn_no;
            // dd($fifoGrn);
            $fifoGrnCard=$fifoCheck->rc_id;
            if ($fifoGrn==$grn_qc_datas->grn_data->id) {
                $success = true;
                $grn_coil_fifoCheck=GrnQuality::with(['grn_data','heat_no_data','rack_data'])->where('grnnumber_id','=',$grnnumber_id)->select('*',DB::raw('((approved_qty)-(issue_qty)) as t_avl_qty'))->havingRaw('t_avl_qty >?', [0])->orderBy('id','ASC')->first();
                // dd($grn_coil_fifoCheck);
                $grn_coil_check=$grn_coil_fifoCheck->id;
                $grn_coil_no=$grn_coil_fifoCheck->heat_no_data->coil_no;
                $grn_coil_heat_no=$grn_coil_fifoCheck->heat_no_data->heatnumber;
                $grn_coil_lot_no=$grn_coil_fifoCheck->heat_no_data->lot_no;
                if ($grn_coil_check==$grn_qc_datas->id) {
                    $coil_msg=true;
                } else {
                    $coil_msg=false;
                    $grn_coil_no=$grn_qc_datas->heat_no_data->coil_no;
                    $grn_coil_heat_no=$grn_qc_datas->heat_no_data->heatnumber;
                    $grn_coil_lot_no=$grn_qc_datas->heat_no_data->lot_no;
                }
            }else {
                $success = false;
                $coil_msg=false;
                $grn_coil_no=$grn_qc_datas->heat_no_data->coil_no;
                $grn_coil_heat_no=$grn_qc_datas->heat_no_data->heatnumber;
                $grn_coil_lot_no=$grn_qc_datas->heat_no_data->lot_no;
            }
            $partDatas=BomMaster::with('childpart_master')->where('rm_id','=',$grn_qc_datas->grn_data->poproduct->supplier_products->material->id)->get();
            $part='<option value="" selected>Select The Part Number</option>';
            foreach ($partDatas as $key => $partData) {
                $part.='<option value="'.$partData->childpart_master->id.'">'.$partData->childpart_master->child_part_no.'</option>';
            }
            $grnQcDatas=GrnQuality::find($grn_qc_id);
            $avl_qty=(($grnQcDatas->approved_qty)-($grnQcDatas->issue_qty)-($grnQcDatas->return_qty));
            if ($avl_qty>0) {
                $avl_msg=true;
                $html='';
            }else{
                $avl_msg=false;
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
                $html.='<svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Danger:"><use xlink:href="#exclamation-triangle-fill"/></svg><div>This GRN Number Is Not Available...</div></div>';
            }
            return response()->json(['count'=>$count,'grn_no'=>$grn_no,'rm_id'=>$rm_id,'heat_id'=>$heat_id,'heat_no'=>$heat_no,'coil_no'=>$coil_no,'lot_no'=>$lot_no,'tc_no'=>$tc_no,'uom'=>$uom,'fifoGrn'=>$fifoGrnCard,'success'=>$success,'avl_qty'=>$avl_qty,'part'=>$part,'html'=>$html,'avl_msg'=>$avl_msg,'coil_msg'=>$coil_msg,'grn_coil_no'=>$grn_coil_no,'grn_coil_heat_no'=>$grn_coil_heat_no,'grn_coil_lot_no'=>$grn_coil_lot_no]);
        }else{
            $count=0;
            return response()->json(['count'=>$count]);
        }

    }

    public function grnHeatFetchData(Request $request){
        $grn_id=$request->grn_id;
        $heat_id=$request->heat_id;
        $count=HeatNumber::where('heatnumber','=',$heat_id)->where('status','=',1)->get()->count();
        if($count>0){
            $fifoCheck=HeatNumber::where('heatnumber','=',$heat_id)->where('grnnumber_id','=',$grn_id)->where('status','=',1)->first();
            $fifoHeatno=$fifoCheck->heatnumber;
            $heatDatas=HeatNumber::where('heatnumber','=',$heat_id)->where('grnnumber_id','=',$grn_id)->where('status','=',1)->get();
            $coil_no='<option value="" selected>Select The Coil No</option>';
            foreach ($heatDatas as $key => $heatData) {
                $coil_no.='<option value="'.$heatData->coil_no.'">'.$heatData->coil_no.'</option>';
            }
            if ($fifoHeatno==$heat_id) {
                $success = true;
            }else {
                $success = false;
            }

            return response()->json(['count'=>$count,'coil_no'=>$coil_no,'success'=>$success,'fifoHeatno'=>$fifoHeatno]);
        }else{
            return response()->json(['count'=>0]);
        }
    }

    public function grnCoilFetchData(Request $request){
        $grn_id=$request->grn_id;
        $heat_no_id=$request->heat_id;
        $coil_no=$request->coil_no;
        $count=HeatNumber::where('heatnumber','=',$heat_no_id)->where('coil_no','=',$coil_no)->where('status','=',1)->first()->count();
        if($count>0){
            $fifoCheck=HeatNumber::where('heatnumber','=',$heat_no_id)->where('status','=',1)->first();
            $fifoCoilno=$fifoCheck->coil_no;
            $heatDatas=HeatNumber::where('heatnumber','=',$heat_no_id)->where('coil_no','=',$coil_no)->where('status','=',1)->first();
            $heat_id=$heatDatas->id;
            $tc_no=$heatDatas->tc_no;
            $lot_no=$heatDatas->lot_no;
            $count2=GrnQuality::where('heat_no_id','=',$heat_id)->where('grnnumber_id','=',$grn_id)->first()->count();
            if ($count2>0) {
                $grnQcDatas=GrnQuality::where('heat_no_id','=',$heat_id)->where('grnnumber_id','=',$grn_id)->first();
                $avl_qty=(($grnQcDatas->approved_qty)-($grnQcDatas->issue_qty));
                $grn_qc_id=$grnQcDatas->id;
            }else{
                $avl_qty=0;
            }
            if ($fifoCoilno==$coil_no) {
                $success = true;
            }else{
                $success = false;
            }
        return response()->json(['count'=>$count,'avl_qty'=>$avl_qty,'grn_qc_id'=>$grn_qc_id,'heat_id'=>$heat_id,'tc_no'=>$tc_no,'lot_no'=>$lot_no,'success'=>$success,'fifoCoilno'=>$fifoCoilno]);
        }else{
        return response()->json(['count'=>0]);
        }

    }
    public function storeData(Request $request)
    {
        // dd($request->all());
        DB::beginTransaction();
        try {
            $process=ItemProcesmaster::where('operation','=','Store')->where('status','=',1)->first();
            // dd($process);
                $process_id=$process->id;

                $rcMaster=new RouteMaster;
                $rcMaster->create_date=$request->rc_date;
                $rcMaster->process_id=$process_id;
                $rcMaster->rc_id=$request->rc_no;
                $rcMaster->prepared_by=auth()->user()->id;
                $rcMaster->save();

                // stock add in grn inward table
                $grnInwardDatas=GRNInwardRegister::find($request->grnnumber);
                $total_issue_qty=(($grnInwardDatas->issued_qty)+($request->issue_kg));
                $grnInwardDatas->issued_qty = $total_issue_qty;
                $approved_qty=$grnInwardDatas->approved_qty;
                $grn_avl_qty=$approved_qty-$total_issue_qty;
                $grnInwardDatas->avl_qty =$grn_avl_qty;
                if ($grn_avl_qty==0) {
                    $grnInwardDatas->status=1;
                }
                $grnInwardDatas->updated_by = auth()->user()->id;
                $grnInwardDatas->updated_at = Carbon::now();
                $grnInwardDatas->update();

            // dd($grnInwardDatas->grnnumber);
            $rcMasterData=RouteMaster::where('rc_id','=',$request->rc_no)->where('process_id','=',$process_id)->first();
            $rc_id=$rcMasterData->id;
            // dd($rcMasterData);

            // stock add in grn quality table
            $grnqcDatas=GrnQuality::find($request->grn_qc_id);
            $total_issue_qty=(($grnqcDatas->issue_qty)+($request->issue_kg));
            $grnqcDatas->issue_qty=$total_issue_qty;
            $approved_qty=$grnqcDatas->approved_qty;
            $avl_qty=$approved_qty-$total_issue_qty;
            $grnqcDatas->rm_req_status=1;
            // if ($avl_qty==0) {
            //     $grnqcDatas->status=0;
            // }
            $grnqcDatas->updated_by = auth()->user()->id;
            $grnqcDatas->updated_at = Carbon::now();
            $grnqcDatas->update();

            $heatNoDatas=HeatNumber::find($grnqcDatas->heat_no_id);

            // dd($total_issue_qty);
            $part_id=$request->part_id;
            $productProcess=DB::table('item_procesmasters as a')
            ->join('product_process_masters AS b', 'a.id', '=', 'b.process_master_id')
            ->join('child_product_masters as c', 'b.part_id', '=', 'c.id')
            ->select('b.process_master_id as process_id','b.process_order_id','b.id')
            ->where('a.operation','=','Store')
            ->where('a.status','=',1)
            ->where('c.id','=',$part_id)
            ->first();
            // dd($productProcess);

            $current_processproduct_id=$productProcess->id;
            $current_process_id=$productProcess->process_id;
            $current_order_id=$productProcess->process_order_id;

            $next_productProcess=DB::table('item_procesmasters as a')
            ->join('product_process_masters AS b', 'a.id', '=', 'b.process_master_id')
            ->join('child_product_masters as c', 'b.part_id', '=', 'c.id')
            ->select('b.process_master_id as process_id','b.process_order_id','b.id')
            ->where('a.operation_type','=','STOCKING POINT')
            ->where('b.process_order_id','>',$current_order_id)
            ->where('a.status','=',1)
            ->where('b.status','=',1)
            ->where('c.id','=',$part_id)
            ->first();
            // dd($next_productProcess);

            $next_processproduct_id=$next_productProcess->id;
            $next_process_id=$next_productProcess->process_id;
            $next_order_id=$next_productProcess->process_order_id;
            // $productProcess=ProductProcessMaster::where('part_id','=',$part_id)->where('status','=',1)->where('process_order_id','=',1)->first();
            // $nextproductProcess=ProductProcessMaster::where('part_id','=',$part_id)->where('status','=',1)->where('process_order_id','=',2)->first();
            // $current_processproduct_id=$productProcess->id;
            // $current_process_id=$productProcess->process_master_id;


            // $next_processproduct_id=$nextproductProcess->id;
            // $next_process_id=$nextproductProcess->process_master_id;

            $d11Datas=new TransDataD11;
            $d11Datas->open_date=$request->rc_date;
            $d11Datas->rc_id=$rc_id;
            $d11Datas->rm_req_id=$request->id;
            $d11Datas->part_id=$request->part_id;
            $d11Datas->process_id=$current_process_id;
            $d11Datas->product_process_id=$current_processproduct_id;
            $d11Datas->next_process_id=$next_process_id;
            $d11Datas->next_product_process_id=$next_processproduct_id;
            $d11Datas->process_issue_qty=$request->issue_kg;
            $d11Datas->prepared_by = auth()->user()->id;
            $d11Datas->save();

            $d12Datas=new TransDataD12;
            $d12Datas->open_date=$request->rc_date;
            $d12Datas->rc_id=$rc_id;
            $d12Datas->previous_rc_id=$grnInwardDatas->grnnumber;
            $d12Datas->part_id=$request->part_id;
            $d12Datas->rm_id=$request->rm_id;
            $d12Datas->process_id=$current_process_id;
            $d12Datas->product_process_id=$current_processproduct_id;
            $d12Datas->rm_issue_qty=$request->issue_kg;
            $d12Datas->grn_id=$request->grnnumber;
            $d12Datas->heat_id=$request->heat_id;
            $d12Datas->coil_no=$request->coil_no;
            $d12Datas->prepared_by = auth()->user()->id;
            $d12Datas->save();

            $d13Datas=new TransDataD13;
            $d13Datas->rc_id=$rc_id;
            $d13Datas->previous_rc_id=$grnInwardDatas->grnnumber;
            $d13Datas->prepared_by = auth()->user()->id;
            $d13Datas->save();

            $rm_avl=GRNInwardRegister::where('rm_id','=',$request->rm_id)->sum('avl_qty');

            $rm_datas=RawMaterial::find($request->rm_id);
            $old_rm_avl=$rm_datas->avl_stock;
            $avl_stock=(($old_rm_avl)-($request->issue_kg));
            $rm_datas->avl_stock=$rm_avl;
            $rm_datas->updated_by = auth()->user()->id;
            $rm_datas->updated_at = Carbon::now();
            $rm_datas->update();

            if ($request->requsition_id!=NULL) {
                // dd($request->all());
                $rmRequistionGrnDetails=RmRequistionGrnDetails::where('req_rc_id','=',$request->id)->where('grn_qc_id','=',$request->grn_qc_id)->where('status','=',0)->first();
                $rmRequistionGrnDetails->status=1;
                $rmRequistionGrnDetails->issue_rc_id=$rc_id;
                $rmRequistionGrnDetails->approve_by = auth()->user()->id;
                $rmRequistionGrnDetails->updated_by = auth()->user()->id;
                $rmRequistionGrnDetails->updated_at = Carbon::now();
                $rmRequistionGrnDetails->update();

                $RmRequistion=RmRequistion::find($request->id);
                $old_issue_kg=$RmRequistion->issue_kg;
                $RmRequistion->issue_kg=(($old_issue_kg)+($request->issue_kg));
                if ($request->req_count==1) {
                    $RmRequistion->status=1;
                }else{
                    $RmRequistion->status=0;
                }
                $RmRequistion->approve_by = auth()->user()->id;
                $RmRequistion->updated_by = auth()->user()->id;
                $RmRequistion->updated_at = Carbon::now();
                $RmRequistion->update();
            }

            DB::commit();
            // return redirect()->route('grn_qc.index')->withSuccess('Your Inspection Data Is Submitted Successfully!');
            if ($request->req_count==1) {
                // dd('ok');
                $success=false;
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
                </svg><div class="alert alert-success d-flex align-items-center" role="alert">';
                $html.='<svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Success:"><use xlink:href="#check-circle-fill"/></svg><div>The RM Issued Successfully..!</div></div>';
                // return redirect()->route('rmissuance.index')->withSuccess('RM Issued is Created Successfully!');

            }else{
                // dd('Not ok');
                $success=true;
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
                </svg><div class="alert alert-success d-flex align-items-center" role="alert">';
                $html.='<svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Success:"><use xlink:href="#check-circle-fill"/></svg><div>The RM Issued Successfully..!</div></div>';
            }
            return response()->json(['success'=>$success,'html'=>$html]);

        } catch (\Throwable $th) {
            //throw $th;
            DB::rollback();
            dd($th->getMessage());
            return redirect()->back()->withErrors($th->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        //
        // dd($id);
        $count=GrnQuality::with(['grn_data','heat_no_data','rack_data','inspected_user'])->where('grnnumber_id','=',$id)->where('status','!=',0)->get()->count();
        // dd($count);
        if ($count > 0) {
            $grn_qc_datas=GrnQuality::with(['grn_data','heat_no_data','rack_data'])->where('grnnumber_id','=',$id)->where('status','!=',0)->get();
            // $qrCodes=QrCode::size(95)->style('round')->generate($id);

            // dd($grn_qc_datas);
            // return view('grn_inward.grn_print',compact('grn_qc_datas'));
            $html = view('grn_inward.grn_print',compact('grn_qc_datas'))->render();
            $width=101.6;$height=101.6;
            $pdf=Browsershot::html($html)->setIncludePath(config('services.browsershot.include_path'))->paperSize($width, $height)->pdf();
            return new Response($pdf,200,[
                'Content-Type'=>'application/pdf',
                'Content-Disposition'=>'inline;filename="invoice.pdf"'
            ]);

        } else {
            return back()->withMessage('Sorry No Inspected RM Not Availble!');

        }


    }
    public function rmIssuancePrint2($id)
    {
        // dd($id);
        $d12Datas=TransDataD12::with('partmaster','current_rcmaster','heat_nomaster','grndata','rm_master')->where('rc_id','=',$id)->where('process_id','=',3)->first();
        $next_count=TransDataD12::with('partmaster','current_rcmaster','heat_nomaster','grndata','rm_master')->where('previous_rc_id','=',$id)->count();
        $nextd12Datas=TransDataD12::with('partmaster','current_rcmaster','heat_nomaster','grndata','rm_master')->where('previous_rc_id','=',$id)->get();
        // dd($d12Datas);
        $count=RmRequistionGrnDetails::where('issue_rc_id','=',$id)->count();
        $rmRequistionGrnDetails=RmRequistionGrnDetails::with('req_master','group_master','machine_master')->where('issue_rc_id','=',$id)->first();
        $qrCodes=QrCode::size(70)->style('round')->generate($id);
        // return view('rm_issuance.routecard_print',compact('d12Datas','qrCodes'));
        // $html = view('rm_issuance.routecard_print',compact('d12Datas','qrCodes'))->render();
        $html=view('rm_issuance.rc',compact('d12Datas','qrCodes','count','rmRequistionGrnDetails','next_count','nextd12Datas'))->render();
        // $width=101.6;$height=101.6;
        $pdf=Browsershot::html($html)->setIncludePath(config('services.browsershot.include_path'))->format('A4')->pdf();
        return new Response($pdf,200,[
            'Content-Type'=>'application/pdf',
            'Content-Disposition'=>'inline;filename="invoice.pdf"'
        ]);
    }
    public function rmIssuancePrint($id)
    {
        // dd($id);
        $d12Datas=TransDataD12::with('partmaster','current_rcmaster','heat_nomaster','grndata','rm_master')->where('rc_id','=',$id)->where('process_id','=',3)->first();
        $next_count = DB::table('trans_data_d12_s as a')
        ->join('child_product_masters AS b', 'a.part_id', '=', 'b.id')
        ->join('item_procesmasters AS c', 'a.process_id', '=', 'c.id')
        ->join('route_masters AS e', 'a.rc_id', '=', 'e.id')
        ->join('users AS f', 'a.prepared_by', '=', 'f.id')
        ->where('a.previous_rc_id','=',$id)
        ->get()->count();
        $nextd12Datas = DB::table('trans_data_d12_s as a')
        ->join('child_product_masters AS b', 'a.part_id', '=', 'b.id')
        ->join('item_procesmasters AS c', 'a.process_id', '=', 'c.id')
        ->join('route_masters AS e', 'a.rc_id', '=', 'e.id')
        ->join('users AS f', 'a.prepared_by', '=', 'f.id')
        ->select('a.open_date','c.operation','b.child_part_no','a.area','e.rc_id','f.name','a.receive_qty','a.reject_qty','a.rework_qty','a.issue_qty','a.created_at','a.remarks')
        ->where('a.previous_rc_id','=',$id)
        ->get();
        $nextd12Summaries = TransDataD12::where('previous_rc_id','=',$id)->select(DB::raw('SUM(receive_qty) as total_receive_qty'),DB::raw('SUM(reject_qty) as total_reject_qty'),DB::raw('SUM(rework_qty) as total_rework_qty'),DB::raw('SUM(issue_qty) as total_issue_qty'))
        ->first();
        // dd($nextd12Summaries);
        // $next_count=TransDataD12::with('partmaster','receiver','current_rcmaster')->where('previous_rc_id','=',$id)->count();
        // $nextd12Datas=TransDataD12::with('partmaster','receiver','current_rcmaster')->where('previous_rc_id','=',$id)->get();
        // dd($nextd12Datas);
        $count=RmRequistionGrnDetails::where('issue_rc_id','=',$id)->count();
        $rmRequistionGrnDetails=RmRequistionGrnDetails::with('req_master','group_master','machine_master')->where('issue_rc_id','=',$id)->first();
        $qrCodes=QrCode::size(70)->style('round')->generate($id);
        // dd($rmRequistionGrnDetails);
        // return view('rm_issuance.routecard_print',compact('d12Datas','qrCodes'));
        // $html = view('rm_issuance.routecard_print',compact('d12Datas','qrCodes'))->render();
        $html=view('rm_issuance.rc2',compact('d12Datas','qrCodes','count','rmRequistionGrnDetails','next_count','nextd12Datas','nextd12Summaries'))->render();
        // $width=101.6;$height=101.6;
        $pdf=Browsershot::html($html)->setIncludePath(config('services.browsershot.include_path'))->format('A4')->pdf();
        return new Response($pdf,200,[
            'Content-Type'=>'application/pdf',
            'Content-Disposition'=>'inline;filename="invoice.pdf"'
        ]);
    }
    /**
     * Show the form for editing the specified resource.
     */
    public function edit(GRNInwardRegister $gRNInwardRegister)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateGRNInwardRegisterRequest $request, GRNInwardRegister $gRNInwardRegister)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(GRNInwardRegister $gRNInwardRegister)
    {
        //
    }
}
