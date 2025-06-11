@extends('layouts.app')
@section('content')
<form action="{{route('salesconfirmationentry')}}" id="salesconfirmationentry_formdata" method="POST">
    @csrf
    @method('POST')
<div class="row d-flex justify-content-center">

    <div class="col-12">
        <div class="card">
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

            <div class="card-header d-flex" style="justify-content:space-between"><span> <b> Sales Despatch Plan Confirmation List</b> </span>
                {{-- <a class="btn btn-sm btn-primary" href="{{route('salesdespatchplansummary.create')}}">Add Sales Despatch Plan</a> --}}
                <div class="col-2">
                    <input type="submit" class="form-control btn btn-primary save d-flex" id="save" value="save">
                </div>
            </div>
            <div class="card-body">
                <div class="table">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-responsive">
                            <thead>
                                <tr>
                                    <th><input type="checkbox" class="form-check-input select_all" name="select_all" id="select_all"></th>
                                    <th style="width: 200px;">Plan ID</th>
                                    <th>Plan Date</th>
                                    <th>Customer Code</th>
                                    <th>Customer Name</th>
                                    <th>Customer Type</th>
                                    <th>Part Number</th>
                                    <th>Cover Quantity</th>
                                    <th>Require Quantity</th>
                                    <th>Actual FG Received Quantity</th>
                                    <th>Confirmed Invoice Quantity</th>
                                    <th>Status</th>
                                    <th>Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($salesDespatchPlanSummaries as $salesDespatchPlanSummary)
                                <tr  id="tr_{{$salesDespatchPlanSummary->id}}">
                                    <input type="hidden" name="select_checkbox[{{$salesDespatchPlanSummary->id}}]" id="select_checkbox" class="select_checkbox" value="0">
                                    <input type="hidden" name="manufacturing_part_id[{{$salesDespatchPlanSummary->id}}]" id="manufacturing_part_id" value="{{$salesDespatchPlanSummary->manufacturing_part_id}}" class="form-control bg-light manufacturing_part_id @error('manufacturing_part_id') is-invalid @enderror">
                                    <td><input type="checkbox" class="form-check-input sub_id" name="sub_id[]" id="sub_id" data-id="{{$salesDespatchPlanSummary->id}}" value="{{$salesDespatchPlanSummary->id}}"></td>
                                    <td style="width: 350px"><input type="text" name="plan_no[{{$salesDespatchPlanSummary->id}}]" id="plan_no" value="{{$salesDespatchPlanSummary->plan_no}}" @readonly(true) class="form-control bg-light plan_no @error('plan_no') is-invalid @enderror"></td>
                                    <td ><input type="date" name="open_date[{{$salesDespatchPlanSummary->id}}]" id="open_date" value="{{$salesDespatchPlanSummary->open_date}}" @readonly(true) class="form-control bg-light open_date @error('open_date') is-invalid @enderror"></td>
                                    <td style="width: 140px"><select name="cus_id[{{$salesDespatchPlanSummary->id}}]" id="cus_id" class="form-control  bg-light @error('cus_id') is-invalid @enderror">
                                        <option value="{{$salesDespatchPlanSummary->customermaster->id}}" selected>{{$salesDespatchPlanSummary->customermaster->cus_code}}</option>
                                    </select></td>
                                    <td style="width: 160px"><input type="text" name="cus_name[{{$salesDespatchPlanSummary->id}}]" id="cus_name" value="{{$salesDespatchPlanSummary->customermaster->cus_name}}" @readonly(true) class="form-control bg-light cus_name @error('cus_name') is-invalid @enderror"></td>
                                    <td><input type="text" name="cus_type_name[{{$salesDespatchPlanSummary->id}}]" id="cus_type_name" value="{{$salesDespatchPlanSummary->customermaster->cus_type_name}}" @readonly(true) class="form-control bg-light cus_type_name @error('cus_type_name') is-invalid @enderror"></td>
                                    <td style="width: 180px"><select name="part_id[{{$salesDespatchPlanSummary->id}}]" id="part_id" class="form-control bg-light part_id @error('part_id') is-invalid @enderror">
                                        <option value="{{$salesDespatchPlanSummary->productmaster->id}}" selected>{{$salesDespatchPlanSummary->productmaster->part_no}}</option>
                                    </select></td>
                                    <td><input type="text" name="cover_qty[{{$salesDespatchPlanSummary->id}}]" id="cover_qty" value="{{$salesDespatchPlanSummary->packingmaster->cover_qty}}" @readonly(true) class="form-control bg-light cover_qty @error('cover_qty') is-invalid @enderror"></td>
                                    <td><input type="text" name="cus_req_qty[{{$salesDespatchPlanSummary->id}}]" id="cus_req_qty" value="{{$salesDespatchPlanSummary->cus_req_qty}}" @readonly(true) class="form-control bg-light cus_req_qty @error('cus_req_qty') is-invalid @enderror"></td>
                                    <td><input type="text" name="actual_fg_qty[{{$salesDespatchPlanSummary->id}}]" id="actual_fg_qty" value="{{$salesDespatchPlanSummary->actual_fg_qty}}" @readonly(true) class="form-control bg-light actual_fg_qty @error('actual_fg_qty') is-invalid @enderror"></td>
                                    <td><input type="number" name="to_confirm_qty[{{$salesDespatchPlanSummary->id}}]" id="to_confirm_qty" @if ($salesDespatchPlanSummary->to_confirm_qty==0)
                                        value="{{$salesDespatchPlanSummary->actual_fg_qty}}"
                                    @else
                                        value="{{$salesDespatchPlanSummary->to_confirm_qty}}"
                                    @endif minimum="{{$salesDespatchPlanSummary->packingmaster->cover_qty}}" maximum="{{$salesDespatchPlanSummary->actual_fg_qty}}" @readonly(false) class="form-control to_confirm_qty @error('to_confirm_qty') is-invalid @enderror"></td>
                                    <td style="width: 180px"><select name="status[{{$salesDespatchPlanSummary->id}}]"  id="status"  class="form-control status select2 @error('status') is-invalid @enderror">
                                        <option value="3" selected>CONFIRMED</option>
                                        <option value="4">REJECTED</option>
                                    </select></td>
                                    <td><textarea name="remarks[{{$salesDespatchPlanSummary->id}}]" id="remarks" class="form-control remarks @error('remarks') is-invalid @enderror" cols="30" rows="5">{{$salesDespatchPlanSummary->remarks}}</textarea></td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="15" align="center">No Records Found!</td>
                                </tr>
                                @endforelse

                            </tbody>
                        </table>
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
$('.remarks').hide();
$("#status").select2({
        placeholder:"Select The Status",
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

    $('.select_all').on('click', function(e) {
          if($(this).is(':checked',true))
          {
              $(".sub_id").prop('checked', true);
              $(".select_checkbox").val(1);
          } else {
              $(".sub_id").prop('checked',false);
              $(".select_checkbox").val(0);
          }
    });

    $('.sub_id').change(function (e) {
    e.preventDefault();
    getRow();
    });

    function getRow(){
        // $('table > tbody  > tr > td.sub_id').each(function(index, row) {
        //     if($(".sub_id").is(':checked',true))
        //   {
        //       $(".select_all").prop('checked', true);
        //   } else {
        //       $(".select_all").prop('checked',false);
        //   }
        // });
        $("table > tbody  > tr").each(function (index,row) {
            var isChecked = $(".sub_id input:checkbox").checked;
            var check_status=$(row).find('.select_checkbox').val();
            // alert(check_status);
            // $(this).find('td .sub_id').each(function () {
                if(isChecked){
                    $(".select_all").prop('checked', true);
                    // alert('1');
                    var diff=1;
                    $(this).closest('tr').find('td.select_checkbox').text(diff);
                    // $(this).closest('td.select_checkbox').val(diff);
                    // $(row).find('.select_checkbox').val(1);
                }else{
                    // alert('0');
                    $(row).find('.select_checkbox').val(0);
                }
            // });
        });
    }

    $('.to_confirm_qty').change(function (e) {
        e.preventDefault();
        var to_confirm_qty=$(this).val();
        var actual_fg_qty=$('.actual_fg_qty').val();
        var diff_qty=actual_fg_qty-to_confirm_qty;
        // alert(actual_fg_qty);
        // alert(to_confirm_qty);
        // alert(diff_qty);
        if (diff_qty>0) {
            // alert('ok');
        } else {
            alert('Sorry You Have Enter More than Actual FG Stock...!');
            location.reload(true);

        }
    });
    $('.status').change(function (e) { 
        e.preventDefault();
        $('.status').each(function (index, row) {
            // element == this
            var status = ($(row).val());
            // alert(status);
            if (status==4) {
                // alert('ok');
                $(this).closest("tr").find('td .remarks').show();
                $(this).closest("tr").find('td .remarks').prop("required", true);
                // textarea.show();
            } else {
                // alert('not ok');
                $(this).closest("tr").find('td .remarks').hide();
                $(this).closest("tr").find('td .remarks').prop("required", false);
            }
        });
    });
    $('#save').on('click', function(e) {
        e.preventDefault();
            var allVals = [];
            $(".sub_id:checked").each(function() {
                allVals.push($(this).attr('data-id'));
            });
            if(allVals.length <=0)
            {
                alert("Please select row.");
                return false;
            }  else {
                var check = confirm("Are you sure you want to confirm this data for Invoice Generation Now?");
                if(check == true){
                    // alert(allVals);
                    var join_selected_values = allVals.join(",");
                    alert(join_selected_values);
                    var a=$('#salesconfirmationentry_formdata').serialize();
                    $.ajax({
                        type: "POST",
                        url: "{{ route('salesconfirmationentry') }}",
                        data:a,
                        success: function (response) {
                            // console.log(response);
                            // alert('ok');
                            $('#res').html(response.html);
                            setTimeout(function() {
                                $('#res').fadeOut('slow');
                            }, 1000);
                            location.reload(true);
                            
                        }
                    });
                }else{
                    return false;
                }
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


