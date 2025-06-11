@extends('layouts.app')
@push('styles')

@endpush
@section('content')

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
        <div class="row col-md-3"></div>
        <div id="res"></div>
        <div class="card">
            <div class="card-header d-flex" style="justify-content:space-between"><span> <b>QC Rejection Register</b></span><a class="btn btn-sm btn-primary" href="{{route('qcrejectionlist')}}">QC Rejection List</a>
            </div>
            <div class="card-body">
                @if ($qrCodes_count!=0)
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="scan_rc_id">Scan Route Card ID *</label>
                            <input type="text" name="scan_rc_id" id="scan_rc_id"  class="form-control @error('scan_rc_id') is-invalid @enderror" autofocus autocomplete="off">
                            @error('scan_rc_id')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                </div>
                @else

                @endif
                <form action="{{route('qcrejection.store')}}" id="sf_receive_formdata" method="POST">
                    @csrf
                    @method('POST')
                        <div class="row d-flex justify-content-center">
                            <input type="hidden" name="rej_wt" id="rej_wt">
                            <input type="hidden" name="current_rc_id" id="current_rc_id">
                            <input type="hidden" name="previous_process_id" id="previous_process_id">
                            <input type="hidden" name="previous_product_process_id" id="previous_product_process_id">
                            <input type="hidden" name="next_productprocess_id" id="next_productprocess_id">
                            <input type="hidden" name="fqc_count" id="fqc_count">
                            <input type="hidden" name="qrcodes_count" id="qrcodes_count" value="{{$qrCodes_count}}">
                            <input type="hidden" name="qr_rc_id" id="qr_rc_id">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="rc_no">Route Card Number *</label>
                                    <select name="rc_no" class="form-control @error('rc_no') is-invalid @enderror" @if ($qrCodes_count!=0)
                                    @readonly(true)
                                @else
                                    @readonly(false)
                                @endif  id="rc_no">
                                        <option value="" selected></option>
                                        @foreach ($d11Datas as $d11Data)
                                            <option value="{{$d11Data->rcmaster->id}}" >{{$d11Data->rcmaster->rc_id}}</option>
                                        @endforeach
                                    </select>
                                    @error('rc_no')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="rc_date">Route Card Date *</label>
                                    <input type="date" name="rc_date" id="rc_date" value="{{$current_date}}" readonly class="form-control bg-light @error('rc_date') is-invalid @enderror" >
                                    @error('rc_date')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="part_id">Part Number *</label>
                                    <select name="part_id" id="part_id" class="form-control bg-light @error('part_id') is-invalid @enderror" @readonly(true)>
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
                                    <label for="next_process_id">Stocking Point *</label>
                                    <select name="next_process_id" id="next_process_id" class="form-control bg-light @error('next_process_id') is-invalid @enderror" @readonly(true)>
                                    </select>
                                    @error('next_process_id')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row d-flex justify-content-center">
                            {{-- <div class="col-md-3">
                                <div class="form-group">
                                    <label for="avl_kg">Available Stock (In KG) *</label>
                                    <input type="number" name="avl_kg" id="avl_kg"  class="form-control bg-light @error('avl_kg') is-invalid @enderror" @readonly(true)>
                                    @error('avl_kg')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div> --}}
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="avl_qty">Available Stock (In Numbers) *</label>
                                    <input type="number" name="avl_qty" id="avl_qty"  class="form-control bg-light @error('avl_qty') is-invalid @enderror" @readonly(true)>
                                    @error('avl_qty')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            {{-- <div class="col-md-3">
                                <div class="form-group">
                                    <label for="receive_kg">Receive Quantity IN KG *</label>
                                    <input type="number" name="receive_kg" id="receive_kg" required min="0" step="0.0000000000000001" class="form-control @error('receive_kg') is-invalid @enderror">
                                    @error('receive_kg')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div> --}}
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="receive_qty">Rejected Quantity In Numbers*</label>
                                    <input type="number" name="receive_qty" id="receive_qty" required min="0" class="form-control @error('receive_qty') is-invalid @enderror">
                                    @error('receive_qty')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="area">Area*</label>
                                    <input type="text" name="area" id="area" required class="form-control @error('area') is-invalid @enderror">
                                    @error('area')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="operation">Operation*</label>
                                    <input type="text" name="operation" id="operation" required class="form-control @error('operation') is-invalid @enderror">
                                    @error('operation')
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
                                    <label for="reason">Remarks*</label>
                                    <textarea name="reason" id="reason" class="form-control reason @error('reason') is-invalid @enderror" cols="10" rows="5"></textarea>
                                    @error('reason')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row d-flex justify-content-center mt-3">
                            <div class="col-md-3">
                                <p><b>Route Card Close :</b></p>
                                  <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="rc_close" id="inlineRadio1" value="yes">
                                    <label class="form-check-label" for="inlineRadio1">Yes</label>
                                  </div>
                                  <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="rc_close" id="inlineRadio2" checked value="no">
                                    <label class="form-check-label" for="inlineRadio2">No</label>
                                  </div>
                            </div>
                        </div>

                        <div class="row d-flex justify-content-center ">
                            <div class="col-md-2 mt-4">
                                <input type="submit" class="btn btn-success  text-white align-center" id="btn" value="Save">
                                <input class="btn btn-danger text-white" id="reset" type="reset" value="Reset">
                            </div>
                        </div>
                </form>


            </div>
        </div>
        <div class="card mt-3">
            <div class="card-header">
                <h4 class="text-center">BOM CALCULATOR</h4>
            </div>
            <div class="card-body">
                <div class="row mt-4">
                    <div class="table">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-responsive">
                                <thead>
                                    <tr>
                                        <th>BOM (IN KG)</th>
                                        <th>ENTER QUANTITY (KG)</th>
                                        <th>QUANTITY IN NOS</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <input type="text" name="bom" id="bom" class="form-control bg-light" @readonly(true)>
                                        </td>
                                        <td>
                                            <input type="number" name="input_wt" id="input_wt" class="form-control">
                                        </td>
                                        <td>
                                            <input type="number" name="bom_qty" id="bom_qty" class="form-control bg-light" @readonly(true)>
                                        </td>
                                    </tr>
                                </tbody>
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
    $('input').on('input', function() {
        var inputId = $(this).attr('id');
        $('#' + inputId + '-error').remove();
    });
    $('#inlineRadio1').hide();

});

    $("#rc_no").select2({
        placeholder:"Select Route Card Number",
        allowedClear:true
    });


    $("#reset").click(function (e) {
        e.preventDefault();
        location.reload(true);
    });

    $('#scan_rc_id').change(function (e) {
        e.preventDefault();
        var rc_no=$(this).val();
        // let rc=rc_no.split("-");
        // var stricker_id=rc[8];
        // alert(stricker_id);
        // alert('ok');
        // alert(rc_no);
        if (rc_no!='') {
            $.ajax({
                type: "POST",
                url: "{{ route('qcqrrcrejfetchdata') }}",
                data:{
                    "_token": "{{ csrf_token()}}",
                    "rc_no":rc_no,
                },
                success: function (response) {
                    // console.log(response);
                    if(response.rc_msg){
                        if (response.avl_msg) {
                            $('#part_id').html(response.part_data);
                                $('#rc_no').html(response.rc_data);
                                // $('#avl_kg').val(response.avl_kg);
                                $('#avl_qty').val(response.avl_qty);
                                $('#receive_qty').attr('max', response.avl_qty);
                                $('#receive_qty').attr('min', 0);
                                $('#bom').val(response.bom);
                                // $('#cover_order_id').val(response.cover_order_id);
                                // $('#cover_qty').val(response.cover_qty);
                                $('#previous_process_id').val(response.process_id);
                                $('#previous_product_process_id').val(response.product_process_id);
                                $('#next_productprocess_id').val(response.product_process);
                                $('#next_process_id').html(response.process);
                                // $('#rc_no').html(response.rc_data);
                                $('#scan_rc_id').val(response.rc_id);
                                $('#qr_rc_id').val(response.rc_id);
                                // $('#current_rc_id').val(response.current_rc_id);
                        } else {
                        //    alert('Sorry This Cover Not Properly Handover From Prevovious Stage...');
                        $('#res').html(response.html);
                            setTimeout(function() {
                                location.reload(true);
                                }, 3000);
                        }

                    }else{
                        $('#res').html(response.html);
                            setTimeout(function() {
                                location.reload(true);
                                }, 3000);
                    }
                }
            });
        }
    });

    $('#input_wt').change(function (e) {
        e.preventDefault();
        var bom_kg=$('#bom').val();
        var input_wt=$(this).val();
        if (bom_kg!=''&&input_wt!='') {
            var nos=input_wt/bom_kg;
            var bom= Math.floor(nos);
            if (bom) {
                $('#bom_qty').val(bom);
            }
        }else{
            alert('Please Check The Input Weight And BOM...');
        }
    });

    $('#receive_qty').change(function (e) {
        e.preventDefault();
        var avl_qty=$('#avl_qty').val();
        var bom_kg=$('#bom').val();
        var receive_qty=$(this).val();
        if (avl_qty!=''&&receive_qty!='') {
                var diff=avl_qty-receive_qty;
                var rej_kg=bom_kg*receive_qty;
                alert(rej_kg);
                // alert(diff);
                if(diff==0){
                    $('#rej_wt').val(rej_kg);
                    $('#inlineRadio1').show();
                }else{
                    $('#rej_wt').val(rej_kg);
                    $('#inlineRadio1').hide();
                }
        }else{
            alert('Please Check The Receive Quantity And Available Quantity...');
            $('#rej_wt').val(0);
            $('#inlineRadio1').hide();

        }
    });

</script>
@endpush

