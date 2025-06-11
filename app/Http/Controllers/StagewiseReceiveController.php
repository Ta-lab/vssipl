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
use App\Models\DcTransactionDetails;
use App\Models\PtsphospatingMaster;
use App\Models\CoverStrickerDetails;
use App\Models\PtsTransactionSummary;
use App\Models\PtsTransactionDetail;
use App\Models\CustomerProductMaster;
use App\Models\PackingMaster;
use App\Models\PackingStrickerDetails;
use App\Models\ProductMaster;
use App\Models\RouteMaster;
use App\Models\FirewallInspectionDetails;
use App\Models\HeatNumber;
use App\Models\TransDataD11;
use App\Models\TransDataD12;
use App\Models\TransDataD13;
use App\Models\BomMaster;
use App\Models\DcMaster;
use App\Models\DcPrint;
use App\Models\ChildProductMaster;
use App\Models\FinalQcInspection;
use App\Models\ItemProcesmaster;
use App\Models\StageQrCodeLock;
use Illuminate\Support\Number;
use Illuminate\Support\Facades\DB;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Http\Response;
use Spatie\Browsershot\Browsershot;
use Carbon\Carbon;
use App\Exports\PtsstockExport as ExportsPtsstockExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Controllers\RawmaterialExport;

class StagewiseReceiveController extends Controller
{

    public function ptsReworkRevertList(){
        // $reworkrevertdatas=PtsTransactionDetail::where('return_issue_qty','!=',0)->where('stricker_id','!=',0)->get();
        $reworkrevertdatas=PtsTransactionSummary::where('cle_return_qty','!=',0)->get();
        // dd($reworkrevertdatas);
        return view('stagewise-receive.pts_reworkrevert_list',compact('reworkrevertdatas'));
    }

    public function ptsReworkRevertCreate(){
        date_default_timezone_set('Asia/Kolkata');
        $current_date=date('Y-m-d');
        $rework_rcdatas=PtsTransactionSummary::where('cle_rework_qty','!=',0)->get();
        // dd($rework_rcdatas);
        $activity='SF Receive';
        $stage='Store';
        $qrCodes_count=StageQrCodeLock::where('stage','=',$stage)->where('activity','=',$activity)->where('status','=',1)->count();
        return view('stagewise-receive.pts_reworkrevert_create',compact('rework_rcdatas','current_date','qrCodes_count'));
    }

    public function ptsRcreworkrevertfetchdata(Request $request){
        // dd($request->all());
        $part_id=$request->part_id;
        $rc_count=PtsTransactionSummary::where('cle_rework_qty','!=',0)->where('part_id','=',$part_id)->count();
        if ($rc_count>0) {
            $rc_msg=true;
                $rc_datas=PtsTransactionSummary::where('cle_rework_qty','!=',0)->where('part_id','=',$part_id)->first();
                $html='<option value="">Select The Route Card No</option>';
                $html.='<option value="'.$rc_datas->rcmaster->id.'">'.$rc_datas->rcmaster->rc_id.'</option>';
        }else{
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
            $html.='<svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Danger:"><use xlink:href="#exclamation-triangle-fill"/></svg><div>Sorry Rework Route Card No is Not Availble...</div></div>';

        }
        return response()->json(['rc_msg'=>$rc_msg,'html'=>$html]);
    }

    public function ptsReworkrevertpartreceivefetchdata(Request $request){
        // dd($request->all());
        $rc_id=$request->rc_no;
        $part_id=$request->part_id;
        $rework_avl_qty=PtsTransactionSummary::where('cle_rework_qty','!=',0)->where('part_id','=',$part_id)->where('rc_id','=',$rc_id)->sum('cle_rework_qty');
        // dd($rework_qty);
        $process_id='<option value="27">FG For Checking</option>';
        if ($rework_avl_qty>0) {
            $qty_msg=true;
            $html='';
        } else {
            $qty_msg=false;
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
            $html.='<svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Danger:"><use xlink:href="#exclamation-triangle-fill"/></svg><div>Sorry Rework Route Card No is Not Availble...</div></div>';
        }
        
        // $pts_datas=PackingStrickerDetails::where('part_id','=',$part_id)->where('rc_id','=',$rc_id)->where('rework_packed_qty','!=',0)->get();
        return response()->json(['qty_msg'=>$qty_msg,'html'=>$html,'process'=>$process_id,'rework_avl_qty'=>$rework_avl_qty]);
    }

    public function ptsreworkstickerfetchdata(Request $request){
        // dd($request->all());
        $rc_id=$request->rc_no;
        $part_id=$request->part_id;
        $rework_revert_qty=$request->rework_revert_qty;
        $avl_qty=$request->avl_qty;
        $pts_datas=PackingStrickerDetails::where('part_id','=',$part_id)->where('rc_id','=',$rc_id)->where('rework_packed_qty','!=',0)->get();
        // dd($pts_datas);
        $html = view('stagewise-receive.revert_item',compact('avl_qty','rework_revert_qty','pts_datas'))->render();
        return response()->json(['html'=>$html]);
    }

    public function ptsreworkrevertStoredata(Request $request){
        // dd($request->all());
        $stricker_ids=$request->stricker_id;
        $part_id=$request->part_id;
        $rc_id=$request->rc_no;
        $cover_total_qty=$request->cover_total_qty;
        $rework_revert_qty=$request->rework_revert_qty;
        $filled=$request->filled;
        foreach ($stricker_ids as $key => $stricker_id) {
            if ($filled[$key]!=0) {
                $PackingStrickerDetails=PackingStrickerDetails::find($stricker_id);
                $PackingStrickerDetails->total_receive_qty=$cover_total_qty[$key];
                $old_total_receive_qty=$PackingStrickerDetails->total_receive_qty;
                $total_receive_qty=$old_total_receive_qty-$cover_total_qty[$key];
                $old_rework_packed_qty=$PackingStrickerDetails->rework_packed_qty;
                $rework_qty=$old_rework_packed_qty-$cover_total_qty[$key];
                $PackingStrickerDetails->total_receive_qty=$old_total_receive_qty-$total_receive_qty;
                $PackingStrickerDetails->rework_packed_qty=$old_rework_packed_qty-$rework_qty;
                $PackingStrickerDetails->status=0;
                $PackingStrickerDetails->inspect_by=0;
                $PackingStrickerDetails->print_status=1;
                $PackingStrickerDetails->updated_by = auth()->user()->id;
                $PackingStrickerDetails->updated_at = Carbon::now();
                $PackingStrickerDetails->update();
            }
        }
        $PtsTransactionSummaries=PtsTransactionSummary::where('part_id','=',$part_id)->where('rc_id','=',$rc_id)->first();
        $old_cle_rework_qty=$PtsTransactionSummaries->cle_rework_qty;
        $old_cle_return_qty=$PtsTransactionSummaries->cle_return_qty;
        $PtsTransactionSummaries->cle_return_qty=$old_cle_return_qty+$rework_revert_qty;
        $PtsTransactionSummaries->cle_rework_qty=$old_cle_rework_qty-$rework_revert_qty;
        $PtsTransactionSummaries->updated_by = auth()->user()->id;
        $PtsTransactionSummaries->updated_at = Carbon::now();
        $PtsTransactionSummaries->update();
        return redirect()->route('ptsreworkrevertlist')->withSuccess('Part Rework Reverted Successfully!');
    }
    //Semi Finished Receive Entry  Start
    public function sfReceiveList(){
        $d12Datas=DB::table('trans_data_d12_s as a')
        ->join('item_procesmasters AS b', 'a.process_id', '=', 'b.id')
        ->join('child_product_masters AS c', 'a.part_id', '=', 'c.id')
        ->join('users AS d', 'a.prepared_by', '=', 'd.id')
        ->join('route_masters AS e', 'a.rc_id', '=', 'e.id')
        ->join('route_masters AS f', 'a.previous_rc_id', '=', 'f.id')
        ->select('a.id','b.operation','b.id as process_id','a.open_date','e.rc_id as rc_no','f.rc_id as previous_rc_no','a.receive_qty','c.child_part_no as part_no','a.prepared_by','a.created_at','d.name as user_name')
        ->whereIn('a.process_id', [21])
        ->whereRaw('a.rc_id=a.previous_rc_id')
        ->orderBy('a.id', 'DESC')
        ->get();
        // dd($d12Datas);
        return view('stagewise-receive.sf_view',compact('d12Datas'));
    }
    public function sfReceiveCreateForm(){
        date_default_timezone_set('Asia/Kolkata');
        $current_date=date('Y-m-d');
        $d11Datas=TransDataD11::where('process_id','=',17)->where('status','=',1)->get();
        $activity='SF Receive';
        $stage='Store';
        $qrCodes_count=StageQrCodeLock::where('stage','=',$stage)->where('activity','=',$activity)->where('status','=',1)->count();
        return view('stagewise-receive.sf_create',compact('d11Datas','current_date','qrCodes_count'));
    }

    public function sfPartReceiveQrCode($id){
        // dd($id);
        $t12Datas=TransDataD12::with(['partmaster','previous_rcmaster','receiver'])->find($id);
        $rc_id=$t12Datas->previous_rc_id;
        $receive_date=$t12Datas->created_at;
        $receive_qty=$t12Datas->receive_qty;
        $receive_by=$t12Datas->receiver->name;
        $part_no=$t12Datas->partmaster->child_part_no;
        $rc_no=$t12Datas->previous_rcmaster->rc_id;
        $t11Datas=TransDataD11::with(['nextprocessmaster','currentprocessmaster'])->where('rc_id','=',$rc_id)->first();
        $next_process=$t11Datas->nextprocessmaster->operation;
        if ($t11Datas->currentprocessmaster->operation=='Store') {
            $current_process='CNC Coiling';
        } else {
            # code...
        }

        $html = view('stagewise-receive.sfreceive_qrcodeprint',compact('rc_no','receive_date','receive_qty','receive_by','current_process','next_process','rc_id','part_no'))->render();
        $width=75;$height=125;
        $pdf=Browsershot::html($html)->setIncludePath(config('services.browsershot.include_path'))->paperSize($width, $height)->landscape()->pdf();
        return new Response($pdf,200,[
            'Content-Type'=>'application/pdf',
            'Content-Disposition'=>'inline;filename="sfreceiveqrcode.pdf"'
        ]);
    }

    public function sfPartFetchEntry(Request $request){
        // $request->all();
        // dd($request->all());
        $rc_no=$request->rc_no;
        $d11Datas=TransDataD11::with('rcmaster')->where('process_id','=',17)->where('rc_id','=',$rc_no)->where('status','=',1)->first();
        // dd($d11Datas);
        $part_id=$d11Datas->part_id;
        $current_process_id=$d11Datas->process_id;
        $current_product_process_id=$d11Datas->product_process_id;
        $previous_process_issue_qty=$d11Datas->process_issue_qty;
        $receive_qty=$d11Datas->receive_qty;
        $reject_qty=$d11Datas->reject_qty;
        $rework_qty=$d11Datas->rework_qty;
        $rc_data='<option value="'.$d11Datas->rcmaster->id.'">'.$d11Datas->rcmaster->rc_id.'</option>';
        $qr_rc_id=$d11Datas->rcmaster->id;

        $bomDatas=BomMaster::where('child_part_id','=',$part_id)->where('status','=',1)->sum('input_usage');
        $process_issue_qty=floor(($previous_process_issue_qty/$bomDatas));

        $partCheck=ChildProductMaster::find($part_id);
        $part_no=$partCheck->child_part_no;

        $fifoCheck=TransDataD11::with('rcmaster')->where('process_id','=',17)->where('part_id','=',$part_id)->where('status','=',1)->orderBy('id', 'ASC')->first();
        $fifoRcNo=$fifoCheck->rc_id;
        $fifoRcCard=$fifoCheck->rcmaster->rc_id;

        if($rc_no==$fifoRcNo){
            $success = true;
            $avl_qty=(($process_issue_qty)-($receive_qty)-($reject_qty)-($rework_qty));
            $part='<option value="'.$part_id.'">'.$part_no.'</option>';
            $fifoRcNo=$fifoCheck->rc_no;
            $bom=$bomDatas;
            $avl_kg=$avl_qty*$bom;
            $process_id=$current_process_id;
            $product_process_id=$current_product_process_id;
            $process_check1=ProductProcessMaster::whereIn('process_master_id',[21])->where('part_id','=',$part_id)->where('status','=',1)->orderBy('id', 'ASC')->count();
            // dd($process_check1);
            if ($process_check1==0) {
                $process=false;
                $next_process_id=0;
                $next_productprocess_id='<option value=""></option>';
            }else{
                $process=true;
                $process_checkData=DB::table('product_process_masters as a')
                ->join('item_procesmasters AS b', 'a.process_master_id', '=', 'b.id')
                ->select('b.operation','b.id as next_process_id','a.id as next_productprocess_id')
                ->whereIn('process_master_id', [21])
                ->where('part_id','=',$part_id)
                ->orderBy('a.id', 'DESC')
                ->first();
                // dd($process_checkData);
                $next_process_id=$process_checkData->next_process_id;
                $next_productprocess_id='<option value="'.$process_checkData->next_productprocess_id.'">'.$process_checkData->operation.'</option>';
            }
            $process_check=ProductProcessMaster::where('process_master_id','=',$process_id)->where('id','=',$current_product_process_id)->where('part_id','=',$part_id)->where('status','=',1)->orderBy('id', 'ASC')->count();
            if($process_check==0){
                $message=false;
            }else{
                $message=true;
            }

        }else{
            $success = false;
            $fifoRcNo=$fifoCheck->rc_id;
            $avl_qty=0;
            $avl_kg=0;
            $part='<option value=""></option>';
            $bom=0;
            $process_id=0;
            $product_process_id=0;
            $message=false;
            $process=false;
            $next_process_id=0;
            $next_productprocess_id='<option value=""></option>';

        }

        // dd($success);

        return response()->json(['success'=>$success,'fifoRcNo'=>$fifoRcNo,'avl_qty'=>$avl_qty,'part'=>$part,'bom'=>$bom,'avl_kg'=>$avl_kg,'message'=>$message,'process_id'=>$process_id,'product_process_id'=>$product_process_id,'next_process_id'=>$next_process_id,'next_productprocess_id'=>$next_productprocess_id,'process'=>$process,'fifoRcCard'=>$fifoRcCard,'rc_data'=>$rc_data,'qr_rc_id'=>$qr_rc_id]);

        // $avl_qty=(($process_issue_qty)-($receive_qty)-($reject_qty)-($rework_qty));
        // dd($d11Datas->part_id);
    }



    public function sfReceiveEntry(Request $request){
        // dd($request->all());
        DB::beginTransaction();
        try {
            $qrcodes_count=$request->qrcodes_count;
            if ($qrcodes_count==0) {
                $rc_card_id=$request->rc_no;
            } else {
                $rc_card_id=$request->qr_rc_id;
            }
            $receive_qty=$request->receive_qty;
            $avl_qty=$request->avl_qty;
            if ($receive_qty<=$avl_qty) {
                $d11Datas=TransDataD11::where('process_id','=',$request->previous_process_id)->where('product_process_id','=',$request->previous_product_process_id)->where('rc_id','=',$rc_card_id)->first();
            if($request->rc_close=="yes"){
                // dd($request->rc_date);
                $d11Datas->close_date=$request->rc_date;
                $d11Datas->status=0;
            }
            $total_receive_qty=($d11Datas->receive_qty+$request->receive_qty);
            $d11Datas->receive_qty=$total_receive_qty;
            $d11Datas->updated_by = auth()->user()->id;
            $d11Datas->updated_at = Carbon::now();
            $d11Datas->update();
            // dd($d11Datas->receive_qty);

            $d12Datas=new TransDataD12;
            $d12Datas->open_date=$request->rc_date;
            $d12Datas->rc_id=$rc_card_id;
            $d12Datas->previous_rc_id=$rc_card_id;
            $d12Datas->part_id=$request->part_id;
            $d12Datas->process_id=$request->next_process_id;
            $d12Datas->product_process_id=$request->next_productprocess_id;
            $d12Datas->receive_qty=$request->receive_qty;
            $d12Datas->prepared_by = auth()->user()->id;
            $d12Datas->save();
            DB::commit();
            return redirect()->route('sfreceive')->withSuccess('Part Received is Successfully!');
            }else {
                return redirect()->route('sfreceive')->withMessage('Please Check Your Available Quantity & You Enter More Available Quantity!');
            }


        } catch (\Throwable $th) {
            //throw $th;
            DB::rollback();
            dd($th->getMessage());
            return redirect()->back()->withErrors($th->getMessage());
        }
    }

    //Semi Finished Receive Entry  End


    // Out Store Receive Entry Start

    public function osReceiveList(){
    //     $status = 1;
    //    dd(TransDataD12::whereHas('previous_rcmaster',function(Builder $query ) use ($status){
    //     $query->where(['status'=>$status]);
    //    })->whereColumn('created_at','updated_at')
    //    ->get()) ;

        $d12Datas=DB::table('trans_data_d12_s as a')
        ->join('item_procesmasters AS b', 'a.process_id', '=', 'b.id')
        ->join('child_product_masters AS c', 'a.part_id', '=', 'c.id')
        ->join('users AS d', 'a.prepared_by', '=', 'd.id')
        ->join('route_masters AS e', 'a.rc_id', '=', 'e.id')
        ->join('route_masters AS f', 'a.previous_rc_id', '=', 'f.id')
        ->select('b.operation','b.id as process_id','a.open_date','e.rc_id as rc_no','f.rc_id as previous_rc_no','a.receive_qty','c.child_part_no as part_no','a.prepared_by','a.created_at','d.name as user_name')
        ->whereIn('a.process_id', [16,17,21])
        ->whereRaw('a.rc_id=a.previous_rc_id')
        ->orderBy('a.id', 'DESC')
        ->get();
        // dd($d12Datas);
        return view('stagewise-receive.os_view',compact('d12Datas'));
    }

    public function osReceiveCreateForm(){
        date_default_timezone_set('Asia/Kolkata');
        $current_date=date('Y-m-d');
        $d11Datas=TransDataD11::with('rcmaster')->whereIn('next_process_id',[16,17])->where('status','=',1)->get();
        // dd($d11Datas);
        $activity='Material Receive';
        $stage='Out Store';
        $qrCodes_count=StageQrCodeLock::where('stage','=',$stage)->where('activity','=',$activity)->where('status','=',1)->count();
        return view('stagewise-receive.os_create',compact('d11Datas','current_date','qrCodes_count'));
    }

    public function osPartFetchEntry(Request $request){
        // $request->all();
        // dd($request->all());
        $rc_no=$request->rc_no;
        $d11Datas=TransDataD11::with('rcmaster')->whereIn('next_process_id',[16,17])->where('rc_id','=',$rc_no)->where('rc_status','=',1)->first();
        // dd($d11Datas);
        $part_id=$d11Datas->part_id;
        $current_process_id=$d11Datas->process_id;
        $current_product_process_id=$d11Datas->product_process_id;
        $next_operation_id=$d11Datas->next_process_id;
        $next_operation_process_id=$d11Datas->next_product_process_id;
        $previous_process_issue_qty=$d11Datas->process_issue_qty;
        $receive_qty=$d11Datas->receive_qty;
        $reject_qty=$d11Datas->reject_qty;
        $rework_qty=$d11Datas->rework_qty;
        $rc_data='<option value="'.$d11Datas->rcmaster->id.'">'.$d11Datas->rcmaster->rc_id.'</option>';
        $qr_rc_id=$d11Datas->rcmaster->id;

        $bomDatas=BomMaster::where('child_part_id','=',$part_id)->sum('manual_usage');
        if ($current_process_id==3) {
            $process_issue_qty=floor(($previous_process_issue_qty/$bomDatas));
        }else{
            $process_issue_qty=$d11Datas->process_issue_qty;
        }

        $partCheck=ChildProductMaster::find($part_id);
        $part_no=$partCheck->child_part_no;
        $fifoCheck=TransDataD11::with('rcmaster')->where('process_id','=',$current_process_id)->where('part_id','=',$part_id)->where('rc_status','=',1)->orderBy('id', 'ASC')->first();
        $fifoRcNo=$fifoCheck->rc_id;
        $fifoRcCard=$fifoCheck->rcmaster->rc_id;
        // dd($fifoRcNo);
        if($rc_no==$fifoRcNo){
            $success = true;
            $fqcqty=FinalQcInspection::where('rc_id','=',$fifoRcNo)->where('status','=',0)->sum('offer_qty');
            $avl_qty=(($process_issue_qty)-($receive_qty)-($reject_qty)-($rework_qty)-($fqcqty));
            $part='<option value="'.$part_id.'">'.$part_no.'</option>';
            $fifoRcNo=$fifoCheck->rc_no;
            $bom=$bomDatas;
            if ($current_process_id==3) {
                $avl_kg=$avl_qty*$bom;
            }else{
                $avl_kg=$avl_qty;
            }
        // dd($avl_qty);

            $process_id=$current_process_id;
            $product_process_id=$current_product_process_id;
            $process_check1=ProductProcessMaster::whereIn('process_master_id',[16,17])->where('part_id','=',$part_id)->where('status','=',1)->orderBy('id', 'ASC')->count();
            // dd($process_check1);
            if ($process_check1==0) {
                $process=false;
                $next_process_id=0;
                $next_productprocess_id='<option value=""></option>';
            }else{
                $process=true;
                $process_checkData=DB::table('product_process_masters as a')
                ->join('item_procesmasters AS b', 'a.process_master_id', '=', 'b.id')
                ->select('b.operation','b.id as next_process_id','a.id as next_productprocess_id')
                ->where('process_master_id','=' ,$next_operation_id)
                ->where('part_id','=',$part_id)
                ->orderBy('a.id', 'DESC')
                ->first();
                // dd($process_checkData);
                $next_process_id=$process_checkData->next_process_id;
                $next_productprocess_id='<option value="'.$process_checkData->next_productprocess_id.'">'.$process_checkData->operation.'</option>';
            }
            $process_check=ProductProcessMaster::where('process_master_id','=',$process_id)->where('id','=',$current_product_process_id)->where('part_id','=',$part_id)->where('status','=',1)->orderBy('id', 'ASC')->count();
            if($process_check==0){
                $message=false;
            }else{
                $message=true;
            }

        }else{
            $success = false;
            $fifoRcNo=$fifoCheck->rc_id;
            $avl_qty=0;
            $avl_kg=0;
            $part='<option value=""></option>';
            $bom=0;
            $process_id=0;
            $product_process_id=0;
            $message=false;
            $process=false;
            $next_process_id=0;
            $next_productprocess_id='<option value=""></option>';
        }

        // dd($success);


        return response()->json(['success'=>$success,'fifoRcNo'=>$fifoRcNo,'avl_qty'=>$avl_qty,'part'=>$part,'bom'=>$bom,'avl_kg'=>$avl_kg,'message'=>$message,'process_id'=>$process_id,'product_process_id'=>$product_process_id,'next_process_id'=>$next_process_id,'next_productprocess_id'=>$next_productprocess_id,'process'=>$process,'fifoRcCard'=>$fifoRcCard,'rc_data'=>$rc_data,'qr_rc_id'=>$qr_rc_id]);

        // $avl_qty=(($process_issue_qty)-($receive_qty)-($reject_qty)-($rework_qty));
        // dd($d11Datas->part_id);
    }

    public function osReceiveEntry(Request $request){
        // dd($request->all());
        DB::beginTransaction();
        try {
            $qrcodes_count=$request->qrcodes_count;
            if ($qrcodes_count==0) {
                $rc_card_id=$request->rc_no;
            } else {
                $rc_card_id=$request->qr_rc_id;
            }
            $receive_qty=$request->receive_qty;
            $avl_qty=$request->avl_qty;
            $count=1;
            if ($receive_qty<=$avl_qty) {
                if ($count==0) {
                    $d11Datas=TransDataD11::where('process_id','=',$request->previous_process_id)->where('product_process_id','=',$request->previous_product_process_id)->where('rc_id','=',$rc_card_id)->first();
                    if($request->rc_close=="yes"){
                        // dd($request->rc_date);
                        $d11Datas->close_date=$request->rc_date;
                        $d11Datas->status=0;
                        $d11Datas->rc_status=0;
                    }
                    $total_receive_qty=($d11Datas->receive_qty+$request->receive_qty);
                    $d11Datas->receive_qty=$total_receive_qty;
                    $d11Datas->updated_by = auth()->user()->id;
                    $d11Datas->updated_at = Carbon::now();
                    $d11Datas->update();

                    $d12Datas=new TransDataD12;
                    $d12Datas->open_date=$request->rc_date;
                    $d12Datas->rc_id=$rc_card_id;
                    $d12Datas->previous_rc_id=$rc_card_id;
                    $d12Datas->part_id=$request->part_id;
                    $d12Datas->process_id=$request->next_process_id;
                    $d12Datas->product_process_id=$request->next_productprocess_id;
                    $d12Datas->receive_qty=$request->receive_qty;
                    $d12Datas->prepared_by = auth()->user()->id;
                    $d12Datas->save();

                    DB::commit();
                    return redirect()->route('osreceive')->withSuccess('Part Received is Successfully!');
                } else {
                    $fqcInspectionData=new FinalQcInspection;
                    $fqcInspectionData->offer_date=$request->rc_date;
                    $fqcInspectionData->rc_id=$rc_card_id;
                    $fqcInspectionData->previous_rc_id=$rc_card_id;
                    $fqcInspectionData->part_id=$request->part_id;
                    $fqcInspectionData->process_id=$request->previous_process_id;
                    $fqcInspectionData->product_process_id=$request->previous_product_process_id;
                    $fqcInspectionData->next_process_id=$request->next_process_id;
                    $fqcInspectionData->next_product_process_id=$request->next_productprocess_id;
                    $fqcInspectionData->offer_qty=$request->receive_qty;
                    if($request->rc_close=="yes"){
                    $fqcInspectionData->rc_status=0;
                    }else{
                    $fqcInspectionData->rc_status=1;
                    }
                    $fqcInspectionData->prepared_by = auth()->user()->id;
                    $fqcInspectionData->save();

                    $d11Datas=TransDataD11::where('process_id','=',$request->previous_process_id)->where('product_process_id','=',$request->previous_product_process_id)->where('rc_id','=',$rc_card_id)->first();
                    if($request->rc_close=="yes"){
                        $d11Datas->close_date=$request->rc_date;
                        $d11Datas->status=0;
                        $d11Datas->rc_status=0;
                    }
                    $d11Datas->updated_by = auth()->user()->id;
                    $d11Datas->updated_at = Carbon::now();
                    $d11Datas->update();

                    DB::commit();
                    return redirect()->route('osfqc')->withSuccess('Part Received is Successfully And Waiting For Final Quality Inspection!');
                }

            }else {
                return redirect()->route('osreceive')->withMessage('Please Check Your Available Quantity & You Enter More Available Quantity!');
            }
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollback();
            dd($th->getMessage());
            return redirect()->back()->withErrors($th->getMessage());
        }
    }
    public function fgReceiveList(){
        $d12Datas=DB::table('trans_data_d12_s as a')
        ->join('item_procesmasters AS b', 'a.process_id', '=', 'b.id')
        ->join('child_product_masters AS c', 'a.part_id', '=', 'c.id')
        ->join('users AS d', 'a.prepared_by', '=', 'd.id')
        ->join('route_masters AS e', 'a.rc_id', '=', 'e.id')
        ->join('route_masters AS f', 'a.previous_rc_id', '=', 'f.id')
        ->select('b.operation','b.id as process_id','a.open_date','e.rc_id as rc_no','f.rc_id as previous_rc_no','a.receive_qty','c.child_part_no as part_no','a.prepared_by','a.created_at','d.name as user_name')
        ->where('a.process_id','=',22)
        ->whereRaw('a.rc_id=a.previous_rc_id')
        ->orderBy('a.id', 'DESC')
        ->get();
        // dd($d12Datas);
        return view('stagewise-receive.fg_view',compact('d12Datas'));
    }

