{{-- <span class="me-auto mb-3"><button class="btn btn-secondary text-white">STEP 3-Route Card Details</button></span> --}}
<div class="row myDiv mb-3 justify-content-start clearfix"  id="myDiv" style="display: none">
    <div class="col-md-3">
        <div class="form-group ">
            <label for="status_all"><b>Status All *</b></label>
            <select name="status_all" id="status_all" class="form-control status_all">
                <option value="0" selected >Pending</option>
                <option value="1">OK</option>
                <option value="2">Reject</option>
                <option value="3">Rework</option>
            </select>
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group ">
            <label for="reason_all"><b>Reason All *</b></label>
            <textarea name="reason_all" class="form-control reason_all" id="reason_all" cols="15" rows="5"></textarea>
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group ">
            <label for="no_of_inspector"><b>No of Inspectors *</b></label>
            <select name="no_of_inspector" id="no_of_inspector" class="form-control no_of_inspector">
                    <option value="1" selected>1 Person</option>
                    <option value="2">More Than 1 Person</option>
            </select>
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group ">
            <label for="inspect_all"><b>Inspectors *</b></label>
            <select name="inspect_all" id="inspect_all" class="form-control inspect_all">
                @foreach ($cleMasterDatas as $cleMasterData)
                <option value="{{$cleMasterData->id}}">{{$cleMasterData->name}}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>
<div class="col-md-12 mt-3 mb-3">
    <div class="table-responsive">
        <table class='table table-bordered table-striped table-responsive part' id="part">
            <thead>
            <tr>
                <th><input type="checkbox" class="form-check-input select_all" name="select_all" id="select_all"></th>
                <th width="250px"><b>Cover DC Number</b></th>
                <th><b>Cover Size</b></th>
                <th><b>Cover Quantity</b></th>
                <th><b>Total Receive Quantity</b></th>
                <th><b>Total Accept Quantity</b></th>
                <th><b>Total Reject Quantity</b></th>
                <th><b>Total Rework Quantity</b></th>
                <th><b>Total Available Quantity</b></th>
                <th><b>Status</b></th>
                <th><b>Inspected By</b></th>
                <th><b>Remarks</b></th>
            </tr>
            </thead>
            <tbody >
                @forelse ($packingStrickerDetails as $packingStrickerDetail)
                <tr class="order_{{$packingStrickerDetail->id}}">
                    <input type="hidden" name="id_datas[]" value="{{$packingStrickerDetail->id}}">
                    <input type="hidden" name="row_count[]" class="form-control row_count" id="row_count" value="0">
                    <td><input type="checkbox" class="form-check-input sub_id" name="sub_id[]" data-id="{{$packingStrickerDetail->id}}" value="{{$packingStrickerDetail->id}}"></td>
                    <td width="250px"><input type="text" name="cover_dc_number[{{$packingStrickerDetail->id}}]" id="cover_dc_number" class="form-control bg-light cover_dc_number" value="{{$packingStrickerDetail->rcmaster->rc_id}}-{{$packingStrickerDetail->cover_order_id}}"></td>
                    <td><select name="cover_id[{{$packingStrickerDetail->id}}]" class="form-control bg-light cover_id" id="cover_id"><option value="{{$packingStrickerDetail->covermaster->id}}">{{$packingStrickerDetail->covermaster->cover_name.'&'.$packingStrickerDetail->covermaster->cover_size}}</option></select></td>
                    <td><input type="number" name="cover_qty[{{$packingStrickerDetail->id}}]" @readonly(true) class="form-control bg-light cover_qty"  id="cover_qty" value="{{$packingStrickerDetail->cover_qty}}"></td>
                    <td><input type="number" name="received_qty[{{$packingStrickerDetail->id}}]" @readonly(true) class="form-control bg-light received_qty"  id="received_qty" value="{{$packingStrickerDetail->total_receive_qty}}" min="0" max="{{($packingStrickerDetail->total_cover_qty)-($packingStrickerDetail->total_receive_qty)}}"></td>
                    <td><input type="number" name="ok_qty[{{$packingStrickerDetail->id}}]" @readonly(true) class="form-control bg-light ok_qty"  id="ok_qty" value="{{$packingStrickerDetail->ok_packed_qty}}" min="0" max="{{($packingStrickerDetail->total_cover_qty)-($packingStrickerDetail->total_receive_qty)}}"></td>
                    <td><input type="number" name="reject_qty[{{$packingStrickerDetail->id}}]" @readonly(true) class="form-control bg-light reject_qty"  id="reject_qty" value="{{$packingStrickerDetail->reject_packed_qty}}" min="0" max="{{($packingStrickerDetail->total_cover_qty)-($packingStrickerDetail->total_receive_qty)}}"></td>
                    <td><input type="number" name="rework_qty[{{$packingStrickerDetail->id}}]" @readonly(true) class="form-control bg-light rework_qty"  id="rework_qty" value="{{$packingStrickerDetail->rework_packed_qty}}" min="0" max="{{($packingStrickerDetail->total_cover_qty)-($packingStrickerDetail->total_receive_qty)}}"></td>
                    <td><input type="number" name="available_quantity[{{$packingStrickerDetail->id}}]" @readonly(true) class="form-control bg-light available_quantity"  id="available_quantity" value="{{($packingStrickerDetail->total_cover_qty)-($packingStrickerDetail->total_receive_qty)}}"></td>
                    <td><select name="status[{{$packingStrickerDetail->id}}]" @if ((($packingStrickerDetail->total_cover_qty)-($packingStrickerDetail->total_receive_qty))==0)
                        @disabled(true)
                    @endif class="form-control status">
                        <option value="0" @if($packingStrickerDetail->status==0) selected @endif >Pending</option>
                        <option value="1" @if($packingStrickerDetail->status==1) selected @endif>OK</option>
                        <option value="2" @if($packingStrickerDetail->status==2) selected @endif>Reject</option>
                        <option value="3" @if($packingStrickerDetail->status==3) selected @endif>Rework</option>
                    </select></td>
                    <td><select name="inspect_by[{{$packingStrickerDetail->id}}]" id="inspect_by_{{$loop->iteration}}" onchage="inspectBy({{ $loop->iteration }})"  class="form-control inspect_by">
                        @foreach ($cleMasterDatas as $cleMasterData)
                        <option value="{{$cleMasterData->id}}">{{$cleMasterData->name}}</option>
                        @endforeach
                    </select>
                </td>
                    <td><textarea name="remarks[{{$packingStrickerDetail->id}}]" class="form-control remarks" id="remarks" cols="30" rows="10">{{$packingStrickerDetail->remarks}}</textarea></td>
                    </tr>
                @empty
                <tr>
                    <td colspan="6" align="center">No Records Found!</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="row mb-3 d-flex justify-content-end clearfix">

    <div class="col-2"><h6>Total Issued Quantity:</h6></div>
    <div class="col-2"><input type="number" name="total_issue_qty" class="form-control total_issue_qty bg-light" id="total_issue_qty" value="{{$total_issue_qty}}"  readonly></div>

    <div class="col-2"><h6>Total Receive Quantity:</h6></div>
    <div class="col-2"><input type="number" name="total_receive_qty" class="form-control total_receive_qty bg-light" id="total_receive_qty" value="{{($total_receive_qty)+($total_reject_qty)+($total_rework_qty)}}"  readonly></div>

    <div class="col-2"><h6>Total Accepted Quantity:</h6></div>
    <div class="col-2"><input type="number" name="total_accepted_qty" class="form-control total_accepted_qty bg-light" id="total_accepted_qty" value="{{$total_receive_qty}}"  readonly></div>

