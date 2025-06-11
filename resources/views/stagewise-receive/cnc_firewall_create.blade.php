@extends('layouts.app')
@push('styles')

@endpush
@section('content')
<form action="{{route('ptscleissue.store')}}" id="pts_receive_formdata" method="POST">
    @csrf
    @method('POST')

<div class="row d-flex justify-content-center">
    <div class="col-12" id="res"></div>

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
            <div class="card-header d-flex" style="justify-content:space-between"><span> <b>CNC Production Coverwise Assign Register</b></span><a class="btn btn-sm btn-primary" href="{{route('cncproductioncoverwiselist')}}">CNC Production Coverwise Assign List</a>
            </div>
            <div class="card-body">
                <input type="hidden" name="invoice_part_id" id="invoice_part_id" class="invoice_part_id">
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
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="cus_type">Customer Type *</label>
                                    <select name="cus_type" class="form-control @error('cus_type') is-invalid @enderror" @required(true) id="cus_type">
                                    </select>
                                    @error('cus_type')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="cover_id">Cover Name & Size *</label>
                                    <select name="cover_id" class="form-control @error('cover_id') is-invalid @enderror" @required(true) id="cover_id">
                                    </select>
                                    @error('cover_id')
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
                                    <label for="cover_qty">Cover Quantity*</label>
                                    <input type="number" name="cover_qty" id="cover_qty" required min="0" class="form-control bg-light @error('cover_qty') is-invalid @enderror" readonly>
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
                                    <label for="no_of_cover">No Of Cover*</label>
                                    <input type="number" name="no_of_cover" id="no_of_cover" required min="0" class="form-control bg-light  @error('no_of_cover') is-invalid @enderror" @readonly(true)>
                                    @error('no_of_cover')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="cover_color">Cover Color*</label>
                                    <input type="text" name="cover_color" id="cover_color" required  class="form-control bg-light @error('cover_color') is-invalid @enderror" readonly>
                                    @error('cover_color')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="receive_qty">Issue Quantity In Numbers*</label>
                                    <input type="number" name="receive_qty" id="receive_qty" required min="0" class="form-control  bg-light @error('receive_qty') is-invalid @enderror" readonly>
                                    @error('receive_qty')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
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
    $("#part_id").select2({
        placeholder:"Select The Part Number",
        allowedClear:true
    });
    $("#rc_no").select2({
        placeholder:"Select Route Card Number",
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
                url: "{{ route('cncproductioncoverwisercfetchdata') }}",
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
            url: "{{ route('cncproductioncoverwisepartissuefetchdata') }}",
            data:{
                "_token": "{{ csrf_token()}}",
                "rc_no":rc_no,
                "part_id":part_id,
            },
            success: function (response) {
                console.log(response);
                 if(response.success){
                         if(response.message){
                        //  $('#part_id').html(response.part);
                         $('#process_id').html(response.process);
                         $('#avl_qty').val(response.avl_qty);
                         $('#receive_qty').attr('max', response.avl_qty);
                         $('#receive_qty').attr('min', 0);
                         $('#bom').val(response.bom);
                         $('#cus_type').html(response.customer);
                         $('#cus_type').select2();
                         $('#invoice_part_id').val(response.invoice_part_id);
                         $('#inlineRadio1').hide();
                         }else{
                            $('#res').html(response.process_msg);
                        setTimeout(function() {
                            location.reload(true);
                            }, 3000);
                            //  alert('This Part Number is Not connected Item Process Master..So Please Contact Mr.PPC/ERP Team');
                         }
                 }else{
                    $('#res').html(response.html);
                        setTimeout(function() {
                            location.reload(true);
                            }, 3000);
                     $('#inlineRadio1').hide();
                    //  var msg='Please Follow The FIFO ..Try RC No Is '+response.fifoRcCard;
                    //  alert(msg);
                    //  location.reload(true);
                    //  $('#inlineRadio1').hide();
                 }
            }
        });
        }
    });

    $('#cus_type').change(function (e) {
        e.preventDefault();
        var cus_type=$(this).val();
        var invoice_part_id=$('#invoice_part_id').val();
        var avl_qty=$('#avl_qty').val();
        // alert(cus_type);
        if (cus_type!='') {
            $.ajax({
                type: "POST",
                url: "{{ route('ptsclecoverfetchdata') }}",
                data:{
                    "_token": "{{ csrf_token()}}",
                    "cus_type":cus_type,
                    "invoice_part_id":invoice_part_id,
                    "avl_qty":avl_qty,
                },
                success: function (response) {
                    $('#cover_id').html(response.cover_details);
                    $('#cover_id').select2();
                    $('#cover_qty').val(response.cover_qty);
                    $('#cover_color').val(response.cover_color);
                    $('#no_of_cover').val(response.no_of_cover);
                    $('#receive_qty').val(response.avl_qty);
                }
            });
        }
    });
    $('#packing_master_id').change(function (e) {
        e.preventDefault();
        var packing_master_id=$(this).val();
        var cus_type=$('#cus_type').val();
        var invoice_part_id=$('#invoice_part_id').val();
        // alert(cus_type);
        if (cus_type!='') {
            $.ajax({
                type: "POST",
                url: "{{ route('ptsclepackingcoverdata') }}",
                data:{
                    "_token": "{{ csrf_token()}}",
                    "packing_master_id":packing_master_id,
                    "cus_type":cus_type,
                    "invoice_part_id":invoice_part_id,
                },
                success: function (response) {
                    $('#cover_qty').val(response.cover_qty);
                    $('#cover_color').val(response.cover_color);
                }
            });
        }
    });
    $('#no_of_cover').change(function (e) {
        e.preventDefault();
        var no_of_cover=$(this).val();
        // alert(no_of_cover);
        if (no_of_cover!=''&& no_of_cover!=0) {
            var cover_qty=$('#cover_qty').val();
            var avl_qty=$('#avl_qty').val();
            var cover_total_quantity=no_of_cover*cover_qty;
            // alert(cover_total_quantity);
            if (avl_qty>=cover_total_quantity) {
                $('#receive_qty').val(cover_total_quantity);
            }else{
                alert('Your No of Cover Quantity is More Than Available Quantity...!');
                $('#no_of_cover').val('');
                $('#receive_qty').val('');


            }
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
                // if(diff==0){
                //     $('#inlineRadio1').show();
                // }else{
                //     $('#inlineRadio1').hide();
                // }
                if(diff<0){
                var html1='<svg xmlns="http://www.w3.org/2000/svg" style="display: none;"><symbol id="check-circle-fill" fill="currentColor" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/></symbol><symbol id="info-fill" fill="currentColor" viewBox="0 0 16 16"><path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zm.93-9.412-1 4.705c-.07.34.029.533.304.533.194 0 .487-.07.686-.246l-.088.416c-.287.346-.92.598-1.465.598-.703 0-1.002-.422-.808-1.319l.738-3.468c.064-.293.006-.399-.287-.47l-.451-.081.082-.381 2.29-.287zM8 5.5a1 1 0 1 1 0-2 1 1 0 0 1 0 2z"/></symbol><symbol id="exclamation-triangle-fill" fill="currentColor" viewBox="0 0 16 16"><path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/></symbol></svg><div class="alert alert-danger d-flex align-items-center" role="alert"><svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Danger:"><use xlink:href="#exclamation-triangle-fill"/></svg><div>';
                var html2='Please Check The Receive Quantity is more than Available Quantity...';
                var html3='</div></div>';
                var html4=html1+html2+html3;
                    $('#res').html(html4);
                        setTimeout(function() {
                            location.reload(true);
                            }, 3000);
                $('#inlineRadio1').hide();
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