    public function fgReceiveQRList(){
        date_default_timezone_set('Asia/Kolkata');
        $current_date=date('Y-m-d');
        $d12Datas=DB::table('trans_data_d12_s as a')
        ->join('item_procesmasters AS b', 'a.process_id', '=', 'b.id')
        ->join('child_product_masters AS c', 'a.part_id', '=', 'c.id')
        ->join('users AS d', 'a.prepared_by', '=', 'd.id')
        ->join('route_masters AS e', 'a.rc_id', '=', 'e.id')
        ->join('route_masters AS f', 'a.previous_rc_id', '=', 'f.id')
        ->select('b.operation','b.id as process_id','a.open_date','e.rc_id as rc_no','f.rc_id as previous_rc_no','a.receive_qty','c.child_part_no as part_no','a.prepared_by','a.created_at','d.name as user_name')
        ->where('a.process_id','=',22)
        ->where('a.open_date','=',$current_date)
        ->whereRaw('a.rc_id=a.previous_rc_id')
        ->orderBy('a.id', 'DESC')
        ->get();
        return view('stagewise-receive.fg_qr_view',compact('d12Datas'));

    }
    // old fg receice method manual scanning
    public function fgQRPartFetchEntry1(Request $request){
        // dd($request->all());
        $stricker_id=$request->stricker_id;
        // dd($stricker_id);
        $PackingStrickerDetails=PackingStrickerDetails::with('rcmaster','partmaster','covermaster')->find($stricker_id);
        // dd($PackingStrickerDetails);
        $pts_dc_issue_qty=$PackingStrickerDetails->pts_dc_issue_qty;
        $u1_dc_reject_qty=$PackingStrickerDetails->u1_dc_reject_qty;
        $u1_dc_receive_qty=$PackingStrickerDetails->u1_dc_receive_qty;
        $avl_qty=(($pts_dc_issue_qty)-($u1_dc_reject_qty)-($u1_dc_receive_qty));
        // dd($avl_qty);
        // dd($pts_dc_issue_qty);
        if (($avl_qty>0) && ($pts_dc_issue_qty>0)) {
            $success=true;
        } else {
            $success=false;
        }
        if ($pts_dc_issue_qty!=0) {
            $message=true;
            $current_rc_datas=CoverStrickerDetails::where('part_id','=',$PackingStrickerDetails->partmaster->id)->where('prc_id','=',$PackingStrickerDetails->rcmaster->id)->where('stricker_id','=',$stricker_id)->first();
            // dd($current_rc_datas);
            $current_rc_id=$current_rc_datas->rc_id;
        } else {
            $message=false;
            $current_rc_id=0;
        }

        $rc_data='<option value="'.$PackingStrickerDetails->rcmaster->id.'" selected>'.$PackingStrickerDetails->rcmaster->rc_id.'</option>';
        $part_data='<option value="'.$PackingStrickerDetails->partmaster->id.'" selected>'.$PackingStrickerDetails->partmaster->child_part_no.'</option>';
        $operation='<option value="22" selected>FG For Invoicing</option>';
        $cover_order_id=$PackingStrickerDetails->cover_order_id;
        $cover_qty=$PackingStrickerDetails->cover_qty;
        $cover_rc=$PackingStrickerDetails->rcmaster->rc_id.'-'.$cover_order_id;

        $bom=BomMaster::where('child_part_id','=',$PackingStrickerDetails->partmaster->id)->where('status','=',1)->sum('output_usage');
        // dd($bom);
        return response()->json(['success'=>$success,'message'=>$message,'stricker_data'=>$stricker_id,'avl_qty'=>$avl_qty,'pts_dc_issue_qty'=>$pts_dc_issue_qty,'u1_dc_receive_qty'=>$u1_dc_receive_qty,'u1_dc_reject_qty'=>$u1_dc_reject_qty,'rc_data'=>$rc_data,'part_data'=>$part_data,'cover_order_id'=>$cover_order_id,'cover_qty'=>$cover_qty,'bom'=>$bom,'operation'=>$operation,'cover_rc'=>$cover_rc,'current_rc_id'=>$current_rc_id]);
    }
    public function fgQRPartFetchEntry(Request $request){
        // dd($request->all());
        $stricker_id=$request->stricker_id;
        date_default_timezone_set('Asia/Kolkata');
        $rc_date=date('Y-m-d');
        // dd($stricker_id);
        $PackingStrickerDetails=PackingStrickerDetails::with('rcmaster','partmaster','covermaster')->find($stricker_id);

        // dd($PackingStrickerDetails);
        $new_part_id=$PackingStrickerDetails->part_id;
        $new_rc_id=$PackingStrickerDetails->rc_id;
        $oldPtsTransactionSummary=PtsTransactionSummary::where('part_id','=',$new_part_id)->where('rc_id','=',$new_rc_id)->first();
        $old_c_process_id=$oldPtsTransactionSummary->process_id;
        $old_n_process_id=$oldPtsTransactionSummary->next_process_id;
        $cover_issue_qty=$oldPtsTransactionSummary->cover_issue_qty;
        $pts_dc_issue_qty=$PackingStrickerDetails->pts_dc_issue_qty;
        $u1_dc_reject_qty=$PackingStrickerDetails->u1_dc_reject_qty;
        $u1_dc_receive_qty=$PackingStrickerDetails->u1_dc_receive_qty;
        if ($old_n_process_id==22) {
            $avl_qty=(($cover_issue_qty)-($u1_dc_reject_qty)-($u1_dc_receive_qty));
            $dc_qty=$avl_qty;
        } else {
            $avl_qty=(($pts_dc_issue_qty)-($u1_dc_reject_qty)-($u1_dc_receive_qty));
            $dc_qty=$pts_dc_issue_qty;
        }
        // $avl_qty=(($pts_dc_issue_qty)-($u1_dc_reject_qty)-($u1_dc_receive_qty));

        // dd($dc_qty);
        // dd($pts_dc_issue_qty);
        if ($dc_qty!=0) {
            $message=true;
            $current_rc_datas=CoverStrickerDetails::where('part_id','=',$PackingStrickerDetails->partmaster->id)->where('prc_id','=',$PackingStrickerDetails->rcmaster->id)->where('stricker_id','=',$stricker_id)->first();
            // dd($current_rc_datas);
            $current_rc_id=$current_rc_datas->rc_id;
            if ($avl_qty>0) {
                // dd('ok');
                $success_msg=true;
                $rc_data='<option value="'.$PackingStrickerDetails->rcmaster->id.'" selected>'.$PackingStrickerDetails->rcmaster->rc_id.'</option>';
                $part_data='<option value="'.$PackingStrickerDetails->partmaster->id.'" selected>'.$PackingStrickerDetails->partmaster->child_part_no.'</option>';
                $operation='<option value="22" selected>FG For Invoicing</option>';
                $cover_order_id=$PackingStrickerDetails->cover_order_id;
                $cover_qty=$PackingStrickerDetails->cover_qty;
                $cover_rc=$PackingStrickerDetails->rcmaster->rc_id.'-'.$cover_order_id;
                $bom=BomMaster::where('child_part_id','=',$PackingStrickerDetails->partmaster->id)->where('status','=',1)->sum('output_usage');
                $prc_no=$PackingStrickerDetails->rcmaster->id;
                $part_id=$PackingStrickerDetails->partmaster->id;
                $next_process_id=22;
                $receive_qty=$avl_qty;


                $transD11Datas=TransDataD11::where('rc_id','=',$current_rc_id)->first();
                // dd($transD11Datas);
                $next_product_process_id=$transD11Datas->next_product_process_id;
                $old_receive_qty=$transD11Datas->receive_qty;
                $transD11Datas->receive_qty=(($old_receive_qty)+($receive_qty));
                $transD11Datas->updated_by = auth()->user()->id;
                $transD11Datas->updated_at = Carbon::now();
                $transD11Datas->update();

                $transD12Datas=new TransDataD12;
                $transD12Datas->open_date=$rc_date;
                $transD12Datas->process_id=$next_process_id;
                $transD12Datas->product_process_id=$next_product_process_id;
                $transD12Datas->part_id=$part_id;
                $transD12Datas->rc_id=$current_rc_id;
                $transD12Datas->previous_rc_id=$current_rc_id;
                $transD12Datas->receive_qty=$receive_qty;
                $transD12Datas->prepared_by = auth()->user()->id;
                $transD12Datas->save();

                $coverStrickerDetails=CoverStrickerDetails::where('part_id','=',$part_id)->where('rc_id','=',$current_rc_id)->where('prc_id','=',$prc_no)->where('stricker_id','=',$stricker_id)->first();
                // dd($coverStrickerDetails);
                $total_receive_qty=$coverStrickerDetails->total_receive_qty;
                $coverStrickerDetails->total_receive_qty=(($total_receive_qty)+($receive_qty));
                $coverStrickerDetails->fg_receive_date = Carbon::now();
                $coverStrickerDetails->fg_receive_by = auth()->user()->id;
                $coverStrickerDetails->updated_by = auth()->user()->id;
                $coverStrickerDetails->updated_at = Carbon::now();
                $coverStrickerDetails->update();

                $PackingStrickerDetails=PackingStrickerDetails::find($stricker_id);
                // dd($PackingStrickerDetails);
                $old_u1_dc_receive_qty=$PackingStrickerDetails->u1_dc_receive_qty;
                $PackingStrickerDetails->u1_dc_receive_qty=(($old_u1_dc_receive_qty)+($receive_qty));
                $PackingStrickerDetails->updated_by = auth()->user()->id;
                $PackingStrickerDetails->updated_at = Carbon::now();
                $PackingStrickerDetails->update();

                $PtsTransactionSummary=PtsTransactionSummary::where('part_id','=',$part_id)->where('rc_id','=',$prc_no)->first();
                // dd($PtsTransactionSummary);
                $old_u1_dc_receive_qty=$PtsTransactionSummary->u1_dc_receive_qty;
                $PtsTransactionSummary->u1_dc_receive_qty=(($old_u1_dc_receive_qty)+($receive_qty));
                $PtsTransactionSummary->updated_by = auth()->user()->id;
                $PtsTransactionSummary->updated_at = Carbon::now();
                $PtsTransactionSummary->update();

                if ($old_c_process_id!=3) {
                    $DcTransactionDetails=DcTransactionDetails::where('rc_id','=',$prc_no)->first();
                    // dd($DcTransactionDetails);
                    $old_receive_qty=$DcTransactionDetails->receive_qty;
                    $old_total_receive_qty=$DcTransactionDetails->total_receive_qty;
                    $DcTransactionDetails->receive_qty=(($old_receive_qty)+($receive_qty));
                    $DcTransactionDetails->total_receive_qty=(($old_total_receive_qty)+($receive_qty));
                    $DcTransactionDetails->updated_by = auth()->user()->id;
                    $DcTransactionDetails->updated_at = Carbon::now();
                    $DcTransactionDetails->update();

                    $DcTransactionDetailsData=DcTransactionDetails::where('rc_id','=',$current_rc_id)->first();
                    // dd($DcTransactionDetailsData);
                    $old_receive_qty=$DcTransactionDetailsData->receive_qty;
                    $old_total_receive_qty=$DcTransactionDetailsData->total_receive_qty;
                    $DcTransactionDetailsData->receive_qty=(($old_receive_qty)+($receive_qty));
                    $DcTransactionDetailsData->total_receive_qty=(($old_total_receive_qty)+($receive_qty));
                    $DcTransactionDetailsData->updated_by = auth()->user()->id;
                    $DcTransactionDetailsData->updated_at = Carbon::now();
                    $DcTransactionDetailsData->update();
                }


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
                $html.='<svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Success:"><use xlink:href="#check-circle-fill"/></svg><div>This Cover Received Successfully..!</div></div>';
            } else {
                // dd('not ok');

                $success_msg=false;
                $cover_order_id=$PackingStrickerDetails->cover_order_id;
                $cover_rc=$PackingStrickerDetails->rcmaster->rc_id.'-'.$cover_order_id;
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
                $html.='<svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Danger:"><use xlink:href="#exclamation-triangle-fill"/></svg><div>This Cover No is '.$cover_rc.' Already Received by FG Team</div></div>';

                $rc_data='<option value="" selected>No RC</option>';
                $part_data='<option value="" selected>No Part Number</option>';
                $operation='<option value="" selected>No Part Number</option>';
                $cover_qty=0;
                $bom=0;
            }
        } else {
            $message=false;
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
            $html.='<svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Danger:"><use xlink:href="#exclamation-triangle-fill"/></svg><div>This Cover Has Already Received</div></div>';
            $current_rc_id=0;
            $rc_data='<option value="" selected>No RC</option>';
            $part_data='<option value="" selected>No Part Number</option>';
            $operation='<option value="" selected>No Part Number</option>';
            $cover_order_id=0;
            $cover_qty=0;
            $cover_rc='';
            $bom=0;
        }

        return response()->json(['html'=>$html]);

        // dd($bom);
        // return response()->json(['success_msg'=>$success_msg,'message'=>$message,'stricker_data'=>$stricker_id,'avl_qty'=>$avl_qty,'pts_dc_issue_qty'=>$pts_dc_issue_qty,'u1_dc_receive_qty'=>$u1_dc_receive_qty,'u1_dc_reject_qty'=>$u1_dc_reject_qty,'rc_data'=>$rc_data,'part_data'=>$part_data,'cover_order_id'=>$cover_order_id,'cover_qty'=>$cover_qty,'bom'=>$bom,'operation'=>$operation,'cover_rc'=>$cover_rc,'current_rc_id'=>$current_rc_id]);
    }
    public function qcQRRcRejFetchEntry(Request $request){
        // dd($request->all());
        $rc_id=$request->rc_no;
        $transDataD11Datas=TransDataD11::where('rc_status','=',1)->where('rc_id','=',$rc_id)->select('*',DB::raw('((process_issue_qty)-((receive_qty)+(reject_qty)+(rework_qty))) as avl_qty'))->first();
        if ($transDataD11Datas!='') {
            $rc_msg=true;
            $process_issue_kg=$transDataD11Datas->process_issue_qty;
            $receive_qty=$transDataD11Datas->receive_qty;
            $reject_qty=$transDataD11Datas->reject_qty;
            $rework_qty=$transDataD11Datas->rework_qty;
            if ($transDataD11Datas->process_id==3) {
                $bomData=BomMaster::where('child_part_id','=',$transDataD11Datas->partmaster->id)->where('status','=',1)->sum('input_usage');
                $process_issue_qty=round($process_issue_kg/$bomData);
            } else {
                $process_issue_qty=$process_issue_kg;
            }
            $avl_qty=(($process_issue_qty)-(($receive_qty)+($reject_qty)+($rework_qty)));
            if ($avl_qty>0) {
                $avl_msg=true;
                $rc_data='<option value="'.$transDataD11Datas->rcmaster->id.'" selected>'.$transDataD11Datas->rcmaster->rc_id.'</option>';
                $part_data='<option value="'.$transDataD11Datas->partmaster->id.'" selected>'.$transDataD11Datas->partmaster->child_part_no.'</option>';
                $process_id=$transDataD11Datas->process_id;
                $product_process_id=$transDataD11Datas->product_process_id;
                $next_process_id=$transDataD11Datas->next_process_id;
                $next_product_process_id=$transDataD11Datas->next_product_process_id;
                if ($process_id==3) {
                    $bomDatas=BomMaster::where('child_part_id','=',$transDataD11Datas->partmaster->id)->where('status','=',1)->sum('input_usage');
                }elseif ($process_id==6) {
                    $bomDatas=BomMaster::where('child_part_id','=',$transDataD11Datas->partmaster->id)->where('status','=',1)->sum('manual_usage');
                }elseif ($process_id==7) {
                    $bomDatas=BomMaster::where('child_part_id','=',$transDataD11Datas->partmaster->id)->where('status','=',1)->sum('manual_usage');
                }elseif ($process_id==8) {
                    $bomDatas=BomMaster::where('child_part_id','=',$transDataD11Datas->partmaster->id)->where('status','=',1)->sum('manual_usage');
                }elseif ($process_id==16) {
                    $bomDatas=BomMaster::where('child_part_id','=',$transDataD11Datas->partmaster->id)->where('status','=',1)->sum('output_usage');
                }elseif ($process_id==22) {
                    $bomDatas=BomMaster::where('child_part_id','=',$transDataD11Datas->partmaster->id)->where('status','=',1)->sum('output_usage');
                }else{
                    $bomDatas=BomMaster::where('child_part_id','=',$transDataD11Datas->partmaster->id)->where('status','=',1)->sum('finish_usage');
                }
                $id=29;
                $procesmasterDatas=ProductProcessMaster::with('processMaster')->where('process_master_id','=',$id)->where('part_id','=',$transDataD11Datas->partmaster->id)->where('status','=',1)->first();
                $process='<option value="'.$procesmasterDatas->processMaster->id.'" selected>'.$procesmasterDatas->processMaster->operation.'</option>';
                $product_process=$procesmasterDatas->id;
                // dd($bomDatas);
                $bom=$bomDatas;
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
                $html.='<svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Danger:"><use xlink:href="#exclamation-triangle-fill"/></svg><div>Sorry..This RC No Is Not Available Stock...</div></div>';
                $rc_data='<option value="0" selected>No Result Found</option>';
                $part_data='<option value="0" selected>No Result Found</option>';
                $bom=0;
                $operation_data='<option value="0" selected>No Result Found</option>';
                $process='<option value="0" selected>No Result Found</option>';
                $product_process=0;
                $process_id=0;
                $product_process_id=0;
            }
        }else{
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
            $html.='<svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Danger:"><use xlink:href="#exclamation-triangle-fill"/></svg><div>Sorry..This RC No Is Not Open...</div></div>';
            $avl_msg=false;
            $avl_qty=0;
            $rc_data='<option value="0" selected>No Result Found</option>';
            $part_data='<option value="0" selected>No Result Found</option>';
            $bom=0;
            $operation_data='<option value="0" selected>No Result Found</option>';
            $process='<option value="0" selected>No Result Found</option>';
            $product_process=0;
            $process_id=0;
            $product_process_id=0;
        }
        return response()->json(['rc_msg'=>$rc_msg,'avl_msg'=>$avl_msg,'avl_qty'=>$avl_qty,'rc_data'=>$rc_data,'part_data'=>$part_data,'bom'=>$bom,'rc_id'=>$rc_id,'html'=>$html,'process'=>$process,'product_process'=>$product_process,'process_id'=>$process_id,'product_process_id'=>$product_process_id]);

    }

    public function qcQRRcRejStore(Request $request){
        // dd($request->all());
        $TransDataD11Datas=TransDataD11::where('rc_id','=',$request->rc_no)->first();
        $old_reject_qty=$TransDataD11Datas->reject_qty;
        $TransDataD11Datas->reject_qty=(($old_reject_qty)+($request->receive_qty));
        if ($request->rc_close=="yes") {
            $TransDataD11Datas->rc_status==0;
        }else{
            $TransDataD11Datas->rc_status==1;
        }
        $TransDataD11Datas->updated_by = auth()->user()->id;
        $TransDataD11Datas->updated_at = Carbon::now();
        $TransDataD11Datas->update();

        $transD12Datas=new TransDataD12;
        $transD12Datas->open_date=$request->rc_date;
        $transD12Datas->process_id=$request->next_process_id;
        $transD12Datas->product_process_id=$request->next_productprocess_id;
        $transD12Datas->part_id=$request->part_id;
        $transD12Datas->rc_id=$request->rc_no;
        $transD12Datas->previous_rc_id=$request->rc_no;
        $transD12Datas->reject_qty=$request->receive_qty;
        $transD12Datas->reject_wt=$request->rej_wt;
        $transD12Datas->operation=$request->operation;
        $transD12Datas->area=$request->area;
        $transD12Datas->rejected_type_id=0;
        $transD12Datas->remarks=$request->reason;
        $transD12Datas->prepared_by = auth()->user()->id;
        $transD12Datas->save();

        return redirect()->route('qcrejectionlist')->withSuccess('Part Rejected is Successfully Completed!');
    }
    public function qcRejection(){
        date_default_timezone_set('Asia/Kolkata');
        $current_date=date('Y-m-d');
        $d11Datas=TransDataD11::where('process_issue_qty','!=',0)->select('*',DB::raw('((process_issue_qty)-((receive_qty)+(reject_qty)+(rework_qty))) as avl_qty'))->havingRaw('avl_qty >?', [0])->groupBy('rc_id')->get();
        $activity='Rejection';
        $stage='QC';
        $qrCodes_count=StageQrCodeLock::where('stage','=',$stage)->where('activity','=',$activity)->where('status','=',1)->count();
        // $transDataD11Datas=TransDataD11::where('process_issue_qty','!=',0)->select('*',DB::raw('((process_issue_qty)-((receive_qty)+(reject_qty)+(rework_qty))) as avl_qty'))->havingRaw('avl_qty >?', [0])->groupBy('rc_id')->get();
        // dd($transDataD11Datas);
        return view('stagewise-receive.qc_rej_create',compact('d11Datas','current_date','qrCodes_count'));
    }

    public function qcRejectionList(){
        $transDataD12Datas=TransDataD12::with('partmaster','currentprocessmaster','currentproductprocessmaster','current_rcmaster','receiver')->where('reject_qty','!=',0)->where('rejected_type_id','!=',1)->orderBy('open_date','DESC')->get();
        // dd($transDataD12Datas);
        return view('stagewise-receive.qc_rej_view',compact('transDataD12Datas'));
    }

    public function productionQRRcRejFetchEntry(Request $request){
        // dd($request->all());
        $rc_id=$request->rc_no;
        $transDataD11Datas=TransDataD11::where('rc_status','=',1)->where('rc_id','=',$rc_id)->select('*',DB::raw('((process_issue_qty)-((receive_qty)+(reject_qty)+(rework_qty))) as avl_qty'))->first();
        if ($transDataD11Datas!='') {
            $rc_msg=true;
            $process_issue_kg=$transDataD11Datas->process_issue_qty;
            $receive_qty=$transDataD11Datas->receive_qty;
            $reject_qty=$transDataD11Datas->reject_qty;
            $rework_qty=$transDataD11Datas->rework_qty;
            if ($transDataD11Datas->process_id==3) {
                $bomData=BomMaster::where('child_part_id','=',$transDataD11Datas->partmaster->id)->where('status','=',1)->sum('input_usage');
                $process_issue_qty=round($process_issue_kg/$bomData);
                $process_msg=true;
                $avl_qty=(($process_issue_qty)-(($receive_qty)+($reject_qty)+($rework_qty)));
                if ($avl_qty>0) {
                    $avl_msg=true;
                    $rc_data='<option value="'.$transDataD11Datas->rcmaster->id.'" selected>'.$transDataD11Datas->rcmaster->rc_id.'</option>';
                    $part_data='<option value="'.$transDataD11Datas->partmaster->id.'" selected>'.$transDataD11Datas->partmaster->child_part_no.'</option>';
                    $process_id=$transDataD11Datas->process_id;
                    $product_process_id=$transDataD11Datas->product_process_id;
                    $next_process_id=$transDataD11Datas->next_process_id;
                    $next_product_process_id=$transDataD11Datas->next_product_process_id;
                    if ($process_id==3) {
                        $bomDatas=BomMaster::where('child_part_id','=',$transDataD11Datas->partmaster->id)->where('status','=',1)->sum('input_usage');
                    }elseif ($process_id==6) {
                        $bomDatas=BomMaster::where('child_part_id','=',$transDataD11Datas->partmaster->id)->where('status','=',1)->sum('manual_usage');
                    }elseif ($process_id==7) {
                        $bomDatas=BomMaster::where('child_part_id','=',$transDataD11Datas->partmaster->id)->where('status','=',1)->sum('manual_usage');
                    }elseif ($process_id==8) {
                        $bomDatas=BomMaster::where('child_part_id','=',$transDataD11Datas->partmaster->id)->where('status','=',1)->sum('manual_usage');
                    }elseif ($process_id==16) {
                        $bomDatas=BomMaster::where('child_part_id','=',$transDataD11Datas->partmaster->id)->where('status','=',1)->sum('output_usage');
                    }elseif ($process_id==22) {
                        $bomDatas=BomMaster::where('child_part_id','=',$transDataD11Datas->partmaster->id)->where('status','=',1)->sum('output_usage');
                    }else{
                        $bomDatas=BomMaster::where('child_part_id','=',$transDataD11Datas->partmaster->id)->where('status','=',1)->sum('finish_usage');
                    }
                    $id=29;
                    $procesmasterDatas=ProductProcessMaster::with('processMaster')->where('process_master_id','=',$id)->where('part_id','=',$transDataD11Datas->partmaster->id)->where('status','=',1)->first();
                    $process='<option value="'.$procesmasterDatas->processMaster->id.'" selected>'.$procesmasterDatas->processMaster->operation.'</option>';
                    $product_process=$procesmasterDatas->id;
                    // dd($bomDatas);
                    $bom=$bomDatas;
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
                    $html.='<svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Danger:"><use xlink:href="#exclamation-triangle-fill"/></svg><div>Sorry..This RC No Is Not Available Stock...</div></div>';
                    $rc_data='<option value="0" selected>No Result Found</option>';
                    $part_data='<option value="0" selected>No Result Found</option>';
                    $bom=0;
                    $operation_data='<option value="0" selected>No Result Found</option>';
                    $process='<option value="0" selected>No Result Found</option>';
                    $product_process=0;
                    $process_id=0;
                    $product_process_id=0;
                }
            } else {
                $process_msg=false;
                $process_issue_qty=$process_issue_kg;
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
                $html.='<svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Danger:"><use xlink:href="#exclamation-triangle-fill"/></svg><div>Sorry..This RC No Is In CNC Stage...</div></div>';
                $rc_data='<option value="0" selected>No Result Found</option>';
                $part_data='<option value="0" selected>No Result Found</option>';
                $bom=0;
                $operation_data='<option value="0" selected>No Result Found</option>';
                $process='<option value="0" selected>No Result Found</option>';
                $product_process=0;
                $process_id=0;
                $product_process_id=0;
                $avl_qty=0;
            }

        }else{
            $rc_msg=false;
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
            $html.='<svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Danger:"><use xlink:href="#exclamation-triangle-fill"/></svg><div>Sorry..This RC No Is Not Open...</div></div>';
            $avl_msg=false;
            $avl_qty=0;
            $rc_data='<option value="0" selected>No Result Found</option>';
            $part_data='<option value="0" selected>No Result Found</option>';
            $bom=0;
            $operation_data='<option value="0" selected>No Result Found</option>';
            $process='<option value="0" selected>No Result Found</option>';
            $product_process=0;
            $process_id=0;
            $product_process_id=0;
        }
        return response()->json(['rc_msg'=>$rc_msg,'process_msg'=>$process_msg,'avl_msg'=>$avl_msg,'avl_qty'=>$avl_qty,'rc_data'=>$rc_data,'part_data'=>$part_data,'bom'=>$bom,'rc_id'=>$rc_id,'html'=>$html,'process'=>$process,'product_process'=>$product_process,'process_id'=>$process_id,'product_process_id'=>$product_process_id]);

    }

