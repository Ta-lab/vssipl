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
        <div class="row col-md-3"id="res"></div>

        <div class="card">
            <div class="card-header d-flex" style="justify-content:space-between"><span> <b>RM Issuance Register4</b></span><a class="btn btn-sm btn-primary" href="{{route('rmissuance.index')}}">RM Issuance List</a>
            </div>
            <div class="card-body">
                <div class="row">
                    <span class="me-auto mb-3 mt-3"><button class="btn btn-primary text-white">STEP 1-SCAN GRN QR CODE</button></span>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="rm_qc_id">Scan The GRN QR Code ID *</label>
                            {{-- <input type="text" name="rm_qc_id" id="rm_qc_id"  class="form-control @error('rm_qc_id') is-invalid @enderror" onCopy="return false" onDrag="return false" onDrop="return false" onPaste="return false" autocomplete="off"> --}}
                            <input type="text" name="rm_qc_id" id="rm_qc_id"  class="form-control @error('rm_qc_id') is-invalid @enderror" onCopy="return false" onDrag="return false" onDrop="return false"  autocomplete="off">
                            @error('rm_qc_id')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                    </div>
                </div>
                <form action="{{route('rmissuance.storedata')}}" id="rm_issuance_formdata" method="POST">
                    @csrf
                    @method('POST')
                        <div class="row d-flex justify-content-center">
                            <input type="hidden" name="heat_id" id="heat_id">
                            <input type="hidden" name="grn_qc_id" id="grn_qc_id">
                            <input type="hidden" name="req_count" id="req_count">
                            <input type="hidden" name="id" id="id" value="{{$rmrequistion->id}}">
                            <input type="hidden" name="req_rc_id" id="req_rc_id" value="{{$rmrequistion->rc_master->id}}">
                            <input type="hidden" name="req_grn_id" id="req_grn_id"
                             @if (($rmRequistionGrnDetails[0])!='')
                                value="{{$rmRequistionGrnDetails[0]->id}}"
                            @else
                                value="0"
                            @endif>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="rc_no">Route Card Number *</label>
                                    <input type="text" name="rc_no" id="rc_no" value="{{$new_rcnumber}}" class="form-control bg-light @error('rc_no') is-invalid @enderror" @readonly(true)>
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
                                    <label for="grnnumber">GRN Number *</label>
                                    <select name="grnnumber" class="form-control bg-light @error('grnnumber') is-invalid @enderror" @readonly(true) id="grnnumber" >
                                    </select>
                                    @error('grnnumber')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="rm_id">RM Description *</label>
                                    <select name="rm_id" id="rm_id" class="form-control bg-light @error('rm_id') is-invalid @enderror">
                                        <option value="{{$rmrequistion->rm_master->id}}" selected>{{$rmrequistion->rm_master->name}}</option>
                                    </select>
                                    @error('rm_id')
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
                                    <label for="requsition_id">Requistion No *</label>
                                    <select name="requsition_id" id="requsition_id" class="form-control bg-light @error('requsition_id') is-invalid @enderror">
                                        <option value="{{$rmrequistion->id}}" selected>{{$rmrequistion->rc_master->rc_id}}</option>
                                    </select>
                                    @error('requsition_id')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="machine_id">Machine No *</label>
                                    <select name="machine_id" id="machine_id" class="form-control bg-light @error('machine_id') is-invalid @enderror">
                                        <option value="{{$rmrequistion->machine_master->id}}" selected>{{$rmrequistion->machine_master->machine_name}}</option>
                                    </select>
                                    @error('machine_id')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="group_id">Group *</label>
                                    <select name="group_id" id="group_id" class="form-control bg-light @error('group_id') is-invalid @enderror">
                                        <option value="{{$rmrequistion->group_master->id}}" selected>{{$rmrequistion->group_master->name}}</option>
                                    </select>
                                    @error('group_id')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="require_kg">Required Quantity IN KGS*</label>
                                    <input type="number" name="require_kg" id="require_kg"  class="form-control bg-light @error('require_kg') is-invalid @enderror" value="{{$rmRequistionGrnDetails[0]->req_kg}}" @readonly(true)>
                                    @error('require_kg')
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
                                    <label for="require_qty">Required Quantity IN NOS*</label>
                                    <input type="number" name="require_qty" id="require_qty"  class="form-control bg-light @error('require_qty') is-invalid @enderror" value="{{$rmRequistionGrnDetails[0]->req_qty}}" @readonly(true)>
                                    @error('require_qty')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="uom_id">Unit Of Measurement (UOM) *</label>
                                    <select name="uom_id" id="uom_id" class="form-control bg-light @error('uom_id') is-invalid @enderror" @readonly(true)>
                                    </select>
                                    @error('uom_id')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="part_id">Part Number *</label>
                                    <select name="part_id" id="part_id" class="form-control @error('part_id') is-invalid @enderror">
                                        <option value="{{$rmrequistion->partmaster->id}}" selected>{{$rmrequistion->partmaster->child_part_no}}</option>
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
                                    <label for="avl_kg">RM Available Stock IN KGS*</label>
                                    <input type="number" name="avl_kg" id="avl_kg"  class="form-control bg-light @error('avl_kg') is-invalid @enderror" @readonly(true)>
                                    @error('avl_kg')
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
                                    <label for="avl_qty">RM Available Stock IN NOS*</label>
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
                                    <label for="issue_kg">Issue Quantity IN KGS*</label>
                                    <input type="number" name="issue_kg" id="issue_kg" min="0" class="form-control bg-light @error('issue_kg') is-invalid @enderror">
                                    @error('issue_kg')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="issue_qty">Issue Quantity In NOS*</label>
                                    <input type="number" name="issue_qty" id="issue_qty" min="0" class="form-control bg-light @error('issue_qty') is-invalid @enderror">
                                    @error('issue_qty')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="heatnumber">Heat No *</label>
                                    <select name="heatnumber" id="heatnumber" @readonly(true) class="form-control bg-light  @error('heatnumber') is-invalid @enderror">
                                    </select>
                                    @error('heatnumber')
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
                                    <label for="lot_no">Lot Number *</label>
                                    <input type="text" name="lot_no" id="lot_no" @readonly(true) class="form-control  bg-light @error('lot_no') is-invalid @enderror" @readonly(true)>
                                    @error('lot_no')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="coil_no">Coil No *</label>
                                    <select name="coil_no" id="coil_no" @readonly(true) class="form-control  bg-light  @error('coil_no') is-invalid @enderror">
                                    </select>
                                    @error('coil_no')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="tc_no">Test Certificate Number *</label>
                                    <input type="text" name="tc_no" id="tc_no" @readonly(true) class="form-control  bg-light @error('tc_no') is-invalid @enderror" @readonly(true)>
                                    @error('tc_no')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row d-flex justify-content-center ">
                            <div class="col-md-2 mt-4">
                                <input type="button" class="btn btn-success  text-white align-center" id="btn" value="Save">
                                <input class="btn btn-danger text-white" id="reset" type="reset" value="Reset">
                            </div>
                        </div>
                        <div class="row">
                            <span class="me-auto mb-3 mt-3"><button class="btn btn-secondary text-white">STEP 2-REQUISTION RM GRN Details</button></span>
                            <div class="table">
                                <div class="table-responsive">
                                    <table class='table table-bordered table-striped table-responsive' id="">
                                        <thead>
                                            <tr>
                                                <th>Rack ID</th>
                                                <th>Requisition No</th>
                                                <th>Date</th>
                                                <th>GRN NO</th>
                                                <th>Heat No</th>
                                                <th>Coil No</th>
                                                <th>Lot No</th>
                                                <th>Required Qty</th>
                                                <th>Required KG</th>
                                                <th>To Be Returned Qty</th>
                                                <th>To Be Returned KG</th>
                                                <th>To Be Issue Qty</th>
                                                <th>To Be Issue KG</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($rmRequistionGrnDetails as $rmRequistionGrnDetail)
                                                <tr>
                                                    <td>{{$rmRequistionGrnDetail->grnqc_master->rack_data->rack_name}}</td>
                                                    <td>{{$rmRequistionGrnDetail->req_master->rc_master->rc_id}}</td>
                                                    <td>{{$rmRequistionGrnDetail->open_date}}</td>
                                                    <td>{{$rmRequistionGrnDetail->grn_master->rcmaster->rc_id}}</td>
                                                    <td>{{$rmRequistionGrnDetail->heatno_master->heatnumber}}</td>
                                                    <td>{{$rmRequistionGrnDetail->heatno_master->coil_no}}</td>
                                                    <td>{{$rmRequistionGrnDetail->heatno_master->lot_no}}</td>
                                                    <td>{{$rmRequistionGrnDetail->req_qty}}</td>
                                                    <td>{{$rmRequistionGrnDetail->req_kg}}</td>
                                                    <td>{{$rmRequistionGrnDetail->to_be_return_qty}}</td>
                                                    <td>{{$rmRequistionGrnDetail->to_be_return_kg}}</td>
                                                    <td>{{$rmRequistionGrnDetail->issue_qty}}</td>
                                                    <td>{{$rmRequistionGrnDetail->issue_kg}}</td>
                                                    <td><span class="btn btn-sm btn-warning text-white">Pending</span>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="13" align="center">No Records Found!</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <span class="me-auto mb-3 mt-3"><button class="btn btn-info text-white">STEP 3-ISSUED RM DETAILS AGAINST THE REQUISTION</button></span>
                            <div class="table">
                                <div class="table-responsive">
                                    <table class='table table-bordered table-striped table-responsive' id="">
                                        <thead>
                                            <tr>
                                                <th>Rack ID</th>
                                                <th>Issued RC No</th>
                                                <th>Requisition No</th>
                                                <th>Date</th>
                                                <th>GRN NO</th>
                                                <th>Heat No</th>
                                                <th>Coil No</th>
                                                <th>Lot No</th>
                                                <th>Required Qty</th>
                                                <th>Required KG</th>
                                                <th>To Be Returned Qty</th>
                                                <th>To Be Returned KG</th>
                                                <th>To Be Issue Qty</th>
                                                <th>To Be Issue KG</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($pickedrmRequistionGrnDetails as $pickedrmRequistionGrnDetail)
                                                <tr>
                                                    <td>{{$pickedrmRequistionGrnDetail->grnqc_master->rack_data->rack_name}}</td>
                                                    <td>{{$pickedrmRequistionGrnDetail->rc_master->rc_id}}</td>
                                                    <td>{{$pickedrmRequistionGrnDetail->req_master->rc_master->rc_id}}</td>
                                                    <td>{{$pickedrmRequistionGrnDetail->open_date}}</td>
                                                    <td>{{$pickedrmRequistionGrnDetail->grn_master->rcmaster->rc_id}}</td>
                                                    <td>{{$pickedrmRequistionGrnDetail->heatno_master->heatnumber}}</td>
                                                    <td>{{$pickedrmRequistionGrnDetail->heatno_master->coil_no}}</td>
                                                    <td>{{$pickedrmRequistionGrnDetail->heatno_master->lot_no}}</td>
                                                    <td>{{$pickedrmRequistionGrnDetail->req_qty}}</td>
                                                    <td>{{$pickedrmRequistionGrnDetail->req_kg}}</td>
                                                    <td>{{$pickedrmRequistionGrnDetail->to_be_return_qty}}</td>
                                                    <td>{{$pickedrmRequistionGrnDetail->to_be_return_kg}}</td>
                                                    <td>{{$pickedrmRequistionGrnDetail->issue_qty}}</td>
                                                    <td>{{$pickedrmRequistionGrnDetail->issue_kg}}</td>
                                                    <td><span class="btn btn-sm btn-success text-white">Issued</span>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="14" align="center">No Records Found!</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </form>
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
    $('#rm_qc_id').focus();
    // getRow();
});

