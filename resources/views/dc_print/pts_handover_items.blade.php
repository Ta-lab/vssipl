
@foreach ($dc_transactionDatas as $key => $dc_transactionData)
    <tr class="{{$dc_transactionData->dc_print_id}}">
    <td><input type="checkbox" class="form-check-input sub_id" name="sub_id[]" data-id="{{$dc_transactionData->dc_print_id}}" value="{{$dc_transactionData->dc_print_id}}"></td>
    <td style="width:300px"><select name="dc_id[{{$dc_transactionData->dc_print_id}}]" class="form-control bg-light dc_id" readonly id="dc_id"><option value="{{$dc_transactionData->dc_id}}" selected>{{$dc_transactionData->dc_no}}</option></select></td>
    <td><input type="date" name="issue_date[{{$dc_transactionData->dc_print_id}}]"  class="form-control bg-light issue_date" readonly  id="issue_date" value="{{$dc_transactionData->issue_date}}"></td>
    <td><select name="part_id[{{$dc_transactionData->dc_print_id}}]" class="form-control bg-light part_id" readonly id="part_id"><option value="{{$dc_transactionData->part_id}}" selected>{{$dc_transactionData->part_no}}</option></select></td>
    <td><select name="current_process_id[{{$dc_transactionData->dc_print_id}}]" class="form-control bg-light current_process_id" readonly id="current_process_id"><option value="{{$dc_transactionData->process_id}}" selected>{{$dc_transactionData->process_name}}</option></select></td>
    <td><select name="next_process_id[{{$dc_transactionData->dc_print_id}}]" class="form-control bg-light next_process_id" readonly id="next_process_id"><option value="{{$dc_transactionData->next_process_id}}" selected>{{$dc_transactionData->next_process_name}}</option></select></td>
    {{-- <td><input type="number" name="current_process_id[{{$dc_transactionData->dc_print_id}}]"  class="form-control bg-light current_process_id" readonly  id="current_process_id" value="{{$dc_transactionData->process_id}}"></td> --}}
    {{-- <td><input type="number" name="next_process_id[{{$dc_transactionData->dc_print_id}}]"  class="form-control bg-light next_process_id" readonly  id="next_process_id" value="{{$dc_transactionData->next_process_id}}"></td> --}}
    <td><input type="number" name="issue_qty[{{$dc_transactionData->dc_print_id}}]"  class="form-control bg-light issue_qty" readonly  id="issue_qty" value="{{$dc_transactionData->pts_store_dc_receive_qty}}"></td>
    <td><input type="number" name="received_qty[{{$dc_transactionData->dc_print_id}}]"  class="form-control bg-light received_qty" id="received_qty" value="{{$dc_transactionData->pts_production_receive_qty}}" maximum="{{$dc_transactionData->pts_store_dc_receive_qty}}" readonly></td>
    <td><input type="number" name="receive_qty[{{$dc_transactionData->dc_print_id}}]"  class="form-control receive_qty" id="receive_qty" value="{{($dc_transactionData->pts_store_dc_receive_qty)-($dc_transactionData->pts_production_receive_qty)}}" minimum="0" maximum="{{($dc_transactionData->pts_store_dc_receive_qty)-($dc_transactionData->pts_production_receive_qty)}}"></td>
    <td><input type="number" name="balance_qty[{{$dc_transactionData->dc_print_id}}]"  class="form-control balance_qty  bg-light" readonly id="balance_qty" value="{{($dc_transactionData->pts_store_dc_receive_qty)-($dc_transactionData->pts_store_dc_receive_qty)}}"></td>
    <td><select name="uom_id[{{$dc_transactionData->dc_print_id}}]" class="form-control bg-light uom_id"  id="uom_id"><option value="{{$dc_transactionData->uom_id}}" selected>{{$dc_transactionData->uom}}</option></select></td>
    <td><select name="status[{{$dc_transactionData->dc_print_id}}]" class="form-control status select2" id="status"><option value="0">NOT OK</option><option value="1" selected>OK</option></select></td>
    <td><textarea name="reason[{{$dc_transactionData->dc_print_id}}]" class="form-control reason" id="reason" cols="15" rows="5"></textarea></td>
    </tr>
    @endforeach
<script>
$('.reason').hide();
$('.receive_qty').prop("readonly", true);
$('.receive_qty').addClass("bg-light");
$(".status").select2({
    placeholder:"Select Status",
    allowedClear:true
});
$('.status').change(function (event) {
    $(this).closest("tr").find('td .sub_id').prop('checked', true);
    var issue_qty=$(this).closest("tr").find('td .issue_qty').val();
    var status=$(this).val();
    alert(issue_qty);
    if (status==0) {
        $(this).closest("tr").find('td .reason').show();
        $(this).closest("tr").find('td .receive_qty').prop("readonly", false);
        $(this).closest("tr").find('td .receive_qty').removeClass("bg-light");
    }else{
        $(this).closest("tr").find('td .reason').hide();
        $(this).closest("tr").find('td .receive_qty').prop("readonly", true);
        $(this).closest("tr").find('td .receive_qty').addClass("bg-light");
        $(this).closest("tr").find('td .receive_qty').val(issue_qty);
        $(this).closest("tr").find('td .balance_qty').val(0);

    }
});
$('.receive_qty').change(function (event) {
    var receive_qty=$(this).val();

    var issue_qty=$(this).closest("tr").find('td .issue_qty').val();
    var received_qty=$(this).closest("tr").find('td .received_qty').val();
    var balance=issue_qty-received_qty-receive_qty;
    var avl_qty=issue_qty-received_qty;
    if (receive_qty<0) {
        alert('Sorry Your Receive Quantity is wrong Please Check It..');
        location.reload(true);

    }
    if (balance==0) {
        $(this).closest("tr").find('td .receive_qty').val(avl_qty);
        $(this).closest("tr").find('td .balance_qty').val(0);

    }
    if (avl_qty<receive_qty) {
        $(this).closest("tr").find('td .receive_qty').val(receive_qty);
        $(this).closest("tr").find('td .balance_qty').val(balance);
    }
    if (avl_qty>=receive_qty) {
        $(this).closest("tr").find('td .receive_qty').val(receive_qty);
        $(this).closest("tr").find('td .balance_qty').val(balance);

    }
    // alert(balance);
});



</script>