    public function productionQRRcRejStore(Request $request){
        // dd($request->all());
        $TransDataD11Datas=TransDataD11::where('rc_id','=',$request->rc_no)->first();
        $old_reject_qty=$TransDataD11Datas->reject_qty;
        $TransDataD11Datas->reject_qty=(($old_reject_qty)+($request->receive_qty));
        if ($request->rc_close=="yes") {
            $TransDataD11Datas->rc_status==0;
        }else{
            $TransDataD11Datas->rc_status==1;
        }
        $TransDataD11Datas->updated_by = auth()->user()->id;
        $TransDataD11Datas->updated_at = Carbon::now();
        $TransDataD11Datas->update();

        $transD12Datas=new TransDataD12;
        $transD12Datas->open_date=$request->rc_date;
        $transD12Datas->process_id=$request->next_process_id;
        $transD12Datas->product_process_id=$request->next_productprocess_id;
        $transD12Datas->part_id=$request->part_id;
        $transD12Datas->rc_id=$request->rc_no;
        $transD12Datas->previous_rc_id=$request->rc_no;
        $transD12Datas->reject_qty=$request->receive_qty;
        $transD12Datas->rejected_type_id=1;
        $transD12Datas->operation=$request->operation;
        $transD12Datas->area=$request->area;
        $transD12Datas->remarks=$request->reason;
        $transD12Datas->prepared_by = auth()->user()->id;
        $transD12Datas->save();

        return redirect()->route('productionrejectionlist')->withSuccess('Part Rejected is Successfully Completed!');
    }
    public function productionRejection(){
        date_default_timezone_set('Asia/Kolkata');
        $current_date=date('Y-m-d');
        $d11Datas=TransDataD11::where('process_issue_qty','!=',0)->select('*',DB::raw('((process_issue_qty)-((receive_qty)+(reject_qty)+(rework_qty))) as avl_qty'))->havingRaw('avl_qty >?', [0])->groupBy('rc_id')->get();
        $activity='Rejection';
        $stage='QC';
        $qrCodes_count=StageQrCodeLock::where('stage','=',$stage)->where('activity','=',$activity)->where('status','=',1)->count();
        // $transDataD11Datas=TransDataD11::where('process_issue_qty','!=',0)->select('*',DB::raw('((process_issue_qty)-((receive_qty)+(reject_qty)+(rework_qty))) as avl_qty'))->havingRaw('avl_qty >?', [0])->groupBy('rc_id')->get();
        // dd($transDataD11Datas);
        return view('stagewise-receive.production_rej_create',compact('d11Datas','current_date','qrCodes_count'));
    }

    public function productionRejectionList(){
        $transDataD12Datas=TransDataD12::with('partmaster','currentprocessmaster','currentproductprocessmaster','current_rcmaster','receiver')->where('reject_qty','!=',0)->where('rejected_type_id','!=',0)->get();
        // dd($transDataD12Datas);
        return view('stagewise-receive.production_rej_view',compact('transDataD12Datas'));
    }
    public function fgReceiveQRCreateForm(){
        date_default_timezone_set('Asia/Kolkata');
        $current_date=date('Y-m-d');
        $today = Carbon::today();          // 2025-06-04
        $yesterday = Carbon::today()->subDays(7);
        $d11Datas=TransDataD11::whereIn('next_process_id',[21,22])->where('status','=',1)->get();
        $activity='FG Receiving';
        $stage='FG Area';
        $qrCodes_count=StageQrCodeLock::where('stage','=',$stage)->where('activity','=',$activity)->where('status','=',1)->count();
        // $coverStrickerDetails=CoverStrickerDetails::with('stickermaster','rcmaster','fgreceivedby')->where('total_receive_qty','!=',0)->whereBetween('fg_receive_date', [$yesterday, $today])->orderBy('fg_receive_date','DESC')->get();
        $coverStrickerDetails=CoverStrickerDetails::with('stickermaster','rcmaster','fgreceivedby')->where('total_receive_qty','!=',0)->orderBy('fg_receive_date','DESC')->where('fg_receive_date', $current_date)->get();
        // dd($coverStrickerDetails);
        // return view('stagewise-receive.fg_qr_create2',compact('d11Datas','current_date','qrCodes_count'));
        // return view('stagewise-receive.fg_qr_create',compact('d11Datas','current_date','qrCodes_count'));
        return view('stagewise-receive.fg_qr_create3',compact('d11Datas','current_date','qrCodes_count','coverStrickerDetails'));
    }

    public function fgReceiveQREntry(Request $request){
        // dd($request->all());
        $current_rc_id=$request->current_rc_id;
        $stricker_id=$request->qr_rc_id;
        $prc_no=$request->rc_no;
        $rc_date=$request->rc_date;
        $part_id=$request->part_id;
        $next_process_id=$request->next_process_id;
        $receive_qty=$request->receive_qty;
        $avl_qty=$request->avl_qty;
        $cover_order_id=$request->cover_order_id;
        $cover_qty=$request->cover_qty;

        $transD11Datas=TransDataD11::where('rc_id','=',$current_rc_id)->first();
        // dd($transD11Datas);
        $next_product_process_id=$transD11Datas->next_product_process_id;
        $old_receive_qty=$transD11Datas->receive_qty;
        $transD11Datas->receive_qty=(($old_receive_qty)+($receive_qty));
        $transD11Datas->updated_by = auth()->user()->id;
        $transD11Datas->updated_at = Carbon::now();
        $transD11Datas->update();

        $transD12Datas=new TransDataD12;
        $transD12Datas->open_date=$rc_date;
        $transD12Datas->process_id=$next_process_id;
        $transD12Datas->product_process_id=$next_product_process_id;
        $transD12Datas->part_id=$part_id;
        $transD12Datas->rc_id=$current_rc_id;
        $transD12Datas->previous_rc_id=$current_rc_id;
        $transD12Datas->receive_qty=$receive_qty;
        $transD12Datas->prepared_by = auth()->user()->id;
        $transD12Datas->save();

        $coverStrickerDetails=CoverStrickerDetails::where('part_id','=',$part_id)->where('rc_id','=',$current_rc_id)->where('prc_id','=',$prc_no)->where('stricker_id','=',$stricker_id)->first();
        // dd($coverStrickerDetails);
        $total_receive_qty=$coverStrickerDetails->total_receive_qty;
        $coverStrickerDetails->total_receive_qty=(($total_receive_qty)+($receive_qty));
        $coverStrickerDetails->fg_receive_date = Carbon::now();
        $coverStrickerDetails->updated_by = auth()->user()->id;
        $coverStrickerDetails->updated_at = Carbon::now();
        $coverStrickerDetails->update();

        $PackingStrickerDetails=PackingStrickerDetails::find($stricker_id);
        // dd($PackingStrickerDetails);
        $old_u1_dc_receive_qty=$PackingStrickerDetails->u1_dc_receive_qty;
        $PackingStrickerDetails->u1_dc_receive_qty=(($old_u1_dc_receive_qty)+($receive_qty));
        $PackingStrickerDetails->updated_by = auth()->user()->id;
        $PackingStrickerDetails->updated_at = Carbon::now();
        $PackingStrickerDetails->update();

        $PtsTransactionSummary=PtsTransactionSummary::where('part_id','=',$part_id)->where('rc_id','=',$prc_no)->first();
        // dd($PtsTransactionSummary);
        $old_u1_dc_receive_qty=$PtsTransactionSummary->u1_dc_receive_qty;
        $PtsTransactionSummary->u1_dc_receive_qty=(($old_u1_dc_receive_qty)+($receive_qty));
        $PtsTransactionSummary->updated_by = auth()->user()->id;
        $PtsTransactionSummary->updated_at = Carbon::now();
        $PtsTransactionSummary->update();

        $DcTransactionDetails=DcTransactionDetails::where('rc_id','=',$prc_no)->first();
        // dd($DcTransactionDetails);
        $old_receive_qty=$DcTransactionDetails->receive_qty;
        $old_total_receive_qty=$DcTransactionDetails->total_receive_qty;
        $DcTransactionDetails->receive_qty=(($old_receive_qty)+($receive_qty));
        $DcTransactionDetails->total_receive_qty=(($old_total_receive_qty)+($receive_qty));
        $DcTransactionDetails->updated_by = auth()->user()->id;
        $DcTransactionDetails->updated_at = Carbon::now();
        $DcTransactionDetails->update();

        $DcTransactionDetailsData=DcTransactionDetails::where('rc_id','=',$current_rc_id)->first();
        // dd($DcTransactionDetailsData);
        $old_receive_qty=$DcTransactionDetailsData->receive_qty;
        $old_total_receive_qty=$DcTransactionDetailsData->total_receive_qty;
        $DcTransactionDetailsData->receive_qty=(($old_receive_qty)+($receive_qty));
        $DcTransactionDetailsData->total_receive_qty=(($old_total_receive_qty)+($receive_qty));
        $DcTransactionDetailsData->updated_by = auth()->user()->id;
        $DcTransactionDetailsData->updated_at = Carbon::now();
        $DcTransactionDetailsData->update();

        return redirect()->route('fgqrreceive')->withSuccess('FG Part Received is Successfully!');

    }

    public function fgDcReceiveQRCreateForm(){
        date_default_timezone_set('Asia/Kolkata');
        $current_date=date('Y-m-d');
        $dcPrint=DcPrint::with('dctransaction')->where('from_unit','=',4)->group('s_no')->get();
        // $d11Datas=TransDataD11::whereIn('next_process_id',[21,22])->where('status','=',1)->get();
        $activity='FG Receiving';
        $stage='FG Area';
        $qrCodes_count=StageQrCodeLock::where('stage','=',$stage)->where('activity','=',$activity)->where('status','=',1)->count();
        return view('stagewise-receive.fg_qr_dc_create',compact('dcPrint','current_date','qrCodes_count'));
    }

    public function fgReceiveCreateForm(){
        date_default_timezone_set('Asia/Kolkata');
        $current_date=date('Y-m-d');
        $d11Datas=TransDataD11::whereIn('next_process_id',[21,22])->where('status','=',1)->get();
        $activity='FG Receiving';
        $stage='FG Area';
        $qrCodes_count=StageQrCodeLock::where('stage','=',$stage)->where('activity','=',$activity)->where('status','=',1)->count();
        return view('stagewise-receive.fg_create',compact('d11Datas','current_date','qrCodes_count'));
    }

    public function fgPartFetchEntry(Request $request){
        // $request->all();
        // dd($request->all());
        $rc_no=$request->rc_no;
        $d11Datas=TransDataD11::with('rcmaster')->whereIn('next_process_id',[16,22])->where('rc_id','=',$rc_no)->where('status','=',1)->first();
        // dd($d11Datas);
        $part_id=$d11Datas->part_id;
        $fqcData=DcMaster::where('part_id','=',$part_id)->where('supplier_id','=','1')->count();
        $current_process_id=$d11Datas->process_id;
        $current_product_process_id=$d11Datas->product_process_id;
        $next_operation_id=$d11Datas->next_process_id;
        $next_operation_process_id=$d11Datas->next_product_process_id;
        $previous_process_issue_qty=$d11Datas->process_issue_qty;
        $receive_qty=$d11Datas->receive_qty;
        $reject_qty=$d11Datas->reject_qty;
        $rework_qty=$d11Datas->rework_qty;
        $rc_data='<option value="'.$d11Datas->rcmaster->id.'">'.$d11Datas->rcmaster->rc_id.'</option>';
        $qr_rc_id=$d11Datas->rcmaster->id;
        $bomDatas=BomMaster::where('child_part_id','=',$part_id)->sum('output_usage');
        if ($current_process_id==3) {
            $process_issue_qty=floor(($previous_process_issue_qty/$bomDatas));
        }else{
            $process_issue_qty=$d11Datas->process_issue_qty;
        }
        $fqc_offer_qty=FinalQcInspection::where('status','=',0)->where('next_process_id','=',22)->where('rc_id','=',$rc_no)->sum('offer_qty');
        // dd($fqc_offer_qty);
        $partCheck=ChildProductMaster::find($part_id);
        $part_no=$partCheck->child_part_no;
        $fifoCheck=TransDataD11::with('rcmaster')->where('process_id','=',$current_process_id)->where('part_id','=',$part_id)->where('status','=',1)->orderBy('id', 'ASC')->first();
        $fifoRcNo=$fifoCheck->rc_id;
        $fifoRcCard=$fifoCheck->rcmaster->rc_id;

        // dd($fifoRcNo);
        if($rc_no==$fifoRcNo){
            $success = true;
            $avl_qty=(($process_issue_qty)-($receive_qty)-($reject_qty)-($rework_qty)-($fqc_offer_qty));
            $part='<option value="'.$part_id.'">'.$part_no.'</option>';
            $fifoRcNo=$fifoCheck->rc_no;
            $bom=$bomDatas;
            if ($current_process_id==3) {
                $avl_kg=$avl_qty*$bom;
            }else{
                $avl_kg=$avl_qty;
            }
        // dd($avl_qty);

            $process_id=$current_process_id;
            $product_process_id=$current_product_process_id;
            $process_check1=ProductProcessMaster::whereIn('process_master_id',[6,7,8,21])->where('part_id','=',$part_id)->where('status','=',1)->orderBy('id', 'ASC')->count();
            // dd($process_check1);
            if ($process_check1==0) {
                $process=false;
                $next_process_id=0;
                $next_productprocess_id='<option value=""></option>';
            }else{
                $process=true;
                $process_checkData=DB::table('product_process_masters as a')
                ->join('item_procesmasters AS b', 'a.process_master_id', '=', 'b.id')
                ->select('b.operation','b.id as next_process_id','a.id as next_productprocess_id')
                ->where('process_master_id','=' ,$next_operation_id)
                ->where('part_id','=',$part_id)
                ->orderBy('a.id', 'DESC')
                ->first();
                // dd($process_checkData);
                $next_process_id=$process_checkData->next_process_id;
                $next_productprocess_id='<option value="'.$process_checkData->next_productprocess_id.'">'.$process_checkData->operation.'</option>';
            }
            $process_check=ProductProcessMaster::where('process_master_id','=',$process_id)->where('id','=',$current_product_process_id)->where('part_id','=',$part_id)->where('status','=',1)->orderBy('id', 'ASC')->count();
            if($process_check==0){
                $message=false;
            }else{
                $message=true;
            }

        }else{
            $success = false;
            $fifoRcNo=$fifoCheck->rc_id;
            $avl_qty=0;
            $avl_kg=0;
            $part='<option value=""></option>';
            $bom=0;
            $process_id=0;
            $product_process_id=0;
            $message=false;
            $process=false;
            $next_process_id=0;
            $next_productprocess_id='<option value=""></option>';
        }

        // dd($success);

        return response()->json(['success'=>$success,'fifoRcNo'=>$fifoRcNo,'avl_qty'=>$avl_qty,'part'=>$part,'bom'=>$bom,'avl_kg'=>$avl_kg,'message'=>$message,'process_id'=>$process_id,'product_process_id'=>$product_process_id,'next_process_id'=>$next_process_id,'next_productprocess_id'=>$next_productprocess_id,'process'=>$process,'fqc_count'=>$fqcData,'fifoRcCard'=>$fifoRcCard,'rc_data'=>$rc_data,'qr_rc_id'=>$qr_rc_id]);

        // $avl_qty=(($process_issue_qty)-($receive_qty)-($reject_qty)-($rework_qty));
        // dd($d11Datas->part_id);
    }

    public function fgFqcApproval(){
        $fqcDatas=FinalQcInspection::with(['current_rcmaster','previous_rcmaster','partmaster','currentprocessmaster','nextprocessmaster','inspector_usermaster'])->where('status','=',0)->whereIn('next_process_id',[22,16])->orderBy('id','DESC')->get();
        // dd($fqcDatas);
        return view('fqc_inspection.fg_fqc_view',compact('fqcDatas'));
    }
    public function osFqcApproval(){
        $fqcDatas=FinalQcInspection::with(['current_rcmaster','previous_rcmaster','partmaster','currentprocessmaster','nextprocessmaster','inspector_usermaster'])->where('status','=',0)->whereIn('next_process_id',[17])->orderBy('id','DESC')->get();
        // dd($fqcDatas);
        return view('fqc_inspection.os_fqc_view',compact('fqcDatas'));
    }
    public function traceabilty(Request $request){
        $rc_id=8;
        $rc_count=TransDataD11::with('rcmaster')->where('rc_id','=',$rc_id)->get()->count();
        if ($rc_count>0) {
            $rcDatas=TransDataD11::with('rcmaster')->where('rc_id','=',$rc_id)->get();
            foreach ($rcDatas as $key => $rcData) {
                $manufacturing_part_id=$rcData->part_id;
                $process_id=$rcData->process_id;
                $product_process_id=$rcData->product_process_id;
                $value='STOCKING POINT';
                $processDatas=ProductProcessMaster::with('processMaster')->find($product_process_id);
                $process_order_id=$processDatas->process_order_id;
                // dd($process_order_id);
                $nextProcessDatas=ProductProcessMaster::with('processMaster')->WhereHas('processMaster', function ($q) use ($value) {
                    $q->where('operation_type', '=', $value);
                })->where('part_id','=',$manufacturing_part_id)->where('process_order_id','<',$process_order_id)->where('process_order_id','>=',2)->where('status','=',1)->OrderBy('process_order_id','DESC')->get();
                // dd($nextProcessDatas);
                // foreach ($nextProcessDatas as $key => $nextProcessData) {
                //     # code...
                //     dump($nextProcessData);
                    $transD13Count=TransDataD13::with('current_rcmaster')->where('rc_id','=',$rc_id)->get()->count();
                    if ($transD13Count>1) {
                        # code...
                    } else {
                        $transD13Datas=TransDataD13::with('current_rcmaster')->where('rc_id','=',$rc_id)->first();
                        $rc_process_id=$transD13Datas->current_rcmaster->process_id;
                        $prc_id=$transD13Datas->previous_rc_id;
                        $transd12Datas=TransDataD12::with('partmaster','currentprocessmaster','currentproductprocessmaster','previous_rcmaster','current_rcmaster')->where('rc_id','=',$rc_id)->where('previous_rc_id','=',$prc_id)->first();
                        $newtransd12Datas=TransDataD12::with('partmaster','currentprocessmaster','currentproductprocessmaster','previous_rcmaster','current_rcmaster')->whereColumn('rc_id','=','previous_rc_id')->where('previous_rc_id','=',$prc_id)->get();
                        dump($transd12Datas);
                        dump($newtransd12Datas);
                        $pretransD13Datas=TransDataD13::with('current_rcmaster')->where('rc_id','=',$prc_id)->first();
                        $prc_process_id=$pretransD13Datas->current_rcmaster->process_id;
                        $prc_id2=$pretransD13Datas->previous_rc_id;
                        $pretransd12Datas=TransDataD12::with('partmaster','currentprocessmaster','currentproductprocessmaster','previous_rcmaster','current_rcmaster')->where('rc_id','=',$prc_id2)->where('previous_rc_id','=',$prc_id2)->first();
                        $prenewtransd12Datas=TransDataD12::with('partmaster','currentprocessmaster','currentproductprocessmaster','previous_rcmaster','current_rcmaster')->whereColumn('rc_id','=','previous_rc_id')->where('previous_rc_id','=',$prc_id2)->get();
                        dump($pretransd12Datas);
                        dump($prenewtransd12Datas);

                    }


                // }
            }

        } else {
            # code...
        }
    }
    public function fgQrDC(){
        // $value=7;
        // $fgqrDc=DcTransactionDetails::with('dcmaster')->WhereHas('dcmaster', function ($q) use ($value) {
        //     $q->where('supplier_id', '=', $value);
        // })->select('*',DB::raw('((issue_qty)-(receive_qty)) as avl_qty'))->havingRaw('avl_qty >?', [0])->orderBy('id', 'ASC')->get();
        // dd($fgqrDc);
        $coverStrickerDetails=CoverStrickerDetails::with('stickermaster','rcmaster')->where('total_receive_qty','=',0)->get();
        $packingStrickerDetails=PackingStrickerDetails::with('rcmaster','partmaster','covermaster')->where('u1_dc_reject_qty','=',0)->select('*',DB::raw('((pts_dc_issue_qty)-(u1_dc_receive_qty)) as avl_qty'))->havingRaw('avl_qty >?', [0])->orderBy('id', 'ASC')->get();
        // dd($coverStrickerDetails);
        return view('stagewise-receive.fg_qrdc_view',compact('coverStrickerDetails'));
    }

    public function fgReceiveEntry(Request $request){
        // dd($request->all());
        DB::beginTransaction();
        try {
            $count=$request->fqc_count;
            $qrcodes_count=$request->qrcodes_count;
            if ($qrcodes_count==0) {
                $rc_card_id=$request->rc_no;
            } else {
                $rc_card_id=$request->qr_rc_id;
            }
            if($count!=0){
                $d11Datas=TransDataD11::where('process_id','=',$request->previous_process_id)->where('product_process_id','=',$request->previous_product_process_id)->where('rc_id','=',$rc_card_id)->first();
                $total_receive_qty=($d11Datas->receive_qty+$request->receive_qty);
                if($request->rc_close=="yes"){
                    // dd($request->rc_date);
                    $d11Datas->close_date=$request->rc_date;
                    $d11Datas->status=0;
                }
                $d11Datas->receive_qty=$total_receive_qty;
                $d11Datas->updated_by = auth()->user()->id;
                $d11Datas->updated_at = Carbon::now();
                $d11Datas->update();
                // dd($d11Datas->receive_qty);

                $d12Datas=new TransDataD12;
                $d12Datas->open_date=$request->rc_date;
                $d12Datas->rc_id=$rc_card_id;
                $d12Datas->previous_rc_id=$rc_card_id;
                $d12Datas->part_id=$request->part_id;
                $d12Datas->process_id=$request->next_process_id;
                $d12Datas->product_process_id=$request->next_productprocess_id;
                $d12Datas->receive_qty=$request->receive_qty;
                $d12Datas->prepared_by = auth()->user()->id;
                $d12Datas->save();
                DB::commit();
                return redirect()->route('fgreceive')->withSuccess('Part Received is Successfully!');
            }else{
                $fqcInspectionData=new FinalQcInspection;
                $fqcInspectionData->offer_date=$request->rc_date;
                $fqcInspectionData->rc_id=$rc_card_id;
                $fqcInspectionData->previous_rc_id=$rc_card_id;
                $fqcInspectionData->part_id=$request->part_id;
                $fqcInspectionData->process_id=$request->previous_process_id;
                $fqcInspectionData->product_process_id=$request->previous_product_process_id;
                $fqcInspectionData->next_process_id=$request->next_process_id;
                $fqcInspectionData->next_product_process_id=$request->next_productprocess_id;
                $fqcInspectionData->offer_qty=$request->receive_qty;
                if($request->rc_close=="yes"){
                $fqcInspectionData->rc_status=0;
                }else{
                $fqcInspectionData->rc_status=1;
                }
                $fqcInspectionData->prepared_by = auth()->user()->id;
                $fqcInspectionData->save();

                $d11Datas=TransDataD11::where('process_id','=',$request->previous_process_id)->where('product_process_id','=',$request->previous_product_process_id)->where('rc_id','=',$rc_card_id)->first();
                if($request->rc_close=="yes"){
                    $d11Datas->close_date=$request->rc_date;
                    $d11Datas->status=0;
                }
                $d11Datas->updated_by = auth()->user()->id;
                $d11Datas->updated_at = Carbon::now();
                $d11Datas->update();

                DB::commit();
                return redirect()->route('fgfqc')->withSuccess('Part Received is Successfully And Waiting For Final Quality Inspection!');

            }

        } catch (\Throwable $th) {
            //throw $th;
            DB::rollback();
            dd($th->getMessage());
            return redirect()->back()->withErrors($th->getMessage());
        }
    }
    public function ptsProductionIssueList(){
        $d12Datas=PtsTransactionSummary::with('rcmaster','partmaster')->select('part_id','open_date','rc_id','u1_dc_issue_qty','pts_store_dc_receive_qty','pts_production_receive_qty','remarks',DB::raw('((pts_production_receive_qty)) as avl_qty'))->havingRaw('avl_qty >?', [0])->orderBy('id','DESC')->get();
        // $d12Datas=DB::table('trans_data_d12_s as a')
        // ->join('item_procesmasters AS b', 'a.process_id', '=', 'b.id')
        // ->join('child_product_masters AS c', 'a.part_id', '=', 'c.id')
        // ->join('users AS d', 'a.prepared_by', '=', 'd.id')
        // ->join('route_masters AS e', 'a.rc_id', '=', 'e.id')
        // ->join('route_masters AS f', 'a.previous_rc_id', '=', 'f.id')
        // ->select('b.operation','b.id as process_id','a.open_date','e.rc_id as rc_no','f.rc_id as previous_rc_no','a.receive_qty','c.child_part_no as part_no','a.prepared_by','a.created_at','d.name as user_name')
        // ->whereIn('a.process_id', [18])
        // ->whereRaw('a.rc_id=a.previous_rc_id')
        // ->orderBy('a.id', 'DESC')
        // ->get();
        // dd($d12Datas);
        return view('stagewise-receive.pts_production_view',compact('d12Datas'));
        // return view('stagewise-receive.i');
        // dd('kkk');

    }

