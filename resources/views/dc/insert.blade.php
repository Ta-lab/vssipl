@extends('layouts.app')
@push('styles')

@endpush
@section('content')
<form action="{{route('delivery_challan.store')}}" id="delivery_challan_formdata" method="POST">
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
                                    <select name="operation_id" id="operation_id" class="form-control operation_id  @error('operation_id') is-invalid @enderror">
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
                                    <label for="avl_quantity">DC Available Quantity*</label>
                                    <input type="number" name="avl_quantity" id="avl_quantity" class="form-control @error('avl_quantity') is-invalid @enderror" >
                                    @error('avl_quantity')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="dc_quantity">DC Quantity *</label>
                                    <input type="number" name="dc_quantity" min="0" id="dc_quantity" class="form-control @error('dc_quantity') is-invalid @enderror" >
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
                        </div>
                        <div class="row d-flex justify-content-center">
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
                            <input type="hidden" name="regular" class="form-control regular" id="regular">
                            <input type="hidden" name="alter" class="form-control alter" id="alter">
                            <input type="hidden" name="bom" class="form-control bom" id="bom">
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
                    url: "{{route('dcitemrc')}}",
                    data: {"supplier_id":supplier_id,"part_id":part_id},
                    success: function (response) {
                        // console.log(response);
                        $('#avl_quantity').val(response.t_avl_qty);
                        $('#avl_quantity'). attr('readonly',true);
                        $('#operation_id').html(response.operation);
                        $('#dc_quantity'). attr('max',response.t_avl_qty);
                        $('#dc_quantity'). attr('readonly',true);
                        $('#dc_quantity'). val(response.t_avl_qty);
                        // $('#table_logic').html(response.table);
                        $('#regular').val(response.regular);
                        $('#alter').val(response.alter);
                        $('#table_row').html(response.table);
                        $('#bom').val(response.bom);
                        $('#issue_wt').val(response.dc_kg);
                    }
                });
            });
            $("#dc_quantity").change(function(e){
            e.preventDefault();
            var supplier_id=$("#supplier_id").val();
            var operation_id=$("#operation_id").val();
            var part_id=$('#part_id').val();
            var dc_quantity = $(this).val();
            var dc_avl_qty = $('#avl_quantity').val();
            var diff=dc_avl_qty-dc_quantity;
                if (diff>=0) {
                    $.ajax({
                        type: "POST",
                        url: "{{route('dcitemrcquantity')}}",
                        data: {"supplier_id":supplier_id,"operation_id":operation_id,"part_id":part_id,"dc_quantity":dc_quantity},
                        success: function (response) {
                            // alert(response.table);
                            $('#table_row').html(response.table);
                            $('#bom').val(response.bom);
                            $('#issue_wt').val(response.dc_kg);

                        }
                    });
                }else{
                    alert('Sorry This Quantity More Than Available Quantity..');
                    return false;
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