</div>
<div class="row">
    <div class="col-2"><h6>Total Rejected Quantity:</h6></div>
    <div class="col-2"><input type="number" name="total_reject_qty" class="form-control total_reject_qty bg-light" id="total_reject_qty" value="{{$total_reject_qty}}"  readonly></div>

    <div class="col-2"><h6>Total Rework Quantity:</h6></div>
    <div class="col-2"><input type="number" name="total_rework_qty" class="form-control total_rework_qty bg-light" id="total_rework_qty" value="{{$total_rework_qty}}"  readonly></div>
</div>
<script>
// $('#myDiv').hide();
$('.reason_all').hide();
$('.remarks').hide();
$('.inspect_by').hide();
// $('#myDiv').hide();
$('.sub_id').change(function (e) {
    e.preventDefault();
    getRow();
});
function getRow(){

        $("table > tbody  > tr").each(function () {
            if($('.sub_id:checked').length == $('.sub_id').length){
            // var isChecked = $(".sub_id input:checkbox").checked;
            //     if(isChecked){
                    $(".select_all").prop('checked', true);
                    $('#myDiv').addClass('d','flex');
                    $('#myDiv').removeClass('d','none');

                }else{
                    $(".select_all").prop('checked', false);
                    $('#myDiv').addClass('d','none');
                    $('#myDiv').removeClass('d','flex');
                }
        });
    }
// $('.inspect_by').select2();
$(document).ready(function () {
    // $("select.inspect_by").each(function () {
    // // element == this
    //     $(this).select2();
    // // });
    // var msg='table.part';
    // $(msg+' > tbody  > tr').each(function (index, row) {
    //     $(row).find('td.inspect_by').select2();
    // });
    getRow();

});