    public function ptsProductionIssueCreateForm(){
        date_default_timezone_set('Asia/Kolkata');
        $current_date=date('Y-m-d');
        $ptsDatas=PtsTransactionSummary::with('rcmaster')->select('rc_id',DB::raw('((pts_store_dc_receive_qty)-(pts_production_receive_qty)) as avl_qty'))
        ->havingRaw('avl_qty >?', [0])->groupBy('rc_id')->get();
        // $d11Datas=TransDataD11::whereIn('process_id',[18])->where('status','=',1)->get();
        return view('stagewise-receive.pts_production_create',compact('ptsDatas','current_date'));
    }
    public function ptsProductionIssuePartFetchEntry(Request $request){
        // dd($request->all());
        $rc_no=$request->rc_no;
        $ptsDatas=PtsTransactionSummary::with('partmaster')->select('part_id','process_id',DB::raw('((pts_store_dc_receive_qty)-(pts_production_receive_qty)) as avl_qty'))
        ->where('rc_id','=',$rc_no)->havingRaw('avl_qty >?', [0])->first();

        $fifoCheck=PtsTransactionSummary::select('rc_id',DB::raw('((pts_store_dc_receive_qty)-(pts_production_receive_qty)) as avl_qty'))
        ->where('part_id','=',$ptsDatas->partmaster->id)->havingRaw('avl_qty >?', [0])->first();
        $fifoRcNo=$fifoCheck->rc_id;

        if ($rc_no==$fifoRcNo) {
            $success = true;
            $partNo='<option value="'.$ptsDatas->partmaster->id.'" selected>'.$ptsDatas->partmaster->child_part_no.'</option>';
            $avl_qty=$ptsDatas->avl_qty;
            $process='<option value="18" selected>PAINTSHOP</option>';
        }else {
            $success = false;
            $partNo='<option value="" selected></option>';
            $avl_qty=0;
            $process='<option value="18" selected>PAINTSHOP</option>';
        }

        $bomDatas=BomMaster::where('child_part_id','=',$ptsDatas->partmaster->id)->where('status','=',1)->sum('finish_usage');

         $process_check=ProductProcessMaster::where('process_master_id','=',$ptsDatas->process_id)->where('part_id','=',$ptsDatas->partmaster->id)->where('status','=',1)->orderBy('id', 'ASC')->count();
        //  dd($process_check);
         if($process_check==0){
             $message=false;
         }else{
             $message=true;
         }

        return response()->json(['success'=>$success,'fifoRcNo'=>$fifoRcNo,'part'=>$partNo,'message'=>$message,'process'=>$process,'avl_qty'=>$avl_qty,'bom'=>$bomDatas]);
    }

    public function ptsProductionIssueEntry(Request $request){
        dd($request->all());
        date_default_timezone_set('Asia/Kolkata');
        $current_date=date('Y-m-d');

        $current_processDatas=ProductProcessMaster::with('processMaster')->where('part_id','=',$request->part_id)->where('process_master_id','=',$request->process_id)->first();
        $current_process=$current_processDatas->processMaster->operation;

        // dd($current_processDatas);
        $current_process_order_id=$current_processDatas->process_order_id;

        $next_processDatas=ProductProcessMaster::with('processMaster')->where('part_id','=',$request->part_id)->where('process_order_id','>',$current_process_order_id)->where('status','=',1)->first();
        $next_product_process_id=$next_processDatas->id;
        $next_process_id=$next_processDatas->process_master_id;
        $next_process=$next_processDatas->processMaster->operation;
        $next_process_order_id=$next_processDatas->process_order_id;
        // dd($next_processDatas);
        // dd($next_process);

        $ptsTransactionDetail=new PtsTransactionDetail;
        $ptsTransactionDetail->open_date=$current_date;
        $ptsTransactionDetail->part_id=$request->part_id;
        $ptsTransactionDetail->process_id=$request->process_id;
        $ptsTransactionDetail->process=$current_process;
        $ptsTransactionDetail->rc_id=$request->rc_no;
        $ptsTransactionDetail->previous_rc_id=$request->rc_no;
        $ptsTransactionDetail->issue_qty=$request->receive_qty;
        $ptsTransactionDetail->prepared_by = auth()->user()->id;
        $ptsTransactionDetail->save();

        $ptsTransactionSummary=PtsTransactionSummary::where('rc_id','=',$request->rc_no)->where('status','=',1)->first();
        // dd($ptsTransactionSummary);
        $old_receive_qty=$ptsTransactionSummary->pts_production_receive_qty;
        $current_receive_qty=$old_receive_qty+$request->receive_qty;
        // dd($current_receive_qty);
        $ptsTransactionSummary->pts_production_receive_qty=$current_receive_qty;
        $ptsTransactionSummary->updated_by = auth()->user()->id;
        $ptsTransactionSummary->updated_at = Carbon::now();
        $ptsTransactionSummary->update();

        return redirect()->route('ptsproductionissue')->withSuccess('Successfully Part No Issue To Pts Production Team...!');
    }



    public function ptsProductionReceiveList(){
        $d12Datas=PtsTransactionSummary::with('rcmaster','partmaster')->select('part_id','open_date','rc_id','u1_dc_issue_qty','pts_store_dc_receive_qty','pts_production_receive_qty','pts_production_issue_qty','pts_production_reject_qty','pts_production_rework_qty','remarks',DB::raw('((pts_production_issue_qty)+(pts_production_reject_qty)+(pts_production_rework_qty)) as avl_qty'))->havingRaw('avl_qty >?', [0])->orderBy('updated_at','DESC')->get();
        // $d12Datas=DB::table('trans_data_d12_s as a')
        // ->join('item_procesmasters AS b', 'a.process_id', '=', 'b.id')
        // ->join('child_product_masters AS c', 'a.part_id', '=', 'c.id')
        // ->join('users AS d', 'a.prepared_by', '=', 'd.id')
        // ->join('route_masters AS e', 'a.rc_id', '=', 'e.id')
        // ->join('route_masters AS f', 'a.previous_rc_id', '=', 'f.id')
        // ->select('b.operation','b.id as process_id','a.open_date','e.rc_id as rc_no','f.rc_id as previous_rc_no','a.receive_qty','c.child_part_no as part_no','a.prepared_by','a.created_at','d.name as user_name')
        // ->whereIn('a.process_id', [18])
        // ->whereRaw('a.rc_id=a.previous_rc_id')
        // ->orderBy('a.id', 'DESC')
        // ->get();
        // dd($d12Datas);
        return view('stagewise-receive.pts_production_issue_view',compact('d12Datas'));
        // return view('stagewise-receive.i');
        // dd('kkk');

    }
    public function ptsStockList(Request $request){
        $partDatas=PtsTransactionSummary::with('partmaster')->groupBy('part_id')->get();
        // dd($partDatas);
        $rcDatas=PtsTransactionSummary::with('rcmaster')->groupBy('rc_id')->get();
        $query = PtsTransactionSummary::with('rcmaster','partmaster')->select('part_id','open_date','rc_id','u1_dc_issue_qty','pts_store_dc_receive_qty','pts_production_receive_qty','pts_production_issue_qty','pts_production_reject_qty','pts_production_rework_qty','remarks',DB::raw('(u1_dc_issue_qty)-(pts_store_dc_receive_qty) as u1_avl_qty'),DB::raw('(pts_store_dc_receive_qty)-((pts_production_issue_qty)+(pts_production_reject_qty)+(pts_production_rework_qty)) as pts_store_avl_qty'),DB::raw('(((pts_production_issue_qty)+(pts_production_reject_qty)+(pts_production_rework_qty))-(cle_receive_qty)) as pts_production_avl_qty'),DB::raw('((cle_receive_qty)-((cle_issue_qty)+(cle_reject_qty)+(cle_rework_qty))) as cle_avl_qty'),DB::raw('(((cle_issue_qty)+(cle_reject_qty)+(cle_rework_qty))-(pts_store_dc_issue_qty)) as pts_dc_avl_qty'),DB::raw('((pts_store_dc_issue_qty)-((u1_dc_receive_qty)+(u1_dc_reject_qty)+(u1_dc_rework_qty)+(u1_dc_return_qty))) as fg_dc_avl_qty'))->orderBy('open_date','DESC')->where('process_id','=',17);
        if(!empty($request->part_id)){
            $query = $query->where('part_id','=',$request->part_id);
        }if(!empty($request->rc_id)){
            $query = $query->where('rc_id','=',$request->rc_id);
        }
        $d12Datas = $query->get();

    //    $d12Datas=PtsTransactionSummary::with('rcmaster','partmaster')->select('part_id','open_date','rc_id','u1_dc_issue_qty','pts_store_dc_receive_qty','pts_production_receive_qty','pts_production_issue_qty','pts_production_reject_qty','pts_production_rework_qty','remarks',DB::raw('(u1_dc_issue_qty)-(pts_store_dc_receive_qty) as u1_avl_qty'),DB::raw('(pts_store_dc_receive_qty)-((pts_production_issue_qty)+(pts_production_reject_qty)+(pts_production_rework_qty)) as pts_store_avl_qty'),DB::raw('(((pts_production_issue_qty)+(pts_production_reject_qty)+(pts_production_rework_qty))-(cle_receive_qty)) as pts_production_avl_qty'),DB::raw('((cle_receive_qty)-((cle_issue_qty)+(cle_reject_qty)+(cle_rework_qty))) as cle_avl_qty'),DB::raw('(((cle_issue_qty)+(cle_reject_qty)+(cle_rework_qty))-(pts_store_dc_issue_qty)) as pts_dc_avl_qty'),DB::raw('((pts_store_dc_issue_qty)-((u1_dc_receive_qty)+(u1_dc_reject_qty)+(u1_dc_rework_qty)+(u1_dc_return_qty))) as fg_dc_avl_qty'))->orderBy('open_date','DESC')->get();
        // dd($d12Datas);
        return view('stagewise-receive.pts_stock_view',compact('d12Datas','partDatas','rcDatas'));
    }

    public function pts_export(Request $request)
    {
        // dd($request->all());
        // dd(Session::get('date_from'));
        $partDatas=PtsTransactionSummary::with('partmaster')->groupBy('part_id')->get();

        $query = PtsTransactionSummary::with('rcmaster','partmaster')->select('part_id','open_date','rc_id','u1_dc_issue_qty','pts_store_dc_receive_qty','pts_production_receive_qty','pts_production_issue_qty','pts_production_reject_qty','pts_production_rework_qty','remarks',DB::raw('(u1_dc_issue_qty)-(pts_store_dc_receive_qty) as u1_avl_qty'),DB::raw('(pts_store_dc_receive_qty)-((pts_production_issue_qty)+(pts_production_reject_qty)+(pts_production_rework_qty)) as pts_store_avl_qty'),DB::raw('(((pts_production_issue_qty)+(pts_production_reject_qty)+(pts_production_rework_qty))-(cle_receive_qty)) as pts_production_avl_qty'),DB::raw('((cle_receive_qty)-((cle_issue_qty)+(cle_reject_qty)+(cle_rework_qty))) as cle_avl_qty'),DB::raw('(((cle_issue_qty)+(cle_reject_qty)+(cle_rework_qty))-(pts_store_dc_issue_qty)) as pts_dc_avl_qty'),DB::raw('((pts_store_dc_issue_qty)-((u1_dc_receive_qty)+(u1_dc_reject_qty)+(u1_dc_rework_qty)+(u1_dc_return_qty))) as fg_dc_avl_qty'))->orderBy('open_date','DESC')->where('process_id','=',17);
        if(!empty($request->part_id)){
            $query = $query->where('part_id','=',$request->part_id);
        }if(!empty($request->rc_id)){
            $query = $query->where('rc_id','=',$request->rc_id);
        }
        $d12Datas = $query->get();
        // dd($total_avl_kg);
        return Excel::download(new ExportsPtsstockExport($d12Datas), 'pts_stock.xlsx');
        // return Excel::download(new InvoiceExport, 'invoice.xlsx');
    }

    public function ptsProductionReceiveCreateForm(){
        date_default_timezone_set('Asia/Kolkata');
        $current_date=date('Y-m-d');

        $ptsDatas=PtsTransactionSummary::with('partmaster')->select('part_id')
        ->groupBy('part_id')->get();
        // dd($ptsDatas);
        // $ptsDatas=PtsTransactionSummary::with('rcmaster')->select('rc_id',DB::raw('((pts_production_receive_qty)-(pts_production_issue_qty)) as avl_qty'))
        // ->havingRaw('avl_qty >?', [0])->groupBy('rc_id')->get();
        // $d11Datas=TransDataD11::whereIn('process_id',[18])->where('status','=',1)->get();
        return view('stagewise-receive.pts_production_issue_create',compact('ptsDatas','current_date'));
    }

    public function ptsProductionReceiveRcFetchEntry(Request $request){
        // dd($request->all());
        $count=PtsTransactionSummary::with('rcmaster')->selectRaw('rc_id,((pts_production_receive_qty)-((pts_production_issue_qty)+(pts_production_reject_qty)+(pts_production_rework_qty))) as avl_qty')
        ->where('part_id','=',$request->part_id)->havingRaw('avl_qty >?', [0])->groupBy('rc_id')->count();
        if ($count>0) {
            $rc_msg=true;
            $ptsDatas=PtsTransactionSummary::with('rcmaster')->selectRaw('rc_id,((pts_production_receive_qty)-((pts_production_issue_qty)+(pts_production_reject_qty)+(pts_production_rework_qty))) as avl_qty')
            ->where('part_id','=',$request->part_id)->havingRaw('avl_qty >?', [0])->groupBy('rc_id')->get();
            // dd($ptsDatas);
            $html='<option value="">Select The RC Number</option>';
            foreach ($ptsDatas as $key => $ptsData) {
                $html.='<option value="'.$ptsData->rcmaster->id.'">'.$ptsData->rcmaster->rc_id.'</option>';
            }
        } else {
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
            $html.='<svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Danger:"><use xlink:href="#exclamation-triangle-fill"/></svg><div>Sorry..This Part Number Is Not Available Stock...</div></div>';
        }
        return response()->json(['rc_msg'=>$rc_msg,'html'=>$html]);
    }

    public function ptsProductionReceivePartFetchEntry(Request $request){
        // dd($request->all());
        $rc_no=$request->rc_no;
        $ptsDatas=PtsTransactionSummary::with('partmaster')->select('part_id','process_id',DB::raw('((pts_production_receive_qty)-(pts_production_issue_qty)) as avl_qty'))
        ->where('rc_id','=',$rc_no)->havingRaw('avl_qty >?', [0])->first();

        $fifoCheck=PtsTransactionSummary::with('rcmaster')->select('rc_id',DB::raw('((pts_production_receive_qty)-(pts_production_issue_qty)) as avl_qty'))
        ->where('part_id','=',$ptsDatas->partmaster->id)->havingRaw('avl_qty >?', [0])->first();
        $fifoRcNo=$fifoCheck->rc_id;
        $fifoRcCard=$fifoCheck->rcmaster->rc_id;
        // dd($fifoRcCard);
        // dd($rc_no);
        if ($rc_no==$fifoRcNo) {
            $success = true;
            $html='';
            $dcTransactionDetails=DcTransactionDetails::where('rc_id','=',$rc_no)->first();
            $dc_id=$dcTransactionDetails->id;
            $partNo='<option value="'.$ptsDatas->partmaster->id.'" selected>'.$ptsDatas->partmaster->child_part_no.'</option>';
            $avl_qty=$ptsDatas->avl_qty;
            $process='<option value="20" selected>FG For Painting</option>';
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
            $html.='<svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Danger:"><use xlink:href="#exclamation-triangle-fill"/></svg><div>Please Follow The FIFO ..Try RC No Is '.$fifoRcCard.'</div></div>';
            $partNo='<option value="" selected></option>';
            $avl_qty=0;
            $process='<option value="20" selected>FG For Painting</option>';
            $dc_id=0;
        }

        $bomDatas=BomMaster::where('child_part_id','=',$ptsDatas->partmaster->id)->where('status','=',1)->sum('finish_usage');

         $process_check=ProductProcessMaster::where('process_master_id','=',$ptsDatas->process_id)->where('part_id','=',$ptsDatas->partmaster->id)->where('status','=',1)->orderBy('id', 'ASC')->count();
        //  dd($process_check);
         if($process_check==0){
             $message=false;
             $process_msg='<svg xmlns="http://www.w3.org/2000/svg" style="display: none;">
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
             $process_msg.='<svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Danger:"><use xlink:href="#exclamation-triangle-fill"/></svg><div>This Part Number is Not connected Item Process Master..So Please Contact Mr.PPC/ERP Team</div></div>';
             $partNo='<option value="" selected></option>';
         }else{
             $message=true;
             $process_msg='';
         }

        return response()->json(['success'=>$success,'html'=>$html,'process_msg'=>$process_msg,'fifoRcNo'=>$fifoRcNo,'part'=>$partNo,'message'=>$message,'process'=>$process,'avl_qty'=>$avl_qty,'bom'=>$bomDatas,'dc_id'=>$dc_id]);
    }

    public function ptsProductionReceiveEntry(Request $request){
        // dd('ok');
        // dd($request->all());
        date_default_timezone_set('Asia/Kolkata');
        $current_date=date('Y-m-d');

        $current_processDatas=ProductProcessMaster::with('processMaster')->where('part_id','=',$request->part_id)->where('process_master_id','=',$request->process_id)->first();
        $current_process=$current_processDatas->processMaster->operation;

        // dd($current_processDatas);
        $current_process_order_id=$current_processDatas->process_order_id;

        $next_processDatas=ProductProcessMaster::with('processMaster')->where('part_id','=',$request->part_id)->where('process_order_id','>',$current_process_order_id)->where('status','=',1)->first();
        $next_product_process_id=$next_processDatas->id;
        $next_process_id=$next_processDatas->process_master_id;
        $next_process=$next_processDatas->processMaster->operation;
        $next_process_order_id=$next_processDatas->process_order_id;
        // dd($next_processDatas);
        // dd($next_process);

        $ptsTransactionDetail=new PtsTransactionDetail;
        $ptsTransactionDetail->open_date=$current_date;
        $ptsTransactionDetail->part_id=$request->part_id;
        $ptsTransactionDetail->process_id=$request->process_id;
        $ptsTransactionDetail->process=$current_process;
        $ptsTransactionDetail->rc_id=$request->rc_no;
        $ptsTransactionDetail->previous_rc_id=$request->rc_no;
        $ptsTransactionDetail->receive_qty=$request->receive_qty;
        $ptsTransactionDetail->prepared_by = auth()->user()->id;
        $ptsTransactionDetail->save();

        $ptsTransactionSummary=PtsTransactionSummary::where('rc_id','=',$request->rc_no)->where('status','=',1)->first();
        // dd($ptsTransactionSummary);
        $old_receive_qty=$ptsTransactionSummary->pts_production_issue_qty;
        $current_receive_qty=$old_receive_qty+$request->receive_qty;
        // dd($current_receive_qty);
        $ptsTransactionSummary->pts_production_issue_qty=$current_receive_qty;
        $ptsTransactionSummary->updated_by = auth()->user()->id;
        $ptsTransactionSummary->updated_at = Carbon::now();
        $ptsTransactionSummary->update();

        if ($request->avl_qty==$request->receive_qty) {
            $dcPrint=DcPrint::where('dc_id','=',$request->dc_id)->first();
            $dcPrint->pts_production_status=0;
            $dcPrint->updated_by = auth()->user()->id;
            $dcPrint->updated_at = Carbon::now();
            $dcPrint->update();
        }
        return redirect()->route('ptsproductionreceive')->withSuccess('Successfully Part No Issue To Pts Store...!');
    }

    public function ptsCleIssueList(){
        // $d12Datas=DB::table('trans_data_d12_s as a')
        // ->join('item_procesmasters AS b', 'a.process_id', '=', 'b.id')
        // ->join('child_product_masters AS c', 'a.part_id', '=', 'c.id')
        // ->join('users AS d', 'a.prepared_by', '=', 'd.id')
        // ->join('route_masters AS e', 'a.rc_id', '=', 'e.id')
        // ->join('route_masters AS f', 'a.previous_rc_id', '=', 'f.id')
        // ->select('b.operation','b.id as process_id','a.open_date','e.rc_id as rc_no','f.rc_id as previous_rc_no','a.receive_qty','c.child_part_no as part_no','a.prepared_by','a.created_at','d.name as user_name')
        // ->whereIn('a.process_id', [18])
        // ->whereRaw('a.rc_id=a.previous_rc_id')
        // ->orderBy('a.id', 'DESC')
        // ->get();
        $ptsStockDatas=PtsTransactionDetail::with('rcmaster','previous_rcmaster','partmaster','currentprocessmaster','prepareuserdetails')->where('process_id','=',20)->where('issue_qty','!=',0)->where('receive_qty','=',0)->where('reject_qty','=',0)->where('rework_qty','=',0)->where('return_issue_qty','=',0)->orderBy('id','DESC')->get();
        // dd($ptsStockDatas);
        return view('stagewise-receive.pts_cle_view',compact('ptsStockDatas'));
        // dd('kkk');

    }

    public function ptsCleIssueCreateForm(){
        date_default_timezone_set('Asia/Kolkata');
        $current_date=date('Y-m-d');
        $ptsDatas=PtsTransactionSummary::with('partmaster')->select('part_id')
        ->groupBy('part_id')->get();
        // $ptsDatas=PtsTransactionSummary::with('rcmaster')->select('rc_id',DB::raw('((pts_production_issue_qty)-(cle_receive_qty)) as avl_qty'))
        // ->havingRaw('avl_qty >?', [0])->groupBy('rc_id')->get();
        // $d11Datas=TransDataD11::whereIn('process_id',[18])->where('status','=',1)->get();
        return view('stagewise-receive.pts_cle_create',compact('ptsDatas','current_date'));
    }
    public function ptsCleIssueRcFetchEntry(Request $request){
        // dd($request->all());
        // PtsTransactionSummary::with('partmaster')->select('part_id','process_id','cle_receive_qty','cle_issue_qty','cle_reject_qty','cle_rework_qty',DB::raw('((cle_receive_qty)-(cle_issue_qty)-(cle_reject_qty)-(cle_rework_qty)-(cle_return_qty)) as avl_qty'))
        // ->where('rc_id','=',$rc_no)->where('part_id','=',$part_id)->havingRaw('avl_qty >?', [0])->first();
        $count=PtsTransactionSummary::with('rcmaster')->selectRaw('rc_id,((cle_receive_qty)-(cle_issue_qty)-(cle_reject_qty)-(cle_rework_qty)-(cle_return_qty)) as avl_qty')
        ->where('part_id','=',$request->part_id)->havingRaw('avl_qty >?', [0])->groupBy('rc_id')->count();
        if ($count>0) {
            $rc_msg=true;
            $ptsDatas=PtsTransactionSummary::with('rcmaster')->selectRaw('rc_id,((cle_receive_qty)-(cle_issue_qty)-(cle_reject_qty)-(cle_rework_qty)-(cle_return_qty)) as avl_qty')
            ->where('part_id','=',$request->part_id)->havingRaw('avl_qty >?', [0])->groupBy('rc_id')->get();
            // dd($ptsDatas);
            $html='<option value="">Select The RC Number</option>';
            foreach ($ptsDatas as $key => $ptsData) {
                $html.='<option value="'.$ptsData->rcmaster->id.'">'.$ptsData->rcmaster->rc_id.'</option>';
            }
        } else {
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
            $html.='<svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Danger:"><use xlink:href="#exclamation-triangle-fill"/></svg><div>Sorry..This Part Number Is Not Available Stock...</div></div>';
        }
        return response()->json(['rc_msg'=>$rc_msg,'html'=>$html]);
    }
    public function ptsCleIssuePartFetchEntry(Request $request){
        // dd($request->all());
        $rc_no=$request->rc_no;
        $part_id=$request->part_id;
        $ptsDatas=PtsTransactionSummary::with('partmaster')->select('part_id','process_id',DB::raw('((pts_production_issue_qty)-(cle_receive_qty)) as avl_qty'))
        ->where('rc_id','=',$rc_no)->where('part_id','=',$part_id)->havingRaw('avl_qty >?', [0])->first();

        $fifoCheck=PtsTransactionSummary::select('rc_id',DB::raw('((pts_production_issue_qty)-(cle_receive_qty)) as avl_qty'))
        ->where('part_id','=',$part_id)->havingRaw('avl_qty >?', [0])->first();
        $fifoRcNo=$fifoCheck->rc_id;

        if ($rc_no==$fifoRcNo) {
            $success = true;
            $partNo='<option value="'.$ptsDatas->partmaster->id.'" selected>'.$ptsDatas->partmaster->child_part_no.'</option>';
            $avl_qty=$ptsDatas->avl_qty;
            $process='<option value="20" selected>FG For Painting</option>';
        }else {
            $success = false;
            $partNo='<option value="" selected></option>';
            $avl_qty=0;
            $process='<option value="20" selected>FG For Painting</option>';
        }
        $childProductDatas=ChildProductMaster::with('invoicepart')->where('id','=',$part_id)->where('stocking_point','=',22)->first();
        $invoice_part_no=$childProductDatas->invoicepart->id;
        // $customer_datas=CustomerProductMaster::with('productmasters','customermaster')->where('part_id','=',$invoice_part_no)->get();
        // $customer_datas=CustomerProductMaster::with('productmasters','customermaster')->WhereHas('customermaster', function ($q) {
        //     $q->groupBy('cus_type_name');
        // })->where('part_id','=',$invoice_part_no)->orderBy('id', 'DESC')->get();
        $customer_datas=DB::table('customer_product_masters as a')
        ->join('customer_masters AS b', 'a.cus_id', '=', 'b.id')
        ->select('a.*','b.*')
        ->where('a.part_id','=',$invoice_part_no)
        ->orderBy('a.id', 'ASC')
        ->groupBy('b.cus_type_name')
        ->get();
        // dd($customer_datas);
        $html='<option value="" selected>Select The Customer</option>';
        foreach ($customer_datas as $key => $customer_data) {
            $html.='<option value="'.$customer_data->cus_type_name.'" >'.$customer_data->cus_type_name.'</option>';
        }
        $packingMasterDatas=PackingMaster::with('covermaster')->where('part_id','=',$invoice_part_no)->where('status','=',1)->groupBy('cover_qty')->get();
        // dd($packingMasterDatas);
        $bomDatas=BomMaster::where('child_part_id','=',$part_id)->where('status','=',1)->sum('finish_usage');

         $process_check=ProductProcessMaster::where('process_master_id','=',$ptsDatas->process_id)->where('part_id','=',$part_id)->where('status','=',1)->orderBy('id', 'ASC')->count();
        //  dd($process_check);
         if($process_check==0){
             $message=false;
         }else{
             $message=true;
         }

        return response()->json(['success'=>$success,'fifoRcNo'=>$fifoRcNo,'part'=>$partNo,'message'=>$message,'process'=>$process,'avl_qty'=>$avl_qty,'bom'=>$bomDatas,'customer'=>$html,'invoice_part_id'=>$invoice_part_no]);
    }

