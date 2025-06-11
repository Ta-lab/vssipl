<?php

namespace App\Http\Controllers;

use App\Exports\RawmaterialCategoryExport as ExportsRawmaterialCategoryExport;
use Illuminate\Http\Request;
use App\Models\RawMaterialCategory;
use App\Http\Requests\StoreRawMaterialCategoryRequest;
use App\Http\Requests\UpdateRawMaterialCategoryRequest;
use Illuminate\Support\Facades\DB;
use Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Controllers\RawmaterialCategoryExport;
class RawMaterialCategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:create-rm_category|edit-rm_category|view-rm_category', ['only' => ['index','show']]);
        $this->middleware('permission:view-rm_category', ['only' => ['index','show']]);
        $this->middleware('permission:create-rm_category', ['only' => ['create','store']]);
        $this->middleware('permission:edit-rm_category', ['only' => ['edit','update']]);
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if(!empty($request->raw_material_category_id)){
            $categories = RawMaterialCategory::where('id','=',$request->raw_material_category_id)->get();
        }else{
            $categories = RawMaterialCategory::get();
        }
        return view('raw_material_category.index',compact('categories'));
    }

    public function export(Request $request)
    {
        if(!empty($request->raw_material_category_id)){
            $categories = RawMaterialCategory::where('id','=',$request->raw_material_category_id)->get();
        }else{
            $categories = RawMaterialCategory::get();
        }
        return Excel::download(new ExportsRawmaterialCategoryExport($categories), 'rawmaterialcategory.xlsx');
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('raw_material_category.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRawMaterialCategoryRequest $request)
    {
        DB::beginTransaction();
        try {
            $category = new RawMaterialCategory;
            $category->name = $request->name;
            $category->prepared_by = auth()->user()->id;
            $category->save();
            DB::commit();
            return redirect()->route('raw_material_category.index')->withSuccess('Raw Material Category Created Successfully!');
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollback();
            return redirect()->back()->withErrors($th->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(RawMaterialCategory $rawMaterialCategory)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(RawMaterialCategory $rawMaterialCategory)
    {
        return view('raw_material_category.edit',compact('rawMaterialCategory'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRawMaterialCategoryRequest $request, RawMaterialCategory $rawMaterialCategory)
    {
        DB::beginTransaction();
        try {
            $rawMaterialCategory->name = $request->name;
            $rawMaterialCategory->status = $request->status;
            $rawMaterialCategory->updated_by = auth()->user()->id;
            $rawMaterialCategory->save();
            DB::commit();
            return redirect()->back(route('raw_material_category.index'))->withSuccess('Raw Material Category Updated Successfully!');

        } catch (\Throwable $th) {
            //throw $th;
            DB::rollback();
            return redirect()->back()->withErrors($th->getMessage());

        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(RawMaterialCategory $rawMaterialCategory)
    {
        //
    }
}
