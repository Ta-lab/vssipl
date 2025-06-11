<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Http\Request;
use App\Models\SalesDespatchPlanSummary;
use App\Models\SalesDespatchPlanTransaction;
use App\Models\CustomerMaster;
use App\Models\CustomerProductMaster;
use App\Models\CustomerPoMaster;
use App\Models\TransDataD11;
use App\Models\TransDataD12;
use App\Models\TransDataD13;
use App\Models\ChildProductMaster;
use App\Models\CoverStrickerDetails;
use App\Models\PtsTransactionSummary;
use App\Models\PtsTransactionDetail;
use App\Models\PackingMaster;
use App\Models\InvoiceDetails;
use App\Models\InvoicePrint;
use App\Models\RouteMaster;
use App\Models\PackingStrickerDetails;
use App\Models\ProductProcessMaster;
use App\Http\Requests\StoreSalesDespatchPlanSummaryRequest;
use App\Http\Requests\UpdateSalesDespatchPlanSummaryRequest;
use App\Models\ProductMaster;
use Illuminate\Support\Number;
use Illuminate\Support\Facades\DB;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Http\Response;
use Spatie\Browsershot\Browsershot;
use Carbon\Carbon;

class SalesDespatchPlanSummaryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $salesDespatchPlanSummaries=SalesDespatchPlanSummary::with('packingmaster','productmaster','customermaster')->get();
        // dd($salesDespatchPlanSummaries);
        return view('sales_despatch_plan.index',compact('salesDespatchPlanSummaries'));
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
        $rc="TP-U1";
		$current_rcno=$rc.$finacial_year;
        $count=SalesDespatchPlanSummary::with('packingmaster','productmaster','customermaster')->where('plan_no','LIKE','%'.$current_rcno.'%')->orderBy('id','DESC')->get()->count();
        if ($count>0) {
            $rc_data=SalesDespatchPlanSummary::with('packingmaster','productmaster','customermaster')->where('plan_no','LIKE','%'.$current_rcno.'%')->orderBy('id','DESC')->first();
            $rcnumber=$rc_data['plan_no']??NULL;
            $old_rcnumber=str_replace($current_rcno,"",$rcnumber);
            $old_rcnumber_data=str_pad($old_rcnumber+1,5,0,STR_PAD_LEFT);
            $new_rcnumber=$current_rcno.$old_rcnumber_data;
        }else{
            $str='00001';
            $new_rcnumber=$current_rcno.$str;
        }
        $customerDatas=CustomerMaster::where('status','=',1)->get();
        // dd($customerDatas);
        return view('sales_despatch_plan.create',compact('customerDatas','new_rcnumber','current_date'));

    }

    public function planCusPartFetchData(Request $request)
    {
        // dd($request->all());
        $count=CustomerProductMaster::with('productmasters')->where('cus_id','=',$request->cus_id)->where('status','=',1)->get()->count();
        if ($count>0) {
            $customerProductMasterDatas=CustomerProductMaster::with('productmasters')->where('cus_id','=',$request->cus_id)->where('status','=',1)->get();
            // dd($customerProductMasterDatas);
            $html='<option value="" selected></option>';
            foreach ($customerProductMasterDatas as $key => $customerProductMasterData) {
                $html.='<option value="'.$customerProductMasterData->productmasters->id.'">'.$customerProductMasterData->productmasters->part_no.'</option>';
            }
        } else {
            $html='<option value="" selected>No Customer Available</option>';
        }
        return response()->json(['html'=>$html]);
    }

    public function planPartCoverfetchData(Request $request){
        // dd($request->all());
        $part_id=$request->part_id;
        $cus_id=$request->cus_id;
        $manufacturingDatas=ChildProductMaster::where('part_id','=',$part_id)->where('status','=',1)->where('stocking_point','=',22)->first();
        $item_type=$manufacturingDatas->item_type;
        $pickup_part_id=$manufacturingDatas->pickup_part_id;
        if ($item_type==1) {
            $manufacturing_part_id=$manufacturingDatas->id;
        }else {
            $childManufacturingDatas=ChildProductMaster::where('part_id','=',$pickup_part_id)->where('status','=',1)->where('stocking_point','=',22)->where('item_type','=',1)->first();
            $manufacturing_part_id=$childManufacturingDatas->id;
        }
        // dd($manufacturing_part_id);
        $packingMaster=PackingMaster::with('covermaster')->where('part_id','=',$part_id)->where('cus_id','=',$cus_id)->where('status','=',1)->first();
        // dd($packingMaster);
        $cover_datas='<option value="'.$packingMaster->covermaster->id.'" selected>'.$packingMaster->covermaster->cover_name.'&'.$packingMaster->covermaster->cover_size.'</option>';
        $cus_type_name='<option value="'.$packingMaster->cus_type_name.'" selected>'.$packingMaster->cus_type_name.'</option>';
        $packing_master_id=$packingMaster->id;
        $cover_qty=$packingMaster->cover_qty;
        return response()->json(['manufacturing_part_id'=>$manufacturing_part_id,'cover_datas'=>$cover_datas,'cus_type_name'=>$cus_type_name,'cover_qty'=>$cover_qty,'packing_master_id'=>$packing_master_id]);

        // $cus=
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSalesDespatchPlanSummaryRequest $request)
    {
        //SELECT `id`, `plan_no`, `open_date`, `cus_id`, `part_id`, `packing_master_id`, `cus_req_qty`, `req_cover_qty`, `actual_fg_qty`, `to_confirm_qty`, `invoiced_qty`, `status`, `remarks`, `prepared_by`, `updated_by`, `created_at`, `updated_at` FROM `sales_despatch_plan_summaries` WHERE 1
        // dd($request->all());
        $salesdespatchplansummary=new salesDespatchPlanSummary;
        $salesdespatchplansummary->plan_no=$request->plan_no;
        $salesdespatchplansummary->open_date=$request->open_date;
        $salesdespatchplansummary->cus_id=$request->cus_id;
        $salesdespatchplansummary->part_id=$request->part_id;
        $salesdespatchplansummary->manufacturing_part_id=$request->manufacturing_part_id;
        $salesdespatchplansummary->packing_master_id=$request->packing_master_id;
        $salesdespatchplansummary->cus_req_qty=$request->cus_req_qty;
        $salesdespatchplansummary->req_cover_qty=$request->no_of_cover;
        $salesdespatchplansummary->prepared_by = auth()->user()->id;
        $salesdespatchplansummary->save();
        return redirect()->route('salesdespatchplansummary.index')->withSuccess('Despatch Plan Created Successfully!');

    }

    // fg despatch plan list
    public function salesPlanFGList(){
        $salesDespatchPlanSummaries=SalesDespatchPlanSummary::with('packingmaster','productmaster','customermaster')->get();
        return view('sales_despatch_plan.fg_view',compact('salesDespatchPlanSummaries'));
    }


    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        //
        $salesdespatchplansummary=SalesDespatchPlanSummary::with('packingmaster','productmaster','customermaster')->find($id);
        $cus_req_qty=$salesdespatchplansummary->cus_req_qty;
        $actual_fg_qty=$salesdespatchplansummary->actual_fg_qty;
        $manufacturing_part_id=$salesdespatchplansummary->manufacturing_part_id;
        $cover_qty=$salesdespatchplansummary->packingmaster->cover_qty;
        $count=SalesDespatchPlanTransaction::with('productmaster','packingstrickerdetails','customermaster','salesplanmaster')->where('plan_id','=',$id)->count();
        // dd($count);
        $salesdespatchplantransactions=SalesDespatchPlanTransaction::with('packingstrickerdetails','productmaster','customermaster','salesplanmaster','manufacturingpartmaster')->where('plan_id','=',$id)->orderBy('created_at','DESC')->get();
        $fifocheck=CoverStrickerDetails::with('stickermaster')->where('part_id','=',$manufacturing_part_id)->where('total_cover_qty','=',$cover_qty)->where('total_reject_qty','=',0)->where('total_receive_qty','!=',0)->where('total_rework_qty','=',0)->where('cover_status','=',1)->first();
        if ($cus_req_qty!=$actual_fg_qty) {
            if ($fifocheck!='') {
                $fifostricker_id=$fifocheck->stricker_id;
                $fifodc_id=$fifocheck->prc_id;
                $fifoDetails=CoverStrickerDetails::with('stickermaster')->where('part_id','=',$manufacturing_part_id)->where('total_cover_qty','=',$cover_qty)->where('total_reject_qty','=',0)->where('total_receive_qty','!=',0)->where('total_rework_qty','=',0)->where('cover_status','=',1)->where('prc_id','=',$fifodc_id)->get();
            // dd($fifoDetails);
            // dd($salesdespatchplantransactions);
                return view('sales_despatch_plan.fg_create',compact('salesdespatchplansummary','salesdespatchplantransactions','count','fifoDetails'));
            } else {
                return redirect()->route('salesplanfglist')->withError('Sorry No Stock For This Part Number In FG Area !');
            }
        } else {
            return redirect()->route('salesplanfglist')->withSuccess('This Plan Completed Successfully !');
        }


    }

    public function salesPlanFGStore(Request $request){
        // dd($request->all());
        date_default_timezone_set('Asia/Kolkata');
        $current_date=date('Y-m-d');
        $stricker_id=$request->stricker_id;
        $cus_id=$request->cus_id;
        $part_id=$request->part_id;
        $plan_id=$request->plan_id;
        $plan_no=$request->plan_no;
        $open_date=$request->open_date;
        $manufacturing_part_id=$request->manufacturing_part_id;
        $packing_master_id=$request->packing_master_id;
        $cover_id=$request->cover_id;
        $cus_type_name=$request->cus_type_name;
        $cover_qty=$request->cover_qty;
        $no_of_cover=$request->no_of_cover;
        $cus_req_qty=$request->cus_req_qty;
        $actual_fg_qty=$request->actual_fg_qty;

        if ($actual_fg_qty==$cus_req_qty) {
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
            $html.='<svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Danger:"><use xlink:href="#exclamation-triangle-fill"/></svg><div>Sorry.You Already Completed This Sales Plan...</div></div>';
        }else{
            $packingstrickerdetails=PackingStrickerDetails::find($stricker_id);
            $stricker_part_id=$packingstrickerdetails->part_id;
            // dd($stricker_part_id);
            // dd($manufacturing_part_id);
            if ($stricker_part_id==$manufacturing_part_id) {
                $part_no_success=true;
                $fg_receive_count=CoverStrickerDetails::where('stricker_id','=',$stricker_id)->get()->count();
                // dd($fg_receive_count);
                if ($fg_receive_count>0) {
                    $fg_receive_success=true;
                    $plan_receive_check_count=SalesDespatchPlanTransaction::where('stricker_id','=',$stricker_id)->get()->count();
                    if ($plan_receive_check_count>0) {
                        $plan_receive_success=true;
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
                    } else {
                        $plan_receive_success=false;
                        $issuecount=CoverStrickerDetails::with('stickermaster')->where('part_id','=',$manufacturing_part_id)->where('total_cover_qty','=',$cover_qty)->where('total_reject_qty','=',0)->where('total_receive_qty','!=',0)->where('total_rework_qty','=',0)->where('cover_status','=',1)->where('stricker_id','=',$stricker_id)->get()->count();
                        // dd($issuecount);
                        if ($issuecount>0) {
                            $fifocount=CoverStrickerDetails::with('stickermaster')->where('part_id','=',$manufacturing_part_id)->where('total_cover_qty','=',$cover_qty)->where('total_reject_qty','=',0)->where('total_receive_qty','!=',0)->where('total_rework_qty','=',0)->where('cover_status','=',1)->get()->count();
                            // dd($fifocount);
                            if ($fifocount>0) {
                                $fifocheck=CoverStrickerDetails::with('stickermaster')->where('part_id','=',$manufacturing_part_id)->where('total_cover_qty','=',$cover_qty)->where('total_reject_qty','=',0)->where('total_receive_qty','!=',0)->where('total_rework_qty','=',0)->where('cover_status','=',1)->first();
                                $fifostricker_id=$fifocheck->stricker_id;
                                $fifodc_id=$fifocheck->prc_id;
                                $issuecheck=CoverStrickerDetails::with('stickermaster')->where('part_id','=',$manufacturing_part_id)->where('total_cover_qty','=',$cover_qty)->where('total_reject_qty','=',0)->where('total_receive_qty','!=',0)->where('total_rework_qty','=',0)->where('cover_status','=',1)->where('stricker_id','=',$stricker_id)->first();
                                $issuedc_id=$issuecheck->prc_id;
                                // dd($fifostricker_id);
                                // if ($fifostricker_id==$stricker_id) {
                                    // if ($fifodc_id==$issuedc_id) {
                                    // dd('ok');
                                        $fg_cover_receivedata=CoverStrickerDetails::where('stricker_id','=',$stricker_id)->first();
                                        $fg_totalcover_qty=$fg_cover_receivedata->total_cover_qty;
                                        $fg_totalreceive_qty=$fg_cover_receivedata->total_receive_qty;
                                        $fg_cover_receivedata->cover_status=0;
                                        $fg_cover_receivedata->updated_by = auth()->user()->id;
                                        $fg_cover_receivedata->updated_at = Carbon::now();
                                        $fg_cover_receivedata->update();

                                        $prc_id=$fg_cover_receivedata->rc_id;
                                        // dd($prc_id);
                                        // dd($fg_totalreceive_qty);
                                        $SalesDespatchPlanSummary=SalesDespatchPlanSummary::find($plan_id);
                                        // dd($SalesDespatchPlanSummary);
                                        $old_actual_fg_qty=$SalesDespatchPlanSummary->actual_fg_qty;
                                        $SalesDespatchPlanSummary->actual_fg_qty=(($old_actual_fg_qty)+($fg_totalreceive_qty));
                                        // dd($SalesDespatchPlanSummary->actual_fg_qty);
                                        if ($SalesDespatchPlanSummary->actual_fg_qty==$SalesDespatchPlanSummary->cus_req_qty) {
                                            $SalesDespatchPlanSummary->status = 2;
                                        } else {
                                            $SalesDespatchPlanSummary->status = 1;
                                        }
                                        $SalesDespatchPlanSummary->updated_by = auth()->user()->id;
                                        $SalesDespatchPlanSummary->updated_at = Carbon::now();
                                        $SalesDespatchPlanSummary->update();

                                     // <svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Success:"><use xlink:href="#check-circle-fill"/></svg>


                                        $SalesDespatchPlanTransaction=new SalesDespatchPlanTransaction;
                                        $SalesDespatchPlanTransaction->plan_id=$plan_id;
                                        $SalesDespatchPlanTransaction->open_date=$current_date;
                                        $SalesDespatchPlanTransaction->cus_id=$cus_id;
                                        $SalesDespatchPlanTransaction->part_id=$part_id;
                                        $SalesDespatchPlanTransaction->manufacturing_part_id=$manufacturing_part_id;
                                        $SalesDespatchPlanTransaction->prc_id=$prc_id;
                                        $SalesDespatchPlanTransaction->stricker_id=$stricker_id;
                                        $SalesDespatchPlanTransaction->cover_qty=$cover_qty;
                                        $SalesDespatchPlanTransaction->receive_qty=$fg_totalreceive_qty;
                                        $SalesDespatchPlanTransaction->prepared_by = auth()->user()->id;
                                        $SalesDespatchPlanTransaction->save();

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
                                    $html.='<svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Success:"><use xlink:href="#check-circle-fill"/></svg><div>This Cover Received Successfully Against Sales Plan..!</div></div>';
                                // } else {

                                    // dd('not ok');

                                    // $html='<svg xmlns="http://www.w3.org/2000/svg" style="display: none;">
                                    // <symbol id="check-circle-fill" fill="currentColor" viewBox="0 0 16 16">
                                    //     <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                                    // </symbol>
                                    // <symbol id="info-fill" fill="currentColor" viewBox="0 0 16 16">
                                    //     <path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zm.93-9.412-1 4.705c-.07.34.029.533.304.533.194 0 .487-.07.686-.246l-.088.416c-.287.346-.92.598-1.465.598-.703 0-1.002-.422-.808-1.319l.738-3.468c.064-.293.006-.399-.287-.47l-.451-.081.082-.381 2.29-.287zM8 5.5a1 1 0 1 1 0-2 1 1 0 0 1 0 2z"/>
                                    // </symbol>
                                    // <symbol id="exclamation-triangle-fill" fill="currentColor" viewBox="0 0 16 16">
                                    //     <path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
                                    // </symbol>
                                    // </svg><div class="alert alert-danger d-flex align-items-center" role="alert">';
                                    // // $html.='<svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Danger:"><use xlink:href="#exclamation-triangle-fill"/></svg><div>Sorry Follow The RC No Is '.$fifocheck->stickermaster->rcmaster->rc_id.' And Cover No Is '.$fifocheck->stickermaster->cover_order_id.'</div></div>';
                                    // $html.='<svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Danger:"><use xlink:href="#exclamation-triangle-fill"/></svg><div>Sorry Follow The RC No Is '.$fifocheck->stickermaster->rcmaster->rc_id.'</div></div>';
                                // }
                            } else {
                                $fg_receive_success=false;
                                $plan_receive_success=false;
                                $InvoicePartDatas=ProductMaster::find($part_id);
                                $part_no=$InvoicePartDatas->part_no;
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
                                $html.='<svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Danger:"><use xlink:href="#exclamation-triangle-fill"/></svg><div>Sorry No Stock In FG Area For This Part Number Is '.$part_no.' And Cover Qty Is '.$cover_qty.'</div></div>';
                            }
                        } else {
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
                            $html.='<svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Danger:"><use xlink:href="#exclamation-triangle-fill"/></svg><div>Sorry This Cover is not received In FG Team..</div></div>';
                        }
                    }
                } else {
                    $fg_receive_success=false;
                    $plan_receive_success=false;
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
                    $html.='<svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Danger:"><use xlink:href="#exclamation-triangle-fill"/></svg><div>This Cover Not Receive In FG Area</div></div>';
                }
            }else{
                $part_no_success=false;
                $fg_receive_success=false;
                $plan_receive_success=false;
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
                $html.='<svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Danger:"><use xlink:href="#exclamation-triangle-fill"/></svg><div>This Part Number Not Matched Your Cover Part Number.Please Check The Part Number.</div></div>';
            }
        }
        // dd($plan_receive_success);
        return response()->json(['html'=>$html]);

    }

    public function salesplanconfirm() {
        $salesDespatchPlanSummaries=SalesDespatchPlanSummary::with('packingmaster','productmaster','customermaster')->whereColumn('cus_req_qty','=','actual_fg_qty')->where('status','=',2)->get();
        // dd($salesDespatchPlanSummaries);
        return view('sales_despatch_plan.sales_confirmation',compact('salesDespatchPlanSummaries'));
    }

    public function salesConfirmationEntry(Request $request){
        // dd($request->all());
        $plan_id=$request->sub_id;
        $manufacturing_part_id=$request->manufacturing_part_id;
        $plan_no=$request->plan_no;
        $open_date=$request->open_date;
        $cus_id=$request->cus_id;
        $cus_name=$request->cus_name;
        $cus_type_name=$request->cus_type_name;
        $part_id=$request->part_id;
        $cover_qty=$request->cover_qty;
        $cus_req_qty=$request->cus_req_qty;
        $actual_fg_qty=$request->actual_fg_qty;
        $to_confirm_qty=$request->to_confirm_qty;
        $invoice_quantity=$request->to_confirm_qty;
        $status=$request->status;
        $remarks=$request->remarks;
        foreach ($plan_id as $key => $id) {
            // dd($id);
            // dd($plan_no[$id]);
            date_default_timezone_set('Asia/Kolkata');
            $current_date=date('Y-m-d');
            $current_year=date('Y');
            $current_time=date("H:i");
            # code...
            if ($status[$id]==3) {

                if ( date('m') > 3 ) {
                    $year = date('y');
                    $next_year=date('y')+1;
                    $finacial_year=$year.$next_year;
                }
                else {
                    $year = date('y') - 1;
                    $next_year=date('y');
                    $finacial_year=$year.$next_year;
                }
                // dd($finacial_year);
                    $rc="U1";
                $current_rcno=$rc.$finacial_year;
                $current_rcno1=$rc.$finacial_year.'-';
                $count1=RouteMaster::where('process_id',22)->where('rc_id','LIKE','%'.$current_rcno.'%')->orderBy('rc_id', 'DESC')->get()->count();
                if ($count1 > 0) {
                    $rc_data=RouteMaster::where('process_id',22)->where('rc_id','LIKE','%'.$current_rcno.'%')->orderBy('rc_id', 'DESC')->first();
                    $rcnumber=$rc_data['rc_id']??NULL;
                    // dd($rcnumber);

                    $old_rcnumber=str_replace($current_rcno1,"",$rcnumber);
                    // dd($old_rcnumber);
                    $old_rcnumber_data=str_pad($old_rcnumber+1,5,0,STR_PAD_LEFT);
                    // dd($old_rcnumber_data);
                    $new_rcnumber=$current_rcno1.$old_rcnumber_data;
                }else{
                    $str='00001';
                    $new_rcnumber=$current_rcno."-".$str;
                }
                // dd($new_rcnumber);
                $operation_id=22;
                $rcMaster=new RouteMaster;
                $rcMaster->create_date=$current_date;
                $rcMaster->process_id=$operation_id;
                $rcMaster->rc_id=$new_rcnumber;
                $rcMaster->prepared_by=auth()->user()->id;
                $rcMaster->save();

                $rcMasterData=RouteMaster::where('rc_id','=',$new_rcnumber)->where('process_id','=',$operation_id)->first();
                $rc_id=$rcMasterData->id;

                $customer_product_datas=CustomerProductMaster::where('part_id','=',$part_id[$id])->where('cus_id','=',$cus_id[$id])->where('status','=',1)->first();
                // dd($customer_product_datas);
                $customer_product_id=$customer_product_datas->id;
                $customer_product_hsnc=$customer_product_datas->part_hsnc;
                $customer_product_uom=$customer_product_datas->uom_id;
                $customer_product_rate=$customer_product_datas->part_rate;
                $customer_product_part_per=$customer_product_datas->part_per;
                $customer_product_currency_id=$customer_product_datas->currency_id;
                $customer_product_packing_charges=$customer_product_datas->packing_charges;
                $customer_product_cgst=$customer_product_datas->cgst;
                $customer_product_sgst=$customer_product_datas->sgst;
                $customer_product_igst=$customer_product_datas->igst;
                $customer_product_trans_mode=$customer_product_datas->trans_mode;
                $customer_product_pan_no=$customer_product_datas->pan_no;
                $customer_product_po_id=$customer_product_datas->cus_po_id;
                $customer_product_document_type=$customer_product_datas->document_type;
                $customer_product_igst_on_intra=$customer_product_datas->igst_on_intra;
                $customer_product_reverse_charge=$customer_product_datas->reverse_charge;


                if ($customer_product_igst!=0) {
                    $cori='IGST';
                }else {
                    $cori='CGST';
                }
                $part_rate=round((($customer_product_datas->part_rate)/($customer_product_datas->part_per)),2);
                $cus_cgst=(($customer_product_cgst*0.01));
                $cus_sgst=(($customer_product_sgst*0.01));
                $cus_igst=(($customer_product_igst*0.01));
                $cus_packing_charge=(($customer_product_packing_charges*0.01));

                $basic_value=round((($part_rate)*($invoice_quantity[$id])),2);
                $totalcgst_amt=round((($basic_value)*($cus_cgst)),2);
                $totalsgst_amt=round((($basic_value)*($cus_sgst)),2);
                $totaligst_amt=round((($basic_value)*($cus_igst)),2);
                $totalpacking_charge=round((($basic_value)*($cus_packing_charge)),2);
                // dd($invoice_quantity[$id]);
                // dd($basic_value);
                // dd($totalcgst_amt);
                // dd($totalsgst_amt);
                // dd($totaligst_amt);
                // dd($totalpacking_charge);
                $invtotal=(($basic_value)+($totalcgst_amt)+($totalsgst_amt)+($totaligst_amt)+($totalpacking_charge));

                $invoiceDatas=new InvoiceDetails;
                $invoiceDatas->invoice_no=$rc_id;
                $invoiceDatas->invoice_date=$current_date;
                $invoiceDatas->invoice_time=$current_time;
                $invoiceDatas->cus_product_id=$customer_product_id;
                $invoiceDatas->part_id=$part_id[$id];
                $invoiceDatas->part_hsnc=$customer_product_hsnc;
                $invoiceDatas->cus_po_id=$customer_product_po_id;
                $invoiceDatas->qty=$invoice_quantity[$id];
                $invoiceDatas->uom_id=$customer_product_uom;
                $invoiceDatas->part_per=$customer_product_part_per;
                $invoiceDatas->part_rate=$part_rate;
                $invoiceDatas->currency_id=$customer_product_currency_id;
                $invoiceDatas->packing_charge=$customer_product_packing_charges;
                $invoiceDatas->cgst=$customer_product_cgst;
                $invoiceDatas->sgst=$customer_product_sgst;
                $invoiceDatas->igst=$customer_product_igst;
                // $invoiceDatas->tcs=$customer_product_uom;
                $invoiceDatas->basic_value=$basic_value;
                $invoiceDatas->packing_charge_amt=$totalpacking_charge;
                $invoiceDatas->cgstamt=$totalcgst_amt;
                $invoiceDatas->sgstamt=$totalsgst_amt;
                $invoiceDatas->igstamt=$totaligst_amt;
                // $invoiceDatas->tcsamt=$customer_product_uom;
                $invoiceDatas->invtotal=$invtotal;
                $invoiceDatas->cori=$cori;
                $invoiceDatas->trans_mode=$customer_product_trans_mode;
                $invoiceDatas->document_type=$customer_product_document_type;
                $invoiceDatas->igst_on_intra=$customer_product_igst_on_intra;
                $invoiceDatas->reverse_charge=$customer_product_reverse_charge;
                $invoiceDatas->vehicle_no='';
                $invoiceDatas->ok='F';
                $invoiceDatas->remarks=$remarks[$id];
                $invoiceDatas->prepared_by=auth()->user()->id;
                $invoiceDatas->save();

                $invoicePrint=new InvoicePrint;
                $invoicePrint->invoice_no=$rc_id;
                $invoicePrint->prepared_by=auth()->user()->id;
                $invoicePrint->save();

                $salesDespatchPlanSummaries=SalesDespatchPlanSummary::with('packingmaster','productmaster','customermaster')->find($id);
                // dd($salesDespatchPlanSummaries);
                $salesDespatchPlanSummaries->to_confirm_qty=$invoice_quantity[$id];
                $salesDespatchPlanSummaries->invoiced_qty=$invoice_quantity[$id];
                $salesDespatchPlanSummaries->status=$status[$id];
                $salesDespatchPlanSummaries->remarks=$remarks[$id]??NULL;
                $salesDespatchPlanSummaries->updated_by = auth()->user()->id;
                $salesDespatchPlanSummaries->updated_at = Carbon::now();
                $salesDespatchPlanSummaries->update();

                $salesDespatchPlanTransaction_datas=SalesDespatchPlanTransaction::with('packingstrickerdetails','productmaster','customermaster','salesplanmaster','manufacturingpartmaster')->where('plan_id','=',$id)->get();
                // dd($salesDespatchPlanTransaction_datas);
                foreach ($salesDespatchPlanTransaction_datas as $key => $salesDespatchPlanTransaction_data) {
                    $receive_qty=$salesDespatchPlanTransaction_data->receive_qty;
                    // dump($receive_qty);
                    // dd($receive_qty);
                    $prc_id=$salesDespatchPlanTransaction_data->prc_id;
                    $manufacturing_part_id=$salesDespatchPlanTransaction_data->manufacturing_part_id;
                    $stricker_id=$salesDespatchPlanTransaction_data->stricker_id;
                    $salesDespatchPlanTransaction_data->rc_id=$rc_id;
                    $salesDespatchPlanTransaction_data->to_confirm_qty=$receive_qty;
                    $salesDespatchPlanTransaction_data->invoiced_qty=$receive_qty;
                    $salesDespatchPlanTransaction_data->status=$status[$id];
                    $salesDespatchPlanTransaction_data->updated_by = auth()->user()->id;
                    $salesDespatchPlanTransaction_data->updated_at = Carbon::now();
                    $salesDespatchPlanTransaction_data->update();

                    $coverStrickerDetails=CoverStrickerDetails::where('stricker_id','=',$stricker_id)->where('part_id','=',$manufacturing_part_id)->where('rc_id','=',$prc_id)->first();
                    // dd($coverStrickerDetails);
                    $coverStrickerDetails->total_return_issue_qty=$receive_qty;
                    $coverStrickerDetails->updated_by = auth()->user()->id;
                    $coverStrickerDetails->updated_at = Carbon::now();
                    $coverStrickerDetails->update();

                    $packingStrickerDetails=PackingStrickerDetails::find($stricker_id);
                    // dd($packingStrickerDetails);
                    $packingStrickerDetails->invoice_qty=$receive_qty;
                    $packingStrickerDetails->updated_by = auth()->user()->id;
                    $packingStrickerDetails->updated_at = Carbon::now();
                    $packingStrickerDetails->update();

                    $previousT11Datas=TransDataD11::where('rc_id','=',$prc_id)->where('next_process_id','=',$operation_id)->where('part_id','=',$manufacturing_part_id)->first();
                    // dd($previousT11Datas);
                    $old_issue_qty=$previousT11Datas->issue_qty;
                    $total_issue_qty=(($old_issue_qty)+$receive_qty);
                    $previousT11Datas->issue_qty=$total_issue_qty;
                    $previousT11Datas->updated_by = auth()->user()->id;
                    $previousT11Datas->updated_at = Carbon::now();
                    $previousT11Datas->update();

                    $currentProcess=ProductProcessMaster::where('part_id','=',$manufacturing_part_id)->where('process_master_id','=',$operation_id)->first();
                    // dd($currentProcess);
                    $current_order_id=$currentProcess->process_order_id;
                    $current_product_process_id=$currentProcess->id;
                    $value='STOCKING POINT';
                    $nextProcess=ProductProcessMaster::with('processMaster')->where('process_order_id','>',$current_order_id)->where('status','=',1)->first();
                    // dd($nextProcess);

                    $next_order_id=$nextProcess->process_order_id;
                    $next_product_process_id=$nextProcess->id;
                    $next_process_id=$nextProcess->process_master_id;

                    $d11Datas=new TransDataD11;
                    $d11Datas->open_date=$current_date;
                    $d11Datas->rc_id=$rc_id;
                    $d11Datas->part_id=$manufacturing_part_id;
                    $d11Datas->process_id=$operation_id;
                    $d11Datas->product_process_id=$current_product_process_id;
                    $d11Datas->next_process_id=$next_process_id;
                    $d11Datas->next_product_process_id=$next_product_process_id;
                    $d11Datas->issue_qty=$receive_qty;
                    $d11Datas->prepared_by = auth()->user()->id;
                    $d11Datas->save();

                    $d12Datas=new TransDataD12;
                    $d12Datas->open_date=$current_date;
                    $d12Datas->rc_id=$rc_id;
                    $d12Datas->previous_rc_id=$prc_id;
                    $d12Datas->part_id=$manufacturing_part_id;
                    $d12Datas->process_id=$operation_id;
                    $d12Datas->product_process_id=$current_product_process_id;
                    $d12Datas->issue_qty=$receive_qty;
                    $d12Datas->prepared_by = auth()->user()->id;
                    $d12Datas->save();

                    $d13Datas=new TransDataD13;
                    $d13Datas->rc_id=$rc_id;
                    $d13Datas->previous_rc_id=$prc_id;
                    $d13Datas->prepared_by = auth()->user()->id;
                    $d13Datas->save();

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
                $html.='<svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Success:"><use xlink:href="#check-circle-fill"/></svg><div>Invoice Generated As Per Your Confirmation For Sales Despatch Plan..!</div></div>';
            }else{
                $salesDespatchPlanSummaries=SalesDespatchPlanSummary::with('packingmaster','productmaster','customermaster')->find($id);
                // dd($salesDespatchPlanSummaries);
                // $salesDespatchPlanSummaries->to_confirm_qty=$invoice_quantity[$id];
                // $salesDespatchPlanSummaries->invoiced_qty=$invoice_quantity[$id];
                $salesDespatchPlanSummaries->status=$status[$id];
                $salesDespatchPlanSummaries->remarks=$remarks[$id]??NULL;
                $salesDespatchPlanSummaries->updated_by = auth()->user()->id;
                $salesDespatchPlanSummaries->updated_at = Carbon::now();
                $salesDespatchPlanSummaries->update();

                $salesDespatchPlanTransaction_datas=SalesDespatchPlanTransaction::with('packingstrickerdetails','productmaster','customermaster','salesplanmaster','manufacturingpartmaster')->where('plan_id','=',$id)->get();
                // dd($salesDespatchPlanTransaction_datas);
                foreach ($salesDespatchPlanTransaction_datas as $key => $salesDespatchPlanTransaction_data) {
                    $salesDespatchPlanTransaction_data->status=$status[$id];
                    $salesDespatchPlanTransaction_data->remarks=$remarks[$id]??NULL;
                    $salesDespatchPlanTransaction_data->updated_by = auth()->user()->id;
                    $salesDespatchPlanTransaction_data->updated_at = Carbon::now();
                    $salesDespatchPlanTransaction_data->update();
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
                $html.='<svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Success:"><use xlink:href="#check-circle-fill"/></svg><div>Your Confirmation Submiited Successfully..!</div></div>';
            }

        }
        return response()->json(['html'=>$html]);


    }
    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SalesDespatchPlanSummary $salesdespatchplansummary)
    {
        //
        // dd($salesdespatchplansummary);
        $customerDatas=CustomerMaster::where('status','=',1)->get();
        $salesdespatchplantransactions=SalesDespatchPlanTransaction::with('packingstrickerdetails','productmaster','customermaster','salesplanmaster','manufacturingpartmaster')->where('plan_id','=',$salesdespatchplansummary->id)->get();
        return view('sales_despatch_plan.edit',compact('salesdespatchplansummary','salesdespatchplantransactions','customerDatas'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSalesDespatchPlanSummaryRequest $request, SalesDespatchPlanSummary $salesdespatchplansummary)
    {
        //
        dd($request->all());
    }


    public function invoiceStickerCreate(){
        $invoicedatas=InvoiceDetails::all();
        return view('invoice.boxsticker_create',compact('invoicedatas'));
    }

    public function invoiceStickerFetch(Request $request){
        // dd($request->all());
        $invoicedatas=InvoiceDetails::find($request->invoice_id);
        $invoice_rc_id=$invoicedatas->rcmaster->id;
        $invoice_rc_no=$invoicedatas->rcmaster->rc_id;
        $invoce_date=$invoicedatas->invoice_date;
        $invoce_qty=$invoicedatas->qty;
        $cus_id=$invoicedatas->customerproductmaster->customermaster->cus_code;
        $part_id=$invoicedatas->customerproductmaster->productmasters->part_no;
        $part_desc=$invoicedatas->customerproductmaster->productmasters->part_desc;
        return response()->json(['invoce_date'=>$invoce_date,'invoce_qty'=>$invoce_qty,'cus_id'=>$cus_id,'part_id'=>$part_id,'part_desc'=>$part_desc,'invoice_rc_id'=>$invoice_rc_id,'invoice_rc_no'=>$invoice_rc_no]);

    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SalesDespatchPlanSummary $salesdespatchplansummary)
    {
        //
    }


}