    public function ptsCleReceiveRcFetchEntry(Request $request){
        // dd($request->all());
        $count=PtsTransactionSummary::with('rcmaster')->selectRaw('rc_id,((pts_production_issue_qty)-((cle_receive_qty))) as avl_qty')
        ->where('part_id','=',$request->part_id)->havingRaw('avl_qty >?', [0])->groupBy('rc_id')->count();
        if ($count>0) {
            $rc_msg=true;
            $ptsDatas=PtsTransactionSummary::with('rcmaster')->selectRaw('rc_id,((pts_production_issue_qty)-((cle_receive_qty))) as avl_qty')
            ->where('part_id','=',$request->part_id)->havingRaw('avl_qty >?', [0])->groupBy('rc_id')->get();
            // dd($ptsDatas);
            $html='<option value="">Select The RC Number</option>';
            foreach ($ptsDatas as $key => $ptsData) {
                $html.='<option value="'.$ptsData->rcmaster->id.'">'.$ptsData->rcmaster->rc_id.'</option>';
            }
        } else {
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
            $html.='<svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Danger:"><use xlink:href="#exclamation-triangle-fill"/></svg><div>Sorry..This Part Number Is Not Available Stock...</div></div>';
        }
        return response()->json(['rc_msg'=>$rc_msg,'html'=>$html]);
    }
    public function ptsCleCoverFetchEntry(Request $request){
        // dd($request->all());
        $packingMasterDatas=PackingMaster::with('covermaster')->where('cus_type_name','=',$request->cus_type)->where('part_id','=',$request->invoice_part_id)->where('status','=',1)->first();
        // dd($packingMasterDatas);
        $html='<option value="'.$packingMasterDatas->covermaster->id.'" >'.$packingMasterDatas->covermaster->cover_name.' & '.$packingMasterDatas->covermaster->cover_size.'</option>';
        $cover_qty=$packingMasterDatas->cover_qty;
        $avl_qty=$request->avl_qty;
        $no_of_cover=round($avl_qty/$cover_qty);
        $cover_color=$packingMasterDatas->covermaster->cover_color;
        return response()->json(['cover_details'=>$html,'cover_qty'=>$cover_qty,'cover_color'=>$cover_color,'no_of_cover'=>$no_of_cover,'avl_qty'=>$avl_qty]);
    }

    public function ptsClePackingCoverFetchEntry(Request $request){
        // dd($request->all());
        $packingMasterDatas=PackingMaster::with('covermaster')->find($request->packing_master_id);
        // dd($packingMasterDatas);
        $cover_qty=$packingMasterDatas->cover_qty;
        $cover_color=$packingMasterDatas->covermaster->cover_color;
        return response()->json(['cover_qty'=>$cover_qty,'cover_color'=>$cover_color]);

    }

    public function ptsCleIssueEntry(Request $request){
        // dd($request->all());
        date_default_timezone_set('Asia/Kolkata');
        $current_date=date('Y-m-d');

        $current_processDatas=ProductProcessMaster::with('processMaster')->where('part_id','=',$request->part_id)->where('process_master_id','=',$request->process_id)->first();
        $current_process=$current_processDatas->processMaster->operation;

        // dd($current_processDatas);
        $current_process_order_id=$current_processDatas->process_order_id;
        $packingCount=PackingStrickerDetails::where('part_id','=',$request->part_id)->where('rc_id','=',$request->rc_no)->orderBy('id','DESC')->get()->count();
        // dd($packingCount);
        if ($packingCount>0) {
            $packingCheck=PackingStrickerDetails::where('part_id','=',$request->part_id)->where('rc_id','=',$request->rc_no)->orderBy('id','DESC')->first();
            $cover_order_id=$packingCheck->cover_order_id;
            $start=$cover_order_id+1;
            $end=(($request->no_of_cover)+($cover_order_id));
            for ($i=$start; $i <= $end; $i++) {
                # code...
                // dump($i);
                $packingStrickerDetails=new PackingStrickerDetails;
                $packingStrickerDetails->part_id=$request->part_id;
                $packingStrickerDetails->rc_id=$request->rc_no;
                $packingStrickerDetails->cover_order_id=$i;
                $packingStrickerDetails->cover_id=$request->cover_id;
                $packingStrickerDetails->cover_qty=$request->cover_qty;
                $packingStrickerDetails->total_cover_qty=$request->cover_qty;
                $packingStrickerDetails->cus_type_name=$request->cus_type;
                // $packingStrickerDetails->ok_packed_qty=$request->cover_qty;
                $packingStrickerDetails->prepared_by = auth()->user()->id;
                $packingStrickerDetails->save();
            }
        } else {
            $start=1;
            $end=$request->no_of_cover;
            for ($i=$start; $i <= $end; $i++) {
                # code...
                // dump($i);
                $packingStrickerDetails=new PackingStrickerDetails;
                $packingStrickerDetails->part_id=$request->part_id;
                $packingStrickerDetails->rc_id=$request->rc_no;
                $packingStrickerDetails->cover_order_id=$i;
                $packingStrickerDetails->cover_id=$request->cover_id;
                $packingStrickerDetails->cover_qty=$request->cover_qty;
                $packingStrickerDetails->total_cover_qty=$request->cover_qty;
                $packingStrickerDetails->cus_type_name=$request->cus_type;
                // $packingStrickerDetails->ok_packed_qty=$request->cover_qty;
                $packingStrickerDetails->prepared_by = auth()->user()->id;
                $packingStrickerDetails->save();

            }
        }

        // dd($request->no_of_cover);
        $next_processDatas=ProductProcessMaster::with('processMaster')->where('part_id','=',$request->part_id)->where('process_order_id','>',$current_process_order_id)->where('status','=',1)->first();
        // $next_product_process_id=$next_processDatas->id;
        // $next_process_id=$next_processDatas->process_master_id;
        // $next_process=$next_processDatas->processMaster->operation;
        // $next_process_order_id=$next_processDatas->process_order_id;
        // dd($next_processDatas);
        // dd($next_process);

        $ptsTransactionDetail=new PtsTransactionDetail;
        $ptsTransactionDetail->open_date=$current_date;
        $ptsTransactionDetail->part_id=$request->part_id;
        $ptsTransactionDetail->process_id=$request->process_id;
        $ptsTransactionDetail->process=$current_process;
        $ptsTransactionDetail->rc_id=$request->rc_no;
        $ptsTransactionDetail->previous_rc_id=$request->rc_no;
        $ptsTransactionDetail->issue_qty=$request->receive_qty;
        $ptsTransactionDetail->prepared_by = auth()->user()->id;
        $ptsTransactionDetail->save();

        $ptsTransactionSummary=PtsTransactionSummary::where('rc_id','=',$request->rc_no)->where('status','=',1)->first();
        // dd($ptsTransactionSummary);
        $current_process_id=$ptsTransactionSummary->process_id;
        $next_process_id=$ptsTransactionSummary->next_process_id;

        if (($current_process_id==3)&&($next_process_id==22)) {
            $old_receive_qty=$ptsTransactionSummary->cover_issue_qty;
            $current_receive_qty=$old_receive_qty+$request->receive_qty;
            $ptsTransactionSummary->cover_issue_qty=$current_receive_qty;
        }
        if ($current_process_id==17) {
            $old_receive_qty=$ptsTransactionSummary->cle_receive_qty;
            $current_receive_qty=$old_receive_qty+$request->receive_qty;
            $ptsTransactionSummary->cle_receive_qty=$current_receive_qty;
        }
        $ptsTransactionSummary->updated_by = auth()->user()->id;
        $ptsTransactionSummary->updated_at = Carbon::now();
        $ptsTransactionSummary->update();

        if ($current_process_id==17) {
            return redirect()->route('ptscleissue')->withSuccess('Successfully Part No Issue To CLE Inspection Team...!');
        }
        if (($current_process_id==3)&&($next_process_id==22)) {
            return redirect()->route('cncproductioncoverwiselist')->withSuccess('Successfully Part No Issued To Firewall Inspection Team...!');
        }
    }

    public function productionfirewallList(){
        // dd('ok');
        $cnc_production_datas=TransDataD11::where('process_id','=',3)->whereIn('next_process_id',[22])->where('rc_status','=',0)->get();
        // dd($cnc_production_datas);
        return view('stagewise-receive.productionfiewall_entry_view',compact('cnc_production_datas'));
    }
    public function productionfirewallEntry(){
        date_default_timezone_set('Asia/Kolkata');
        $current_date=date('Y-m-d');
        $d11Datas=TransDataD11::where('rc_status','=',1)->where('process_id','=',3)->whereIn('next_process_id',[16,22])->groupBy('id')->get();
        $partDatas=ChildProductMaster::where('status','=',1)->where('item_type','!=',0)->where('stocking_point','=',22)->get();
        $activity='Production Entry';
        $stage='CNC Area';
        $qrCodes_count=StageQrCodeLock::where('stage','=',$stage)->where('activity','=',$activity)->where('status','=',1)->count();
        return view('stagewise-receive.productionfiewall_entry_create2',compact('d11Datas','current_date','qrCodes_count','partDatas'));
    }

    public function partCoverwiseFetchData(Request $request){
        // dd($request->all());
        $id=$request->part_id;
        $invoicedatas=ChildProductMaster::find($id);
        $invoice_part_id=$invoicedatas->part_id;
        $customer_datas=DB::table('customer_product_masters as a')
        ->join('customer_masters AS b', 'a.cus_id', '=', 'b.id')
        ->select('a.*','b.*')
        ->where('a.part_id','=',$invoice_part_id)
        ->orderBy('a.id', 'ASC')
        ->groupBy('b.cus_type_name')
        ->get();
        // dd($customer_datas);
        $cover_data='<option value="" selected>Select The Customer</option>';
        foreach ($customer_datas as $key => $customer_data) {
            $cover_data.='<option value="'.$customer_data->cus_type_name.'" >'.$customer_data->cus_type_name.'</option>';
        }
        $check=ChildProductMaster::where('status','=',1)->where('part_id','=',$invoice_part_id)->count();
        $check1=ChildProductMaster::where('status','=',1)->where('part_id','=',$invoice_part_id)->where('item_type','=',1)->where('stocking_point','=',22)->count();
        $check3=ChildProductMaster::where('status','=',1)->where('part_id','=',$invoice_part_id)->where('item_type','=',1)->where('stocking_point','=',27)->count();
        $check2=ChildProductMaster::where('status','=',1)->where('part_id','=',$invoice_part_id)->where('item_type','=',0)->count();
        $manufacturingPartDatas=ChildProductMaster::where('status','=',1)->where('part_id','=',$invoice_part_id)->where('item_type','=',1)->where('stocking_point','=',27)->get();
        if ($check==1) {
            $count=$check1;
            $processcheck=TransDataD11::where('rc_status','=',1)->where('part_id','=',$id)->where('process_id','=',3)->whereIn('next_process_id',[16,22])->first();
            // dd($processcheck);
            if ($processcheck!="") {
                // dd('ok');
                $process_id=$processcheck->process_id;
                $process_msg=true;
                $avl_check=TransDataD11::where('rc_status','=',1)->where('part_id','=',$id)->where('process_id','=',3)->whereIn('next_process_id',[16,22])->select(DB::raw('(SUM(process_issue_qty)-SUM(receive_qty)) as t_avl_qty'))
                ->havingRaw('t_avl_qty >?', [0])->first();
                if ($avl_check!="") {
                    $avl_msg=true;
                    $avl_kg=$avl_check->t_avl_qty;
                    $bom=BomMaster::where('child_part_id','=',$id)->sum('output_usage');
                    $avl_qty=round(($avl_kg)/($bom));
                    $html="";
                } else {
                    $avl_msg=false;
                    $avl_qty=0;
                    $avl_kg=0;
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
                    $html.='<svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Danger:"><use xlink:href="#exclamation-triangle-fill"/></svg><div>Sorry This Part Number is not Available..</div></div>';
                }
            } else {
                $process_msg=false;
                $avl_msg=false;
                $avl_qty=0;
                $avl_kg=0;
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
                $html.='<svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Danger:"><use xlink:href="#exclamation-triangle-fill"/></svg><div>Sorry This Part Number Process is not directly move to FG.Please Check The Part Process Master..</div></div>';
            }
        }elseif ($check>1) {
            $count=$check3;
            foreach ($manufacturingPartDatas as $key => $manufacturingPartData) {
                $processcheck=TransDataD11::where('rc_status','=',1)->where('part_id','=',$manufacturingPartData->id)->where('process_id','=',3)->whereIn('next_process_id',[27])->first();
                if ($processcheck!="") {
                    // dd('ok');
                    $process_id=$processcheck->process_id;
                    $process_msg=true;
                } else {
                    $process_msg=false;
                    $avl_msg=false;
                    $avl_qty=0;
                    $avl_kg=0;
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
                    $html.='<svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Danger:"><use xlink:href="#exclamation-triangle-fill"/></svg><div>Sorry This Part Number Process is not directly move to FG.Please Check The Part Process Master..</div></div>';
                }
            }

            $operation_id='27';
            $dcmasterDatas2=DB::table('product_masters as b')
            ->join('child_product_masters AS c', 'c.part_id', '=', 'b.id')
            ->join('trans_data_d11_s AS d', 'd.part_id', '=', 'c.id')
            ->join('route_masters AS e', 'd.rc_id', '=', 'e.id')
            ->join('bom_masters AS f', 'f.child_part_id', '=', 'c.id')
            // ->select(DB::raw('((receive_qty)-(issue_qty)) as t_avl_qty'))
            ->select('c.part_id',DB::raw('(((SUM(process_issue_qty)-SUM(receive_qty)))/(f.output_usage)) as t_avl_qty'))
            ->where('b.id','=',$invoice_part_id)
            ->where('c.stocking_point','=',$operation_id)
            ->where('d.next_process_id','=',$operation_id)
            ->where('c.item_type','=',1)
            ->where('c.status','=',1)
            ->where('f.status','=',1)
            ->havingRaw('t_avl_qty >?', [0])
            ->orderBy('c.no_item_id', 'ASC')
            ->orderBy('e.id', 'ASC')
            ->groupBy('c.id')
            ->min('t_avl_qty');
            $avl_qty=round($dcmasterDatas2);
            if ($avl_qty>0) {
                $bom=DB::table('product_masters as b')
                ->join('child_product_masters AS c', 'c.part_id', '=', 'b.id')
                ->join('bom_masters AS f', 'f.child_part_id', '=', 'c.id')
                // ->select(DB::raw('((receive_qty)-(issue_qty)) as t_avl_qty'))
                ->select('c.part_id',DB::raw('(SUM(output_usage)) as bom'))
                ->where('b.id','=',$invoice_part_id)
                ->where('c.stocking_point','=',$operation_id)
                ->where('c.item_type','=',1)
                ->where('c.status','=',1)
                ->where('f.status','=',1)
                ->havingRaw('bom >?', [0])
                ->orderBy('c.no_item_id', 'ASC')
                ->groupBy('c.id')
                ->max('bom');
                $avl_kg=$bom*$dcmasterDatas2;
                $avl_msg=true;
                $html='';
            } else {
                $avl_msg=false;
                $avl_kg=0;
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
                $html.='<svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Danger:"><use xlink:href="#exclamation-triangle-fill"/></svg><div>Sorry This Part Number is not Available..</div></div>';
            }
            // dd($avl_qty);
            // dd($avl_kg);
        } else {
            $count=$check1;
            $process_msg=false;
            $avl_msg=false;
            $avl_qty=0;
            $avl_kg=0;
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
            $html.='<svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Danger:"><use xlink:href="#exclamation-triangle-fill"/></svg><div>Sorry This Part Number Process is not linked with Master Data.Please Contact to Admin..</div></div>';
        }

        return response()->json(['count'=>$count,'cover_data'=>$cover_data,'process_msg'=>$process_msg,'avl_msg'=>$avl_msg,'avl_kg'=>$avl_kg,'avl_qty'=>$avl_qty,'html'=>$html,'bom'=>$bom,'invoice_part_id'=>$invoice_part_id]);

    }

    public function partCoverwiseQtyFetchData(Request $request){
        // dd($request->all());
        $cus_type_id=$request->cus_type_id;
        $invoice_part_id=$request->invoice_part_id;
        $avl_qty=$request->avl_qty;
        $bom=$request->bom;
        $cover_datas=PackingMaster::where('status','=',1)->where('cus_type_name','=',$cus_type_id)->where('part_id','=',$invoice_part_id)->groupBy('cover_qty')->select('cover_qty')->first();
        $cover_qty=$cover_datas->cover_qty;
        // dd($cover_qty);
        $no_of_cover=floor($avl_qty/$cover_qty);
        $max_qty=$no_of_cover*$cover_qty;
        $max_kg=round((($max_qty)*($bom)),2);
        return response()->json(['cover_qty'=>$cover_qty,'no_of_cover'=>$no_of_cover,'max_qty'=>$max_qty,'max_kg'=>$max_kg]);
    }

    public function partCoverwiseRcFetchData(Request $request){
        // dd($request->all());
        $rcpart_count=$request->rcpart_count;
        $req_kg=$request->req_kg;
        $part_id=$request->part_id;
        $invoice_part_id=$request->invoice_part_id;
        $bom=$request->bom;
        $req_qty=$request->req_qty;
        if ($rcpart_count==1) {
            $avl_checks=TransDataD11::where('rc_status','=',1)->where('part_id','=',$part_id)->where('process_id','=',3)->whereIn('next_process_id',[16,22])->select('*',DB::raw('((process_issue_qty)-(receive_qty)) as t_avl_qty'))
            ->havingRaw('t_avl_qty >?', [0])->get();
            // dd($avl_checks);
            if ($avl_checks!='') {
                $count=1;
                $html = view('stagewise-receive.add_item',compact('avl_checks','bom','count','req_qty'))->render();
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
                $html.='<svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Danger:"><use xlink:href="#exclamation-triangle-fill"/></svg><div>Sorry This Part No is not available..</div></div>';
            }
            return response()->json(['count'=>$count,'html'=>$html]);
        }elseif ($rcpart_count>1) {
            # code...
        }
         else {
            # code...
        }

    }

    public function productionfirewallFetchEntry(Request $request){
        $rc_id=$request->stricker_id;
        $process_check=TransDataD11::where('process_id','=',3)->whereIn('next_process_id',[16,22])->where('rc_id','=',$rc_id)->groupBy('id')->get()->count();
        if ($process_check==0) {
            $process_html='<svg xmlns="http://www.w3.org/2000/svg" style="display: none;">
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
            $process_html.='<svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Danger:"><use xlink:href="#exclamation-triangle-fill"/></svg><div>Sorry this part number is not direct move in FG Area And Please Check The Part Number Process..</div></div>';
        } else {
            $process_html='';
            $d11Datas=TransDataD11::where('rc_id','=',$rc_id)->first();
            $rc_status=$d11Datas->rc_status;
            if ($rc_status==0) {
                $rc_html='<svg xmlns="http://www.w3.org/2000/svg" style="display: none;">
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
                    $rc_html.='<svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Danger:"><use xlink:href="#exclamation-triangle-fill"/></svg><div>Sorry this Route Card is closed and try another route card no..</div></div>';
            } else {
                $rc_html='';
                $part_no='<option value="'.$d11Datas->partmaster->id.'">'.$d11Datas->partmaster->child_part_no.'</option>';
                $rc_no='<option value="'.$d11Datas->rcmaster->id.'">'.$d11Datas->rcmaster->rc_id.'</option>';
                $current_process='<option value="'.$d11Datas->currentprocessmaster->id.'">'.$d11Datas->currentprocessmaster->operation.'</option>';
                $next_process='<option value="'.$d11Datas->nextprocessmaster->id.'">'.$d11Datas->nextprocessmaster->operation.'</option>';
                $previous_process_issue_qty=$d11Datas->process_issue_qty;
                $current_process_id=$d11Datas->currentprocessmaster->id;
                $current_product_process_id=$d11Datas->product_process_id;
                $part_id=$d11Datas->part_id;
                $next_process_id=$d11Datas->nextprocessmaster->id;
                $next_product_process_id=$d11Datas->next_product_process_id;
                $bomDatas=BomMaster::where('child_part_id','=',$d11Datas->partmaster->id)->sum('manual_usage');
                if ($current_process_id==3) {
                    $process_issue_qty=floor(($previous_process_issue_qty/$bomDatas));
                }else{
                    $process_issue_qty=$d11Datas->process_issue_qty;
                }
                if ($next_process_id==22) {
                    $produced_count=PtsTransactionSummary::where('rc_id','=',$rc_id)->get()->count();
                    if ($produced_count>0) {
                        $ptsTransactionSummaryDatas=PtsTransactionSummary::where('rc_id','=',$rc_id)->select(DB::raw('sum(cnc_ok_qty+cnc_rej_qty+cnc_rework_qty+qc_pending_qty) as production_qty'))->first();
                        $produced_qty=$ptsTransactionSummaryDatas->production_qty;
                    } else {
                        $produced_qty=0;
                    }
                    $childProductDatas=ChildProductMaster::with('invoicepart')->where('id','=',$part_id)->where('stocking_point','=',22)->first();
                    $invoice_part_no=$childProductDatas->invoicepart->id;
                    // $customer_datas=CustomerProductMaster::with('productmasters','customermaster')->where('part_id','=',$invoice_part_no)->get();
                    // $customer_datas=CustomerProductMaster::with('productmasters','customermaster')->WhereHas('customermaster', function ($q) {
                    //     $q->groupBy('cus_type_name');
                    // })->where('part_id','=',$invoice_part_no)->orderBy('id', 'DESC')->get();
                    $customer_datas=DB::table('customer_product_masters as a')
                    ->join('customer_masters AS b', 'a.cus_id', '=', 'b.id')
                    ->select('a.*','b.*')
                    ->where('a.part_id','=',$invoice_part_no)
                    ->orderBy('a.id', 'ASC')
                    ->groupBy('b.cus_type_name')
                    ->get();
                    // dd($customer_datas);
                    $html='<option value="" selected>Select The Customer</option>';
                    foreach ($customer_datas as $key => $customer_data) {
                        $html.='<option value="'.$customer_data->cus_type_name.'" >'.$customer_data->cus_type_name.'</option>';
                    }
                    $packingMasterDatas=PackingMaster::with('covermaster')->where('part_id','=',$invoice_part_no)->where('status','=',1)->groupBy('cover_qty')->get();
                    // dd($packingMasterDatas);
                    $bomDatas=BomMaster::where('child_part_id','=',$part_id)->where('status','=',1)->sum('finish_usage');
                }
                $avl_qty=(($process_issue_qty)-($produced_qty));
                $fqc_count=1;
            }
        }
        return response()->json(['process_check'=>$process_check,'process_html'=>$process_html,'rc_status'=>$rc_status,'rc_html'=>$rc_html,'part_no'=>$part_no,'rc_no'=>$rc_no,'current_process'=>$current_process,'next_process'=>$next_process,'current_product_process_id'=>$current_product_process_id,'next_product_process_id'=>$next_product_process_id,'avl_qty'=>$avl_qty,'fqc_count'=>$fqc_count,'rc_id'=>$rc_id,'customer'=>$html,'invoice_part_id'=>$invoice_part_no]);
    }
    public function productionfirewallCoverFetchEntry(Request $request){
        $packingMasterDatas=PackingMaster::with('covermaster')->where('cus_type_name','=',$request->cus_type)->where('part_id','=',$request->invoice_part_id)->where('status','=',1)->first();
        // dd($packingMasterDatas);
        $html='<option value="'.$packingMasterDatas->covermaster->id.'" >'.$packingMasterDatas->covermaster->cover_name.' & '.$packingMasterDatas->covermaster->cover_size.'</option>';
        $cover_qty=$packingMasterDatas->cover_qty;
        $avl_qty=$request->avl_qty;
        $inspected_qty=$request->inspected_qty;
        $no_of_cover=ceil($inspected_qty/$cover_qty);
        $no_of_cover2=floor($inspected_qty/$cover_qty);

        $cover_color=$packingMasterDatas->covermaster->cover_color;
        $cleMasterDatas=FirewallInspectionDetails::where('unit_name','=','VSS UNIT-4')->where('inspection_area','=','CLE')->where('status','!=',0)->get();
        $table='<div class="table-responsive mt-3"><table class="table table-bordered  table-striped">
        <thead>
        <tr>
            <td><b>ID</b></td>
            <td><b>INSPECTOR NAME</b></td>
            <td><b>INSPECTED QTY</b></td>
        </tr>
        </thead><tbody>';
        for ($i=1; $i <= $no_of_cover; $i++) {
            $x=$i;
            $table.='<tr>
            <td><input type="number" class="form-control" name="id['.$x.']" id="id" value="'.$x.'"></td>';
            $table.='<td><select name="inspected['.$x.']" class="form-control" id="inspected">';
            foreach ($cleMasterDatas as $key => $cleMasterData) {
                $table.='<option value="'.$cleMasterData->id.'">'.$cleMasterData->name.'</option>';
            }
            $table.='</select></td>';
            if ($no_of_cover==$no_of_cover2) {
                $table.='<td><input type="number" class="form-control" name="checking_qty['.$x.']" id="checking_qty" value="'.$cover_qty.'"></td>';
            }else{
                if ($no_of_cover==($x)) {
                    $remain_qty=(($inspected_qty)-(($cover_qty)*($no_of_cover2)));
                    $table.='<td><input type="number" class="form-control" name="checking_qty['.$x.']" id="checking_qty" value="'.$remain_qty.'"></td>';
                } else {
                    $table.='<td><input type="number" class="form-control" name="checking_qty['.$x.']" id="checking_qty" value="'.$cover_qty.'"></td>';
                }

            }
            $table.='</tr>';
        }
        $table.='</tbody></table></div>';

        return response()->json(['cover_details'=>$html,'cover_qty'=>$cover_qty,'cover_color'=>$cover_color,'no_of_cover'=>$no_of_cover,'avl_qty'=>$avl_qty,'table'=>$table]);
    }
    public function productionCoverwiseList(){
        // dd('ok');
        $cnc_production_datas=PtsTransactionSummary::where('process_id','=',3)->whereIn('next_process_id',[22])->get();
        return view('stagewise-receive.production_entry_view',compact('cnc_production_datas'));
    }

