@extends('layouts.app')
@push('styles')

@endpush
@section('content')
<form action="{{route('ptsclereceive.store')}}" id="pts_receive_formdata" method="POST">
    @csrf
    @method('POST')

<div class="row d-flex justify-content-center">
    <div id="data"></div>
    <div class="row" id="res"></div>

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

        <div class="card">
            <div class="card-header d-flex" style="justify-content:space-between"><span> <b>CNC Firewall Inspection Register</b></span><a class="btn btn-sm btn-primary" href="{{route('cncproductionfirewallreceive')}}">CNC Firewall Inspection List</a>
            </div>
            <div class="card-body">
                <div class="row d-flex justify-content-center">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="part_id">Part Number *</label>
                            <select name="part_id" id="part_id" class="form-control part_id @error('part_id') is-invalid @enderror" @readonly(true)>
                                <option value="" selected></option>
                                @foreach ($ptsDatas as $ptsData)
                                    <option value="{{$ptsData->partmaster->id}}" >{{$ptsData->partmaster->child_part_no}}</option>
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
                            <label for="rc_no">Route Card Number *</label>
                            <select name="rc_no" class="form-control @error('rc_no') is-invalid @enderror" @required(true) id="rc_no">
                                {{-- <option value="" selected></option>
                                @foreach ($ptsDatas as $ptsData)
                                    <option value="{{$ptsData->rcmaster->id}}" >{{$ptsData->rcmaster->rc_id}}</option>
                                @endforeach --}}
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
                    {{-- <div class="col-md-3">
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
                    </div> --}}
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="process_id">Stocking Point *</label>
                            <select name="process_id" id="process_id" class="form-control bg-light @error('process_id') is-invalid @enderror" @readonly(true)>
                            </select>
                            @error('process_id')
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
                    {{-- <div class="col-md-3">
                        <div class="form-group">
                            <label for="receive_qty">Issue Quantity In Numbers*</label>
                            <input type="number" name="receive_qty" id="receive_qty" required min="0" class="form-control bg-light @error('receive_qty') is-invalid @enderror">
                            @error('receive_qty')
                                <span class="invalid-feedback" role="alert">
                                   <strong>{{ $message }}</strong>
                                </span>
                             @enderror
                        </div>
                    </div> --}}
                </div>

                        {{-- <div class="row d-flex justify-content-center mt-3">
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
                        </div> --}}
                <div id="dc_details">
                </div>
                <div class="row d-flex justify-content-center ">
                    <div class="col-md-2 mt-4">
                        <input type="submit" class="btn btn-success  text-white align-center" id="btn" value="Save">
                        <input class="btn btn-danger text-white" id="reset" type="reset" value="Reset">
                    </div>
                </div>
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

});

    $("#rc_no").select2({
        placeholder:"Select Route Card Number",
        allowedClear:true
    });

    $("#part_id").select2({
        placeholder:"Select The Part Number",
        allowedClear:true
    });

    $("#reset").click(function (e) {
        e.preventDefault();
        location.reload(true);
    });

    $('#part_id').change(function (e) {
        e.preventDefault();
        var part_id=$(this).val();
        if (part_id!='') {
            $.ajax({
                type: "POST",
                url: "{{ route('cncproductionfirewallrcissuefetchdata') }}",
                data:{
                    "_token": "{{ csrf_token()}}",
                    "part_id":part_id,
                },
                success: function (response) {
                    if (response.rc_msg) {
                        $('#rc_no').html(response.html);
                    } else {
                        $('#res').html(response.html);
                            setTimeout(function() {
                                location.reload(true);
                                }, 3000);
                    }
                }
            });
        }
    });

    $('#rc_no').change(function (e) {
        e.preventDefault();
        var rc_no=$(this).val();
        var part_id=$('#part_id').val();
        // alert(rc_no);
        if (rc_no!='') {
            $.ajax({
            type: "POST",
            url: "{{ route('cncproductionfirewallpartreceivefetchdata') }}",
            data:{
                "_token": "{{ csrf_token()}}",
                "rc_no":rc_no,
                "part_id":part_id,
            },
            success: function (response) {
                console.log(response);
                 if(response.success){
                         if(response.message){
                         $('#part_id').html(response.part);
                         $('#process_id').html(response.process);
                         $('#avl_qty').val(response.avl_qty);
                         $('#receive_qty').attr('max', response.avl_qty);
                         $('#receive_qty').attr('min', 0);
                         $('#bom').val(response.bom);
                         $('#dc_details').html(response.html);
                         $('#inlineRadio1').hide();
                         }else{
                             alert('This Part Number is Not connected Item Process Master..So Please Contact Mr.PPC/ERP Team');
                         }
                 }else{
                     // avar msg='Please Follow The FIFO ..Try RC No Is '+response.fifoRcCard;
                    // a alert(msg);
                     // alocation.reload(true);
                     // $('#inlineRadio1').hide();
                     $('#res').html(response.fifo_html);
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
        var receive_qty=$(this).val();
        if (avl_qty!=''&&receive_qty!='') {
                var diff=avl_qty-receive_qty;
                // alert(diff);
                if(diff==0){
                    $('#inlineRadio1').show();
                }else{
                    $('#inlineRadio1').hide();
                }
        }else{
            alert('Please Check The Receive Quantity And Available Quantity...');
            $('#inlineRadio1').hide();

        }
    });

</script>
@endpush