// $(".inspect_by").select2({
//         placeholder:"Choose The Inspector",
//         allowedClear:true
//     });
$('.status').change(function (e) {
    e.preventDefault();
    var status=$(this).val();
    // alert(status);
    // var qty=$(this).closest("tr").find('.ok_qty').val();
    // alert(qty);
    switch (status) {
            case '0':
            $(this).closest("tr").find('td .ok_qty').attr('readonly', true);
            $(this).closest("tr").find('td .reject_qty').attr('readonly', true);
            $(this).closest("tr").find('td .rework_qty').attr('readonly', true);
            $(this).closest("tr").find('td .remarks').hide();
            $(this).closest("tr").find('td .ok_qty').addClass('bg-light');
            $(this).closest("tr").find('td .reject_qty').addClass('bg-light');
            $(this).closest("tr").find('td .rework_qty').addClass('bg-light');
            $(this).closest("tr").find('td .inspect_by').hide();
            break;
            case '1':
            $(this).closest("tr").find('td .sub_id').prop('checked', true);
            $(this).closest("tr").find('td .ok_qty').attr('readonly', false);
            $(this).closest("tr").find('td .reject_qty').attr('readonly', true);
            $(this).closest("tr").find('td .rework_qty').attr('readonly', true);
            $(this).closest("tr").find('td .remarks').hide();
            $(this).closest("tr").find('td .ok_qty').removeClass('bg-light');
            $(this).closest("tr").find('td .reject_qty').addClass('bg-light');
            $(this).closest("tr").find('td .rework_qty').addClass('bg-light');
            $(this).closest("tr").find('td .inspect_by').show();
            $(this).closest("tr").find('td .inspect_by').select2();
            break;
            case '2':
            $(this).closest("tr").find('td .sub_id').prop('checked', true);
            $(this).closest("tr").find('td .ok_qty').attr('readonly', true);
            $(this).closest("tr").find('td .reject_qty').attr('readonly', false);
            $(this).closest("tr").find('td .rework_qty').attr('readonly', true);
            $(this).closest("tr").find('td .remarks').show();
            $(this).closest("tr").find('td .ok_qty').addClass('bg-light');
            $(this).closest("tr").find('td .reject_qty').removeClass('bg-light');
            $(this).closest("tr").find('td .rework_qty').addClass('bg-light');
            $(this).closest("tr").find('td .inspect_by').show();
            $(this).closest("tr").find('td .inspect_by').select2();
            break;
            case '3':
            $(this).closest("tr").find('td .sub_id').prop('checked', true);
            $(this).closest("tr").find('td .ok_qty').attr('readonly', true);
            $(this).closest("tr").find('td .reject_qty').attr('readonly', true);
            $(this).closest("tr").find('td .rework_qty').attr('readonly', false);
            $(this).closest("tr").find('td .remarks').show();
            $(this).closest("tr").find('td .ok_qty').addClass('bg-light');
            $(this).closest("tr").find('td .reject_qty').addClass('bg-light');
            $(this).closest("tr").find('td .rework_qty').removeClass('bg-light');
            $(this).closest("tr").find('td .inspect_by').show();
            $(this).closest("tr").find('td .inspect_by').select2();
            break;
            default:
            $(this).closest("tr").find('td .ok_qty').attr('readonly', true);
            $(this).closest("tr").find('td .reject_qty').attr('readonly', true);
            $(this).closest("tr").find('td .rework_qty').attr('readonly', true);
            $(this).closest("tr").find('td .remarks').hide();
            $(this).closest("tr").find('td .ok_qty').addClass('bg-light');
            $(this).closest("tr").find('td .reject_qty').addClass('bg-light');
            $(this).closest("tr").find('td .rework_qty').addClass('bg-light');
            $(this).closest("tr").find('td .inspect_by').hide();
        }
});
$('.select_all').on('click', function(e) {
          if($(this).is(':checked',true))
          {
              $(".sub_id").prop('checked', true);
              $('#myDiv').show();
          } else {
              $(".sub_id").prop('checked',false);
              $('#myDiv').hide();
          }
    // getRow();

    });
$('.status_all').change(function (e) {
        e.preventDefault();
        var allStatus=$(this).val();
        // alert(allStatus);
        if (allStatus==0) {
            alert('Please Select Other Status');
            $('.status').html('<option value="0" selected>Pending</option>');
          $('#reason_all').hide();

        }
        if (allStatus==1) {
            $('.status').html('<option value="1" selected>OK</option>');
          $('#reason_all').hide();

        }
        if (allStatus==2) {
            $('.status').html('<option value="2" selected>Reject</option>');
          $('#reason_all').show();
        }
        if (allStatus==3) {
            $('.status').html('<option value="3" selected>Rework</option>');
          $('#reason_all').show();

        }

    });