    public function productionCoverwiseEntry(){
        date_default_timezone_set('Asia/Kolkata');
        $current_date=date('Y-m-d');
        $d11Datas=TransDataD11::where('rc_status','=',1)->where('process_id','=',3)->whereIn('next_process_id',[16,22])->groupBy('id')->get();
        $activity='Production Entry';
        $stage='CNC Area';
        $qrCodes_count=StageQrCodeLock::where('stage','=',$stage)->where('activity','=',$activity)->where('status','=',1)->count();
        return view('stagewise-receive.production_entry_create',compact('d11Datas','current_date','qrCodes_count'));
    }

    public function productionCoverwiseFetchEntry(Request $request){
        // dd($request->all());
        $rc_id=$request->stricker_id;
        $process_check=TransDataD11::where('process_id','=',3)->whereIn('next_process_id',[16,22])->where('rc_id','=',$rc_id)->groupBy('id')->get()->count();
        if ($process_check==0) {
            $process_html='<svg xmlns="http://www.w3.org/2000/svg" style="display: none;">
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
            $process_html.='<svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Danger:"><use xlink:href="#exclamation-triangle-fill"/></svg><div>Sorry this part number is not direct move in FG Area And Please Check The Part Number Process..</div></div>';
        } else {
            $process_html='';
            $d11Datas=TransDataD11::where('rc_id','=',$rc_id)->first();
            $rc_status=$d11Datas->rc_status;
            if ($rc_status==0) {
                $rc_html='<svg xmlns="http://www.w3.org/2000/svg" style="display: none;">
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
                    $rc_html.='<svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Danger:"><use xlink:href="#exclamation-triangle-fill"/></svg><div>Sorry this Route Card is closed and try another route card no..</div></div>';
            } else {
                $rc_html='';
                $part_no='<option value="'.$d11Datas->partmaster->id.'">'.$d11Datas->partmaster->child_part_no.'</option>';
                $rc_no='<option value="'.$d11Datas->rcmaster->id.'">'.$d11Datas->rcmaster->rc_id.'</option>';
                $current_process='<option value="'.$d11Datas->currentprocessmaster->id.'">'.$d11Datas->currentprocessmaster->operation.'</option>';
                $next_process='<option value="'.$d11Datas->nextprocessmaster->id.'">'.$d11Datas->nextprocessmaster->operation.'</option>';
                $previous_process_issue_qty=$d11Datas->process_issue_qty;
                $current_process_id=$d11Datas->currentprocessmaster->id;
                $current_product_process_id=$d11Datas->product_process_id;
                $next_process_id=$d11Datas->nextprocessmaster->id;
                $next_product_process_id=$d11Datas->next_product_process_id;
                $bomDatas=BomMaster::where('child_part_id','=',$d11Datas->partmaster->id)->sum('manual_usage');
                if ($current_process_id==3) {
                    $process_issue_qty=floor(($previous_process_issue_qty/$bomDatas));
                }else{
                    $process_issue_qty=$d11Datas->process_issue_qty;
                }
                if ($next_process_id==22) {
                    $produced_count=PtsTransactionSummary::where('rc_id','=',$rc_id)->get()->count();
                    if ($produced_count>0) {
                        $ptsTransactionSummaryDatas=PtsTransactionSummary::where('rc_id','=',$rc_id)->select(DB::raw('sum(cnc_ok_qty+cnc_rej_qty+cnc_rework_qty+qc_pending_qty) as production_qty'))->first();
                        $produced_qty=$ptsTransactionSummaryDatas->production_qty;
                    } else {
                        $produced_qty=0;
                    }
                }
                $avl_qty=(($process_issue_qty)-($produced_qty));
                $fqc_count=1;
            }
        }
        return response()->json(['process_check'=>$process_check,'process_html'=>$process_html,'rc_status'=>$rc_status,'rc_html'=>$rc_html,'part_no'=>$part_no,'rc_no'=>$rc_no,'current_process'=>$current_process,'next_process'=>$next_process,'current_product_process_id'=>$current_product_process_id,'next_product_process_id'=>$next_product_process_id,'avl_qty'=>$avl_qty,'fqc_count'=>$fqc_count,'rc_id'=>$rc_id]);
    }

    public function cncProductionFqcApproval(){
        $fqcDatas=FinalQcInspection::with(['current_rcmaster','previous_rcmaster','partmaster','currentprocessmaster','nextprocessmaster','inspector_usermaster'])->where('status','=',0)->whereIn('next_process_id',[22])->whereIn('process_id',[3])->orderBy('id','DESC')->get();
        // dd($fqcDatas);
        return view('fqc_inspection.cnc_fqc_view',compact('fqcDatas'));
    }
    public function productionCoverwiseStore(Request $request){
        // dd($request->all());
        DB::beginTransaction();
        try {
            $qrcodes_count=$request->qrcodes_count;
            if ($qrcodes_count==0) {
                $rc_card_id=$request->rc_no;
            } else {
                $rc_card_id=$request->qr_rc_id;
            }
            $receive_qty=$request->receive_qty;
            $avl_qty=$request->avl_qty;
            $count=1;
            if ($receive_qty<=$avl_qty) {
                $fqcInspectionData=new FinalQcInspection;
                $fqcInspectionData->offer_date=$request->rc_date;
                $fqcInspectionData->rc_id=$rc_card_id;
                $fqcInspectionData->previous_rc_id=$rc_card_id;
                $fqcInspectionData->part_id=$request->part_id;
                $fqcInspectionData->process_id=$request->current_process;
                $fqcInspectionData->product_process_id=$request->previous_product_process_id;
                $fqcInspectionData->next_process_id=$request->next_process_id;
                $fqcInspectionData->next_product_process_id=$request->next_productprocess_id;
                $fqcInspectionData->offer_qty=$request->receive_qty;
                if($request->rc_close=="yes"){
                $fqcInspectionData->rc_status=0;
                }else{
                $fqcInspectionData->rc_status=1;
                }
                $fqcInspectionData->prepared_by = auth()->user()->id;
                $fqcInspectionData->save();

                $d11Datas=TransDataD11::where('process_id','=',$request->current_process)->where('product_process_id','=',$request->previous_product_process_id)->where('rc_id','=',$rc_card_id)->first();
                if($request->rc_close=="yes"){
                    $d11Datas->close_date=$request->rc_date;
                    $d11Datas->status=0;
                }
                $d11Datas->updated_by = auth()->user()->id;
                $d11Datas->updated_at = Carbon::now();
                $d11Datas->update();

                $pts_count=PtsTransactionSummary::where('rc_id','=',$rc_card_id)->get()->count();
                if ($pts_count>0) {
                    $ptsTransactionSummary=PtsTransactionSummary::where('rc_id','=',$rc_card_id)->first();
                    $old_qty=$ptsTransactionSummary->qc_pending_qty;
                    $ptsTransactionSummary->qc_pending_qty=(($request->receive_qty)+($old_qty));
                    $ptsTransactionSummary->updated_at = Carbon::now();
                    $ptsTransactionSummary->update();
                } else {
                    $ptsTransactionSummary=new PtsTransactionSummary;
                    $ptsTransactionSummary->open_date=$request->rc_date;
                    $ptsTransactionSummary->part_id=$request->part_id;
                    $ptsTransactionSummary->process_id=$request->current_process;
                    $ptsTransactionSummary->process=$d11Datas->currentprocessmaster->operation;
                    $ptsTransactionSummary->next_process_id=$request->next_process_id;
                    $ptsTransactionSummary->next_process=$d11Datas->nextprocessmaster->operation;
                    $ptsTransactionSummary->rc_id=$rc_card_id;
                    $ptsTransactionSummary->qc_pending_qty=$request->receive_qty;
                    $ptsTransactionSummary->prepared_by=auth()->user()->id;
                    $ptsTransactionSummary->save();
                }

                DB::commit();
                return redirect()->route('cncproductionfqc')->withSuccess('Part Received is Successfully And Waiting For Final Quality Inspection!');
            }else {
                return redirect()->route('productioncoverwiselist')->withMessage('Please Check Your Available Quantity & You Enter More Available Quantity!');
            }
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollback();
            dd($th->getMessage());
            return redirect()->back()->withErrors($th->getMessage());
        }
    }

    public function  cncProductionCoverwiselist(){
        //    $PtsTransactionSummary=PtsTransactionSummary::where('process_id','=',3)->whereIn('next_process_id',[22])->select('rc_id',DB::raw('((cnc_ok_qty)-(cover_issue_qty)) as avl_qty'))->havingRaw('avl_qty >?', [0])->groupBy('rc_id')->get();
        //    dd($PtsTransactionSummary);
        //    date_default_timezone_set('Asia/Kolkata');
        //    $current_date=date('Y-m-d');
        //    $ptsDatas=PtsTransactionSummary::with('partmaster')->select('part_id')
        //    ->groupBy('part_id')->get();
        //    return view('stagewise-receive.cnc_firewall_create',compact('ptsDatas','current_date'));
       $ptsStockDatas=PtsTransactionDetail::with('rcmaster','previous_rcmaster','partmaster','currentprocessmaster','prepareuserdetails')->where('process_id','=',22)->where('issue_qty','!=',0)->where('receive_qty','=',0)->where('reject_qty','=',0)->where('rework_qty','=',0)->where('return_issue_qty','=',0)->orderBy('id','DESC')->get();
       // dd($ptsStockDatas);
       return view('stagewise-receive.cnc_firewall_view',compact('ptsStockDatas'));
        //    return view('stagewise-receive.pts_cle_create',compact('ptsDatas','current_date'));
    }

    public function cncproductioncoverwisecreate(){
        date_default_timezone_set('Asia/Kolkata');
        $current_date=date('Y-m-d');
        $ptsDatas=PtsTransactionSummary::with('partmaster')->where('process_id','=',3)->whereIn('next_process_id',[22])->select('part_id')
        ->groupBy('part_id')->get();
        return view('stagewise-receive.cnc_firewall_create',compact('ptsDatas','current_date'));
    }

    public function cncProductionCoverwiseRcFetchEntry(Request $request){
        // dd($request->all());
        $count=PtsTransactionSummary::with('rcmaster')->selectRaw('rc_id,((cnc_ok_qty)-((cover_issue_qty))) as avl_qty')
        ->where('part_id','=',$request->part_id)->havingRaw('avl_qty >?', [0])->groupBy('rc_id')->count();
        if ($count>0) {
            $rc_msg=true;
            $ptsDatas=PtsTransactionSummary::with('rcmaster')->selectRaw('rc_id,((cnc_ok_qty)-((cover_issue_qty))) as avl_qty')
            ->where('part_id','=',$request->part_id)->havingRaw('avl_qty >?', [0])->groupBy('rc_id')->get();
            // dd($ptsDatas);
            $html='<option value="">Select The RC Number</option>';
            foreach ($ptsDatas as $key => $ptsData) {
                $html.='<option value="'.$ptsData->rcmaster->id.'">'.$ptsData->rcmaster->rc_id.'</option>';
            }
        } else {
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
            $html.='<svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Danger:"><use xlink:href="#exclamation-triangle-fill"/></svg><div>Sorry..This Part Number Is Not Available Stock...</div></div>';
        }
        return response()->json(['rc_msg'=>$rc_msg,'html'=>$html]);
    }

    public function cncProductionCoverwisePartFetchEntry(Request $request){
        // dd($request->all());
        $rc_no=$request->rc_no;
        $part_id=$request->part_id;
        $ptsDatas=PtsTransactionSummary::with('partmaster')->select('part_id','process_id',DB::raw('((cnc_ok_qty)-(cover_issue_qty)) as avl_qty'))
        ->where('rc_id','=',$rc_no)->where('part_id','=',$part_id)->havingRaw('avl_qty >?', [0])->first();
        // dd($rc_no);
        $fifoCheck=PtsTransactionSummary::select('rc_id',DB::raw('((cnc_ok_qty)-(cover_issue_qty)) as avl_qty'))
        ->where('part_id','=',$part_id)->havingRaw('avl_qty >?', [0])->first();
        // dd($fifoCheck->nextprocessmaster->id);
        $fifoRcNo=$fifoCheck->rc_id;
        if ($rc_no==$fifoRcNo) {
            $success = true;
            $partNo='<option value="'.$ptsDatas->partmaster->id.'" selected>'.$ptsDatas->partmaster->child_part_no.'</option>';
            $avl_qty=$ptsDatas->avl_qty;
            $process='<option value="22" selected>FG For Invoicing</option>';
            // $process='<option value="'. $fifoCheck->nextprocessmaster->id.'" selected>'. $fifoCheck->nextprocessmaster->operation.'</option>';
        }else {
            $success = false;
            $partNo='<option value="" selected></option>';
            $avl_qty=0;
            $process='<option value="22" selected>FG For Invoicing</option>';
        }
        $childProductDatas=ChildProductMaster::with('invoicepart')->where('id','=',$part_id)->where('stocking_point','=',22)->first();
        $invoice_part_no=$childProductDatas->invoicepart->id;
        // dd($invoice_part_no);
        // $customer_datas=CustomerProductMaster::with('productmasters','customermaster')->where('part_id','=',$invoice_part_no)->get();
        // $customer_datas=CustomerProductMaster::with('productmasters','customermaster')->WhereHas('customermaster', function ($q) {
        //     $q->groupBy('cus_type_name');
        // })->where('part_id','=',$invoice_part_no)->orderBy('id', 'DESC')->get();
        $customer_datas=DB::table('customer_product_masters as a')
        ->join('customer_masters AS b', 'a.cus_id', '=', 'b.id')
        ->select('a.*','b.*')
        ->where('a.part_id','=',$invoice_part_no)
        ->orderBy('a.id', 'ASC')
        ->groupBy('b.cus_type_name')
        ->get();
        // dd($customer_datas);
        $html='<option value="" selected>Select The Customer</option>';
        foreach ($customer_datas as $key => $customer_data) {
            $html.='<option value="'.$customer_data->cus_type_name.'" >'.$customer_data->cus_type_name.'</option>';
        }
        $packingMasterDatas=PackingMaster::with('covermaster')->where('part_id','=',$invoice_part_no)->where('status','=',1)->groupBy('cover_qty')->get();
        // dd($packingMasterDatas);
        $bomDatas=BomMaster::where('child_part_id','=',$part_id)->where('status','=',1)->sum('finish_usage');

         $process_check=ProductProcessMaster::where('process_master_id','=',$ptsDatas->process_id)->where('part_id','=',$part_id)->where('status','=',1)->orderBy('id', 'ASC')->count();
        //  dd($process_check);
         if($process_check==0){
             $message=false;
         }else{
             $message=true;
         }

        return response()->json(['success'=>$success,'fifoRcNo'=>$fifoRcNo,'part'=>$partNo,'message'=>$message,'process'=>$process,'avl_qty'=>$avl_qty,'bom'=>$bomDatas,'customer'=>$html,'invoice_part_id'=>$invoice_part_no]);
    }

    public function ptsClePartIssueQrCode($id){
        // dd($id);
        $packingStrickerDetails=PackingStrickerDetails::with('rcmaster','partmaster','prepareuserdetails','inspectedby')->where('rc_id','=',$id)->get();
        $PtsTransactionSummary=PtsTransactionSummary::where('rc_id','=',$id)->first();
        $c_process_id=$PtsTransactionSummary->process_id;
        $n_process_id=$PtsTransactionSummary->next_process_id;
        // dd($packingStrickerDetails);
        $html = view('stagewise-receive.ptscleissue_qrcodeprint2',compact('packingStrickerDetails','c_process_id'))->render();
        $width=75;$height=100;
        $pdf=Browsershot::html($html)->setIncludePath(config('services.browsershot.include_path'))->paperSize($width, $height)->landscape()->pdf();
        // $pdf=Browsershot::html($html)->setIncludePath(config('services.browsershot.include_path'))->paperSize($width, $height)->landscape()->pdf();
        return new Response($pdf,200,[
            'Content-Type'=>'application/pdf',
            'Content-Disposition'=>'inline;filename="ptscleissueqrcode.pdf"'
        ]);
        // $html = view('stagewise-receive.ptscleissue_qrcodeprint2',compact('packingStrickerDetails'))->render();
        // $width=75;$height=50;
        // $pdf=Browsershot::html($html)->setIncludePath(config('services.browsershot.include_path'))->paperSize($width, $height)->pdf();
        // return new Response($pdf,200,[
        //     'Content-Type'=>'application/pdf',
        //     'Content-Disposition'=>'inline;filename="ptscleissueqrcode.pdf"'
        // ]);
        // dd($packingStrickerDetails);
    }

    public function cncProductionFirewallReceiveList(){
        $ptsStockDatas=PtsTransactionDetail::with('rcmaster','previous_rcmaster','partmaster','currentprocessmaster','prepareuserdetails','strickermaster')->where('process_id','=',22)->where('issue_qty','=',0)->where('stricker_id','!=',0)->orderBy('id','DESC')->get();
        // dd($ptsStockDatas);
        return view('stagewise-receive.cncproduction_firewall_receive_view',compact('ptsStockDatas'));
    }
    public function cncProductionFirewallReceiveCreateForm(){
        date_default_timezone_set('Asia/Kolkata');
        $current_date=date('Y-m-d');
        $ptsDatas=PtsTransactionSummary::with('partmaster')->select('part_id')->where('process_id','=',3)
        ->groupBy('part_id')->get();
        // $ptsDatas=PtsTransactionSummary::with('rcmaster')->select('rc_id',DB::raw('((cle_receive_qty)-(cle_issue_qty)-(cle_reject_qty)-(cle_rework_qty)-(cle_return_qty)) as avl_qty'))
        // ->havingRaw('avl_qty >?', [0])->groupBy('rc_id')->get();
        // $d11Datas=TransDataD11::whereIn('process_id',[18])->where('status','=',1)->get();
        return view('stagewise-receive.cncproduction_firewall_receive_create',compact('ptsDatas','current_date'));
    }

    public function cncProductionFirewallRcFetchEntry(Request $request){
        // dd($request->all());
        // PtsTransactionSummary::with('partmaster')->select('part_id','process_id','cover_issue_qty','cle_issue_qty','cle_reject_qty','cle_rework_qty',DB::raw('((cover_issue_qty)-(cle_issue_qty)-(cle_reject_qty)-(cle_rework_qty)-(cle_return_qty)) as avl_qty'))
        // ->where('rc_id','=',$rc_no)->where('part_id','=',$part_id)->havingRaw('avl_qty >?', [0])->first();
        $count=PtsTransactionSummary::with('rcmaster')->selectRaw('rc_id,((cover_issue_qty)-(cle_issue_qty)-(cle_reject_qty)-(cle_rework_qty)-(cle_return_qty)) as avl_qty')
        ->where('part_id','=',$request->part_id)->havingRaw('avl_qty >?', [0])->groupBy('rc_id')->count();
        if ($count>0) {
            $rc_msg=true;
            $ptsDatas=PtsTransactionSummary::with('rcmaster')->selectRaw('rc_id,((cover_issue_qty)-(cle_issue_qty)-(cle_reject_qty)-(cle_rework_qty)-(cle_return_qty)) as avl_qty')
            ->where('part_id','=',$request->part_id)->havingRaw('avl_qty >?', [0])->groupBy('rc_id')->get();
            // dd($ptsDatas);
            $html='<option value="">Select The RC Number</option>';
            foreach ($ptsDatas as $key => $ptsData) {
                $html.='<option value="'.$ptsData->rcmaster->id.'">'.$ptsData->rcmaster->rc_id.'</option>';
            }
        } else {
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
            $html.='<svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Danger:"><use xlink:href="#exclamation-triangle-fill"/></svg><div>Sorry..This Part Number Is Not Available Stock...</div></div>';
        }
        return response()->json(['rc_msg'=>$rc_msg,'html'=>$html]);
    }

    public function cncProductionFirewallReceivePartFetchEntry(Request $request){
        // dd($request->all());
        $rc_no=$request->rc_no;
        $part_id=$request->part_id;
        $ptsDatas=PtsTransactionSummary::with('partmaster')->select('part_id','process_id','cover_issue_qty as cle_receive_qty','cle_issue_qty','cle_reject_qty','cle_rework_qty',DB::raw('((cover_issue_qty)-(cle_issue_qty)-(cle_reject_qty)-(cle_rework_qty)-(cle_return_qty)) as avl_qty'))
        ->where('rc_id','=',$rc_no)->where('part_id','=',$part_id)->havingRaw('avl_qty >?', [0])->first();
        // dd($ptsDatas);
        $total_issue_qty=$ptsDatas->cle_receive_qty;
        $total_receive_qty=$ptsDatas->cle_issue_qty;
        $total_reject_qty=$ptsDatas->cle_reject_qty;
        $total_rework_qty=$ptsDatas->cle_rework_qty;

        $fifoCheck=PtsTransactionSummary::with('rcmaster')->select('rc_id',DB::raw('((cover_issue_qty)-(cle_issue_qty)-(cle_reject_qty)-(cle_rework_qty)-(cle_return_qty)) as avl_qty'))
        ->where('part_id','=',$part_id)->havingRaw('avl_qty >?', [0])->first();
        $fifoRcNo=$fifoCheck->rc_id;
        $fifoRcCard=$fifoCheck->rcmaster->rc_id;

        if ($rc_no==$fifoRcNo) {
            $success = true;
            $partNo='<option value="'.$ptsDatas->partmaster->id.'" selected>'.$ptsDatas->partmaster->child_part_no.'</option>';
            $avl_qty=$ptsDatas->avl_qty;
            $process='<option value="22" selected>FG For Invoicing</option>';
            $fifo_html='';
        }else {
            $success = false;
            $partNo='<option value="" selected></option>';
            $avl_qty=0;
            $process='<option value="22" selected>FG For Invoicing</option>';
            $fifo_html='<svg xmlns="http://www.w3.org/2000/svg" style="display: none;">
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
            $fifo_html.='<svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Danger:"><use xlink:href="#exclamation-triangle-fill"/></svg><div>Sorry..Please Follow The FIFO And FIFO Route Card Is '.$fifoRcCard.'</div></div>';
        }

        $bomDatas=BomMaster::where('child_part_id','=',$part_id)->where('status','=',1)->sum('finish_usage');

         $process_check=ProductProcessMaster::where('process_master_id','=',$ptsDatas->process_id)->where('part_id','=',$part_id)->where('status','=',1)->orderBy('id', 'ASC')->count();
        //  dd($process_check);
         if($process_check==0){
             $message=false;
         }else{
             $message=true;
         }
         $packingStrickerDetails=PackingStrickerDetails::with('rcmaster','partmaster','prepareuserdetails','covermaster')->where('rc_id','=',$rc_no)->where('status','=',0)->get();
         $cleMasterDatas=FirewallInspectionDetails::where('unit_name','=','VSS UNIT-4')->where('inspection_area','=','VSS UNIT-1')->get();
         //  dd($packingStrickerDetails);
         $html = view('stagewise-receive.pts_cle_partdata',compact('packingStrickerDetails','total_issue_qty','total_receive_qty','total_reject_qty','total_rework_qty','cleMasterDatas'))->render();

        return response()->json(['success'=>$success,'fifoRcNo'=>$fifoRcNo,'part'=>$partNo,'message'=>$message,'process'=>$process,'avl_qty'=>$avl_qty,'bom'=>$bomDatas,'html'=>$html,'fifo_html'=>$fifo_html]);
    }

    public function ptsCleReceiveList(){
        date_default_timezone_set('Asia/Kolkata');
        $current_date=date('Y-m-d');
        // $ptsStockDatas=PtsTransactionDetail::with('rcmaster','previous_rcmaster','partmaster','currentprocessmaster','prepareuserdetails','strickermaster')->where('process_id','=',27)->where('issue_qty','=',0)->orderBy('id','DESC')->get();
        $ptsStockDatas=PtsTransactionDetail::with('rcmaster','previous_rcmaster','partmaster','currentprocessmaster','prepareuserdetails','strickermaster')->where('process_id','=',27)->where('issue_qty','=',0)->orderBy('id','DESC')->where('open_date','=',$current_date)->get();
        // dd($ptsStockDatas);
        return view('stagewise-receive.pts_cle__receive_view',compact('ptsStockDatas'));
    }
    public function ptsCleReceiveCreateForm(){
        date_default_timezone_set('Asia/Kolkata');
        $current_date=date('Y-m-d');
        $ptsDatas=PtsTransactionSummary::with('partmaster')->select('part_id')
        ->groupBy('part_id')->get();
        // $ptsDatas=PtsTransactionSummary::with('rcmaster')->select('rc_id',DB::raw('((cle_receive_qty)-(cle_issue_qty)-(cle_reject_qty)-(cle_rework_qty)-(cle_return_qty)) as avl_qty'))
        // ->havingRaw('avl_qty >?', [0])->groupBy('rc_id')->get();
        // $d11Datas=TransDataD11::whereIn('process_id',[18])->where('status','=',1)->get();
        return view('stagewise-receive.pts_cle__receive_create',compact('ptsDatas','current_date'));
    }

