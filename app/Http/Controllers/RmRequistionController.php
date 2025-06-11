<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Http\Request;
use App\Models\Supplier;
use App\Models\SupplierProduct;
use App\Models\RawMaterial;
use App\Models\Rackmaster;
use App\Models\ModeOfUnit;
use App\Models\GRNInwardRegister;
use App\Models\GrnQuality;
use App\Models\PODetail;
use App\Models\POProductDetail;
use App\Models\ProductProcessMaster;
use App\Models\RouteMaster;
use App\Models\HeatNumber;
use App\Models\ItemProcesmaster;
use App\Models\TransDataD11;
use App\Models\TransDataD12;
use App\Models\TransDataD13;
use App\Models\BomMaster;
use App\Models\ChildProductMaster;
use App\Models\FinalQcInspection;
use App\Models\StageQrCodeLock;
use App\Models\GroupMaster;
use App\Models\CellMaster;
use App\Models\MachineMaster;
use App\Models\RmRequistion;
use App\Models\RmRequistionGrnDetails;
use App\Http\Requests\StoreRmRequistionRequest;
use App\Http\Requests\UpdateRmRequistionRequest;
use Illuminate\Support\Number;
use Illuminate\Support\Facades\DB;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Http\Response;
use Spatie\Browsershot\Browsershot;
use Carbon\Carbon;

class RmRequistionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:create-rmrequsition|edit-rmrequsition|delete-rmrequsition|view-rmrequsition|issue-rmrequsition', ['only' => ['index']]);
        $this->middleware('permission:create-rmrequsition', ['only' => ['create','store']]);
        $this->middleware('permission:edit-rmrequsition', ['only' => ['edit','update']]);
        $this->middleware('permission:issue-rmrequsition', ['only' => ['show']]);
        $this->middleware('permission:delete-rmrequsition', ['only' => ['destroy']]);
        $this->middleware('permission:view-rmrequsition', ['only' => ['index']]);
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $rmrequistionDatas=RmRequistion::with('partmaster','rm_master','machine_master','group_master','rc_master','request_user','approved_user')->orderBy('status','ASC')->orderBy('id','ASC')->get();
        // dd($rmrequistionDatas);
        return view('rm_requistion.index',compact('rmrequistionDatas'));
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
        // $current_process_id=28;
        // if ($current_process_id==28) {
        //     $rc="RM";
        //     $current_operation_id=28;
        // }
		// $current_rcno=$rc.$current_year;
        // $count1=RouteMaster::where('process_id',$current_operation_id)->where('rc_id','LIKE','%'.$current_rcno.'%')->orderBy('rc_id', 'DESC')->get()->count();
        // if ($count1 > 0) {
        //     $rc_data=RouteMaster::where('process_id',$current_operation_id)->where('rc_id','LIKE','%'.$current_rcno.'%')->orderBy('rc_id', 'DESC')->first();
        //     $rcnumber=$rc_data['rc_id']??NULL;
        //     $old_rcnumber=str_replace("RM","",$rcnumber);
        //     $old_rcnumber_data=str_pad($old_rcnumber+1,9,0,STR_PAD_LEFT);
        //     $new_rcnumber='RM'.$old_rcnumber_data;

        // }else{
        //     $str='000001';
        //     $new_rcnumber=$current_rcno.$str;
        // }
        $partDatas=ChildProductMaster::where('status','=',1)->where('item_type','=',1)->groupBy('child_part_no')->get();
        return view('rm_requistion.create2',compact('partDatas','current_date'));
        // return view('rm_requistion.create',compact('partDatas','current_date','new_rcnumber'));
    }

    public function partRmRequistionFetchData(Request $request){
        // dd($request->all());
        $part_id=$request->part_id;
        // dd($part_id);

        $BomMaster=BomMaster::with('rm_master')->where('child_part_id','=',$part_id)->where('status','=',1)->first();
        if ($BomMaster!='') {
            $rm_msg=true;
            $rm_id='<option value="'.$BomMaster->rm_master->id.'">'.$BomMaster->rm_master->name.'</option>';
            $rm=$BomMaster->rm_master->id;
            $avl_kg=GrnQuality::with('grn_data','heat_no_data')->WhereHas('grn_data', function ($q) use ($rm) {
                $q->where('rm_id', '=', $rm);
            })->where('status','=',1)->where('rm_req_status','=',1)->select('*',DB::Raw('((approved_qty)-(issue_qty)) as avl_kg'))->havingRaw('avl_kg >?', [0])->orderBy('id','ASC')->sum('avl_kg');
            // dd($avl_kg);
            $bom=$BomMaster->input_usage;
            // dd($bom);
            $avl_qty=round($avl_kg/$bom);
            if ($avl_qty>0) {
                $avl_msg=true;
                $ProductProcessMaster=ProductProcessMaster::with('machine_master','group_master')->where('part_id','=',$part_id)->where('status','=',1)->where('process_master_id','=',3)->first();
                if ($ProductProcessMaster!='') {
                    $process_msg=true;
                    $machine=$ProductProcessMaster->machine_master->id;
                    // dd($machine);
                    $machineCheck=RmRequistionGrnDetails::with('machine_master','partmaster','rm_master')->where('machine_id','=',$machine)->where('rc_status','=',1)->get()->count();
                    // dd($machineCheck);
                    if ($machineCheck>0) {
                        $machine_msg=false;
                        $returnrm_msg=false;
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
                        $html.='<svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Danger:"><use xlink:href="#exclamation-triangle-fill"/></svg><div>Sorry This Machine Have Already Stock.So You Cannot Buy The New RM In Stores..</div></div>';
                        $group_id='';
                        $machine_id='';
                    }else{
                        $machine_msg=true;
                        $returnrmCheck=RmRequistionGrnDetails::with('machine_master','partmaster','rm_master','rc_master')->where('machine_id','=',$machine)->where('return_status','=',1)->first();
                        if ($returnrmCheck!='') {
                            $issue_rc_id=$returnrmCheck->rc_master->rc_id;
                            $returnrm_msg=false;
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
                            $html.='<svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Danger:"><use xlink:href="#exclamation-triangle-fill"/></svg><div>Sorry Return RM Is Already Pending And Pending Route Card No Is '.$issue_rc_id.'...</div></div>';
                            $group_id='';
                            $machine_id='';
                        }else{
                            $returnrm_msg=true;
                            $html="";
                            $group_id='<option value="'.$ProductProcessMaster->group_master->id.'">'.$ProductProcessMaster->group_master->name.'</option>';
                            $machine_id='<option value="'.$ProductProcessMaster->machine_master->id.'">'.$ProductProcessMaster->machine_master->machine_name.'</option>';
                        }
                    }
                } else {
                    $process_msg=false;
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
                    $html.='<svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Danger:"><use xlink:href="#exclamation-triangle-fill"/></svg><div>Sorry This Part Number Is Not Assigned Any Process Or Machine Or Group/Foreman..</div></div>';
                    $machine_msg=false;
                    $returnrm_msg=false;
                }
            } else {
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
                $html.='<svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Danger:"><use xlink:href="#exclamation-triangle-fill"/></svg><div>Sorry RM Stock Is Not Available...</div></div>';
                $process_msg=false;
                $machine_msg=false;
                $returnrm_msg=false;
                $rm_id='';
                $group_id='';
                $machine_id='';
            }

        } else {
            $rm_msg=false;
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
            $html.='<svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Danger:"><use xlink:href="#exclamation-triangle-fill"/></svg><div>Sorry This Part Number Is Not Assigned Any Raw Material..</div></div>';
            $avl_msg=false;
            $process_msg=false;
            $machine_msg=false;
            $returnrm_msg=false;
            $rm_id='';
            $group_id='';
            $machine_id='';
            $avl_qty=0;
            $avl_kg=0;
            $bom=0;
        }
        // dd($group_id);
        return response()->json(['rm_msg'=>$rm_msg,'avl_msg'=>$avl_msg,'process_msg'=>$process_msg,'machine_msg'=>$machine_msg,'returnrm_msg'=>$returnrm_msg,'rm_id'=>$rm_id,'group_id'=>$group_id,'machine_id'=>$machine_id,'avl_qty'=>$avl_qty,'avl_kg'=>$avl_kg,'bom'=>$bom,'html'=>$html]);
    }

    public function rmRequistionCheckData(Request $request){
        // dd($request->all());
        $req_type_id=$request->req_type_id;
        $req_kg=$request->req_kg;
        $part_id=$request->part_id;
        $rm_id=$request->rm_id;
        $group_id=$request->group_id;
        $machine_id=$request->machine_id;
        if ($req_type_id==1) {
            $rc_count=TransDataD11::where('part_id','=',$part_id)->where('process_id','=',3)->where('rc_status','!=',0)->count();
            if ($rc_count>2) {
                $rc_msg=false;
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
                $html.='<svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Danger:"><use xlink:href="#exclamation-triangle-fill"/></svg><div>This Part Number Have Already 2 Coil Available CNC Production..So Plan For Another Part Number..</div></div>';
                return response()->json(['html'=>$html,'rc_msg'=>$rc_msg,'req_type_id'=>$req_type_id]);

            } else {
                # code...
                $rc_msg=true;
                $rm_check=GRNInwardRegister::where('rm_id','=',$rm_id)->sum('avl_qty');
                $request_rm=RmRequistion::where('rm_id','=',$rm_id)->where('status','=',0)->where('req_type_id','=',1)->sum('req_kg');
                $new_rm_avl=$rm_check-$request_rm;
                if ($rm_check>0) {
                    $rm_msg=true;
                    if ($new_rm_avl<$req_kg) {
                        $rm_avl_msg=false;
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
                        $html.='<svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Danger:"><use xlink:href="#exclamation-triangle-fill"/></svg><div>Sorry You Requested More Than RM Available Stock..</div></div>';
                    }else{
                        $rm_avl_msg=true;
                        $html='';
                    }
                } else {
                    $rm_msg=false;
                    $rm_avl_msg=true;
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
                    $html.='<svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Danger:"><use xlink:href="#exclamation-triangle-fill"/></svg><div>Sorry This RM is not available stores..</div></div>';
                }
                return response()->json(['html'=>$html,'rc_msg'=>$rc_msg,'rm_msg'=>$rm_msg,'rm_avl_msg'=>$rm_avl_msg,'req_type_id'=>$req_type_id]);

            }

        }else{
            return response()->json(['req_type_id'=>$req_type_id]);
        }
    }


    public function rmRequistionFetchData(Request $request){
        // dd($request->all());
        $req_type_id=$request->req_type_id;
        $req_kg=$request->req_kg;
        $part_id=$request->part_id;
        $rm_id=$request->rm_id;
        $group_id=$request->group_id;
        $machine_id=$request->machine_id;
        $bom=$request->bom;
        $rmcheckDatas=GrnQuality::with('grn_data','heat_no_data')->WhereHas('grn_data', function ($q) use ($rm_id) {
                            $q->where('rm_id', '=', $rm_id);
                        })->where('status','=',1)->where('rm_req_status','=',1)->select('*',DB::Raw('((approved_qty)-(issue_qty)) as avl_kg'))->havingRaw('avl_kg >?', [0])->orderBy('id','ASC')->first();
        if ($rmcheckDatas!='') {
            $count=1;
            $html = view('rm_requistion.add',compact('rmcheckDatas','bom','count'))->render();
        } else {
            $count=0;
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
            $html.='<svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Danger:"><use xlink:href="#exclamation-triangle-fill"/></svg><div>Sorry This RM is not available stores..</div></div>';
        }
        return response()->json(['count'=>$count,'html'=>$html]);
    }

    public function rmRequistionFetchData2(Request $request){
        // dd($request->all());
        $req_type_id=$request->req_type_id;
        $req_kg=$request->req_kg;
        $part_id=$request->part_id;
        $rm_id=$request->rm_id;
        $group_id=$request->group_id;
        $machine_id=$request->machine_id;
        $bom=$request->bom;
        $req_id=$request->req_id;
        $rmcheckDatas=GrnQuality::with('grn_data','heat_no_data')->WhereHas('grn_data', function ($q) use ($rm_id) {
                    $q->where('rm_id', '=', $rm_id);
                })->where('status','=',1)->where('rm_req_status','=',1)->select('*',DB::Raw('((approved_qty)-(issue_qty)) as avl_kg'))->havingRaw('avl_kg >?', [0])->orderBy('id','ASC')->first();
        // dd($rmcheckDatas);
        $rmRequistionGrnDetails=RmRequistionGrnDetails::with('req_master','grn_master','heatno_master','grnqc_master')->where('req_rc_id','=',$req_id)->get();
        // dd($rmRequistionGrnDetails);
        if ($rmcheckDatas!='') {
            $count=1;
            $html = view('rm_requistion.add2',compact('rmcheckDatas','bom','count','rmRequistionGrnDetails'))->render();
        } else {
            $count=0;
            $html='';
        }
        return response()->json(['html'=>$html]);
    }
    public function rmRequistionStore(Request $request){
        $req_id=$request->req_id;
        if ($req_id!='') {
        // dd($request->all());
            // dd('not ok');
            $req_rc_id=$req_id;
            $rmRequistionDatas=RmRequistion::find($req_id);
            $old_issue_kg=$rmRequistionDatas->issue_kg;
            $old_issue_qty=$rmRequistionDatas->issue_qty;
            if (($request->req_qty)<(($old_issue_qty)+($request->issue_avl_qty))) {
                $rmRequistionDatas->to_be_return_kg=((($old_issue_kg)+($request->issue_avl_kg))-($request->req_kg));
                $rmRequistionDatas->to_be_return_qty=((($old_issue_qty)+($request->issue_avl_qty))-($request->req_qty));
            }
            $rmRequistionDatas->issue_kg=(($old_issue_kg+$request->issue_avl_kg));
            $rmRequistionDatas->issue_qty=(($old_issue_qty)+($request->issue_avl_qty));
            $rmRequistionDatas->remarks=$request->remarks??NULL;
            $rmRequistionDatas->updated_by = auth()->user()->id;
            $rmRequistionDatas->updated_at = Carbon::now();
            $rmRequistionDatas->update();

            $rmRequistionGrnDetails=new RmRequistionGrnDetails;
            $rmRequistionGrnDetails->open_date=$request->open_date;
            $rmRequistionGrnDetails->req_rc_id=$req_rc_id;
            $rmRequistionGrnDetails->part_id=$request->part_id;
            $rmRequistionGrnDetails->rm_id=$request->rm_id;
            $rmRequistionGrnDetails->machine_id=$request->machine_id;
            $rmRequistionGrnDetails->group_id=$request->group_id;
            $rmRequistionGrnDetails->req_type_id=$request->req_type_id;
            $rmRequistionGrnDetails->grn_id=$request->grn_id;
            $rmRequistionGrnDetails->grn_qc_id=$request->grn_qc_id;
            $rmRequistionGrnDetails->heat_id=$request->heat_id;
            $rmRequistionGrnDetails->req_kg=$request->req_kg;
            $rmRequistionGrnDetails->req_qty=$request->req_qty;
            if (($request->req_qty)<(($old_issue_qty)+($request->issue_avl_qty))) {
                $rmRequistionGrnDetails->to_be_return_kg=((($old_issue_kg)+($request->issue_avl_kg))-($request->req_kg));
                $rmRequistionGrnDetails->to_be_return_qty=((($old_issue_qty)+($request->issue_avl_qty))-($request->req_qty));
            }
            $rmRequistionGrnDetails->issue_kg=$request->issue_avl_kg;
            $rmRequistionGrnDetails->issue_qty=$request->issue_avl_qty;
            // machine wise route card lock
            // $rmRequistionGrnDetails->rc_status=0;
            $rmRequistionGrnDetails->remarks=$request->remarks??NULL;
            $rmRequistionGrnDetails->request_by=auth()->user()->id;
            $rmRequistionGrnDetails->prepared_by=auth()->user()->id;
            $rmRequistionGrnDetails->save();

            $grnQuality=GrnQuality::find($request->grn_qc_id);
            // dd($grnQuality);
            $grnQuality->rm_req_status=0;
            $grnQuality->updated_by = auth()->user()->id;
            $grnQuality->updated_at = Carbon::now();
            $grnQuality->update();

            if (($request->req_qty)<=(($old_issue_qty)+($request->issue_avl_qty))) {
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
                $html.='<svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Success:"><use xlink:href="#check-circle-fill"/></svg><div>The RM Requistion Is Successfully Completed..!</div></div>';
            } else {
                $success=false;
                $html='';
            }
            return response()->json(['req_rc_id'=>$req_rc_id,'success'=>$success,'html'=>$html]);
        } else {
            // dd('ok');
            date_default_timezone_set('Asia/Kolkata');
            $current_date=date('Y-m-d');
            $current_year=date('Y');
            $current_process_id=28;
            if ($current_process_id==28) {
                $rc="RM";
                $current_operation_id=28;
            }
            $current_rcno=$rc.$current_year;
            $count1=RouteMaster::where('process_id',$current_operation_id)->where('rc_id','LIKE','%'.$current_rcno.'%')->orderBy('rc_id', 'DESC')->get()->count();
            if ($count1 > 0) {
                $rc_data=RouteMaster::where('process_id',$current_operation_id)->where('rc_id','LIKE','%'.$current_rcno.'%')->orderBy('rc_id', 'DESC')->first();
                $rcnumber=$rc_data['rc_id']??NULL;
                $old_rcnumber=str_replace("RM","",$rcnumber);
                $old_rcnumber_data=str_pad($old_rcnumber+1,9,0,STR_PAD_LEFT);
                $new_rcnumber='RM'.$old_rcnumber_data;

            }else{
                $str='000001';
                $new_rcnumber=$current_rcno.$str;
            }
            $previous_process_id=28;
            $rcMaster=new RouteMaster;
            $rcMaster->create_date=$current_date;
            $rcMaster->process_id=$previous_process_id;
            $rcMaster->rc_id=$new_rcnumber;
            $rcMaster->prepared_by=auth()->user()->id;
            $rcMaster->save();

            $rcMasterData=RouteMaster::where('rc_id','=',$new_rcnumber)->where('process_id','=',$previous_process_id)->first();
            $rc_id=$rcMasterData->id;

            $rmRequistionDatas=new RmRequistion;
            $rmRequistionDatas->rc_id=$rc_id;
            $rmRequistionDatas->open_date=$request->open_date;
            $rmRequistionDatas->part_id=$request->part_id;
            $rmRequistionDatas->rm_id=$request->rm_id;
            $rmRequistionDatas->machine_id=$request->machine_id;
            $rmRequistionDatas->group_id=$request->group_id;
            $rmRequistionDatas->req_type_id=$request->req_type_id;
            $rmRequistionDatas->req_kg=$request->req_kg;
            $rmRequistionDatas->req_qty=$request->req_qty;
            if ($request->req_kg<$request->issue_avl_kg) {
                $rmRequistionDatas->to_be_return_kg=(($request->issue_avl_kg)-($request->req_kg));
                $rmRequistionDatas->to_be_return_qty=(($request->issue_avl_qty)-($request->req_qty));
            }
            $rmRequistionDatas->issue_kg=$request->issue_avl_kg;
            $rmRequistionDatas->issue_qty=$request->issue_avl_qty;
            $rmRequistionDatas->remarks=$request->remarks??NULL;
            $rmRequistionDatas->request_by=auth()->user()->id;
            $rmRequistionDatas->prepared_by=auth()->user()->id;
            $rmRequistionDatas->save();

            $req_rc_id=$rmRequistionDatas->id;

            $rmRequistionGrnDetails=new RmRequistionGrnDetails;
            $rmRequistionGrnDetails->open_date=$request->open_date;
            $rmRequistionGrnDetails->req_rc_id=$req_rc_id;
            $rmRequistionGrnDetails->part_id=$request->part_id;
            $rmRequistionGrnDetails->rm_id=$request->rm_id;
            $rmRequistionGrnDetails->machine_id=$request->machine_id;
            $rmRequistionGrnDetails->group_id=$request->group_id;
            $rmRequistionGrnDetails->req_type_id=$request->req_type_id;
            $rmRequistionGrnDetails->grn_id=$request->grn_id;
            $rmRequistionGrnDetails->grn_qc_id=$request->grn_qc_id;
            $rmRequistionGrnDetails->heat_id=$request->heat_id;
            $rmRequistionGrnDetails->req_kg=$request->req_kg;
            $rmRequistionGrnDetails->req_qty=$request->req_qty;
            if ($request->req_kg<$request->issue_avl_kg) {
                $rmRequistionGrnDetails->to_be_return_kg=(($request->issue_avl_kg)-($request->req_kg));
                $rmRequistionGrnDetails->to_be_return_qty=(($request->issue_avl_qty)-($request->req_qty));
            }
            $rmRequistionGrnDetails->issue_kg=$request->issue_avl_kg;
            $rmRequistionGrnDetails->issue_qty=$request->issue_avl_qty;
            // machine wise route card lock
            $rmRequistionGrnDetails->rc_status=0;
            $rmRequistionGrnDetails->remarks=$request->remarks??NULL;
            $rmRequistionGrnDetails->request_by=auth()->user()->id;
            $rmRequistionGrnDetails->prepared_by=auth()->user()->id;
            $rmRequistionGrnDetails->save();

            $grnQuality=GrnQuality::find($request->grn_qc_id);
            $grnQuality->rm_req_status=0;
            $grnQuality->updated_by = auth()->user()->id;
            $grnQuality->updated_at = Carbon::now();
            $grnQuality->update();

            if (($request->req_kg)<=($request->issue_avl_kg)) {
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
            $html.='<svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Success:"><use xlink:href="#check-circle-fill"/></svg><div>The RM Requistion Is Successfully Completed..!</div></div>';
            }else{
                $success=false;
                $html='';
            }
        return response()->json(['req_rc_id'=>$req_rc_id,'success'=>$success,'html'=>$html]);

        }

    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRmRequistionRequest $request)
    {
        //
        // dd($request->all());
        DB::beginTransaction();
        try {
            $previous_process_id=28;
            $rcMaster=new RouteMaster;
            $rcMaster->create_date=$request->open_date;
            $rcMaster->process_id=$previous_process_id;
            $rcMaster->rc_id=$request->rc_no;
            $rcMaster->prepared_by=auth()->user()->id;
            $rcMaster->save();

            $rcMasterData=RouteMaster::where('rc_id','=',$request->rc_no)->where('process_id','=',$previous_process_id)->first();
            $rc_id=$rcMasterData->id;

            $RmRequistion=new RmRequistion;
            $RmRequistion->rc_id=$rc_id;
            $RmRequistion->open_date=$request->open_date;
            $RmRequistion->part_id=$request->part_id;
            $RmRequistion->rm_id=$request->rm_id;
            $RmRequistion->machine_id=$request->machine_id;
            $RmRequistion->group_id=$request->group_id;
            $RmRequistion->req_type_id=$request->req_type_id;
            $RmRequistion->req_kg=$request->req_qty;
            $RmRequistion->remarks=$request->remarks??NULL;
            $RmRequistion->request_by=auth()->user()->id;
            $RmRequistion->prepared_by=auth()->user()->id;
            $RmRequistion->save();

            DB::commit();

            return redirect()->route('rmrequistion.index')->withSuccess('Rawmaterial Requistion Is Submitted Successfully!');
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollback();
            return redirect()->back()->withErrors($th->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        //
        $rmrequistion=RmRequistion::with('partmaster','rm_master','machine_master','group_master','rc_master','request_user','approved_user')->find($id);
        // dd($rmrequistion);
        $rmRequistionGrnDetails=RmRequistionGrnDetails::with('req_master','grn_master','heatno_master','grnqc_master')->where('req_rc_id','=',$id)->where('status','=',0)->get();
        $pickedrmRequistionGrnDetails=RmRequistionGrnDetails::with('req_master','grn_master','heatno_master','grnqc_master','rc_master')->where('req_rc_id','=',$id)->where('status','=',1)->get();

        // dd($rmRequistionGrnDetails);
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
        // if ($qrCodes_count>0) {
        //     # code...
        //     // dd('QR Code Entry...');
        //     return view('rm_issuance.qr_create',compact('new_rcnumber','current_date'));
        // } else {
        //     return view('rm_issuance.create',compact('grnDatas','new_rcnumber','current_date'));
        // }
            return view('rm_requistion.rm_issuance',compact('new_rcnumber','current_date','rmrequistion','rmRequistionGrnDetails','pickedrmRequistionGrnDetails'));

    }


    public function grnQcRmIssueFetchData(Request $request){
        // dd($request->all());
        $grn_qc_id=$request->rm_qc_id;
        $part_id=$request->part_id;
        $rm_id=$request->rm_id;
        $group_id=$request->group_id;
        $grn_qc_datas=GrnQuality::with(['grn_data','heat_no_data','rack_data'])->where('id','=',$grn_qc_id)->first();
        // dd($grn_qc_datas);
        $grnnumber_id=$grn_qc_datas->grnnumber_id;
        if (($grn_qc_datas->grn_data->poproduct->supplier_products->material->id)==($rm_id)) {
            $rm_msg=true;
            if ($grn_qc_datas->status==1) {
                $gqc_msg=true;
                $count=1;
                $grn_no='<option value="'.$grn_qc_datas->grn_data->id.'">'.$grn_qc_datas->grn_data->rcmaster->rc_id.'</option>';
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
                        $grnQcDatas=GrnQuality::find($grn_qc_id);
                        $avl_qty=(($grnQcDatas->approved_qty)-($grnQcDatas->issue_qty)-($grnQcDatas->return_qty));
                        if ($avl_qty>0) {
                            $avl_msg=true;
                            $html='';
                            $bom=BomMaster::with('childpart_master')->where('rm_id','=',$rm_id)->where('child_part_id','=',$part_id)->where('status','=',1)->sum('input_usage');
                        }else{
                            $avl_msg=false;
                            $avl_qty=0;
                            $bom=0;
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
                            $html.='<svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Danger:"><use xlink:href="#exclamation-triangle-fill"/></svg><div>Sorry This GRN Number Is Not RM Available...</div></div>';
                        }
                    } else {
                        $coil_msg=false;
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
                        $html.='<svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Danger:"><use xlink:href="#exclamation-triangle-fill"/></svg><div>Please Follow The FIFO ..Try GRN Number Is '.$fifoGrnCard.' And The Coil No Is '.$grn_coil_no .' And The Heat Number Is '.$grn_coil_heat_no.' And Lot Number Is '.$grn_coil_lot_no.'</div></div>';
                        $grn_coil_no=$grn_qc_datas->heat_no_data->coil_no;
                        $grn_coil_heat_no=$grn_qc_datas->heat_no_data->heatnumber;
                        $grn_coil_lot_no=$grn_qc_datas->heat_no_data->lot_no;
                        $avl_msg=true;

                    $avl_qty=0;
                    $bom=0;
                    }
                }else {
                    $success = false;
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
                    $html.='<svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Danger:"><use xlink:href="#exclamation-triangle-fill"/></svg><div>Please Follow The FIFO ..Try GRN Number Is '.$fifoGrnCard.'</div></div>';
                    $coil_msg=true;
                    $grn_coil_no=$grn_qc_datas->heat_no_data->coil_no;
                    $grn_coil_heat_no=$grn_qc_datas->heat_no_data->heatnumber;
                    $grn_coil_lot_no=$grn_qc_datas->heat_no_data->lot_no;
                    $avl_qty=0;
                    $avl_msg=true;

                    $bom=0;

                }
            return response()->json(['count'=>$count,'rm_msg'=>$rm_msg,'gqc_msg'=>$gqc_msg,'coil_msg'=>$coil_msg,'grn_no'=>$grn_no,'heat_id'=>$heat_id,'heat_no'=>$heat_no,'coil_no'=>$coil_no,'lot_no'=>$lot_no,'tc_no'=>$tc_no,'uom'=>$uom,'fifoGrn'=>$fifoGrnCard,'success'=>$success,'avl_qty'=>$avl_qty,'html'=>$html,'avl_msg'=>$avl_msg,'coil_msg'=>$coil_msg,'grn_coil_no'=>$grn_coil_no,'grn_coil_heat_no'=>$grn_coil_heat_no,'grn_coil_lot_no'=>$grn_coil_lot_no,'bom'=>$bom]);

            }else{
                $gqc_msg=false;
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
                $html.='<svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Danger:"><use xlink:href="#exclamation-triangle-fill"/></svg><div>Sorry This GRN Number is Not Approval From Incoming Quanlity And Try Another GRN Number..</div></div>';
                $count=0;
                return response()->json(['count'=>$count,'rm_msg'=>$rm_msg,'gqc_msg'=>$gqc_msg,'html'=>$html]);
            }
        }else{
            $count=0;
            $rm_msg=false;
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
            $html.='<svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Danger:"><use xlink:href="#exclamation-triangle-fill"/></svg><div>Sorry This GRN No is not matched with required Raw Material..</div></div>';
        }
        return response()->json(['count'=>$count,'rm_msg'=>$rm_msg,'html'=>$html]);

    }

    public function grnQcRmIssueFetchData2(Request $request) {
        // dd($request->all());
        $grn_qc_id=$request->rm_qc_id;
        $part_id=$request->part_id;
        $rm_id=$request->rm_id;
        $requsition_id=$request->requsition_id;
        $req_grn_id=$request->req_grn_id;
        $group_id=$request->group_id;
        $rmRequistionGrnDetails=RmRequistionGrnDetails::where('req_rc_id','=',$requsition_id)->where('grn_qc_id','=',$grn_qc_id)->where('status','=',0)->first();
        if ($rmRequistionGrnDetails!='') {
            $rm_req_msg=true;
            $count=RmRequistionGrnDetails::where('req_rc_id','=',$requsition_id)->where('status','=',0)->orderBy('id','ASC')->get()->count();
            $fifoCheck=RmRequistionGrnDetails::with('req_master','grn_master','heatno_master','grnqc_master')->where('req_rc_id','=',$requsition_id)->where('status','=',0)->orderBy('id','ASC')->first();
            $bomMaster=BomMaster::where('child_part_id','=',$part_id)->where('rm_id','=',$rm_id)->where('status','=',1)->first();
            $avl_kg=(($fifoCheck->grnqc_master->approved_qty)-($fifoCheck->grnqc_master->issue_qty));
            // dd($avl_kg);
            if ($bomMaster!='') {
                $bom=$bomMaster->input_usage;
                // dd($bom);
                $avl_qty=(round($avl_kg/$bom));
            }else{
                $bom=0;
                $avl_qty=0;
            }
            // dd($fifoCheck);
            $fifoGrnQc=$fifoCheck->grn_qc_id;
            $grn_no='<option value="'.$fifoCheck->grn_master->id.'">'.$fifoCheck->grn_master->rcmaster->rc_id.'</option>';
            // dd($grn_no);
            $uom='<option value="'.$fifoCheck->grn_master->poproduct->uom_datas->id.'">'.$fifoCheck->grn_master->poproduct->uom_datas->name.'</option>';
            $heat_id=$fifoCheck->heatno_master->id;
            $heat_no='<option value="'.$fifoCheck->heatno_master->heatnumber.'">'.$fifoCheck->heatno_master->heatnumber.'</option>';
            $coil_no='<option value="'.$fifoCheck->heatno_master->coil_no.'">'.$fifoCheck->heatno_master->coil_no.'</option>';
            $tc_no=$fifoCheck->heatno_master->tc_no;
            $lot_no=$fifoCheck->heatno_master->lot_no;
            $req_kg=$fifoCheck->req_kg;
            $req_qty=$fifoCheck->req_qty;
            $to_be_return_kg=$fifoCheck->to_be_return_kg;
            $to_be_return_qty=$fifoCheck->to_be_return_qty;
            $issue_kg=$fifoCheck->issue_kg;
            $issue_qty=$fifoCheck->issue_qty;
            if ($fifoGrnQc==$grn_qc_id) {
                $fifo_msg=true;
                $html='';
            } else {
                $fifo_msg=false;
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
                $html.='<svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Danger:"><use xlink:href="#exclamation-triangle-fill"/></svg><div>Sorry Please Follow The FIFO And FIFO GRN No Is '.$fifoCheck->grn_master->rcmaster->rc_id.' FIFO Coil No Is '.$fifoCheck->heatno_master->coil_no.' FIFO Lot No Is '.$fifoCheck->heatno_master->lot_no.' FIFO Heat No Is '.$fifoCheck->heatno_master->heatnumber.'..</div></div>';
            }

        } else {
            $rm_req_msg=false;
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
            $html.='<svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Danger:"><use xlink:href="#exclamation-triangle-fill"/></svg><div>Sorry This Raw Material Is Not For This Requistion Number And Please Check It..</div></div>';
            $fifo_msg=false;
            $grn_no='<option value="0">No Record Found</option>';
            $uom='<option value="0">No Record Found</option>';
            $heat_id=0;
            $heat_no='<option value="0">No Record Found</option>';
            $coil_no='<option value="0">No Record Found</option>';
            $tc_no=0;
            $lot_no=0;
            $count=0;
            $avl_kg=0;
            $avl_qty=0;
            $req_kg=0;
            $req_qty=0;
            $to_be_return_kg=0;
            $to_be_return_qty=0;
            $issue_kg=0;
            $issue_qty=0;
            $bom=0;
        }

        return response()->json(['count'=>$count,'rm_req_msg'=>$rm_req_msg,'fifo_msg'=>$fifo_msg,'grn_no'=>$grn_no,'heat_id'=>$heat_id,'heat_no'=>$heat_no,'coil_no'=>$coil_no,'lot_no'=>$lot_no,'tc_no'=>$tc_no,'uom'=>$uom,'bom'=>$bom,'avl_kg'=>$avl_kg,'avl_qty'=>$avl_qty,'html'=>$html,'req_kg'=>$req_kg,'req_qty'=>$req_qty,'to_be_return_kg'=>$to_be_return_kg,'to_be_return_qty'=>$to_be_return_qty,'issue_kg'=>$issue_kg,'issue_qty'=>$issue_qty]);

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(RmRequistion $rmrequistion)
    {
        //
        // dd($rmrequistion->id);
        $rmRequistionGrnDetails=RmRequistionGrnDetails::where('req_rc_id','=',$rmrequistion->id)->where('rc_status','=',1)->get();
        return view('rm_requistion.edit',compact('rmrequistion','rmRequistionGrnDetails'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRmRequistionRequest $request, RmRequistion $rmrequistion)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(RmRequistion $rmrequistion)
    {
        //
    }
}
