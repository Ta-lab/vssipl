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
        <div class="col-12" id="res"></div>

        <div class="card">
            <div class="card-header d-flex" style="justify-content:space-between"><span> <b>Despatch Plan FG Register</b></span><a class="btn btn-sm btn-primary" href="{{route('salesplanfglist')}}">Despatch Plan FG List</a>
            </div>
            <div class="card-body">
                {{-- <div class="row">
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
                </div> --}}
                <form action="{{route('salesdespatchplansummary.update',$salesdespatchplansummary->id)}}" id="salesplanfg_formdata" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="row d-flex justify-content-center">
                        <input type="hidden" name="plan_id" id="plan_id" value="{{$salesdespatchplansummary->id}}">
                        <input type="hidden" name="packing_master_id" id="packing_master_id" value="{{$salesdespatchplansummary->packing_master_id}}">
                        <input type="hidden" name="manufacturing_part_id" id="manufacturing_part_id" value="{{$salesdespatchplansummary->manufacturing_part_id}}">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="plan_no">Plan No *</label>
                                <input type="text" name="plan_no" id="plan_no" value="{{$salesdespatchplansummary->plan_no}}" @readonly(true) class="form-control bg-light @error('plan_no') is-invalid @enderror">
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
                                <input type="date" name="open_date" id="open_date" value="{{$salesdespatchplansummary->open_date}}" @readonly(true) class="form-control bg-light @error('open_date') is-invalid @enderror" >
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
                                <select name="cus_id" id="cus_id" class="form-control  bg-light @error('cus_id') is-invalid @enderror">
                                    <option value="" selected></option>
                                    @foreach ($customerDatas as $customerData)
                                        @if ($salesdespatchplansummary->customermaster->id==$customerData->id)
                                            <option value="{{$customerData->id}}" selected>{{$customerData->cus_code}}</option>
                                        @else
                                            @if ($salesdespatchplansummary->status!=0)
                                                <option value="{{$customerData->id}}" >{{$customerData->cus_code}}</option>
                                            @else

                                            @endif
                                        @endif
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
                                    <option value="{{$salesdespatchplansummary->productmaster->id}}" selected>{{$salesdespatchplansummary->productmaster->part_no}}</option>
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
                                <select name="cover_id" class="form-control  bg-light @error('cover_id') is-invalid @enderror"  id="cover_id">
                                    <option value="{{$salesdespatchplansummary->packingmaster->covermaster->id}}" selected>{{($salesdespatchplansummary->packingmaster->covermaster->cover_name).'-'.($salesdespatchplansummary->packingmaster->covermaster->cover_size)}}</option>
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
                                <select name="cus_type_name" class="form-control  bg-light @error('cus_type_name') is-invalid @enderror"  id="cus_type_name">
                                    <option value="{{$salesdespatchplansummary->packingmaster->cus_type_name}}" selected>{{($salesdespatchplansummary->packingmaster->cus_type_name)}}</option>
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
                                <input type="number" name="cover_qty" id="cover_qty"  class="form-control bg-light @error('cover_qty') is-invalid @enderror" value="{{$salesdespatchplansummary->packingmaster->cover_qty}}" step="0.01" @readonly(true)  @required(true)>
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
                                <input type="number" name="no_of_cover" id="no_of_cover"  class="form-control bg-light @error('no_of_cover') is-invalid @enderror"  @required(true) value="{{($salesdespatchplansummary->req_cover_qty)}}" @if (($salesdespatchplansummary->status!=3)||($salesdespatchplansummary->status!=4))
                                    @readonly(false)
                                @else
                                @readonly(true)

                                @endif>
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
                                <input type="number" name="cus_req_qty" id="cus_req_qty"   @required(true)   value="{{$salesdespatchplansummary->cus_req_qty}}" @if (($salesdespatchplansummary->status==3)||($salesdespatchplansummary->status==4))
                                @readonly(true)
                                class="form-control bg-light @error('cus_req_qty') is-invalid @enderror"
                            @elseif($salesdespatchplansummary->status==0)
                                class="form-control @error('cus_req_qty') is-invalid @enderror"
                                min="0"
                            @else
                                @readonly(false)
                                class="form-control @error('cus_req_qty') is-invalid @enderror"
                                min="{{$salesdespatchplansummary->actual_fg_qty}}"

                            @endif>
                                @error('cus_req_qty')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="actual_fg_qty">Actual FG Received Quantity *</label>
                                <input type="number" name="actual_fg_qty" id="actual_fg_qty"  class="form-control bg-light @error('actual_fg_qty') is-invalid @enderror" @readonly(true)  @required(true) value="{{$salesdespatchplansummary->actual_fg_qty}}">
                                @error('actual_fg_qty')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="table">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-responsive">
                                    <thead>
                                        <tr>
                                            <th>S.No</th>
                                            <th>Plan ID</th>
                                            <th>RC No</th>
                                            <th>Cover No</th>
                                            <th>Part No</th>
                                            <th>Part Description</th>
                                            <th>Cover Name & Size</th>
                                            <th>Cover Standard Quantity</th>
                                            <th>Actual Cover Received Quantity</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($salesdespatchplantransactions as $salesdespatchplantransaction)
                                        <tr>
                                            <td>{{$loop->iteration}}</td>
                                            <td>{{$salesdespatchplantransaction->salesplanmaster->plan_no}}</td>
                                            <td>{{$salesdespatchplantransaction->packingstrickerdetails->rcmaster->rc_id}}</td>
                                            <td>{{$salesdespatchplantransaction->packingstrickerdetails->cover_order_id}}</td>
                                            <td>{{$salesdespatchplantransaction->manufacturingpartmaster->child_part_no}}</td>
                                            <td>{{$salesdespatchplantransaction->productmaster->part_desc}}</td>
                                            <td>{{($salesdespatchplantransaction->packingstrickerdetails->covermaster->cover_name).'-'.($salesdespatchplantransaction->packingstrickerdetails->covermaster->cover_size)}}</td>
                                            <td>{{$salesdespatchplantransaction->packingstrickerdetails->cover_qty}}</td>
                                            <td>{{$salesdespatchplantransaction->receive_qty}}</td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="9" align="center">No Records Found!</td>
                                        </tr>
                                        @endforelse

                                    </tbody>
                                </table>
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

    // $('#scan_rc_id').change(function (e) {
    //     e.preventDefault();
    //     var rc_no=$(this).val();
    //     var cus_id=$('#cus_id').val();
    //     var part_id=$('#part_id').val();
    //     var plan_id=$('#plan_id').val();
    //     var plan_no=$('#plan_no').val();
    //     var open_date=$('#open_date').val();
    //     var manufacturing_part_id=$('#manufacturing_part_id').val();
    //     var packing_master_id=$('#packing_master_id').val();
    //     var cover_id=$('#cover_id').val();
    //     var cus_type_name=$('#cus_type_name').val();
    //     var cover_qty=$('#cover_qty').val();
    //     var no_of_cover=$('#no_of_cover').val();
    //     var cus_req_qty=$('#cus_req_qty').val();
    //     var actual_fg_qty=$('#actual_fg_qty').val();
    //     var diff_qty=cus_req_qty-actual_fg_qty;
    //     let rc=rc_no.split("-");
    //     var stricker_id=rc[8];
    //     // alert(stricker_id);
    //     // alert(diff_qty);
    //     if (stricker_id!='') {
    //         if ((diff_qty!='')||(diff_qty>0)) {
    //             $.ajax({
    //             type: "POST",
    //             url: "{{ route('salesplanfgstore') }}",
    //             data:{
    //                 "_token": "{{ csrf_token() }}",
    //                 "stricker_id":stricker_id,
    //                 "cus_id":cus_id,
    //                 "part_id":part_id,
    //                 "plan_id":plan_id,
    //                 "plan_no":plan_no,
    //                 "open_date":open_date,
    //                 "manufacturing_part_id":manufacturing_part_id,
    //                 "packing_master_id":packing_master_id,
    //                 "cover_id":cover_id,
    //                 "cus_type_name":cus_type_name,
    //                 "cover_qty":cover_qty,
    //                 "no_of_cover":no_of_cover,
    //                 "cus_req_qty":cus_req_qty,
    //                 "actual_fg_qty":actual_fg_qty,
    //             },
    //             success: function (response) {
    //                 $('#res').html(response.html);
    //                 setTimeout(function() {
    //                     $('#res').fadeOut('slow');
    //                 }, 1000);
    //                 location.reload(true);
    //                 // setTimeout(($('#res').html(response.html)),500);
    //             }
    //         });
    //         }
    //     }
    // });


</script>
@endpush