    public function ptsCleReceivePartFetchEntry(Request $request){
        // dd($request->all());
        $rc_no=$request->rc_no;
        $part_id=$request->part_id;
        $ptsDatas=PtsTransactionSummary::with('partmaster')->select('part_id','process_id','cle_receive_qty','cle_issue_qty','cle_reject_qty','cle_rework_qty',DB::raw('((cle_receive_qty)-(cle_issue_qty)-(cle_reject_qty)-(cle_rework_qty)-(cle_return_qty)) as avl_qty'))
        ->where('rc_id','=',$rc_no)->where('part_id','=',$part_id)->havingRaw('avl_qty >?', [0])->first();
        // dd($ptsDatas);
        $total_issue_qty=$ptsDatas->cle_receive_qty;
        $total_receive_qty=$ptsDatas->cle_issue_qty;
        $total_reject_qty=$ptsDatas->cle_reject_qty;
        $total_rework_qty=$ptsDatas->cle_rework_qty;

        $fifoCheck=PtsTransactionSummary::with('rcmaster')->select('rc_id',DB::raw('((cle_receive_qty)-(cle_issue_qty)-(cle_reject_qty)-(cle_rework_qty)-(cle_return_qty)) as avl_qty'))
        ->where('part_id','=',$part_id)->havingRaw('avl_qty >?', [0])->first();
        $fifoRcNo=$fifoCheck->rc_id;
        $fifoRcCard=$fifoCheck->rcmaster->rc_id;

        if ($rc_no==$fifoRcNo) {
            $success = true;
            $partNo='<option value="'.$ptsDatas->partmaster->id.'" selected>'.$ptsDatas->partmaster->child_part_no.'</option>';
            $avl_qty=$ptsDatas->avl_qty;
            $process='<option value="27" selected>FG For Checking</option>';
            $fifo_html='';
        }else {
            $success = false;
            $partNo='<option value="" selected></option>';
            $avl_qty=0;
            $process='<option value="27" selected>FG For Checking</option>';
            $fifo_html='<svg xmlns="http://www.w3.org/2000/svg" style="display: none;">
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
            $fifo_html.='<svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Danger:"><use xlink:href="#exclamation-triangle-fill"/></svg><div>Sorry..Please Follow The FIFO And FIFO Route Card Is '.$fifoRcCard.'</div></div>';
        }

        $bomDatas=BomMaster::where('child_part_id','=',$part_id)->where('status','=',1)->sum('finish_usage');

         $process_check=ProductProcessMaster::where('process_master_id','=',$ptsDatas->process_id)->where('part_id','=',$part_id)->where('status','=',1)->orderBy('id', 'ASC')->count();
        //  dd($process_check);
         if($process_check==0){
             $message=false;
         }else{
             $message=true;
         }
         $packingStrickerDetails=PackingStrickerDetails::with('rcmaster','partmaster','prepareuserdetails','covermaster')->where('rc_id','=',$rc_no)->where('status','=',0)->get();
         $cleMasterDatas=FirewallInspectionDetails::where('unit_name','=','VSS UNIT-4')->where('inspection_area','=','CLE')->where('status','!=',0)->get();
         //  dd($packingStrickerDetails);
         $html = view('stagewise-receive.pts_cle_partdata',compact('packingStrickerDetails','total_issue_qty','total_receive_qty','total_reject_qty','total_rework_qty','cleMasterDatas'))->render();

        return response()->json(['success'=>$success,'fifoRcNo'=>$fifoRcNo,'part'=>$partNo,'message'=>$message,'process'=>$process,'avl_qty'=>$avl_qty,'bom'=>$bomDatas,'html'=>$html,'fifo_html'=>$fifo_html]);
    }

    public function ptsCleReceiveEntry(Request $request){
        // dd($request->all());
        $id_datas=$request->id_datas;
        $rc_id=$request->rc_no;
        $rc_date=$request->rc_date;
        $part_id=$request->part_id;
        $process_id=$request->process_id;
        $avl_qty=$request->available_quantity;
        $cover_qty=$request->cover_qty;
        $received_qty=$request->received_qty;
        $ok_qty=$request->ok_qty;
        $reject_qty=$request->reject_qty;
        $rework_qty=$request->rework_qty;
        $rc_available_quantity=$request->available_quantity;
        $status=$request->status;
        $status_all=$request->status_all;
        $reason_all=$request->reason_all;
        $remarks=$request->remarks;
        $inspect_by=$request->inspect_by;
        $sub_id=$request->sub_id;

        // $total_issue_qty=$request->total_issue_qty;
        // $total_receive_qty=$request->total_receive_qty;
        // $total_accepted_qty=$request->total_accepted_qty;
        // $total_reject_qty=$request->total_reject_qty;
        // $total_rework_qty=$request->total_rework_qty;
        $process_check=ProductProcessMaster::with('processMaster')->where('process_master_id','=',$process_id)->where('part_id','=',$request->part_id)->where('status','=',1)->orderBy('id', 'ASC')->first();
        $current_process=$process_check->processMaster->operation;
        // dd($current_process);
        $select_all=$request->select_all;
        $no_of_inspector=$request->no_of_inspector;
        $inspect_all=$request->inspect_all;
        if ($select_all!='') {
            // dd('select all ok');

            if ($no_of_inspector==1) {
                foreach ($sub_id as $key => $id) {
                    if ($id!='') {
                        if ($status_all==1) {
                            $packingStrickerDetails=PackingStrickerDetails::find($id);
                            $old_total_receive_qty=$packingStrickerDetails->total_receive_qty;
                            $old_ok_packed_qty=$packingStrickerDetails->ok_packed_qty;
                            $packingStrickerDetails->total_receive_qty=(($old_total_receive_qty)+($avl_qty[$id]));
                            $packingStrickerDetails->ok_packed_qty=(($old_ok_packed_qty)+($avl_qty[$id]));
                            // dd($packingStrickerDetails->ok_packed_qty);
                            // dd($avl_qty[$id]);
                            if ((($old_total_receive_qty)+($avl_qty[$id]))==(($old_ok_packed_qty)+($avl_qty[$id]))) {
                                // dd('ok');
                                $packingStrickerDetails->status=$status_all;
                            } else {
                                // dd('not ok');
                                $packingStrickerDetails->status=0;
                            }
                            $packingStrickerDetails->inspect_by=$inspect_all;
                            $packingStrickerDetails->inspect_at=Carbon::now();
                            $packingStrickerDetails->updated_by = auth()->user()->id;
                            $packingStrickerDetails->updated_at = Carbon::now();
                            $packingStrickerDetails->update();

                            $ptsTransactionDetail=new PtsTransactionDetail;
                            $ptsTransactionDetail->open_date=$rc_date;
                            $ptsTransactionDetail->part_id=$request->part_id;
                            $ptsTransactionDetail->process_id=$request->process_id;
                            $ptsTransactionDetail->process=$current_process;
                            $ptsTransactionDetail->rc_id=$request->rc_no;
                            $ptsTransactionDetail->previous_rc_id=$request->rc_no;
                            $ptsTransactionDetail->stricker_id=$id;
                            $ptsTransactionDetail->receive_qty=$avl_qty[$id];
                            $ptsTransactionDetail->prepared_by = auth()->user()->id;
                            $ptsTransactionDetail->save();

                        if ($process_id==22) {
                            $coverStrickerDetails=new CoverStrickerDetails;
                            $coverStrickerDetails->part_id=$request->part_id;
                            $coverStrickerDetails->rc_id=$request->rc_no;
                            $coverStrickerDetails->prc_id=$request->rc_no;
                            $coverStrickerDetails->stricker_id=$id;
                            $coverStrickerDetails->total_cover_qty=$packingStrickerDetails->cover_qty;
                            $coverStrickerDetails->total_issue_qty=$packingStrickerDetails->ok_packed_qty;
                            $coverStrickerDetails->prepared_by = auth()->user()->id;
                            $coverStrickerDetails->save();
                        }
                        } elseif ($status_all==2) {
                            $packingStrickerDetails=PackingStrickerDetails::find($id);
                            $old_total_receive_qty=$packingStrickerDetails->total_receive_qty;
                            $old_reject_packed_qty=$packingStrickerDetails->reject_packed_qty;
                            $packingStrickerDetails->total_receive_qty=(($old_total_receive_qty)+($avl_qty[$id]));
                            if ((($old_total_receive_qty)+($avl_qty[$id]))==(($old_reject_packed_qty)+($avl_qty[$id]))) {
                                $packingStrickerDetails->status=$status_all;
                            } else {
                                $packingStrickerDetails->status=0;
                            }
                            $packingStrickerDetails->inspect_by=$inspect_all;
                            $packingStrickerDetails->inspect_at=Carbon::now();
                            $packingStrickerDetails->remarks=$reason_all??NULL;
                            $packingStrickerDetails->updated_by = auth()->user()->id;
                            $packingStrickerDetails->updated_at = Carbon::now();
                            $packingStrickerDetails->update();

                            $ptsTransactionDetail=new PtsTransactionDetail;
                            $ptsTransactionDetail->open_date=$rc_date;
                            $ptsTransactionDetail->part_id=$request->part_id;
                            $ptsTransactionDetail->process_id=$request->process_id;
                            $ptsTransactionDetail->process=$current_process;
                            $ptsTransactionDetail->rc_id=$request->rc_no;
                            $ptsTransactionDetail->previous_rc_id=$request->rc_no;
                            $ptsTransactionDetail->stricker_id=$id;
                            $ptsTransactionDetail->reject_qty=$avl_qty[$id];
                            $ptsTransactionDetail->remarks=$reason_all;
                            $ptsTransactionDetail->prepared_by = auth()->user()->id;
                            $ptsTransactionDetail->save();

                        } elseif ($status_all==3) {
                            $packingStrickerDetails=PackingStrickerDetails::find($id);
                            $old_total_receive_qty=$packingStrickerDetails->total_receive_qty;
                            $old_rework_packed_qty=$packingStrickerDetails->rework_packed_qty;
                            $packingStrickerDetails->total_receive_qty=(($old_total_receive_qty)+($avl_qty[$id]));
                            $packingStrickerDetails->rework_packed_qty=(($old_rework_packed_qty)+($avl_qty[$id]));
                            if ((($old_total_receive_qty)+($avl_qty[$id]))==(($old_rework_packed_qty)+($avl_qty[$id]))) {
                                $packingStrickerDetails->status=$status_all;
                            } else {
                                $packingStrickerDetails->status=0;
                            }
                            $packingStrickerDetails->inspect_by=$inspect_all;
                            $packingStrickerDetails->inspect_at=Carbon::now();
                            $packingStrickerDetails->remarks=$reason_all??NULL;
                            $packingStrickerDetails->updated_by = auth()->user()->id;
                            $packingStrickerDetails->updated_at = Carbon::now();
                            $packingStrickerDetails->update();

                            $ptsTransactionDetail=new PtsTransactionDetail;
                            $ptsTransactionDetail->open_date=$rc_date;
                            $ptsTransactionDetail->part_id=$request->part_id;
                            $ptsTransactionDetail->process_id=$request->process_id;
                            $ptsTransactionDetail->process=$current_process;
                            $ptsTransactionDetail->rc_id=$request->rc_no;
                            $ptsTransactionDetail->previous_rc_id=$request->rc_no;
                            $ptsTransactionDetail->stricker_id=$id;
                            $ptsTransactionDetail->rework_qty=$avl_qty[$id];
                            $ptsTransactionDetail->remarks=$reason_all;
                            $ptsTransactionDetail->prepared_by = auth()->user()->id;
                            $ptsTransactionDetail->save();
                        }
                    }
                }
            } else {
                // dd('ok1');

                foreach ($sub_id as $key => $id) {
                    # code...
                    // dump($status_all);
                    if ($id!='') {
                        if ($status_all==1) {
                            $packingStrickerDetails=PackingStrickerDetails::find($id);
                            $old_total_receive_qty=$packingStrickerDetails->total_receive_qty;
                            $old_ok_packed_qty=$packingStrickerDetails->ok_packed_qty;
                            $packingStrickerDetails->total_receive_qty=(($old_total_receive_qty)+($avl_qty[$id]));
                            $packingStrickerDetails->ok_packed_qty=(($old_ok_packed_qty)+($avl_qty[$id]));
                            if ((($old_total_receive_qty)+($avl_qty[$id]))==(($old_ok_packed_qty)+($avl_qty[$id]))) {
                                $packingStrickerDetails->status=$status_all;
                            } else {
                                $packingStrickerDetails->status=0;
                            }
                            $packingStrickerDetails->inspect_by=$inspect_by[$id];
                            $packingStrickerDetails->inspect_at=Carbon::now();
                            $packingStrickerDetails->updated_by = auth()->user()->id;
                            $packingStrickerDetails->updated_at = Carbon::now();
                            $packingStrickerDetails->update();

                            $ptsTransactionDetail=new PtsTransactionDetail;
                            $ptsTransactionDetail->open_date=$rc_date;
                            $ptsTransactionDetail->part_id=$request->part_id;
                            $ptsTransactionDetail->process_id=$request->process_id;
                            $ptsTransactionDetail->process=$current_process;
                            $ptsTransactionDetail->rc_id=$request->rc_no;
                            $ptsTransactionDetail->previous_rc_id=$request->rc_no;
                            $ptsTransactionDetail->stricker_id=$id;
                            $ptsTransactionDetail->receive_qty=$avl_qty[$id];
                            $ptsTransactionDetail->prepared_by = auth()->user()->id;
                            $ptsTransactionDetail->save();


                        if ($process_id==22) {
                            $coverStrickerDetails=new CoverStrickerDetails;
                            $coverStrickerDetails->part_id=$request->part_id;
                            $coverStrickerDetails->rc_id=$request->rc_no;
                            $coverStrickerDetails->prc_id=$request->rc_no;
                            $coverStrickerDetails->stricker_id=$id;
                            $coverStrickerDetails->total_cover_qty=$packingStrickerDetails->cover_qty;
                            $coverStrickerDetails->total_issue_qty=$packingStrickerDetails->ok_packed_qty;
                            $coverStrickerDetails->prepared_by = auth()->user()->id;
                            $coverStrickerDetails->save();
                        }

                        } elseif ($status_all==2) {
                            $packingStrickerDetails=PackingStrickerDetails::find($id);
                            $old_total_receive_qty=$packingStrickerDetails->total_receive_qty;
                            $old_reject_packed_qty=$packingStrickerDetails->reject_packed_qty;
                            $packingStrickerDetails->total_receive_qty=(($old_total_receive_qty)+($avl_qty[$id]));
                            if ((($old_total_receive_qty)+($avl_qty[$id]))==(($old_reject_packed_qty)+($avl_qty[$id]))) {
                                $packingStrickerDetails->status=$status_all;
                            } else {
                                $packingStrickerDetails->status=0;
                            }
                            $packingStrickerDetails->inspect_by=$inspect_by[$id];
                            $packingStrickerDetails->inspect_at=Carbon::now();
                            $packingStrickerDetails->remarks=$reason_all??NULL;
                            $packingStrickerDetails->updated_by = auth()->user()->id;
                            $packingStrickerDetails->updated_at = Carbon::now();
                            $packingStrickerDetails->update();

                            $ptsTransactionDetail=new PtsTransactionDetail;
                            $ptsTransactionDetail->open_date=$rc_date;
                            $ptsTransactionDetail->part_id=$request->part_id;
                            $ptsTransactionDetail->process_id=$request->process_id;
                            $ptsTransactionDetail->process=$current_process;
                            $ptsTransactionDetail->rc_id=$request->rc_no;
                            $ptsTransactionDetail->previous_rc_id=$request->rc_no;
                            $ptsTransactionDetail->stricker_id=$id;
                            $ptsTransactionDetail->reject_qty=$avl_qty[$id];
                            $ptsTransactionDetail->remarks=$reason_all;
                            $ptsTransactionDetail->prepared_by = auth()->user()->id;
                            $ptsTransactionDetail->save();

                        } elseif ($status_all==3) {
                            $packingStrickerDetails=PackingStrickerDetails::find($id);
                            $old_total_receive_qty=$packingStrickerDetails->total_receive_qty;
                            $old_rework_packed_qty=$packingStrickerDetails->rework_packed_qty;
                            $packingStrickerDetails->total_receive_qty=(($old_total_receive_qty)+($avl_qty[$id]));
                            $packingStrickerDetails->rework_packed_qty=(($old_rework_packed_qty)+($avl_qty[$id]));
                            if ((($old_total_receive_qty)+($avl_qty[$id]))==(($old_rework_packed_qty)+($avl_qty[$id]))) {
                                $packingStrickerDetails->status=$status_all;
                            } else {
                                $packingStrickerDetails->status=0;
                            }
                            $packingStrickerDetails->inspect_by=$inspect_by[$id];
                            $packingStrickerDetails->inspect_at=Carbon::now();
                            $packingStrickerDetails->remarks=$reason_all??NULL;
                            $packingStrickerDetails->updated_by = auth()->user()->id;
                            $packingStrickerDetails->updated_at = Carbon::now();
                            $packingStrickerDetails->update();

                            $ptsTransactionDetail=new PtsTransactionDetail;
                            $ptsTransactionDetail->open_date=$rc_date;
                            $ptsTransactionDetail->part_id=$request->part_id;
                            $ptsTransactionDetail->process_id=$request->process_id;
                            $ptsTransactionDetail->process=$current_process;
                            $ptsTransactionDetail->rc_id=$request->rc_no;
                            $ptsTransactionDetail->previous_rc_id=$request->rc_no;
                            $ptsTransactionDetail->stricker_id=$id;
                            $ptsTransactionDetail->rework_qty=$avl_qty[$id];
                            $ptsTransactionDetail->remarks=$reason_all;
                            $ptsTransactionDetail->prepared_by = auth()->user()->id;
                            $ptsTransactionDetail->save();
                        }
                    }
                }
            }

        } else {
            foreach ($sub_id as $key => $id) {
                # code...
                // dump($status[$id]);
                if ($id!='') {
                    if ($status[$id]==1) {
                        $packingStrickerDetails=PackingStrickerDetails::find($id);
                        $old_total_receive_qty=$packingStrickerDetails->total_receive_qty;
                        $old_ok_packed_qty=$packingStrickerDetails->ok_packed_qty;
                        $packingStrickerDetails->total_receive_qty=(($old_total_receive_qty)+($avl_qty[$id]));
                        $packingStrickerDetails->ok_packed_qty=(($old_ok_packed_qty)+($avl_qty[$id]));
                        // dd($packingStrickerDetails->ok_packed_qty);
                        // dd($avl_qty[$id]);
                        if ((($old_total_receive_qty)+($avl_qty[$id]))==(($old_ok_packed_qty)+($avl_qty[$id]))) {
                            // dd('ok');
                            $packingStrickerDetails->status=$status[$id];
                        } else {
                            // dd('not ok');
                            $packingStrickerDetails->status=0;
                        }
                        // $packingStrickerDetails->total_receive_qty=(($old_total_receive_qty)+($ok_qty[$id]));
                        // $packingStrickerDetails->ok_packed_qty=(($old_ok_packed_qty)+($ok_qty[$id]));
                        // if ((($old_total_receive_qty)+($ok_qty[$id]))==(($old_ok_packed_qty)+($ok_qty[$id]))) {
                        //     $packingStrickerDetails->status=$status[$id];
                        // } else {
                        //     $packingStrickerDetails->status=0;
                        // }
                        $packingStrickerDetails->inspect_by=$inspect_by[$id];
                        $packingStrickerDetails->inspect_at=Carbon::now();
                        $packingStrickerDetails->updated_by = auth()->user()->id;
                        $packingStrickerDetails->updated_at = Carbon::now();
                        $packingStrickerDetails->update();

                        $ptsTransactionDetail=new PtsTransactionDetail;
                        $ptsTransactionDetail->open_date=$rc_date;
                        $ptsTransactionDetail->part_id=$request->part_id;
                        $ptsTransactionDetail->process_id=$request->process_id;
                        $ptsTransactionDetail->process=$current_process;
                        $ptsTransactionDetail->rc_id=$request->rc_no;
                        $ptsTransactionDetail->previous_rc_id=$request->rc_no;
                        $ptsTransactionDetail->stricker_id=$id;
                        $ptsTransactionDetail->receive_qty=$avl_qty[$id];
                        // $ptsTransactionDetail->receive_qty=$ok_qty[$id];
                        $ptsTransactionDetail->prepared_by = auth()->user()->id;
                        $ptsTransactionDetail->save();

                        if ($process_id==22) {
                            $coverStrickerDetails=new CoverStrickerDetails;
                            $coverStrickerDetails->part_id=$request->part_id;
                            $coverStrickerDetails->rc_id=$request->rc_no;
                            $coverStrickerDetails->prc_id=$request->rc_no;
                            $coverStrickerDetails->stricker_id=$id;
                            $coverStrickerDetails->total_cover_qty=$packingStrickerDetails->cover_qty;
                            $coverStrickerDetails->total_issue_qty=$packingStrickerDetails->ok_packed_qty;
                            $coverStrickerDetails->prepared_by = auth()->user()->id;
                            $coverStrickerDetails->save();
                        }

                    } elseif ($status[$id]==2) {
                        $packingStrickerDetails=PackingStrickerDetails::find($id);
                        $old_total_receive_qty=$packingStrickerDetails->total_receive_qty;
                        $old_reject_packed_qty=$packingStrickerDetails->reject_packed_qty;
                        $packingStrickerDetails->total_receive_qty=(($old_total_receive_qty)+($reject_qty[$id]));
                        if ((($old_total_receive_qty)+($reject_qty[$id]))==(($old_reject_packed_qty)+($reject_qty[$id]))) {
                            $packingStrickerDetails->status=$status[$id];
                        } else {
                            $packingStrickerDetails->status=0;
                        }
                        $packingStrickerDetails->inspect_by=$inspect_by[$id];
                        $packingStrickerDetails->inspect_at=Carbon::now();
                        $packingStrickerDetails->remarks=$remarks[$id]??NULL;
                        $packingStrickerDetails->updated_by = auth()->user()->id;
                        $packingStrickerDetails->updated_at = Carbon::now();
                        $packingStrickerDetails->update();

                        $ptsTransactionDetail=new PtsTransactionDetail;
                        $ptsTransactionDetail->open_date=$rc_date;
                        $ptsTransactionDetail->part_id=$request->part_id;
                        $ptsTransactionDetail->process_id=$request->process_id;
                        $ptsTransactionDetail->process=$current_process;
                        $ptsTransactionDetail->rc_id=$request->rc_no;
                        $ptsTransactionDetail->previous_rc_id=$request->rc_no;
                        $ptsTransactionDetail->stricker_id=$id;
                        $ptsTransactionDetail->reject_qty=$reject_qty[$id];
                        $ptsTransactionDetail->remarks=$remarks[$id];
                        $ptsTransactionDetail->prepared_by = auth()->user()->id;
                        $ptsTransactionDetail->save();

                    } elseif ($status[$id]==3) {
                        $packingStrickerDetails=PackingStrickerDetails::find($id);
                        $old_total_receive_qty=$packingStrickerDetails->total_receive_qty;
                        $old_rework_packed_qty=$packingStrickerDetails->rework_packed_qty;
                        $packingStrickerDetails->total_receive_qty=(($old_total_receive_qty)+($rework_qty[$id]));
                        $packingStrickerDetails->rework_packed_qty=(($old_rework_packed_qty)+($rework_qty[$id]));
                        if ((($old_total_receive_qty)+($rework_qty[$id]))==(($old_rework_packed_qty)+($rework_qty[$id]))) {
                            $packingStrickerDetails->status=$status[$id];
                        } else {
                            $packingStrickerDetails->status=0;
                        }
                        $packingStrickerDetails->inspect_by=$inspect_by[$id];
                        $packingStrickerDetails->inspect_at=Carbon::now();
                        $packingStrickerDetails->remarks=$remarks[$id]??NULL;
                        $packingStrickerDetails->updated_by = auth()->user()->id;
                        $packingStrickerDetails->updated_at = Carbon::now();
                        $packingStrickerDetails->update();

                        $ptsTransactionDetail=new PtsTransactionDetail;
                        $ptsTransactionDetail->open_date=$rc_date;
                        $ptsTransactionDetail->part_id=$request->part_id;
                        $ptsTransactionDetail->process_id=$request->process_id;
                        $ptsTransactionDetail->process=$current_process;
                        $ptsTransactionDetail->rc_id=$request->rc_no;
                        $ptsTransactionDetail->previous_rc_id=$request->rc_no;
                        $ptsTransactionDetail->stricker_id=$id;
                        $ptsTransactionDetail->rework_qty=$rework_qty[$id];
                        $ptsTransactionDetail->remarks=$remarks[$id];
                        $ptsTransactionDetail->prepared_by = auth()->user()->id;
                        $ptsTransactionDetail->save();
                    }
                }
            }
        }

        $PackingStrickerDatas=PackingStrickerDetails::where('rc_id','=',$rc_id)->selectRaw('sum(total_receive_qty) as total_receive_qty,sum(ok_packed_qty) as ok_packed_qty,sum(reject_packed_qty) as reject_packed_qty,sum(rework_packed_qty) as rework_packed_qty,rc_id,part_id')->first();
        $total_accepted_qty=$PackingStrickerDatas->ok_packed_qty;
        $total_reject_qty=$PackingStrickerDatas->reject_packed_qty;
        $total_rework_qty=$PackingStrickerDatas->rework_packed_qty;
        // dd($PackingStrickerDatas);
        $PtsTransactionSummary=PtsTransactionSummary::where('part_id','=',$part_id)->where('rc_id','=',$rc_id)->first();
        $old_cle_issue_qty=$PtsTransactionSummary->cle_issue_qty;
        $old_cle_reject_qty=$PtsTransactionSummary->cle_reject_qty;
        $old_rework_qty=$PtsTransactionSummary->rework_qty;
        $PtsTransactionSummary->cle_issue_qty=(($total_accepted_qty));
        $PtsTransactionSummary->cle_reject_qty=(($total_reject_qty));
        $PtsTransactionSummary->cle_rework_qty=(($total_rework_qty));
        $PtsTransactionSummary->updated_by = auth()->user()->id;
        $PtsTransactionSummary->updated_at = Carbon::now();
        $PtsTransactionSummary->update();

        if ($process_id==22) {
            return redirect()->route('cncproductionfirewallreceive')->withSuccess('Successfully Part No Receive From Firewall Inspection Team...!');
        }else {
            return redirect()->route('ptsclereceive')->withSuccess('Successfully Part No Receive From CLE Inspection Team...!');
        }

    }


