<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ItemProcesmaster;
use App\Models\ChildProductMaster;
use App\Models\BomMaster;
use App\Models\ProductProcessMaster;
use App\Models\RawMaterial;
use App\Models\MachineMaster;
use App\Models\ForemanMaster;
use App\Http\Requests\StoreProductProcessMasterRequest;
use App\Http\Requests\UpdateProductProcessMasterRequest;
use Illuminate\Support\Facades\DB;
use Auth;

class ProductProcessMasterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        //
        $perPage = $request->input('per_page', 5);
        $query = ProductProcessMaster::with('processMaster','childProductMaster')->orderBy('part_id','ASC')->orderBy('process_order_id','ASC');
        if(!empty($request->part_id)){
            $query = $query->where('part_id','=',$request->part_id);
        }
        $productprocessmasters = $query->paginate($perPage);
        $productmasters=ChildProductMaster::get();
        return view('productprocess.index',compact('productprocessmasters','productmasters'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        $processdatas=ItemProcesmaster::where('status','=',1)->orderBy('order','ASC')->get();
        $usedPartIds = ProductProcessMaster::select('part_id')->groupBy('part_id')->pluck('part_id');
        $productmasters = ChildProductMaster::where('status', 1)->where('item_type','!=',0)
        ->whereNotIn('id', $usedPartIds)
        ->get();
        return view('productprocess.create',compact('processdatas','productmasters'));
    }

    public function processPartCheck(Request $request){
        // dd($request->all());
        $part_id=$request->part_id;
        $processdatas=ItemProcesmaster::where('status','=',1)->orderBy('order','ASC')->get();
        $machinedatas=MachineMaster::where('status','=',1)->orderBy('machine_name','ASC')->get();
        $foremandatas=ForemanMaster::where('status','=',1)->orderBy('name','ASC')->get();
        $table=view('productprocess.add_items',compact('processdatas','machinedatas','foremandatas'))->render();
        return response()->json(['table'=>$table]);
    }

    public function processPartMachineCheck(Request $request){
        // dd($request->all());
        $machine_id=$request->machine_id;
        $machinedata=MachineMaster::find($machine_id);
        $cell_id='<option value="'.$machinedata->cellmaster->id.'" selected>'.$machinedata->cellmaster->name.'</option>';
        $group_id='<option value="'.$machinedata->groupmaster->id.'" selected>'.$machinedata->groupmaster->name.'</option>';
        $rm_datas=RawMaterial::where('status','=',1)->get();
        $rm_id='<option value="" selected></option>';
        foreach ($rm_datas as $key => $rm_data) {
            $rm_id.='<option value="'.$rm_data->id.'">'.$rm_data->name.'</option>';
        }
        return response()->json(['cell_id'=>$cell_id,'group_id'=>$group_id,'rm_id'=>$rm_id]);
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductProcessMasterRequest $request)
    {
        //
        // dd($request->all());
        DB::beginTransaction();
        try {
            $status=$request->status;
            foreach ($status as $key => $value) {
                if ($value=="on") {
                    $productprocessmaster = new ProductProcessMaster;
                    $productprocessmaster->part_id = $request->part_id;
                    $productprocessmaster->process_master_id = $request->operation[$key];
                    $productprocessmaster->process_order_id = $request->process_order_id[$key];
                    $productprocessmaster->foreman_id = $request->foreman_id;
                    $productprocessmaster->group_id = $request->group_id;
                    $productprocessmaster->machine_id = $request->machine_id;
                    $productprocessmaster->prepared_by = auth()->user()->id;
                    $productprocessmaster->save();

                }
            }
            $bomMaster=new BomMaster;
            $bomMaster->child_part_id = $request->part_id;
            $bomMaster->rm_id = $request->rm_id;
            $bomMaster->uom_id = 1;
            $bomMaster->input_usage = $request->cnc_bom;
            $bomMaster->manual_usage = $request->cnc_bom;
            $bomMaster->finish_usage = $request->cnc_bom;
            $bomMaster->output_usage = $request->cnc_bom;
            $bomMaster->foreman = $request->foreman_id;
            $bomMaster->prepared_by = auth()->user()->id;
            $bomMaster->save();
            DB::commit();
            return redirect()->route('productprocessmaster.index')->withSuccess('Product Process is Created Successfully!');
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollback();
            return redirect()->back()->withErrors($th->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(ProductProcessMaster $productprocessmaster)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ProductProcessMaster $productprocessmaster)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductProcessMasterRequest $request, ProductProcessMaster $productprocessmaster)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProductProcessMaster $productprocessmaster)
    {
        //
    }
}
