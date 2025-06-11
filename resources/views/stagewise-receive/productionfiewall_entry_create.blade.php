@extends('layouts.app')
@push('styles')

@endpush
@section('content')

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
            <div class="card-header d-flex" style="justify-content:space-between"><span> <b>CNC Production Part Receive Register</b></span><a class="btn btn-sm btn-primary" href="{{route('productioncoverwiselist')}}">CNC Production Part Receive List</a>
            </div>
            <div class="card-body">
                @if ($qrCodes_count!=0)
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="scan_rc_id">Scan Route Card ID *</label>
                            <input type="text" name="scan_rc_id" id="scan_rc_id"  class="form-control qr_scanning @error('scan_rc_id') is-invalid @enderror" autofocus autocomplete="off">
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
                <form action="{{route('productioncoverwise.store')}}" id="sf_receive_formdata" method="POST">
                    @csrf
                    @method('POST')
                        <div class="row d-flex justify-content-center">
                            <input type="hidden" name="current_rc_id" id="current_rc_id">
                            <input type="hidden" name="previous_product_process_id" id="previous_product_process_id">
                            <input type="hidden" name="next_productprocess_id" id="next_productprocess_id">
                            <input type="hidden" name="fqc_count" id="fqc_count">
                            <input type="hidden" name="qrcodes_count" id="qrcodes_count" value="{{$qrCodes_count}}">
                            <input type="hidden" name="qr_rc_id" id="qr_rc_id">
                            <input type="hidden" name="invoice_part_id" id="invoice_part_id">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="rc_no">Route Card Number *</label>
                                    <select name="rc_no" class="form-control rc_no @error('rc_no') is-invalid @enderror" @if ($qrCodes_count!=0)
                                    @readonly(true)
                                @else
                                    @readonly(false)
                                @endif  id="rc_no">
                                        @if ($qrCodes_count!=0)

                                        @else
                                            <option value="" selected></option>
                                            @foreach ($d11Datas as $d11Data)
                                                <option value="{{$d11Data->rcmaster->id}}" >{{$d11Data->rcmaster->rc_id}}</option>
                                            @endforeach
                                        @endif
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
                                    <select name="part_id" id="part_id" class="form-control part_id bg-light @error('part_id') is-invalid @enderror" @readonly(true)>
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
                                    <label for="current_process">Current Stocking Point *</label>
                                    <select name="current_process" id="current_process" class="form-control current_process bg-light @error('current_process') is-invalid @enderror" @readonly(true)>
                                    </select>
                                    @error('current_process')
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
                                    <label for="next_process_id">Next Stocking Point *</label>
                                    <select name="next_process_id" id="next_process_id" class="form-control next_process_id bg-light @error('next_process_id') is-invalid @enderror" @readonly(true)>
                                    </select>
                                    @error('next_process_id')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="avl_qty">Available Stock (In Numbers) *</label>
                                    <input type="number" name="avl_qty" id="avl_qty"  class="form-control avl_qty bg-light @error('avl_qty') is-invalid @enderror" @readonly(true)>
                                    @error('avl_qty')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="inspected_qty">Inspected Quantity (In Numbers) *</label>
                                    <input type="number" name="inspected_qty" id="inspected_qty"  class="form-control inspected_qty @error('inspected_qty') is-invalid @enderror">
                                    @error('inspected_qty')
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
                                    <label for="receive_qty">Receive Quantity In Numbers*</label>
                                    <input type="number" name="receive_qty" id="receive_qty" required min="0" class="form-control @error('receive_qty') is-invalid @enderror">
                                    @error('receive_qty')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row" id="table1"></div>
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
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#inlineRadio1').hide();

    $('.qr_scanning').on('change', function() {
        var stricker_id=$('#scan_rc_id').val();
        // alert(stricker_id);
        if (stricker_id!='') {
            $.ajax({
                type: "POST",
                url: "{{ route('productionfirewallfetchdata') }}",
                data:{
                    "_token": "{{ csrf_token()}}",
                    "stricker_id":stricker_id,
                },
                success: function (response) {
                    console.log(response);
                    if (response.process_check==0) {
                        $('#res').html(response.process_html);
                        setTimeout(function() {
                                location.reload(true);
                                }, 6000);
                    } else {
                        if (response.rc_status==0) {
                            $('#res').html(response.rc_html);
                            setTimeout(function() {
                                location.reload(true);
                                }, 6000);
                        } else {
                            $('.rc_no').html(response.rc_no);
                            $('.part_id').html(response.part_no);
                            $('.current_process').html(response.current_process);
                            $('.next_process_id').html(response.next_process);
                            $('.avl_qty').val(response.avl_qty);
                            $('#receive_qty').attr('max', response.avl_qty);
                            $('#inspected_qty').attr('max', response.avl_qty);
                            $('#inlineRadio1').hide();
                            $('#fqc_count').val(response.fqc_count);
                            $('#previous_product_process_id').val(response.current_product_process_id);
                            $('#next_productprocess_id').val(response.next_product_process_id);
                            $('#current_rc_id').val(response.rc_id);
                            $('#qr_rc_id').val(response.rc_id);
                            $('#cus_type').html(response.customer);
                            $('#cus_type').select2();
                         $('#invoice_part_id').val(response.invoice_part_id);

                        }
                    }
                }
            });
        }
    });
  });
  $('#cus_type').change(function (e) {
        e.preventDefault();
        var cus_type=$(this).val();
        var invoice_part_id=$('#invoice_part_id').val();
        var avl_qty=$('#avl_qty').val();
        var inspected_qty=$('#inspected_qty').val();
        // alert(invoice_part_id);
        if (invoice_part_id!='') {
            $.ajax({
                type: "POST",
                url: "{{ route('productionfirewallcoverfetchdata') }}",
                data:{
                    "_token": "{{ csrf_token()}}",
                    "cus_type":cus_type,
                    "invoice_part_id":invoice_part_id,
                    "avl_qty":avl_qty,
                    "inspected_qty":inspected_qty,
                },
                success: function (response) {
                    $('#cover_id').html(response.cover_details);
                    $('#cover_id').select2();
                    $('#cover_qty').val(response.cover_qty);
                    $('#cover_color').val(response.cover_color);
                    $('#no_of_cover').val(response.no_of_cover);
                    $('#receive_qty').val(response.avl_qty);
                    $('#table1').html(response.table);
                }
            });
        }
    });
  $('#rc_no').select2({
    'placeholder':'Select The RC Number',
    'allowclear':true
  });
  $("#reset").click(function (e) {
        e.preventDefault();
        location.reload(true);
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
                }
                if (diff<0) {
                    alert('Sorry You Enter The More Than Available Quantity...');
                    $('#inlineRadio1').hide();

                }
                else{
                    $('#inlineRadio1').hide();
                }
        }else{
            alert('Please Check The Receive Quantity And Available Quantity...');
            $('#inlineRadio1').hide();
        }
    });
    // function getdata2() {
    //     var rc_no=$('#scan_rc_id').val();
    //     let rc=rc_no.split("-");
    //     var stricker_id=rc[8];
    //     if (stricker_id!='') {
    //         $.ajax({
    //             type: "POST",
    //             url: "{{ route('productioncoverwisefetchdata') }}",
    //             data:{
    //                 "_token": "{{ csrf_token()}}",
    //                 "stricker_id":stricker_id,
    //             },
    //             success: function (response) {
    //                 // console.log(response);
    //                 if(response.message){
    //                     var a=response.avl_qty;
    //                     var avl=parseInt(a);
    //                     alert(avl);
    //                     if (response.success_msg) {
    //                             $('#part_id').html(response.part_data);
    //                             $('#rc_no').html(response.rc_data);
    //                             // $('#avl_kg').val(response.avl_kg);
    //                             $('#avl_qty').val(response.avl_qty);
    //                             $('#receive_qty').val(response.avl_qty);
    //                             $('#receive_qty').attr('max', response.avl_qty);
    //                             $('#receive_qty').attr('min', 0);
    //                             $('#bom').val(response.bom);
    //                             $('#cover_order_id').val(response.cover_order_id);
    //                             $('#cover_qty').val(response.cover_qty);
    //                             // $('#previous_product_process_id').val(response.product_process_id);
    //                             // $('#next_process_id').val(response.next_process_id);
    //                             $('#next_process_id').html(response.operation);
    //                             // $('#rc_no').html(response.rc_data);
    //                             $('#scan_rc_id').val(response.stricker_data);
    //                             $('#qr_rc_id').val(response.stricker_data);
    //                             $('#current_rc_id').val(response.current_rc_id);
    //                     } else {
    //                     //    alert('Sorry This Cover Not Properly Handover From Prevovious Stage...');
    //                             var msg='This Cover No is '+response.cover_rc+' Already Received by FG Team';
    //                             alert(msg);
    //                     //    location.reload(true);
    //                     }
    //                 }else{
    //                     alert('Sorry This Cover Not Properly Handover From Prevovious Stage...');
    //                     // var msg='This Cover No is '+response.cover_rc+' Already Received by FG Team';
    //                     // alert(msg);
    //                 }
    //             }
    //         });
    //     }
    // }


</script>
@endpush

