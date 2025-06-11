    @extends('layouts.app')
@push('styles')

@endpush
@section('content')
<form action="{{route('ptsdcissue.store')}}" id="delivery_challan_formdata" method="POST">
    @csrf
    @method('POST')

<div class="row d-flex justify-content-center">
    <div id="data"></div>
        <div class="row col-md-3"id="res"></div>
        <div class="card">
            <div class="card-header d-flex" style="justify-content:space-between"><span> <b>Create Delivery challan</b></span><a class="btn btn-sm btn-primary" href="{{route('delivery_challan.index')}}">Delivery challan List</a>
            </div>
            <div class="card-body">
                <span class="me-auto mb-3"><button class="btn btn-primary text-white">STEP 1-DC Details</button></span>

                        <div class="row d-flex justify-content-center">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="dc_number">DC No. *</label>
                                    <input type="text" name="dc_number" id="dc_number" value="{{$new_rcnumber}}" readonly class="form-control bg-light @error('dc_number') is-invalid @enderror" >
                                    @error('dc_number')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="dc_date">DC Date *</label>
                                    <input type="date" name="dc_date" id="dc_date" value="{{$current_date}}" readonly class="form-control bg-light @error('dc_date') is-invalid @enderror" >
                                    @error('dc_date')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="supplier_id">Supplier Code *</label>
                                    <select name="supplier_id" id="supplier_id" class="form-control @error('supplier_id') is-invalid @enderror">
                                    <option value=""></option>
                                    @forelse ($dcmasterDatas as $dcmasterData)
                                        <option value="{{$dcmasterData->supplier->id}}">{{$dcmasterData->supplier->supplier_code}}</option>
                                    @empty
                                    @endforelse
                                    </select>
                                    @error('supplier_id')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="part_id">Part Number *</label>
                                    <select name="part_id" id="part_id" class="form-control part_id  @error('part_id') is-invalid @enderror">
                                        <option value="">Select Partnumber</option>
                                    </select>
                                    @error('part_id')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row d-flex justify-content-center">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="operation_id">Operation *</label>
                                    <select name="operation_id" id="operation_id" class="form-control operation_id  bg-light  @error('operation_id') is-invalid @enderror" @readonly(true)>
                                        <option value="">Select Operation</option>
                                    </select>
                                    @error('operation_id')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="cus_type_id">Customer Type *</label>
                                    <select name="cus_type_id" id="cus_type_id" class="form-control cus_type_id  @error('cus_type_id') is-invalid @enderror">
                                        <option value="">Select Customer Type</option>
                                    </select>
                                    @error('cus_type_id')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="cover_quantity">Cover Quantity *</label>
                                    <select name="cover_quantity" id="cover_quantity" class="form-control cover_quantity bg-light  @error('cover_quantity') is-invalid @enderror" @readonly(true)>
                                        <option value="">Select Quantity</option>
                                    </select>
                                    @error('cover_quantity')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="avl_quantity">DC Available Quantity*</label>
                                    <input type="number" name="avl_quantity" id="avl_quantity" class="form-control bg-light @error('avl_quantity') is-invalid @enderror" @readonly(true) >
                                    @error('avl_quantity')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row d-flex justify-content-center">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="no_of_cover">No of Cover*</label>
                                    <input type="number" name="no_of_cover" min="0" id="no_of_cover" class="form-control bg-light  @error('no_of_cover') is-invalid @enderror" @readonly(true)>
                                    @error('no_of_cover')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="dc_quantity">DC Quantity *</label>
                                    <input type="number" name="dc_quantity" min="0" id="dc_quantity" class="form-control bg-light @error('dc_quantity') is-invalid @enderror" @readonly(true) >
                                    @error('dc_quantity')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="trans_mode">Mode Of Transaction*</label>
                                    <select name="trans_mode" id="trans_mode" class="form-control @error('trans_mode') is-invalid @enderror">
                                        <option value="BY ROAD">BY ROAD</option>
                                        <option value="BY COURIER">BY COURIER</option>
                                    </select>
                                    @error('trans_mode')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="vehicle_no">VEHICLE NO *</label>
                                    <input type="text" name="vehicle_no"  id="vehicle_no" pattern="^[A-Z]{2}-\d{2}-[A-Z]{1}.-\d{4}$" onkeyup="format()" placeholder="TN-99-AA-1111 (OR) TN-99-A--1111" class="form-control @error('vehicle_no') is-invalid @enderror" >
                                    @error('vehicle_no')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="issue_wt">ISSUE WEIGHT *</label>
                                    <input type="number" name="issue_wt" @readonly(true) id="issue_wt" min="0" step="0.001" class="form-control bg-light @error('issue_wt') is-invalid @enderror" >
                                    @error('issue_wt')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="remarks">REMARKS *</label>
                                    <textarea name="remarks" id="remarks" class="form-control @error('remarks') is-invalid @enderror" cols="20" rows="3"></textarea>
                                    @error('remarks')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <input type="hidden" name="manufacturingPart" class="form-control manufacturingPart" id="manufacturingPart">
                            <input type="hidden" name="child_part_count" class="form-control child_part_count" id="child_part_count">
                            <input type="hidden" name="regular" class="form-control regular" id="regular">
                            <input type="hidden" name="alter" class="form-control alter" id="alter">
                            <input type="hidden" name="bom" class="form-control bom" id="bom">
                            <input type="hidden" name="cover_qty" class="form-control cover_qty" id="cover_qty">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <button class="btn btn-primary mt-4">Proceed DC</button>
                                </div>
                            </div>
                        </div>
                        <div class="row clearfix mt-3" id="table_row">
                            <div class="col-md-12">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped table-responsive">
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
                                        <tbody  id="table_logic">
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
</form>
@endsection
@push('scripts')
    <script>
        $("#supplier_id").select2();
        $("#part_id").select2();
        $("#trans_mode").select2();
        $('#supplier_id').change(function (e) {
            e.preventDefault();
            var supplier_id=$(this).val();
            // alert(supplier_id);
            $.ajax({
                type: "GET",
                url: "{{route('dcpartdata')}}",
                data: {"supplier_id":supplier_id},
                success: function (response) {
                    console.log(response);
                    if(response.count > 0){
                    $('#part_id').html(response.part_id);
                    }else{
                        alert('Please Choose Supplier...');
                    }
                }
            });

        });

        $('#part_id').change(function (e) {
                e.preventDefault();
                var supplier_id=$("#supplier_id").val();
                var part_id=$(this).val();
             // alert(supplier_id);
                // alert(part_id);
                $.ajax({
                    type: "POST",
                    url: "{{route('ptsdcitemrc')}}",
                    data: {"supplier_id":supplier_id,"part_id":part_id},
                    success: function (response) {
                        // console.log(response);
                        // $('#avl_quantity').val(response.t_avl_qty);
                        $('#operation_id').html(response.operation);
                        // $('#dc_quantity'). attr('max',response.t_avl_qty);
                        // $('#cover_quantity').html(response.cover_data);
                        // $('#cover_quantity').select2();
                        $('#cus_type_id').html(response.cus_type_name);
                        $('#cus_type_id').select2();
                        $('#child_part_count').val(response.part_count);
                        $('#regular').val(response.regular);
                        $('#alter').val(response.alter);
                        $('#bom').val(response.bom);
                        $('#manufacturingPart').val(response.manufacturingPart);
                        $('#cover_qty').val(response.cover_qty);

                    }
                });
            });
            $('#cus_type_id').change(function (e) {
                e.preventDefault();
                var supplier_id=$("#supplier_id").val();
                var operation_id=$("#operation_id").val();
                var part_id=$('#part_id').val();
                var child_part_count=$('#child_part_count').val();
                var regular=$('#regular').val();
                var alter=$('#alter').val();
                var bom=$('#bom').val();
                var cover_qty=$('#cover_qty').val();
                var cus_type_name=$(this).val();
                var manufacturingPart=$('#manufacturingPart').val();
                $.ajax({
                    type: "POST",
                    url: "{{route('ptsdccustype')}}",
                    data: {"supplier_id":supplier_id,"operation_id":operation_id,"part_id":part_id,"child_part_count":child_part_count,"regular":regular,"alter":alter,"bom":bom,"cover_qty":cover_qty,"manufacturingPart":manufacturingPart,"cus_type_name":cus_type_name,"manufacturingPart":manufacturingPart},
                    success: function (response) {
                        $('#avl_quantity').val(response.t_avl_qty);
                        $('#no_of_cover').val(response.no_of_cover);
                        // $('#operation_id').html(response.operation);
                        $('#dc_quantity'). attr('max',response.t_avl_qty);
                        $('#cover_quantity').html(response.cover_qty);
                        $('#cover_quantity').select2();
                        $('#table_row').html(response.table);
                        $('#issue_wt').val(response.dc_kg);
                        $('#dc_quantity').val(response.t_avl_qty);
                    }
                });
            });
            $("#no_of_cover").change(function(e){
            e.preventDefault();
            var supplier_id=$("#supplier_id").val();
            var operation_id=$("#operation_id").val();
            var part_id=$('#part_id').val();
            var child_part_count=$('#child_part_count').val();
            var regular=$('#regular').val();
            var alter=$('#alter').val();
            var manufacturingPart=$('#manufacturingPart').val();
            var bom=$('#bom').val();
            var cus_type_id=$('#cus_type_id').val();
            var no_of_cover=$('#no_of_cover').val();
            var cover_quantity = $('#cover_quantity').val();
            var dc_quantity=no_of_cover*cover_quantity;
            // alert(dc_quantity);
            $("#dc_quantity").val(dc_quantity);
            var dc_avl_qty = $('#avl_quantity').val();
            var diff=dc_avl_qty-dc_quantity;
                if (no_of_cover>0) {
                    if (diff>=0) {
                        $.ajax({
                            type: "POST",
                            url: "{{route('ptsdcitemrcquantity')}}",
                            data: {"supplier_id":supplier_id,"operation_id":operation_id,"part_id":part_id,"child_part_count":child_part_count,"regular":regular,"alter":alter,"bom":bom,"manufacturingPart":manufacturingPart,"dc_quantity":dc_quantity,"cus_type_id":cus_type_id,"cover_quantity":cover_quantity},
                            success: function (response) {
                                // alert(response.table);
                                $('#table_row').html(response.table);
                                $('#issue_wt').val(response.dc_kg);

                            }
                        });
                    }else{
                        alert('Sorry This Quantity More Than Available Quantity..');
                        location.reload(true);
                    }
                }else{
                    alert('Sorry This Quantity less Than 0...');
                    location.reload(true);

                }
        });
        // vehicle number format
        function format() {
            var x=$('#vehicle_no').val();
			x.value=x.value.toUpperCase();
			if(event.keyCode!=8)
			{
				if(x.value.toString().length==2)
				{
					x.value=x.value+'-';
				}
				if(x.value.toString().length==5)
				{
					x.value=x.value+'-';
				}
				if(x.value.toString().length==8)
				{
					x.value=x.value+'-';
				}
			}
		}

    </script>
@endpush