// $("#rm_qc_id").change(function (e) {
//     e.preventDefault();
//     var rm_qc_id=$(this).val();
//     var part_id=$('#part_id').val();
//     var group_id=$('#group_id').val();
//     var rm_id=$('#rm_id').val();
//     $('#grn_qc_id').val(rm_qc_id);
//     if (rm_qc_id!='') {
//             $.ajax({
//             type: "POST",
//             url: "{{ route('grnqcrmissuefetchdata') }}",
//             data:{
//                 "_token": "{{ csrf_token() }}",
//                 "rm_qc_id":rm_qc_id,
//                 "part_id":part_id,
//                 "rm_id":rm_id,
//                 "group_id":group_id,
//             },
//             success: function (response) {
//                 if (response.rm_msg) {
//                     if (response.gqc_msg) {
//                         if (response.success) {
//                             if (response.coil_msg) {
//                                 if (response.avl_msg) {
//                                     $('#grnnumber').html(response.grn_no);
//                                     // $('#rm_id').html(response.rm_id);
//                                     // $('#part_id').html(response.part);
//                                     $('#heatnumber').html(response.heat_no);
//                                     $('#heat_id').val(response.heat_id);
//                                     $('#uom_id').html(response.uom);
//                                     $('#coil_no').html(response.coil_no);
//                                     $('#tc_no').val(response.tc_no);
//                                     $('#lot_no').val(response.lot_no);
//                                     // $('#part_id').html(response.part);
//                                     $('#avl_qty').val(response.avl_qty);
//                                     $('#issue_qty').attr('max', response.avl_qty);
//                                     $('#rm_qc_id').prop('readonly', true);
//                                 } else {
//                                     $('#data').html(response.html);
//                                     setTimeout(function() {
//                                     location.reload(true);
//                                     }, 6000);
//                                 }
//                             } else {
//                                 $('#data').html(response.html);
//                                 setTimeout(function() {
//                                 location.reload(true);
//                                 }, 6000);
//                             }
//                         } else {
//                             $('#data').html(response.html);
//                                 setTimeout(function() {
//                                 location.reload(true);
//                                 }, 6000);
//                         }
//                     } else {
//                         $('#data').html(response.html);
//                                 setTimeout(function() {
//                                 location.reload(true);
//                                 }, 6000);
//                     }
//                 } else {
//                     $('#data').html(response.html);
//                                 setTimeout(function() {
//                                 location.reload(true);
//                                 }, 6000);
//                 }
//             }
//         });
//     }
// });
$("#rm_qc_id").change(function (e) {
    e.preventDefault();
    var rm_qc_id=$(this).val();
    var part_id=$('#part_id').val();
    var group_id=$('#group_id').val();
    var rm_id=$('#rm_id').val();
    var requsition_id=$('#requsition_id').val();
    var req_grn_id=$('#req_grn_id').val();
    $('#grn_qc_id').val(rm_qc_id);
    if (rm_qc_id!='') {
            $.ajax({
            type: "POST",
            url: "{{ route('grnqcrmissuefetchdata2') }}",
            data:{
                "_token": "{{ csrf_token() }}",
                "rm_qc_id":rm_qc_id,
                "req_grn_id":req_grn_id,
                "requsition_id":requsition_id,
                "part_id":part_id,
                "rm_id":rm_id,
                "group_id":group_id,
            },
            success: function (response) {
                if (response.rm_req_msg) {
                    if (response.fifo_msg) {
                        $('#grnnumber').html(response.grn_no);
                        $('#req_count').val(response.count);
                                    // $('#rm_id').html(response.rm_id);
                                    // $('#part_id').html(response.part);
                                    $('#heatnumber').html(response.heat_no);
                                    $('#heat_id').val(response.heat_id);
                                    $('#uom_id').html(response.uom);
                                    $('#coil_no').html(response.coil_no);
                                    $('#tc_no').val(response.tc_no);
                                    $('#lot_no').val(response.lot_no);
                                    // $('#part_id').html(response.part);
                                    $('#avl_kg').val(response.avl_kg);
                                    $('#avl_qty').val(response.avl_qty);
                                    $('#issue_kg').val(response.issue_kg);
                                    $('#issue_qty').val(response.issue_qty);
                                    $('#issue_kg').attr('max', response.avl_kg);
                                    $('#issue_qty').attr('max', response.avl_qty);
                                    $('#issue_kg').prop('readonly', true);
                                    $('#issue_qty').prop('readonly', true);
                                    $('#rm_qc_id').prop('readonly', true);
                    } else {
                        $('#data').html(response.html);
                        setTimeout(function() {
                        location.reload(true);
                        }, 3000);
                    }
                } else {
                    $('#data').html(response.html);
                    setTimeout(function() {
                    location.reload(true);
                    }, 3000);
                }
            }
        });
    }
});
$('#btn').click(function (e) {
    // e.preventDefault();
    $.ajax({
        type: "POST",
        url: "{{ route('rmissuance.storedata') }}",
        data:$("form").serialize(),
        success: function (response) {
            console.log(response.success);
            if (response.success) {
                $('#data').html(response.html);
                        setTimeout(function() {
                        location.reload(true);
                        }, 3000);
            }else{
                $('#data').html(response.html);
                        setTimeout(function() {
                        location.reload(true);
                        }, 6000);
                        // window.location.href="{{route('rmrequistion.index')}}";
                        window.location.href="{{route('rmissuance.index')}}";
            }
        }
    });

});
    $("#part_id").select2({
        placeholder:"Select Part Number",
        allowedClear:true
    });

    $("#reset").click(function (e) {
        e.preventDefault();
        location.reload(true);
    });


</script>
@endpush

