@extends('layouts.app')
@push('styles')

@endpush
@section('content')
<form action="{{route('rmrequistionstore')}}" id="rmrequistion_formdata" method="POST">
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
            <div class="card-header d-flex" style="justify-content:space-between"><span> <b>Part Inspection Register</b></span><a class="btn btn-sm btn-primary" href="{{route('productionfirewalllist')}}">RM Requistion List</a>
            </div>
            <div class="card-body">
                <div class="row d-flex justify-content-center">
                    <input type="hidden" name="bom" class="bom" id="bom">
                    <input type="hidden" name="invoice_part_id" class="invoice_part_id" id="invoice_part_id">
                    <input type="hidden" name="req_id" class="req_id" id="req_id">
                    <input type="hidden" name="req_issue_kg" class="req_issue_kg" id="req_issue_kg">
                    <input type="hidden" name="rcpart_count" class="rcpart_count" id="rcpart_count">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="open_date">Date *</label>
                            <input type="date" name="open_date" id="open_date" value="{{$current_date}}" readonly class="form-control open_date bg-light @error('open_date') is-invalid @enderror" >
                            @error('open_date')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="part_id">Part Number *</label>
                            <select name="part_id" id="part_id" class="form-control part_id bg-light @error('part_id') is-invalid @enderror">
                                <option value="" selected></option>
                                @foreach ($partDatas as $partData)
                                <option value="{{($partData->id)}}">{{($partData->child_part_no)}}</option>
                                @endforeach
                            </select>
                            @error('part_id')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="cus_type_id">Customer Type *</label>
                            <select name="cus_type_id" class="form-control cus_type_id  @error('cus_type_id') is-invalid @enderror" id="cus_type_id">
                                <option value="" selected></option>
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
                            <label for="cover_qty">Cover Quantity (In Nos) *</label>
                            <input type="number" name="cover_qty" id="cover_qty"  class="form-control bg-light cover_qty @error('cover_qty') is-invalid @enderror" min="0" @readonly(true)>
                            @error('cover_qty')
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
                            <label for="max_no_of_cover">Max No Of Cover *</label>
                            <input type="number" name="max_no_of_cover" id="max_no_of_cover"  class="form-control bg-light max_no_of_cover @error('max_no_of_cover') is-invalid @enderror" min="0" >
                            @error('max_no_of_cover')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="avl_qty">RC Available Quantity (In Nos) *</label>
                            <input type="number" name="avl_qty" id="avl_qty"  class="form-control bg-light avl_qty @error('avl_qty') is-invalid @enderror" min="0" step="0.001" @readonly(true)>
                            @error('avl_qty')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="avl_kg">RC Available Quantity (In KGS) *</label>
                            <input type="number" name="avl_kg" id="avl_kg"  class="form-control bg-light avl_kg @error('avl_kg') is-invalid @enderror" min="0" step="0.001" @readonly(true)>
                            @error('avl_kg')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="max_qty">Max Cover Available Quantity (In Nos) *</label>
                            <input type="number" name="max_qty" id="max_qty"  class="form-control bg-light max_qty @error('max_qty') is-invalid @enderror" min="0" step="0.001" @readonly(true)>
                            @error('max_qty')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="row" id="dc_details">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="max_kg">Max Cover Available Quantity (In KGS) *</label>
                            <input type="number" name="max_kg" id="max_kg"  class="form-control bg-light max_kg @error('max_kg') is-invalid @enderror" min="0" step="0.001" @readonly(true)>
                            @error('max_kg')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="req_qty">Inspection Quantity (In NOS) *</label>
                            <input type="number" name="req_qty" id="req_qty"  class="form-control bg-light req_qty @error('req_qty') is-invalid @enderror" min="0" step="1" @readonly(true)>
                            @error('req_qty')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="req_kg">Inspection Quantity (In KGS) *</label>
                            <input type="number" name="req_kg" id="req_kg"  class="form-control bg-light req_kg @error('req_kg') is-invalid @enderror" min="0" step="0.001" @readonly(true)>
                            @error('req_kg')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="req_issue_qty">Request Issue Quantity (In NOS) *</label>
                            <input type="number" name="req_issue_qty" id="req_issue_qty"  class="form-control bg-light req_issue_qty @error('req_issue_qty') is-invalid @enderror" min="0" step="0.001" @readonly(true)>
                            @error('req_issue_qty')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="inspected_no_of_cover">Inspected No Of Cover *</label>
                            <input type="number" name="inspected_no_of_cover" id="inspected_no_of_cover"  class="form-control bg-light inspected_no_of_cover @error('inspected_no_of_cover') is-invalid @enderror" min="0" >
                            @error('inspected_no_of_cover')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                </div>
                <div id="new_mydiv"></div>
                <div class="row d-flex justify-content-center ">
                    <div class="col-md-2 mt-4">
                        <input class="btn btn-success text-white" id="confirm_cover" type="button" value="Confirm Cover">
                        <input type="submit" class="btn btn-success  text-white align-center" id="btn" value="Save" style="display: none;">
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
    $('#inlineRadio1').hide();
    $('#btn').hide();
    $('#confirm_cover').hide();
    $('.req_qty').attr('readonly', true);
    $('.req_qty').addClass('bg-light');
});

    $("#part_id").select2({
        placeholder:"Select The Part Number",
        allowedClear:true
    });
    $("#cus_type_id").select2({
        placeholder:"Select The Customer Type",
        allowedClear:true
    });

    $("#reset").click(function (e) {
        e.preventDefault();
        location.reload(true);
    });

    $('#part_id').change(function (e) {
        e.preventDefault();
        var part_id=$(this).val();
        // alert(part_id);
        if (part_id!='') {
            $.ajax({
                type: "POST",
                url: "{{ route('partcoverwisefetchdata') }}",
                data:{
                    "_token": "{{ csrf_token()}}",
                    "part_id":part_id,
                },
                success: function (response) {
                    if (response.process_msg) {
                        if (response.avl_msg) {
                            $('.cus_type_id').html(response.cover_data);
                            $('.rcpart_count').val(response.count);
                            $('.invoice_part_id').val(response.invoice_part_id);
                            $('.req_qty').val(0);
                            $('.req_qty').attr('max', response.avl_qty);
                            $('.req_qty').attr('min', 0);
                            $('.avl_qty').val(response.avl_qty);
                            $('.avl_kg').val(response.avl_kg);
                            $('.req_kg').val(0);
                            $('.req_kg').attr('max', response.avl_kg);
                            $('.req_kg').attr('min', 0);
                            $('.bom').val(response.bom);
                            $('.req_qty').attr('readonly', true);
                            $('.req_qty').addClass('bg-light');
                            $('#confirm_cover').hide();
                        } else {
                            $('#data').html(response.html);
                            setTimeout(function() {
                                location.reload(true);
                            }, 3000);
                            $('#btn').hide();
                        }
                    } else {
                        $('#data').html(response.html);
                        setTimeout(function() {
                            location.reload(true);
                        }, 3000);
                        $('#btn').hide();
                    }
                }
            });
        }
    });
    $('.cus_type_id').change(function (e) {
        e.preventDefault();
        var cus_type_id=$(this).val();
        var invoice_part_id=$('#invoice_part_id').val();
        var avl_qty=$('.avl_qty').val();
        var bom=$('.bom').val();
        if (cus_type_id!="") {
            if (invoice_part_id!="") {
                $.ajax({
                    type: "POST",
                    url: "{{ route('partcoverwiseqtyfetchdata') }}",
                    data:{
                        "_token": "{{ csrf_token()}}",
                        "cus_type_id":cus_type_id,
                        "invoice_part_id":invoice_part_id,
                        "avl_qty":avl_qty,
                        "bom":bom,
                    },
                    success: function (response) {
                        $('.req_qty').val(0);
                        $('.req_qty').attr('max', response.max_qty);
                        $('.req_qty').attr('min', 0);
                        // Set step to 0.5
                        $('.req_qty').attr('step', response.cover_qty);
                        $('.max_qty').val(response.max_qty);
                        $('.max_kg').val(response.max_kg);
                        $('.req_kg').val(0);
                        $('.req_qty').removeAttr('readonly');
                        $('.req_qty').removeClass('bg-light');
                        $('.req_kg').attr('max', response.max_kg);
                        $('.req_kg').attr('min', 0);
                        $('.cover_qty').val(response.cover_qty);
                        $('.max_no_of_cover').val(response.no_of_cover);
                        $('#confirm_cover').hide();
                    }
                });
            }
        }
    });

    $('.req_qty').change(function (e) {
        e.preventDefault();
        var rcpart_count=$('#rcpart_count').val();
        var req_qty=$(this).val();
        var max_qty=$('.max_qty').val();
        var max_kg=$('.max_kg').val();
        var bom=$('.bom').val();
        var part_id=$('.part_id').val();
        var invoice_part_id=$('#invoice_part_id').val();
        var avl_kg=$('.avl_kg').val();
        var avl_qty=$('.avl_qty').val();
        var cover_qty=$('.cover_qty').val();
        var inspected_no_of_cover=((req_qty)/(cover_qty));
        if (req_qty % cover_qty === 0) {
            var qty=req_qty;
            var req=(bom*qty);
            $('.inspected_no_of_cover').val(inspected_no_of_cover);
        } else {
            let floored = Math.floor(inspected_no_of_cover);
            // alert(floored);
            let qty=(parseInt(floored))*(parseInt(cover_qty));
            // alert(qty);
            // alert(bom);
            var req=(bom*qty);
            $('.inspected_no_of_cover').val(floored);
            $('.req_qty').val(qty);
        }
        var req_kg=req.toFixed(2);
        // alert(req);
        if (qty==0) {
            alert('Your Enter Inspected Quantity is less than Cover Quantity...');
            location.reload(true);
            $('#confirm_cover').hide();
        } else {
            if (max_kg>=req) {
            $('.req_kg').val(req_kg);
            // alert('ok-1');
            if (rcpart_count!=''&& req_kg!='' && part_id!='' && invoice_part_id!=''&& bom!=''&& inspected_no_of_cover!=''&& qty!=0) {
                // alert('ok');
                $.ajax({
                    type: "POST",
                    url: "{{ route('partcoverwisercfetchdata') }}",
                    data:{
                        "_token": "{{ csrf_token()}}",
                        "rcpart_count":rcpart_count,
                        "req_kg":req_kg,
                        "req_qty":qty,
                        "part_id":part_id,
                        "invoice_part_id":invoice_part_id,
                        "inspected_no_of_cover":inspected_no_of_cover,
                        "bom":bom,
                    },
                    success: function (response) {
                        console.log(response);
                        if (response.count==1) {
                            $('#new_mydiv').html(response.html);
                            $('#confirm_cover').show();

                        }else{
                            $('#data').html(response.html);
                            setTimeout(function() {
                            location.reload(true);
                            }, 3000);
                            $('#btn').hide();
                            $('#confirm_cover').hide();

                        }
                    }
                });
            } else {
                $('#btn').hide();
                $('#confirm_cover').hide();
                // alert('Not ok...');
            }
        } else {
            $('#confirm_cover').hide();

        }
        }
        // alert(req_kg);

    });

    $('.rm_id').change(function (e) {
        e.preventDefault();
        var req_type_id=$('.req_type_id').val();
        var req_kg=$('.req_qty').val();
        var part_id=$('.part_id').val();
        var rm_id=$(this).val();
        var group_id=$('.group_id').val();
        var machine_id=$('.machine_id').val();
        if (req_type_id!=''&& req_kg!='' && part_id!='' && rm_id!=''&& group_id!=''&& machine_id!='') {
            $.ajax({
                type: "POST",
                url: "{{ route('rmrequistioncheckdata') }}",
                data:{
                    "_token": "{{ csrf_token()}}",
                    "req_type_id":req_type_id,
                    "req_kg":req_kg,
                    "part_id":part_id,
                    "rm_id":rm_id,
                    "group_id":group_id,
                    "machine_id":machine_id,
                },
                success: function (response) {
                    console.log(response);
                    if (response.req_type_id==1) {
                        if (response.rc_msg) {
                            if (response.rm_msg) {
                                if (response.rm_avl_msg) {
                                    $('#btn').show();
                                } else {
                                    $('#data').html(response.html);
                                    setTimeout(function() {
                                        location.reload(true);
                                    }, 3000);
                                    $('#btn').hide();
                                }
                            } else {
                                $('#data').html(response.html);
                                setTimeout(function() {
                                        location.reload(true);
                                    }, 3000);
                                    $('#btn').hide();
                            }
                        }else{
                            $('#data').html(response.html);
                            setTimeout(function() {
                                    location.reload(true);
                                    }, 3000);
                                    $('#btn').hide();
                        }
                    }else{
                        $('#btn').show();
                    }
                }
            });
        } else {
            // alert('Not ok...');
            $('#btn').hide();

        }
    });
</script>
@endpush

