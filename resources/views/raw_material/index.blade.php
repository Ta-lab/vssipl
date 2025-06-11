@extends('layouts.app')
@section('content')
<div class="row d-flex justify-content-center">

    <div class="col-12">
        <div class="card">
        @if(Session::has('success'))
                <div class="alert alert-success mt-4">
                {{ Session::get('success')}}
                </div>
            @endif
            @if(Session::has('error'))
                <div class="alert alert-danger mt-4">
                {{ Session::get('error')}}
                </div>
            @endif
            <div class="card-header d-flex" style="justify-content:space-between"><span> <b>RawMaterial List</b> </span>
                <a class="btn btn-sm btn-primary" href="{{route('raw_material.create')}}">Add RawMaterial</a>
            </div>
            <div class="card-body">
                <form action="" method="get">
                    @csrf
                    <div class="row mb-3 mt-3">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="raw_material_category_id"><b>Raw Material Category*</b></label>
                                <select name="raw_material_category_id" id="raw_material_category_id" class="form-control @error('raw_material_category_id') is-invalid @enderror">
                                <option value=""></option>
                                @forelse ($categories as $category)
                                    <option value="{{$category->id}}" {{Request::get('raw_material_category_id')==$category->id ? 'selected':''}}>{{$category->name}}</option>
                                @empty
                                @endforelse
                                </select>
                                @error('raw_material_category_id')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="rm_id"><b>Raw Material*</b></label>
                                <select name="rm_id" id="rm_id" class="form-control @error('rm_id') is-invalid @enderror">
                                <option value=""></option>
                                @forelse ($raw_materials as $raw_material)
                                    <option value="{{$raw_material->id}}" {{Request::get('rm_id')==$raw_material->id ? 'selected':''}}>{{$raw_material->name}}</option>
                                @empty
                                @endforelse
                                </select>
                                @error('rm_id')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-3 mt-4">
                            <button type="submit" class="btn btn-sm btn-success text-white" name="submit">Submit</button>
                            <a class="btn btn btn-sm btn-danger text-white" id="reset" href="{{ route('raw_material.index') }}">clear</a>
                            <a class="btn btn-sm btn-success text-white" id="export_excel_btn1" href="{{ route('rawmaterial_export', ['_token'=>csrf_token(),'raw_material_category_id' => Request::get('raw_material_category_id'),'rm_id' => Request::get('rm_id')]) }}">Export To EXCEL</a>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="per_page">Items per page:</label>
                            <select name="per_page" id="per_page" class="form-control" onchange="this.form.submit()">
                                <option value="5" {{ Request::get('per_page') == 5 ? 'selected' : '' }}>5</option>
                                <option value="10" {{ Request::get('per_page') == 10 ? 'selected' : '' }}>10</option>
                                <option value="20" {{ Request::get('per_page') == 20 ? 'selected' : '' }}>20</option>
                                <option value="50" {{ Request::get('per_page') == 50 ? 'selected' : '' }}>50</option>
                            </select>
                        </div>
                    </div>
                </form>
                <div class="table">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-responsive">
                            <thead>
                                <tr>
                                    <th>S.No</th>
                                    <th>Category</th>
                                    <th>Material Code</th>
                                    <th>Material Description</th>
                                    <th>Minimum Stock</th>
                                    <th>Maximum Stock</th>
                                    <th>Available Stock</th>
                                    <th>Stock Level</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($raw_materials as $department)
                                <tr>
                                    <td>{{$loop->iteration}}</td>
                                    <td>{{$department->category->name}}</td>
                                    <td>{{$department->material_code}}</td>
                                    <td>{{$department->name}}</td>
                                    <td>{{$department->minimum_stock}}</td>
                                    <td>{{$department->maximum_stock}}</td>
                                    <td class="avl_stock">{{$department->avl_stock}}</td>
                                    <td>
                                        @if(($department->minimum_stock)>($department->avl_stock))
                                        <span class="btn btn-sm text-white btn-danger">Low</span>
                                        @elseif(($department->maximum_stock)<($department->avl_stock))
                                        <span class="btn btn-sm text-white btn-warning">High</span>
                                        @else
                                        <span class="btn btn-sm text-white btn-success">Available</span>
                                    @endif</td>
                                    <td>@if ($department->status==1)
                                        <span class="btn btn-sm text-white btn-success">Active</span>
                                        @else
                                        <span class="btn btn-sm text-white btn-danger">Inactive</span>
                                    @endif</td>
                                    <td><a href="{{route('raw_material.edit',$department->id)}}" class="btn btn-sm btn-primary">Edit</a></td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="10" align="center">No Records Found!</td>
                                </tr>
                                @endforelse
                                <tr>
                                    <td colspan="6" align="center"><b>Total Availble Stock (In KGS)</b></td>
                                    <td colspan="4" id="total"><b></b></td>
                                </tr>
                            </tbody>
                        </table>
                        <div>
                            {{-- {{ $raw_materials->links() }} --}}
                            {{ $raw_materials->appends(['per_page' => request('per_page')])->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
    <script>
        $(document).ready(function(){
            function calculateTotal() {
                var sum = 0;

                // Iterate through each cell with class 'price' and sum the values
                $(".avl_stock").each(function(){
                    sum += parseFloat($(this).text()) || 0;
                });

                // Display the total
                $("#total").text(sum);
                $("#total").css("font-weight", "bold");
            }

            // Call the function on page load
            calculateTotal();
        });
            $("#raw_material_category_id").select2({
                placeholder:"Select The Raw Material Category",
                allowedClear:true
            });
            $("#rm_id").select2({
                placeholder:"Select The Raw Material",
                allowedClear:true
            });
            $('#raw_material_category_id').change(function (e) {
                e.preventDefault();
                var raw_material_category_id=$('#raw_material_category_id').val();
                var rm_id=$('#rm_id').val();
                if (raw_material_category_id!='') {
                    if (rm_id!='') {

                    }else{
                        $.ajax({
                        type: "GET",
                        url: "{{route('rawfetchdata')}}",
                        data: {'raw_material_category_id':raw_material_category_id},
                        success: function (response) {
                            // console.log(response);
                            $('#rm_id').html(response.html);
                        }
                    });
                    }
                }
            });
            $('#rm_id').change(function (e) {
                e.preventDefault();
                var raw_material_category_id=$('#raw_material_category_id').val();
                var rm_id=$('#rm_id').val();
                // alert(raw_material_category_id);
                if (rm_id!='') {
                    if (raw_material_category_id!='') {

                    }else{
                        $.ajax({
                        type: "GET",
                        url: "{{route('rawcategoryfetchdata')}}",
                        data: {'rm_id':rm_id},
                        success: function (response) {
                            // console.log(response);
                            $('#raw_material_category_id').html(response.html);
                        }
                        });
                    }
                }
            });
    </script>
@endpush
