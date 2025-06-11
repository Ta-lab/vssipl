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
            <div class="card-header d-flex" style="justify-content:space-between"><span> <b>Product Process List</b> </span>
                <a class="btn btn-sm btn-primary" href="{{route('productprocessmaster.create')}}">Add Product Process</a>
            </div>
            <div class="card-body">
                <form action="" method="get">
                    @csrf
                    <div class="row mb-3 mt-3">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="part_id"><b>Product No*</b></label>
                                <select name="part_id" id="part_id" class="form-control @error('part_id') is-invalid @enderror">
                                <option value=""></option>
                                @forelse ($productmasters as $productmaster)
                                    <option value="{{$productmaster->id}}" {{Request::get('part_id')==$productmaster->id ? 'selected':''}}>{{$productmaster->child_part_no}}</option>
                                @empty
                                @endforelse
                                </select>
                                @error('part_id')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-3 mt-4">
                            <button type="submit" class="btn btn-sm btn-success text-white" name="submit">Submit</button>
                            <a class="btn btn btn-sm btn-danger text-white" id="reset" href="{{ route('productprocessmaster.index') }}">clear</a>
                            {{-- <a class="btn btn-sm btn-success text-white" id="export_excel_btn1" href="{{ route('rawmaterial_export', ['_token'=>csrf_token(),'part_id' => Request::get('part_id')]) }}">Export To EXCEL</a> --}}
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
                                <option value="100" {{ Request::get('per_page') == 100 ? 'selected' : '' }}>100</option>
                                <option value="250" {{ Request::get('per_page') == 250 ? 'selected' : '' }}>250</option>
                                <option value="500" {{ Request::get('per_page') == 500 ? 'selected' : '' }}>500</option>
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
                                    <th>Part No</th>
                                    <th>Process Name</th>
                                    <th>Process Order</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($productprocessmasters as $productprocessmaster)
                                <tr>
                                    <td>{{$loop->iteration}}</td>
                                    <td>{{$productprocessmaster->childProductMaster->child_part_no}}</td>
                                    <td>{{$productprocessmaster->processMaster->operation}}</td>
                                    <td>{{$productprocessmaster->process_order_id}}</td>
                                    <td>@if ($productprocessmaster->status==1)
                                        <span class="btn btn-sm text-white btn-success">Active</span>
                                        @else
                                        <span class="btn btn-sm text-white btn-danger">Inactive</span>
                                    @endif</td>
                                    <td><a href="{{route('productprocessmaster.edit',$productprocessmaster->id)}}" class="btn btn-sm btn-primary">Edit</a></td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" align="center">No Records Found!</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                        <div>
                            {{-- {{ $raw_materials->links() }} --}}
                            {{ $productprocessmasters->appends(['per_page' => request('per_page')])->links() }}
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
            $("#part_id").select2({
                placeholder:"Select The Part No",
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
