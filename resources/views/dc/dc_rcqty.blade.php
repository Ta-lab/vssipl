<input type="hidden" name="ino_qty" id="ino_qty" value="{{$dc_quantity}}">
<span class="me-auto mb-3"><button class="btn btn-secondary text-white">STEP 3-Route Card Details</button></span>
<div class="col-md-12">
    <div class="table-responsive">
        <table class='table table-bordered table-striped table-responsive part_{{$m_part_id}}' id="part_{{$m_part_id}}">
            <thead>
            <tr>
                <th><b>Part No</b></th>
                <th><b>Order</b></th>
                <th><b>Route Card</b></th>
                <th><b>Route Card Available Quantity</b></th>
                <th><b>Invoice Quantity</b></th>
                <th><b>Balance</b></th>
            </tr>
            </thead>
            <tbody >
                @forelse ($invoiceRcDatas as $invoiceRcData)
                <tr class="order_{{$invoiceRcData->partmaster->no_item_id}}">
                    <td><select name="route_part_id[]" class="form-control bg-light route_part_id" id="route_part_id"><option value="{{$invoiceRcData->partmaster->id}}">{{$invoiceRcData->partmaster->child_part_no}}</option></select></td>
                    <td><input type="number" name="order_no[]" @readonly(true) class="form-control bg-light order_no"  id="order_no" value="{{$invoiceRcData->partmaster->no_item_id}}"></td>
                    <td><select name="route_card_id[]" class="form-control bg-light route_card_id" id="route_card_id"><option value="{{$invoiceRcData->rcmaster->id}}">{{$invoiceRcData->rcmaster->rc_id}}</option></select></td>
                    <td><input type="number" name="available_quantity[]" @readonly(true) class="form-control bg-light available_quantity"  id="available_quantity" value="{{$invoiceRcData->avl_qty}}"></td>
                    {{-- @if ($loop->iteration==1)
                            @if (($invoiceRcData->avl_qty)>($dc_quantity))
                                {{$issued_qty=$dc_quantity}}
                                {{$balance_qty=$invoiceRcData->avl_qty-$issued_qty}}
                            @elseif(($invoiceRcData->avl_qty)<=($dc_quantity))
                                {{$issued_qty=$invoiceRcData->avl_qty}}
                                {{$balance_qty=$invoiceRcData->avl_qty-$issued_qty}}
                            @endif
                    @else
                    {{$issued_qty=0}}
                    {{$balance_qty=0}} @endif --}}
                    {{-- <td><input type="number" name="issue_quantity[]"  class="form-control bg-light issue_quantity" id="issue_quantity" min="0" max="{{$invoiceRcData->avl_qty}}" value="{{$issued_qty}}" ></td> --}}
                    <td><input type="number" name="issue_quantity[]"  class="form-control bg-light issue_quantity" id="issue_quantity" min="0" max="{{$invoiceRcData->avl_qty}}" value="{{$invoiceRcData->avl_qty}}" ></td>
                    {{-- <td><input type="number" name="balance[]"  class="form-control bg-light balance" id="balance" min="0" max="{{$invoiceRcData->avl_qty}}" value="{{$balance_qty}}"></td> --}}
                    <td><input type="number" name="balance[]"  class="form-control bg-light balance" id="balance" min="0" max="{{$invoiceRcData->avl_qty}}" value="0"></td>
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
<div class="row mb-3 d-flex justify-content-end clearfix" style="background-color: aliceblue">
    {{-- @for ($i = 0; $i < 1; $i++)
        @if ($i==0)
            @if (($invoiceRcDatas[$i]->avl_qty)>($dc_quantity))
                @php
                    $grand_total=$dc_quantity;
                    $total_diff=($dc_quantity)-$grand_total;
                @endphp
            @elseif(($invoiceRcDatas[$i]->avl_qty)<=($dc_quantity))
                @php
                    $grand_total=$invoiceRcDatas[$i]->avl_qty;
                    $total_diff=($dc_quantity)-$grand_total;
                @endphp
            @endif
        @else
            0
        @endif --}}

    <div class="col-2"><h6>Grand Total:</h6></div>
    <div class="col-2"><input type="number" name="grand_total[{{$m_part_id}}]" class="form-control bg-light" id="grand_total_{{$m_part_id}}" value="{{$dc_quantity}}"  readonly>
    {{-- <div class="col-2"><input type="number" name="grand_total[{{$m_part_id}}]" class="form-control bg-light grand_total_{{$m_part_id}}" id="grand_total_{{$m_part_id}}" value="{{$grand_total}}"  readonly> --}}

    </div>

    <div class="col-2"><h6>Balance:</h6></div>
    {{-- <div class="col-2"><input type="number" name="total_diff[{{$m_part_id}}]" class="form-control bg-light" id="total_diff_{{$m_part_id}}" value="{{$total_diff}}"  readonly></div> --}}
    <div class="col-2"><input type="number" name="total_diff[{{$m_part_id}}]" class="form-control bg-light" id="total_diff_{{$m_part_id}}" value="0"  readonly></div>
    {{-- @endfor --}}

