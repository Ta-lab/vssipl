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
use App\Models\GrnRejection;
use App\Models\PODetail;
use App\Models\POProductDetail;
use App\Models\HeatNumber;
use App\Models\User;
use App\Http\Requests\StoreGrnQualityRequest;
use App\Http\Requests\UpdateGrnQualityRequest;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;


class GrnQualityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
//     SELECT
//     a.grnnumber,
//     a.grndate,
//     b.ponumber,
//     d.name AS sc_name,
//     d.supplier_code AS sc_code,
//     f.name AS rm_category,
//     g.name AS rm_desc,
//     a.inward_qty,
//     h.heatnumber,
//     r.rack_name,
//     h.tc_no,
//     h.coil_no,
//     h.lot_no,
//     j.approved_qty,
//     j.onhold_qty,
//     j.rejected_qty,
//     j.inspected_by,
//     j.inspected_date,
//     j.status
// FROM
//     grn_qualities AS j
// INNER JOIN g_r_n_inward_registers AS a
// ON
//     j.grnnumber_id = a.id
// INNER JOIN heat_numbers AS H
// ON
//     j.heat_no_id = h.id
// INNER JOIN rackmasters AS r
// ON
//     h.rack_id = r.id
// INNER JOIN p_o_details AS b
// ON
//     a.po_id = b.id
// INNER JOIN p_o_product_details AS c
// ON
//     a.p_o_product_id = c.id
// INNER JOIN suppliers AS d
// ON
//     c.supplier_id = d.id
// INNER JOIN supplier_products AS e
// ON
//     c.supplier_product_id = e.id
// INNER JOIN raw_material_categories AS f
// ON
//     e.raw_material_category_id = f.id
// INNER JOIN raw_materials AS g
// ON
    // e.raw_material_id = g.id

    // status grn qc details
    // 0-pending,
    // 1-approved,
    // 2-rejected,
    // 3-on-hold
        $grnqc_datas = DB::table('grn_qualities as j')
            ->join('g_r_n_inward_registers AS a', 'j.grnnumber_id', '=', 'a.id')
            ->join('route_masters AS n', 'a.grnnumber', '=', 'n.id')
            ->join('heat_numbers AS h', 'j.heat_no_id', '=', 'h.id')
            ->join('rackmasters AS r', 'h.rack_id', '=', 'r.id')
            ->join('p_o_details AS b', 'a.po_id', '=', 'b.id')
            ->join('p_o_product_details AS c', 'a.p_o_product_id', '=', 'c.id')
            ->join('suppliers AS d', 'c.supplier_id', '=', 'd.id')
            ->join('supplier_products AS e', 'c.supplier_product_id', '=', 'e.id')
            ->join('raw_material_categories AS f', 'e.raw_material_category_id', '=', 'f.id')
            ->join('raw_materials AS g', 'e.raw_material_id', '=', 'g.id')
            // ->join('users AS u', 'j.inspected_by', '=', 'u.id')
            ->select('j.id as id','a.id as grn_id','n.rc_id as grnnumber',
            'a.grndate',
            'b.ponumber',
            'd.name AS sc_name',
            'd.supplier_code AS sc_code',
            'f.name AS rm_category',
            'g.name AS rm_desc',
            'h.coil_inward_qty',
            'h.heatnumber',
            'r.rack_name',
            'h.tc_no',
            'h.coil_no',
            'h.lot_no',
            'j.approved_qty',
            'j.onhold_qty',
            'j.rejected_qty',
            'j.inspected_by',
            // 'u.name as inspected_by',
            'j.inspected_date',
            'j.status')
            ->orderBy('j.id','DESC')
            ->get();
        // dd($grnqc_datas);
        $userDatas=User::get();

        return view('grn_qc.index',compact('grnqc_datas','userDatas'));

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
    public function store(StoreGrnQualityRequest $request)
    {
        //

    }

    /**
     * Display the specified resource.
     */
    // public function show(GrnQuality $grnQuality)
    public function show($id)
    {
        //
        dd(GrnQuality::findorFail($id));
        dd($id);

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(GrnQuality $grnQuality)
    {
        //
        dd($grnQuality);
    }

    public function approval(Request $request){
        // dd($request);
        $id=$request->id;
        $grnqc_datas = DB::table('grn_qualities AS a')
        ->join('heat_numbers AS b', 'a.heat_no_id', '=', 'b.id')
        ->join('g_r_n_inward_registers AS c', 'a.grnnumber_id', '=', 'c.id')
        ->join('route_masters AS n', 'c.grnnumber', '=', 'n.id')
        ->join('rackmasters AS d', 'a.rack_id', '=', 'd.id')
        ->join('p_o_details AS e', 'c.po_id', '=', 'e.id')
        ->join('route_masters AS o', 'e.ponumber', '=', 'o.id')
        ->join('p_o_product_details AS f', 'c.p_o_product_id', '=', 'f.id')
        ->join('suppliers AS g', 'e.supplier_id', '=', 'g.id')
        ->join('supplier_products AS h', 'f.supplier_product_id', '=', 'h.id')
        ->join('raw_material_categories AS i', 'h.raw_material_category_id', '=', 'i.id')
        ->join('raw_materials AS j', 'h.raw_material_id', '=', 'j.id')
        ->join('mode_of_units AS k', 'h.uom_id', '=', 'k.id')
        ->select('a.id AS id',
        'a.grnnumber_id AS grn_id',
        'n.rc_id as grnnumber',
        'c.grndate',
        'c.invoice_number',
        'c.invoice_date',
        'c.dc_number',
        'c.dc_date',
        'e.id As po_id',
        'o.rc_id as ponumber',
        'g.id AS sc_id',
        'g.supplier_code AS sc_code',
        'g.name AS sc_name',
        'j.id AS rm_id',
        'j.name AS rm_desc',
        'b.id AS heat_id',
        'b.heatnumber',
        'b.rack_id AS rack_id',
        'd.rack_name',
        'b.tc_no',
        'b.coil_no',
        'b.lot_no',
        'k.id as uom_id',
        'k.name as uom_name',
        'b.coil_inward_qty',
        'b.status',
        'a.approved_qty',
        'a.onhold_qty',
        'a.rejected_qty',
        'a.inspected_by',
        'a.inspected_qty',
        'a.inspected_date')
        ->where('c.id',$id)
        ->where('b.status','!=',1)
        ->get();
        $grn_data_id=$grnqc_datas[0]->id;
        // dd($grnqc_datas);
        return view('grn_qc.edit', compact('grnqc_datas','grn_data_id'));
    }



    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateGrnQualityRequest $request, GrnQuality $grnQuality)
    {
        //
                // dd($request->select_all);

        // dd($request->all());
        DB::beginTransaction();
        try {
                date_default_timezone_set('Asia/Kolkata');
                $current_date=date('Y-m-d');
                // dd($request->select_all);
                $sub_ids=$request->sub_id;
                $select_all=($request->select_all)??NULL;
                $status_all=$request->status_all;
                // dd($select_all??NULL);
                if($select_all==NULL){
                    // dd($request->status);
                    foreach ($sub_ids as $key => $sub_id) {
                        if ($request->status[$key]==1) {
                            $grnQualityData=GrnQuality::find($sub_id);
                            $grnQualityData->status=$request->status[$key];
                            $grnQualityData->reason=$request->reason[$key];
                            $grnQualityData->approved_qty=$request->coil_inward_qty[$key];
                            $grnQualityData->onhold_qty=0;
                            $grnQualityData->rejected_qty=0;
                            $grnQualityData->inspected_by=auth()->user()->id;
                            $grnQualityData->inspected_date=$current_date;
                            $grnQualityData->updated_by = auth()->user()->id;
                            $grnQualityData->update();

                            $heatNumberData=HeatNumber::find($grnQualityData->heat_no_id);
                            $heatNumberData->status=$request->status[$key];
                            $heatNumberData->updated_by = auth()->user()->id;
                            $heatNumberData->update();

                        }elseif ($request->status[$key]==2) {
                            $grnQualityData=GrnQuality::find($sub_id);
                            $grnQualityData->status=$request->status[$key];
                            $grnQualityData->reason=$request->reason[$key];
                            $grnQualityData->approved_qty=0;
                            $grnQualityData->rejected_qty=$request->coil_inward_qty[$key];
                            $grnQualityData->onhold_qty=0;
                            $grnQualityData->inspected_by=auth()->user()->id;
                            $grnQualityData->inspected_date=$current_date;
                            $grnQualityData->updated_by = auth()->user()->id;
                            $grnQualityData->update();

                            $qc_id=$grnQualityData->id;

                            $grnQcRejectionData=new GrnRejection;
                            $grnQcRejectionData->grnnumber_id=$request->grnnumber;
                            $grnQcRejectionData->heat_no_id=$request->heat_id;
                            $grnQcRejectionData->grnqc_id=$qc_id;
                            $grnQcRejectionData->reason=$request->reason[$key];
                            $grnQualityData->prepared_by = auth()->user()->id;
                            $grnQualityData->save();

                        }elseif ($request->status[$key]==3) {
                            $grnQualityData=GrnQuality::find($sub_id);
                            $grnQualityData->status=$request->status[$key];
                            $grnQualityData->reason=$request->reason[$key];
                            $grnQualityData->approved_qty=0;
                            $grnQualityData->onhold_qty=$request->coil_inward_qty[$key];
                            $grnQualityData->rejected_qty=0;
                            $grnQualityData->inspected_by=auth()->user()->id;
                            $grnQualityData->inspected_date=$current_date;
                            $grnQualityData->updated_by = auth()->user()->id;
                            $grnQualityData->update();
                        }

                        $heatNumberData=HeatNumber::find($grnQualityData->heat_no_id);
                        $heatNumberData->status=$request->status[$key];
                        $heatNumberData->updated_by = auth()->user()->id;
                        $heatNumberData->update();
                        $heat_rack_id=$heatNumberData->rack_id;

                        $grnqc_datas = DB::table('grn_qualities')
                        ->select(DB::raw('SUM(approved_qty) as t_approved_qty'),DB::raw('SUM(onhold_qty) as t_onhold_qty'),DB::raw('SUM(rejected_qty) as t_rejected_qty'))
                        ->where('grnnumber_id','=',$grnQualityData->grnnumber_id)
                        ->first();
                        // dd($grnqc_datas);
                        $grbInwardData=GRNInwardRegister::find($grnQualityData->grnnumber_id);
                        $grbInwardData->approved_qty=$grnqc_datas->t_approved_qty;
                        $grbInwardData->onhold_qty=$grnqc_datas->t_onhold_qty;
                        $grbInwardData->rejected_qty=$grnqc_datas->t_rejected_qty;
                        $grbInwardData->approved_status=$request->status[$key];
                        $grbInwardData->updated_by = auth()->user()->id;
                        $grbInwardData->updated_at = Carbon::now();
                        $grbInwardData->update();

                        $grbInwardData2=GRNInwardRegister::find($grnQualityData->grnnumber_id);
                        // dd($grbInwardData2);

                        $avl_qty=(($grbInwardData2->approved_qty)-($grbInwardData2->return_qty)-($grbInwardData2->return_dc_qty)-($grbInwardData2->issued_qty));
                        $grbInwardData2->avl_qty = $avl_qty;
                        $grbInwardData2->updated_by = auth()->user()->id;
                        $grbInwardData2->updated_at = Carbon::now();
                        $grbInwardData2->update();
                        // dd($grnQualityData);
                        $rm_avl_qty=GRNInwardRegister::where('rm_id','=',$grbInwardData->rm_id)->sum('avl_qty');

                        $rm_Data=RawMaterial::find($grbInwardData->rm_id);
                        $rm_Data->avl_stock=$rm_avl_qty;
                        $rm_Data->updated_by = auth()->user()->id;
                        $rm_Data->updated_at = Carbon::now();
                        $rm_Data->update();

                        $value=$heat_rack_id;
                        $rack_stock=GrnQuality::with('heat_no_data')->where('rack_id', '=', $value)->select(DB::raw('(SUM(approved_qty)-SUM(issue_qty)) as avl_qty'))->first();

                        $rackData=Rackmaster::find($value);
                        $rackData->avl_qty=$rack_stock->avl_qty;
                        $rackData->updated_by = auth()->user()->id;
                        $rackData->updated_at = Carbon::now();
                        $rackData->update();
                    }
                }else{
                    foreach ($sub_ids as $key => $sub_id) {
                        if ($status_all==1) {
                            $grnQualityData=GrnQuality::find($sub_id);
                            $grnQualityData->status=$request->status_all;
                            $grnQualityData->approved_qty=$request->coil_inward_qty[$key];
                            $grnQualityData->onhold_qty=0;
                            $grnQualityData->rejected_qty=0;
                            $grnQualityData->inspected_by=auth()->user()->id;
                            $grnQualityData->inspected_date=$current_date;
                            $grnQualityData->updated_by = auth()->user()->id;
                            $grnQualityData->update();
                        }elseif ($status_all==2) {
                            $grnQualityData=GrnQuality::find($sub_id);
                            $grnQualityData->status=$request->status_all;
                            $grnQualityData->reason=$request->reason_all;
                            $grnQualityData->approved_qty=0;
                            $grnQualityData->rejected_qty=$request->coil_inward_qty[$key];
                            $grnQualityData->onhold_qty=0;
                            $grnQualityData->inspected_by=auth()->user()->id;
                            $grnQualityData->inspected_date=$current_date;
                            $grnQualityData->updated_by = auth()->user()->id;
                            $grnQualityData->update();

                            $qc_id=$grnQualityData->id;

                            $grnQcRejectionData=new GrnRejection;
                            $grnQcRejectionData->grnnumber_id=$request->grnnumber;
                            $grnQcRejectionData->heat_no_id=$request->heat_id;
                            $grnQcRejectionData->grnqc_id=$qc_id;
                            $grnQcRejectionData->reason=$request->reason[$key];
                            $grnQualityData->prepared_by = auth()->user()->id;
                            $grnQualityData->save();
                        }elseif ($status_all==3) {
                            $grnQualityData=GrnQuality::find($sub_id);
                            $grnQualityData->status=$request->status_all;
                            $grnQualityData->reason=$request->reason_all;
                            $grnQualityData->approved_qty=0;
                            $grnQualityData->onhold_qty=$request->coil_inward_qty[$key];
                            $grnQualityData->rejected_qty=0;
                            $grnQualityData->inspected_by=auth()->user()->id;
                            $grnQualityData->inspected_date=$current_date;
                            $grnQualityData->updated_by = auth()->user()->id;
                            $grnQualityData->update();
                        }

                        $heatNumberData=HeatNumber::find($grnQualityData->heat_no_id);
                        $heatNumberData->status=$request->status_all;
                        $heatNumberData->updated_by = auth()->user()->id;
                        $heatNumberData->update();
                        $heat_rack_id=$heatNumberData->rack_id;

                        $grnqc_datas = DB::table('grn_qualities')
                        ->select(DB::raw('SUM(approved_qty) as t_approved_qty'),DB::raw('SUM(onhold_qty) as t_onhold_qty'),DB::raw('SUM(rejected_qty) as t_rejected_qty'))
                        ->where('grnnumber_id','=',$grnQualityData->grnnumber_id)
                        ->first();

                        $grbInwardData=GRNInwardRegister::find($grnQualityData->grnnumber_id);
                        $grbInwardData->approved_qty=$grnqc_datas->t_approved_qty;
                        $grbInwardData->onhold_qty=$grnqc_datas->t_onhold_qty;
                        $grbInwardData->rejected_qty=$grnqc_datas->t_rejected_qty;
                        $grbInwardData->approved_status=$request->status_all;
                        $grbInwardData->updated_by = auth()->user()->id;
                        $grbInwardData->update();

                        $grbInwardData2=GRNInwardRegister::find($grnQualityData->grnnumber_id);
                        // dd($grbInwardData2);

                        $avl_qty=(($grbInwardData2->approved_qty)-($grbInwardData2->return_qty)-($grbInwardData2->return_dc_qty)-($grbInwardData2->issued_qty));
                        $grbInwardData2->avl_qty = $avl_qty;
                        $grbInwardData2->updated_by = auth()->user()->id;
                        $grbInwardData2->update();

                        // dd($grnQualityData);
                        $rm_avl_qty=GRNInwardRegister::where('rm_id','=',$grbInwardData->rm_id)->sum('avl_qty');

                        $rm_Data=RawMaterial::find($grbInwardData->rm_id);
                        $rm_Data->avl_stock=$rm_avl_qty;
                        $rm_Data->updated_by = auth()->user()->id;
                        $rm_Data->updated_at = Carbon::now();
                        $rm_Data->update();

                        $value=$heat_rack_id;
                        $rack_stock=GrnQuality::with('heat_no_data')->where('rack_id', '=', $value)->select(DB::raw('(SUM(approved_qty)-SUM(issue_qty)) as avl_qty'))->first();

                        $rackData=Rackmaster::find($value);
                        $rackData->avl_qty=$rack_stock->avl_qty;
                        $rackData->updated_by = auth()->user()->id;
                        $rackData->updated_at = Carbon::now();
                        $rackData->update();
                    }
                }
                DB::commit();
                return redirect()->route('grn_qc.index')->withSuccess('Your Inspection Data Is Submitted Successfully!');

            } catch (\Throwable $th) {
                //throw $th;
            dd($th->getMessage());

                DB::rollback();
                return back()->withErrors($th->getMessage());
            }
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(GrnQuality $grnQuality)
    {
        //
    }
}
