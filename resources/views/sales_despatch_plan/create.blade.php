@extends('layouts.app')
@push('styles')

@endpush
@section('content')
<form action="{{route('salesdespatchplansummary.store')}}" id="rm_issuance_formdata" method="POST">
    @csrf
    @method('POST')

<div class="row d-flex justify-content-center">
    <div id="data"></div>
    <div class="col-12">
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
        <div class="row col-md-3"id="res"></div>

        <div class="card">
            <div class="card-header d-flex" style="justify-content:space-between"><span> <b>Sales Despatch Plan Register</b></span><a class="btn btn-sm btn-primary" href="{{route('salesdespatchplansummary.index')}}">Sales Despatch Plan List</a>
            </div>
            <div class="card-body">
                        <div class="row d-flex justify-content-center">
                            <input type="hidden" name="packing_master_id" id="packing_master_id">
                            <input type="hidden" name="manufacturing_part_id" id="manufacturing_part_id">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="plan_no">Plan No *</label>
                                    <input type="text" name="plan_no" id="plan_no" value="{{$new_rcnumber}}" class="form-control bg-light @error('plan_no') is-invalid @enderror" @readonly(true)>
                                    @error('plan_no')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="open_date">Required Date *</label>
                                    <input type="date" name="open_date" id="open_date" value="{{$current_date}}" readonly class="form-control bg-light @error('open_date') is-invalid @enderror" >
                                    @error('open_date')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="cus_id">Customer Code *</label>
                                    <select name="cus_id" id="cus_id" class="form-control @error('cus_id') is-invalid @enderror">
                                        <option value="" selected></option>
                                        @foreach ($customerDatas as $customerData)
                                            <option value="{{$customerData->id}}">{{$customerData->cus_code}}</option>
                                        @endforeach
                                    </select>
                                    @error('cus_id')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="part_id">Part Number *</label>
                                    <select name="part_id" id="part_id" class="form-control bg-light @error('part_id') is-invalid @enderror">
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
                                    <label for="cover_id">Cover Size & Name *</label>
                                    <select name="cover_id" class="form-control @error('cover_id') is-invalid @enderror"  id="cover_id">
                                    </select>
                                    @error('cover_id')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="cus_type_name">Customer Type *</label>
                                    <select name="cus_type_name" class="form-control @error('cus_type_name') is-invalid @enderror"  id="cus_type_name">
                                    </select>
                                    @error('cus_type_name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="cover_qty">Cover Quantity *</label>
                                    <input type="number" name="cover_qty" id="cover_qty"  class="form-control bg-light @error('cover_qty') is-invalid @enderror" step="0.01" @readonly(true)  @required(true)>
                                    @error('cover_qty')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="no_of_cover">No Of Cover *</label>
                                    <input type="number" name="no_of_cover" id="no_of_cover"  class="form-control bg-light @error('no_of_cover') is-invalid @enderror" @readonly(true) @required(true)>
                                    @error('no_of_cover')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>

                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="cus_req_qty">Customer Requirement Quantity *</label>
                                    <input type="number" name="cus_req_qty" id="cus_req_qty"  class="form-control @error('cus_req_qty') is-invalid @enderror"  @required(true)>
                                    @error('cus_req_qty')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row d-flex justify-content-center ">
                            <div class="col-md-2 mt-4">
                                <input type="submit" class="btn btn-success  text-white align-center" id="btn" value="Save">
                                <input class="btn btn-danger text-white" id="reset" type="reset" value="Reset">
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
$(document).ready(function(){
    $('input').on('input', function() {
        var inputId = $(this).attr('id');
        $('#' + inputId + '-error').remove();
    });
    // getRow();
});
$("#cus_id").select2({
        placeholder:"Select Customer Code",
        allowedClear:true
    });

    $("#part_id").select2({
        placeholder:"Select Part Number",
        allowedClear:true
    });


    $("#reset").click(function (e) {
        e.preventDefault();
        location.reload(true);
    });

    $('#cus_id').change(function (e) {
        e.preventDefault();
        var cus_id=$(this).val();
        if (cus_id!='') {
            $.ajax({
                type: "POST",
                url: "{{ route('plancuspartfetchData') }}",
                data:{
                    "_token": "{{ csrf_token() }}",
                    "cus_id":cus_id,
                },
                success: function (response) {
                    $('#part_id').html(response.html);
                }
            });
        }
    });

    $('#part_id').change(function (e) {
        e.preventDefault();
        var cus_id=$('#cus_id').val();
        var part_id=$(this).val();
        if (part_id!='') {
            $.ajax({
                type: "POST",
                url: "{{ route('planpartcoverfetchData') }}",
                data:{
                    "_token": "{{ csrf_token() }}",
                    "cus_id":cus_id,
                    "part_id":part_id,
                },
                success: function (response) {
                    $('#cover_id').html(response.cover_datas);
                    $('#manufacturing_part_id').val(response.manufacturing_part_id);
                    $('#packing_master_id').val(response.packing_master_id);
                    $('#cus_type_name').html(response.cus_type_name);
                    $('#cover_qty').val(response.cover_qty);
                }
            });
        }
    });
    $('#cus_req_qty').change(function (e) {
        e.preventDefault();
        var cus_req_qty=$(this).val();
        var cover_qty=$('#cover_qty').val();
        var no_of_cover=cus_req_qty/cover_qty;
        if((Number.isInteger(no_of_cover))==true){
            // alert('ok');
            var no_cover_qty=Math.floor(no_of_cover);
            var req_qty=no_cover_qty*cover_qty;
            // alert(no_cover_qty);

        }else{
            // alert('not ok');
            var no_cover_qty=Math.floor(no_of_cover);
            var req_qty=no_cover_qty*cover_qty;
            // alert(no_cover_qty);

        }
        $('#no_of_cover').val(no_cover_qty);
        $('#cus_req_qty').val(req_qty);
    });

</script>
@endpush