$('.ok_qty').change(function (e) {
    e.preventDefault();
    var ok_qty=$(this).val();
    // alert(ok_qty);
    if (ok_qty>=0) {
        var available_quantity=$(this).closest("tr").find('td .available_quantity').val();
        var diff=available_quantity-ok_qty;
        var total_accepted_qty=$('.total_accepted_qty').val();
        var total_receive_qty=$('.total_receive_qty').val();
        var total_ok_qty=0;
        var total_qty=0;
        if (diff>=0) {
            // total_ok_qty+=parseFloat(ok_qty);
            // total_qty+=parseFloat(ok_qty);
            // $('.total_accepted_qty').val(total_ok_qty);
            // $('.total_receive_qty').val(total_qty);
            $(this).closest("tr").find('.available_quantity').val(diff);
            updateGrandTotal();

            // alert(total_ok_qty);
        }else {
            alert('Sorry...You Entered More Than Available Stock For This Cover...');
            $(this).closest("tr").find('td .ok_qty').val(0);
            location.reload(true);
        }
    } else {
        alert('Sorry...You Entered less Than 0 ...Try Again..');
        location.reload(true);
    }
});
$('.reject_qty').change(function (e) {
    e.preventDefault();
    var reject_qty=$(this).val();
    // alert(reject_qty);
    if (reject_qty>=0) {
        var available_quantity=$(this).closest("tr").find('td .available_quantity').val();
        var diff=available_quantity-reject_qty;
        var total_reject_qty=$('.total_reject_qty').val();
        var total_receive_qty=$('.total_receive_qty').val();
        var total_ok_qty=0;
        var total_qty=0;
        if (diff>=0) {
            // total_ok_qty+=parseFloat(reject_qty);
            // total_qty+=parseFloat(reject_qty);
            // $('.total_reject_qty').val(total_ok_qty);
            // $('.total_receive_qty').val(total_qty);
            $(this).closest("tr").find('.available_quantity').val(diff);
            updateGrandTotal();

            // alert(total_ok_qty);
        } else {
            alert('Sorry...You Entered More Than Available Stock For This Cover...');
            $(this).closest("tr").find('td .reject_qty').val(0);
            location.reload(true);

        }
    } else {
        alert('Sorry...You Entered less Than 0 ...Try Again..');
        location.reload(true);
    }
});
$('.rework_qty').change(function (e) {
    e.preventDefault();
    var rework_qty=$(this).val();
    // alert(rework_qty);
    if (rework_qty>=0) {
        var available_quantity=$(this).closest("tr").find('td .available_quantity').val();
        var diff=available_quantity-rework_qty;
        var total_reject_qty=$('.total_rework_qty').val();
        var total_receive_qty=$('.total_receive_qty').val();
        var total_ok_qty=0;
        var total_qty=0;
        if (diff>=0) {
            // total_ok_qty+=parseFloat(rework_qty);
            // total_qty+=parseFloat(rework_qty);
            // $('.total_rework_qty').val(total_ok_qty);
            // $('.total_receive_qty').val(total_qty);
            $(this).closest("tr").find('.available_quantity').val(diff);
            updateGrandTotal();

            // alert(total_ok_qty);
        } else {
            alert('Sorry...You Entered More Than Available Stock For This Cover...');
            $(this).closest("tr").find('td .rework_qty').val(0);
            location.reload(true);
        }
    } else {
        alert('Sorry...You Entered less Than 0 ...Try Again..');
        location.reload(true);
    }
});

function updateGrandTotal() {
    var g=$('#total_accepted_qty').val();
    var okTotal=parseFloat(g);
    var rejTotal=0;
    var rewTotal=0;
    var msg='table.part';
    var accepttotal='total_accepted_qty';
    var rejecttotal='total_reject_qty';
    var reworktotal='total_rework_qty';

    $(msg+' > tbody  > tr').each(function (index, row) {
        // element == this

        var t=0;
        var ok_qty = ($(row).find('.ok_qty').val());
        var reject_qty = ($(row).find('.reject_qty').val());
        var rework_qty = ($(row).find('.rework_qty').val());
        okTotal+=parseFloat(ok_qty);
        rejTotal+=parseFloat(reject_qty);
        rewTotal+=parseFloat(rework_qty);
        // var total=okTotal+g;
        $('#'+accepttotal).val(okTotal);
        $('#'+rejecttotal).val(rejTotal);
        $('#'+reworktotal).val(rewTotal);
        t+=parseFloat(okTotal);
        t+=parseFloat(rejTotal);
        t+=parseFloat(rewTotal);
        $('#total_receive_qty').val(t);

        // receive_calc();
    });
}
</script>