</div>



<script>
$(document).ready(function () {
    var route_part_id=$('.route_part_id').val();
            // var invoice_quantity = 4;
            var total = $('#ino_qty').val();
    // alert('invoice_quantity');
    var msg='table.part_'+route_part_id;
    var gtotal='grand_total_'+route_part_id;
    var tdiff='total_diff_'+route_part_id;
    // alert(gtotal);
    // alert(tdiff);
//     // $('table.part_'+route_part_id+' > tbody  > tr').each(function(index, row) {
//     $(msg+' > tbody  > tr').not(':first').each(function(index, row) {
//         $(row).find('.issue_quantity').val('');
//         var qty = $(row).find('.available_quantity').val();
//         // var qty = $(row).find('.available_quantity').next();
//         // alert($(row).find('.issue_quantity').val());
//         if(total>=qty && total>0){
//             total-=qty;
//             $(row).find('.issue_quantity').val(qty);
//             // console.log('method 1');
//         }else if(qty>total){
//             var grandTotal =$(gtotal).val();
//             var total_diff =$(tdiff).val();
//             var total_qty = ($(row).find('.issue_quantity').val(total));
//             grandTotal+=parseFloat(total_qty);
//             $(gtotal).val(grandTotal);
//             var diff=total-grandTotal;
//             if (diff==0) {
//                 ($(row).find('.issue_quantity').val(0))
//             }
//             // alert(diff);
//             // $(row).find('.issue_quantity').val(diff);
//         }
//         var balance = qty-($(row).find('.issue_quantity').val());
//             $(row).find('.balance').val(balance);
//     });
    $(msg+' > tbody  > tr').not(':first').each(function(index, row) {
            var grandTotal =$('#'+gtotal).val();
            var totalDiff =$('#'+tdiff).val();
            var qty = $(row).find('.available_quantity').val();
            // alert(totalDiff);

            if (totalDiff==0) {
                // var issued_qty = $(row).find('.issue_quantity').val();
                // var balance = qty-issued_qty;
               //  $(row).find('.balance').val(balance);
                // $(gtotal).val(total);
                    $(row).find('.issue_quantity').prop('readonly', true);
                    $(row).find('.issue_quantity').addClass('bg-light');
                    $(row).find('.balance').prop('readonly', true);
                    $(row).find('.balance').addClass('bg-light');
            }else{
                if ($(msg+' > tbody  > tr:first')) {
                    // console.log($(msg+' > tbody  > tr:first').find('.available_quantity').val());
                    $(msg+' > tbody  > tr:first').find('.issue_quantity').prop('readonly', false);
                    $(msg+' > tbody  > tr:first').find('.issue_quantity').removeClass('bg-light');
                    $(msg+' > tbody  > tr:first').find('.balance').prop('readonly', true);
                    $(msg+' > tbody  > tr:first').find('.balance').addClass('bg-light');
                }
                if ($(msg+' > tbody  > tr:nth-child(2)')) {
                    $(msg+' > tbody  > tr:nth-child(2)').find('.issue_quantity').prop('readonly', false);
                    $(msg+' > tbody  > tr:nth-child(2)').find('.issue_quantity').removeClass('bg-light');
                    $(msg+' > tbody  > tr:nth-child(2)').find('.balance').prop('readonly', true);
                    $(msg+' > tbody  > tr:nth-child(2)').find('.balance').addClass('bg-light');
                } if($(msg+' > tbody  > tr').not(':first',':nth-child(2)')) {
                    $(msg+' > tbody  > tr').not(':first',':nth-child(2)').find('.issue_quantity').prop('readonly', false);
                    $(msg+' > tbody  > tr').not(':first',':nth-child(2)').find('.issue_quantity').removeClass('bg-light');
                    $(msg+' > tbody  > tr').not(':first',':nth-child(2)').find('.balance').prop('readonly', true);
                    $(msg+' > tbody  > tr').not(':first',':nth-child(2)').find('.balance').addClass('bg-light');
                }

            }
    });
    $(".issue_quantity").change(updateGrandTotal);

    function updateGrandTotal()
    {
        var grandTotal = 0;
        var invoiceQty=$('#ino_qty').val();
        // alert(totalDiff);

            var row_index1 = $(this).closest("tr").index();
            var col_index1 = $(this).index();
            // console.log(row_index1);

        var lg=$(msg+'> tbody  > tr').length;
        $(msg+'> tbody  > tr').each(function(index, row) {
            // console.log(index);
        // console.log($(row).find('.issue_quantity').val());
            var qty = ($(row).find('.issue_quantity').val());
            var avl = ($(row).find('.available_quantity').val());
            var diff=avl-qty;
            var mainBalance=$(tdiff).val();

            // console.log(diff);
            if (diff<0) {
                alert('Sorry Route Card Available is more than Issued Quantity');
                $(row).find('.balance').val(0);
                $(row).find('.issue_quantity').val(0);
                grandTotal+=parseFloat(0);
                var totalDiff=invoiceQty-grandTotal;
                $('#'+gtotal).val(grandTotal);
                $('#'+tdiff).val(totalDiff);
            }if (qty<0) {
                alert('Sorry The Issued Quantity is not proper');
                $(row).find('.balance').val(0);
                $(row).find('.issue_quantity').val(0);
                grandTotal+=parseFloat(0);
                var totalDiff=invoiceQty-grandTotal;
                $('#'+gtotal).val(grandTotal);
                $('#'+tdiff).val(totalDiff);
            }if (mainBalance<0) {
                alert('Sorry The Issued Quantity is more than Invoice Total Quantity');
                $(row).find('.balance').val(0);
                $(row).find('.issue_quantity').val(0);
                grandTotal+=parseFloat(0);
                var totalDiff=invoiceQty-grandTotal;
                $('#'+gtotal).val(grandTotal);
                $('#'+tdiff).val(totalDiff);
            }
            else {
                $(row).find('.balance').val(diff);
                grandTotal+=parseFloat(qty);
                var totalDiff=invoiceQty-grandTotal;
                $('#'+gtotal).val(grandTotal);
                $('#'+tdiff).val(totalDiff);
                var ind_count=row_index1+1;
                    console.log(ind_count);

                    var count1=ind_count+1;
                    var previous_avl=$(msg+' > tbody  > tr:nth-child('+ind_count+')').find('.available_quantity').val();
                    var previous_iss=$(msg+' > tbody  > tr:nth-child('+ind_count+')').find('.issue_quantity').val();
                // console.log(previous_avl);
                // console.log(totalDiff);
                // if (totalDiff>=previous_avl) {
                //     $(msg+' > tbody  > tr:nth-child('+ind_count+')').find('.issue_quantity').val(previous_avl);
                //     $(msg+' > tbody  > tr:nth-child('+ind_count+')').find('.balance').val(0);
                // }if (mainBalance<previous_avl){
                //     $(msg+' > tbody  > tr:nth-child('+ind_count+')').find('.issue_quantity').val(mainBalance);
                //     $(msg+' > tbody  > tr:nth-child('+ind_count+')').find('.balance').val(0);
                // }
                if ((totalDiff>0)) {

                    for (let y = ind_count; y <= lg; y++) {
                        // const element = array[y];
                        // console.log(y);
                        if (y==lg) {
                            if ($(msg+' > tbody  > tr:nth-child('+y+')')) {
                                $(msg+' > tbody  > tr:nth-child('+y+')').find('.issue_quantity').prop('readonly', false);
                                $(msg+' > tbody  > tr:nth-child('+y+')').find('.issue_quantity').removeClass('bg-light');
                                $(msg+' > tbody  > tr:nth-child('+y+')').find('.balance').prop('readonly', true);
                                $(msg+' > tbody  > tr:nth-child('+y+')').find('.balance').addClass('bg-light');

                            }
                        }else{
                            var bal=$(msg+' > tbody  > tr:nth-child('+ind_count+')').find('.balance').val();
                    // console.log(count1);
                            if ($(msg+' > tbody  > tr:nth-child('+count1+')')) {
                                $(msg+' > tbody  > tr:nth-child('+count1+')').find('.issue_quantity').prop('readonly', false);
                                $(msg+' > tbody  > tr:nth-child('+count1+')').find('.issue_quantity').removeClass('bg-light');
                                $(msg+' > tbody  > tr:nth-child('+y+')').find('.balance').prop('readonly', true);
                                $(msg+' > tbody  > tr:nth-child('+y+')').find('.balance').addClass('bg-light');
                            }if(($(msg+' > tbody  > tr').not(':first',':nth-child('+count1+')'))){
                                $(row).find('.issue_quantity').prop('readonly', true);
                                $(row).find('.issue_quantity').addClass('bg-light');
                                $(msg+' > tbody  > tr:nth-child('+y+')').find('.balance').prop('readonly', true);
                                $(msg+' > tbody  > tr:nth-child('+y+')').find('.balance').addClass('bg-light');
                            }
                        }
                    }

                }
            }
            // console.log(grandTotal);
            // console.log(totalDiff);
        });
     }
    $("#tab_logic").on('change', 'input', updateGrandTotal);


});

</script>