    public function ptsDcIssueList(){
        $ptsStockDatas=PtsTransactionDetail::with('rcmaster','previous_rcmaster','partmaster','currentprocessmaster','prepareuserdetails')->where('process_id','=',27)->where('issue_qty','=',0)->orderBy('id','DESC')->get();
        $value=0;
        $dcDatas=DcTransactionDetails::with('dcmaster','rcmaster','uom')->WhereHas('dcmaster', function ($q) use ($value) {
            $q->where('type_id', '=', $value)
            ->where('supplier_id','=',4);
        })->orderBy('id', 'DESC')->get();
        // dd($dcDatas);
        return view('dc.pts_dc_view',compact('dcDatas'));
    }

    public function ptsDcIssueCreateForm(){
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
            $rc="DC-U4D";
		$current_rcno=$rc.$finacial_year;
        $count1=RouteMaster::whereIn('process_id',[27,20])->where('rc_id','LIKE','%'.$current_rcno.'%')->orderBy('rc_id', 'DESC')->get()->count();
        if ($count1 > 0) {
            $rc_data=RouteMaster::whereIn('process_id',[27,20])->where('rc_id','LIKE','%'.$current_rcno.'%')->orderBy('rc_id', 'DESC')->first();
            $rcnumber=$rc_data['rc_id']??NULL;
            $old_rcnumber=str_replace($current_rcno,"",$rcnumber);
            $old_rcnumber_data=str_pad($old_rcnumber+1,5,0,STR_PAD_LEFT);
            $new_rcnumber=$current_rcno.$old_rcnumber_data;
        }else{
            $str='00001';
            $new_rcnumber=$current_rcno.$str;
        }
        // dd($new_rcnumber);
        $value=4;
            $dcmasterDatas=DcMaster::with('supplier')->WhereHas('supplier', function ($q) use ($value) {
                $q->where('id', '=', $value);
            })->where('status','=',1)->where('type_id','=',0)->groupBy('supplier_id')->get();
            // dd($dcmasterDatas);
            // return view('dc.create2',compact('dcmasterDatas','new_rcnumber','current_date'));
            return view('dc.pts_dc_create',compact('dcmasterDatas','new_rcnumber','current_date'));
    }

    public function ptsdcItemRc(Request $request){
        // dd($request->all());
        $part_id=$request->part_id;
        $supplier_id=$request->supplier_id;
        $check=ChildProductMaster::where('status','=',1)->where('part_id','=',$part_id)->where('stocking_point','=',22)->count();
        $check1=ChildProductMaster::where('status','=',1)->where('part_id','=',$part_id)->where('stocking_point','=',22)->where('item_type','=',1)->count();
        $check2=ChildProductMaster::where('status','=',1)->where('part_id','=',$part_id)->where('stocking_point','=',22)->where('item_type','=',0)->count();
        // dd($check1);

                if ($check1==1) {
                $manufacturingPartDatas=ChildProductMaster::where('status','=',1)->where('part_id','=',$part_id)->where('stocking_point','=',22)->first();

                    // foreach ($manufacturingPartDatas as $key => $manufacturingPartData) {
                        $manufacturingPart=$manufacturingPartDatas->id;
                        $itemType=$manufacturingPartDatas->item_type;
                    // }
                    // dd($itemType);
                    $bom=BomMaster::where('child_part_id','=',$manufacturingPart)->where('status','=',1)->sum('finish_usage');
                    // dd($bom);
                    if ($itemType==1) {
                        $dcmasterOperationDatas=DcMaster::with('childpart','procesmaster','supplier')->where('status','=',1)->where('supplier_id','=',$supplier_id)->where('part_id','=',$part_id)->first();
                        // dd($dcmasterOperationDatas);
                        $operation_id=$dcmasterOperationDatas->operation_id;
                        $operation_name=$dcmasterOperationDatas->procesmaster->operation;
                        $operation='<option value="'.$operation_id.'" selected>'.$operation_name.'</option>';
                        $customerDatas=PackingMaster::with('covermaster')->where('part_id','=',$part_id)->groupBy('cus_type_name')->get();
                        $cus_type_name='<option value="" selected></option>';
                        foreach ($customerDatas as $key => $customerData) {
                            $cus_type_name.='<option value="'.$customerData->cus_type_name.'">'.$customerData->cus_type_name.'</option>';
                        }
                        $cover_datas=PackingMaster::with('covermaster')->where('part_id','=',$part_id)->where('cus_type_name','=',$customerData->cus_type_name)->where('status','=',1)->first();
                        $cover_qty=$cover_datas->cover_qty;
                        $regular=$check1-1;
                        return response()->json(['operation'=>$operation,'regular'=>$check1,'alter'=>$check2,'bom'=>$bom,'part_count'=>$check,'manufacturingPart'=>$manufacturingPart,'cus_type_name'=>$cus_type_name,'cover_qty'=>$cover_qty]);
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
                        return response()->json(['t_avl_qty'=>$t_avl_qty,'table'=>$table,'operation'=>$operation,'regular'=>$check1,'alter'=>$check2]);
                    }
                }else{
                    // dd('ok');
                    $dcmasterOperationDatas=DcMaster::with('procesmaster','supplier')->where('status','=',1)->where('supplier_id','=',$supplier_id)->where('part_id','=',$part_id)->where('type_id','=',0)->first();
                    // dd($dcmasterOperationDatas);
                    $operation_id=$dcmasterOperationDatas->procesmaster->id;
                    $operation_name=$dcmasterOperationDatas->procesmaster->operation;
                    $operation='<option value="'.$operation_id.'" selected>'.$operation_name.'</option>';

                    $dcmasterDatas2=DB::table('customer_product_masters as a')
                    ->join('product_masters AS b', 'a.part_id', '=', 'b.id')
                    ->join('child_product_masters AS c', 'c.part_id', '=', 'b.id')
                    ->join('trans_data_d11_s AS d', 'd.part_id', '=', 'c.id')
                    ->join('route_masters AS e', 'd.rc_id', '=', 'e.id')
                    // ->select(DB::raw('((receive_qty)-(issue_qty)) as t_avl_qty'))
                    ->select(DB::raw('(SUM(receive_qty)-SUM(issue_qty)) as t_avl_qty'))
                    ->where('a.part_id','=',$part_id)
                    ->where('c.stocking_point','=',$operation_id)
                    ->where('d.next_process_id','=',$operation_id)
                    ->where('c.item_type','=',1)
                    ->where('c.status','=',1)
                    ->havingRaw('t_avl_qty >?', [0])
                    ->orderBy('c.no_item_id', 'ASC')
                    ->orderBy('e.id', 'ASC')
                    ->groupBy('c.id')
                    ->min('t_avl_qty');
                    // dd($dcmasterDatas2);
                    $t_avl_qty=$dcmasterDatas2;
                        $bom=0;
                        $regular=$check1-1;

                        return response()->json(['t_avl_qty'=>$t_avl_qty,'operation'=>$operation,'bom'=>$bom,'regular'=>$regular,'alter'=>$check2]);
                }
    }

    public function ptsdcCusType(Request $request){
        // dd($request->all());
        $part_id=$request->part_id;
        $manufacturingPart=$request->manufacturingPart;
        $cus_type_name=$request->cus_type_name;
        $operation_id=$request->operation_id;
        $supplier_id=$request->supplier_id;
        $cus_type_id=$request->cus_type_name;
        $check=$request->child_part_count;
        $check1=$request->regular;
        $check2=$request->alter;
        $bom=$request->bom;
        $cover_qty=$request->cover_qty;
        $manufacturingPart=$request->manufacturingPart;
        $t_avl_qty=(PackingStrickerDetails::where('part_id','=',$manufacturingPart)->where('cus_type_name','=',$cus_type_name)->sum('ok_packed_qty'))-(PackingStrickerDetails::where('part_id','=',$manufacturingPart)->where('cus_type_name','=',$cus_type_name)->sum('pts_dc_issue_qty'));
        // $t_avl_qty=(PackingStrickerDetails::where('part_id','=',$manufacturingPart)->where('cover_qty','=',$cover_qty)->where('cus_type_name','=',$cus_type_name)->sum('ok_packed_qty'))-(PackingStrickerDetails::where('part_id','=',$manufacturingPart)->where('cus_type_name','=',$cus_type_name)->where('cover_qty','=',$cover_qty)->sum('pts_dc_issue_qty'));
        // dd($t_avl_qty);
        $customerDatas=PackingMaster::with('covermaster')->where('part_id','=',$part_id)->where('cus_type_name','=',$cus_type_name)->where('status','=',1)->first();
        $cover_qty=$customerDatas->cover_qty;
        $cover_datas='<option value="'.$cover_qty.'">'.$cover_qty.'</option>';
        // $PackingStrickerDetails=DB::table('customer_product_masters as a')
        // ->join('product_masters AS b', 'a.part_id', '=', 'b.id')
        // ->join('child_product_masters AS c', 'c.part_id', '=', 'b.id')
        // ->join('packing_masters AS d', 'd.part_id', '=', 'b.id')
        // ->join('packing_stricker_details AS e', 'd.cover_id', '=', 'e.cover_id')
        // // ->select(DB::raw('((receive_qty)-(issue_qty)) as t_avl_qty'))
        // ->select(DB::raw('(SUM(e.ok_packed_qty)-SUM(e.pts_dc_issue_qty)) as t_avl_qty'))
        // ->where('a.part_id','=',$part_id)
        // ->where('b.id','=',$part_id)
        // ->where('c.id','=',$manufacturingPart)
        // ->where('d.cus_type_name','=',$cus_type_name)
        // ->where('d.cover_qty','=',$cover_qty)
        // ->where('c.stocking_point','=',22)
        // ->where('c.status','=',1)
        // ->havingRaw('t_avl_qty >?', [0])
        // ->orderBy('e.id', 'ASC')
        // ->first();
        // $t_avl_qty=$PackingStrickerDetails->t_avl_qty;
        // dd($t_avl_qty);
        $dc_quantity=$t_avl_qty;
        if ($t_avl_qty<=0) {
            $no_of_cover=0;
        }else{
            $no_of_cover=(($t_avl_qty)/($cover_qty));
        }
        $manufacturingPartDatas=ChildProductMaster::where('status','=',1)->where('part_id','=',$part_id)->get();
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
                    <div class='col-2'><input type='number' name='grand_total[$part_id]' class='form-control bg-light' id='grand_total_$part_id' value='0'  readonly>
                    </div>
                    <div class='col-2'><h6>Balance:</h6></div>
                    <div class='col-2'><input type='number' name='total_diff[$part_id]' class='form-control bg-light' id='total_diff_$part_id' value=0  readonly></div></div>";
                        $bom=0;
                        $dc_kg=0;
            return response()->json(['table'=>$table,'cover_qty'=>$cover_datas,'t_avl_qty'=>$t_avl_qty,'bom'=>$bom,'dc_kg'=>$dc_kg,'no_of_cover'=>$no_of_cover]);
        }elseif ($check1==1) {
            $dc_kg=round(($bom*$dc_quantity),2);

            foreach ($manufacturingPartDatas as $key => $manufacturingPartData) {
                $manufacturingPart=$manufacturingPartData->id;
                $m_part_id=$manufacturingPartData->id;
                $itemType=$manufacturingPartData->item_type;
            }
            $packingStrickerDetails=PackingStrickerDetails::with('rcmaster','partmaster','prepareuserdetails','covermaster')->where('part_id','=',$manufacturingPart)->where('cus_type_name','=',$cus_type_id)->where('cover_qty','=',$cover_qty)->select('rc_id','part_id','packing_master_id','cover_order_id','id','cover_qty','ok_packed_qty','pts_dc_issue_qty',DB::raw('((ok_packed_qty)-(pts_dc_issue_qty)) as avl_qty'))->havingRaw('avl_qty >?', [0])->get();
            // dd($packingStrickerDetails);
            $packingStrickerDetails_Count=PackingStrickerDetails::with('rcmaster','partmaster','prepareuserdetails','covermaster')->where('part_id','=',$manufacturingPart)->select('rc_id','part_id','packing_master_id','cover_order_id','id','cover_qty','ok_packed_qty','pts_dc_issue_qty',DB::raw('((ok_packed_qty)-(pts_dc_issue_qty)) as avl_qty'))->havingRaw('avl_qty >?', [0])->get()->count();
            $table=view('dc.pts_dc_rcqty',compact('packingStrickerDetails','dc_quantity','manufacturingPart','packingStrickerDetails_Count'))->render();
            return response()->json(['table'=>$table,'bom'=>$bom,'cover_qty'=>$cover_datas,'t_avl_qty'=>$t_avl_qty,'bom'=>$bom,'dc_kg'=>$dc_kg,'no_of_cover'=>$no_of_cover]);

            // if ($itemType==1) {
            //     $invoiceRcDatas=TransDataD11::with('rcmaster','partmaster')->where('next_process_id','=',$operation_id)->where('part_id','=',$manufacturingPart)->select('rc_id','part_id',DB::raw('((receive_qty)-(issue_qty)) as avl_qty'))
            //         ->havingRaw('avl_qty >?', [0])->get();
            //         $invoicerc_count=TransDataD11::with('rcmaster','partmaster')->where('next_process_id','=',$operation_id)->where('part_id','=',$manufacturingPart)->select('rc_id','part_id',DB::raw('((receive_qty)-(issue_qty)) as avl_qty'))
            //         ->havingRaw('avl_qty >?', [0])->get()->count();
            //     $table2=view('dc.dc_rcqty',compact('invoiceRcDatas','dc_quantity','m_part_id','invoicerc_count'))->render();
            //     return response()->json(['table'=>$table2,'bom'=>$bom,'dc_kg'=>$dc_kg]);
            // }
        }elseif ($check1>=1) {
            $dc_kg=round(($bom*$dc_quantity),2);

            $childPartDatas=ChildProductMaster::where('status','=',1)->where('part_id','=',$part_id)->where('item_type','=',1)->where('stocking_point','=',$operation_id)->get();
            // dd($childPartDatas);
            $table="";
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
                $table.=view('dc.dc_multi_rcqty',compact('invoiceRcDatas','dc_quantity','m_part_id'))->render();
            }
        return response()->json(['table'=>$table,'cover_qty'=>$cover_datas,'t_avl_qty'=>$t_avl_qty,'bom'=>$bom,'dc_kg'=>$dc_kg,'no_of_cover'=>$no_of_cover]);
        }
    }

    public function ptsdcItemRcQuantity(Request $request){
        // dd($request->all());
        $operation_id=$request->operation_id;
        $supplier_id=$request->supplier_id;
        $part_id=$request->part_id;
        $dc_quantity=$request->dc_quantity;
        $cover_quantity=$request->cover_quantity;
        $cus_type_id=$request->cus_type_id;
        $check=$request->child_part_count;
        $check1=$request->regular;
        $check2=$request->alter;
        $bom=$request->bom;
        $manufacturingPart=$request->manufacturingPart;
        $manufacturingPartDatas=ChildProductMaster::where('status','=',1)->where('part_id','=',$part_id)->get();

        // $count=DB::table('child_product_masters as a')
        // ->join('bom_masters AS b', 'a.id', '=', 'b.child_part_id')
        // ->select('b.manual_usage')
        // ->where('a.part_id','=',$part_id)
        // ->where('a.status','=',1)
        // ->where('b.status','=',1)
        // ->count();
        // if ($count>0) {
        //     $bomDatas=DB::table('child_product_masters as a')
        //     ->join('bom_masters AS b', 'a.id', '=', 'b.child_part_id')
        //     ->select('b.manual_usage')
        //     ->where('a.part_id','=',$part_id)
        //     ->where('a.stocking_point','=',22)
        //     ->where('a.status','=',1)
        //     ->where('b.status','=',1)
        //     ->first();
            // dd($bomDatas);
            // $bom=$bomDatas->manual_usage;
            $dc_kg=round(($bom*$dc_quantity),2);
        // } else {
        //     $bom=0;
        //     $dc_kg=0;
        // }

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
                        $bom=0;
                        $dc_kg=0;
            return response()->json(['table'=>$table]);
        }elseif ($check1==1) {
            foreach ($manufacturingPartDatas as $key => $manufacturingPartData) {
                $manufacturingPart=$manufacturingPartData->id;
                $m_part_id=$manufacturingPartData->id;
                $itemType=$manufacturingPartData->item_type;
            }
            $packingStrickerDetails=PackingStrickerDetails::with('rcmaster','partmaster','prepareuserdetails','covermaster')->where('part_id','=',$manufacturingPart)->where('cus_type_name','=',$cus_type_id)->where('cover_qty','=',$cover_quantity)->select('rc_id','part_id','packing_master_id','cover_order_id','id','cover_qty','ok_packed_qty','pts_dc_issue_qty',DB::raw('((ok_packed_qty)-(pts_dc_issue_qty)) as avl_qty'))->havingRaw('avl_qty >?', [0])->get();
            // dd($packingStrickerDetails);
            $packingStrickerDetails_Count=PackingStrickerDetails::with('rcmaster','partmaster','prepareuserdetails','covermaster')->where('part_id','=',$manufacturingPart)->select('rc_id','part_id','packing_master_id','cover_order_id','id','cover_qty','ok_packed_qty','pts_dc_issue_qty',DB::raw('((ok_packed_qty)-(pts_dc_issue_qty)) as avl_qty'))->havingRaw('avl_qty >?', [0])->get()->count();
            $table2=view('dc.pts_dc_rcqty',compact('packingStrickerDetails','dc_quantity','manufacturingPart','packingStrickerDetails_Count'))->render();
            return response()->json(['table'=>$table2,'bom'=>$bom,'dc_kg'=>$dc_kg]);

            // if ($itemType==1) {
            //     $invoiceRcDatas=TransDataD11::with('rcmaster','partmaster')->where('next_process_id','=',$operation_id)->where('part_id','=',$manufacturingPart)->select('rc_id','part_id',DB::raw('((receive_qty)-(issue_qty)) as avl_qty'))
            //         ->havingRaw('avl_qty >?', [0])->get();
            //         $invoicerc_count=TransDataD11::with('rcmaster','partmaster')->where('next_process_id','=',$operation_id)->where('part_id','=',$manufacturingPart)->select('rc_id','part_id',DB::raw('((receive_qty)-(issue_qty)) as avl_qty'))
            //         ->havingRaw('avl_qty >?', [0])->get()->count();
            //     $table2=view('dc.dc_rcqty',compact('invoiceRcDatas','dc_quantity','m_part_id','invoicerc_count'))->render();
            //     return response()->json(['table'=>$table2,'bom'=>$bom,'dc_kg'=>$dc_kg]);
            // }
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

    public function ptsDcIssueEntry(Request $request){
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
        $manufacturingPart=$request->manufacturingPart;
        $child_part_count=$request->child_part_count;
        $bom=$request->bom;
        $alter=$request->alter;
        $route_part_id=$request->route_part_id;
        $order_no=$request->order_no;
        $cover_order_id=$request->cover_order_id;
        $cover_qty=$request->cover_quantity;
        $no_of_cover=$request->no_of_cover;
        $id=$request->id;
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
                    return redirect()->route('ptsdcissue')->withMessage('Sorry...Route Card Quantity is not matching DC Quantity..!');
                }else {
                    // dd('ok');
                    $dcMasterData=DcMaster::with('procesmaster','supplier')->where('part_id','=',$part_id)->where('operation_id','=',$operation_id)->where('supplier_id','=',$supplier_id)->first();
                    $valuation_rate=(($dcMasterData->procesmaster->valuation_rate)/100);
                    $dcMaster_id=$dcMasterData->id;

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
                    // dd($rc_id);
                    $dcTransData=new DcTransactionDetails;
                    $dcTransData->rc_id=$rc_id;
                    $dcTransData->issue_date=$dc_date;
                    $dcTransData->dc_master_id=$dcMaster_id;
                    $dcTransData->issue_qty=$dc_quantity;
                    $dcTransData->no_cover=$no_of_cover;
                    $dcTransData->cover_qty=$cover_qty;
                    $dcTransData->unit_rate=$part_rate;
                    $dcTransData->basic_rate=$basic_value;
                    $dcTransData->total_rate=$basic_value;
                    $dcTransData->issue_wt=$issue_wt;
                    $dcTransData->trans_mode=$trans_mode;
                    $dcTransData->vehicle_no=$vehicle_no??NULL;
                    $dcTransData->remarks=$remarks??NULL;
                    $dcTransData->prepared_by = auth()->user()->id;
                    $dcTransData->save();

                    $dc_id=$dcTransData->id;

                    $dcPrintData=new DcPrint;
                    $dcPrintData->s_no=0;
                    $dcPrintData->dc_id=$dc_id;
                    $dcPrintData->from_unit=4;
                    $dcPrintData->print_status=0;
                    $dcPrintData->prepared_by = auth()->user()->id;
                    $dcPrintData->save();

                    $currentProcess=ProductProcessMaster::where('part_id','=',$manufacturingPart)->where('process_master_id','=',$operation_id)->first();
                    // dd($currentProcess);
                    $current_order_id=$currentProcess->process_order_id;
                    $current_product_process_id=$currentProcess->id;

                    $nextProcess=ProductProcessMaster::where('part_id','=',$manufacturingPart)->where('process_order_id','>',$current_order_id)->where('status','=',1)->first();
                    $next_product_order_id=$nextProcess->process_order_id;
                    $next_product_process_id=$nextProcess->id;
                    $next_process_id=$nextProcess->process_master_id;
                    // dd($nextProcess);

                    $d11Datas=new TransDataD11;
                    $d11Datas->open_date=$dc_date;
                    $d11Datas->rc_id=$rc_id;
                    $d11Datas->part_id=$manufacturingPart;
                    $d11Datas->process_id=$operation_id;
                    $d11Datas->product_process_id=$current_product_process_id;
                    $d11Datas->next_process_id=$next_process_id;
                    $d11Datas->next_product_process_id=$next_product_process_id;
                    $d11Datas->process_issue_qty=$dc_quantity;
                    $d11Datas->prepared_by = auth()->user()->id;
                    $d11Datas->save();

                    foreach ($route_card_id as $key => $card_id) {
                        if ($rc_issue_quantity[$key]!=0) {
                            $previousD11Datas=TransDataD11::where('rc_id','=',$card_id)->first();
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

                            $nextProcess=ProductProcessMaster::with('processMaster')->where('part_id','=',$route_part_id[$key])->where('process_order_id','>',$current_order_id)->where('status','=',1)->first();
                            $next_product_order_id=$nextProcess->process_order_id;
                            $next_product_process_id=$nextProcess->id;
                            $next_process_id=$nextProcess->process_master_id;
                            $next_process=$nextProcess->processMaster->operation;

                            $d12Datas=new TransDataD12;
                            $d12Datas->open_date=$dc_date;
                            $d12Datas->rc_id=$rc_id;
                            $d12Datas->previous_rc_id=$card_id;
                            $d12Datas->part_id=$part_id;
                            $d12Datas->process_id=$operation_id;
                            $d12Datas->product_process_id=$current_product_process_id;
                            $d12Datas->receive_qty=$rc_issue_quantity[$key];
                            $d12Datas->issue_qty=$rc_issue_quantity[$key];
                            $d12Datas->prepared_by = auth()->user()->id;
                            $d12Datas->save();

                            $d13Datas=new TransDataD13;
                            $d13Datas->rc_id=$rc_id;
                            $d13Datas->previous_rc_id=$card_id;
                            $d13Datas->prepared_by = auth()->user()->id;
                            $d13Datas->save();

                            $strickerDatas=PackingStrickerDetails::find($id[$key]);
                            $strickerDatas->pts_dc_issue_qty=$rc_issue_quantity[$key];
                            $strickerDatas->updated_by = auth()->user()->id;
                            $strickerDatas->updated_at = Carbon::now();
                            $strickerDatas->update();

                            $PtsTransactionSummary=PtsTransactionSummary::select('*',DB::raw('((cle_issue_qty)-(pts_store_dc_issue_qty)) as avl_qty'))->where('part_id','=',$route_part_id[$key])
                            ->where('rc_id','=',$card_id)->havingRaw('avl_qty >?', [0])->first();
                            // dd($PtsTransactionSummary);
                            $old_pts_store_dc_issue_qty=$PtsTransactionSummary->pts_store_dc_issue_qty;
                            $PtsTransactionSummary->pts_store_dc_issue_qty=(($rc_issue_quantity[$key])+($old_pts_store_dc_issue_qty));
                            $PtsTransactionSummary->updated_by = auth()->user()->id;
                            $PtsTransactionSummary->updated_at = Carbon::now();
                            $PtsTransactionSummary->update();

                            $PtsTransactionDetail=new PtsTransactionDetail;
                            $PtsTransactionDetail->open_date=$dc_date;
                            $PtsTransactionDetail->part_id=$route_part_id[$key];
                            $PtsTransactionDetail->process_id=$operation_id;
                            $PtsTransactionDetail->process=$next_process;
                            $PtsTransactionDetail->rc_id=$rc_id;
                            $PtsTransactionDetail->previous_rc_id=$card_id;
                            $PtsTransactionDetail->issue_qty=$rc_issue_quantity[$key];
                            $PtsTransactionDetail->prepared_by = auth()->user()->id;
                            $PtsTransactionDetail->save();

                            $coverStrickerDetails=new CoverStrickerDetails;
                            $coverStrickerDetails->part_id=$route_part_id[$key];
                            $coverStrickerDetails->rc_id=$rc_id;
                            $coverStrickerDetails->prc_id=$card_id;
                            $coverStrickerDetails->stricker_id=$id[$key];
                            $coverStrickerDetails->total_cover_qty=$strickerDatas->cover_qty;
                            $coverStrickerDetails->total_issue_qty=$rc_issue_quantity[$key];
                            $coverStrickerDetails->prepared_by = auth()->user()->id;
                            $coverStrickerDetails->save();

                            DB::commit();
                        }
                    }
                    return redirect()->route('ptsdcissue')->withSuccess('Paintshop Delivery Challan Created Successfully!');
                }
            }
        }
    }

}
