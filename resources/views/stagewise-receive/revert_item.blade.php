<input type="hidden" name="req_qty2" class="req_qty2" id="req_qty2" value="{{$rework_revert_qty}}">
<span class="me-auto mb-3 mt-3"><button class="btn btn-secondary text-white">STEP 2-RC Details</button></span>
<div class="col-md-12 mt-3">
    <div class="table-responsive">
        <table class='table table-bordered table-striped table-responsive' id="">
            <thead>
            <tr>
                <th><b>RC No</b></th>
                <th><b>Part No</b></th>
                <th><b>Stricker ID</b></th>
                <th><b>Total Cover Qty</b></th>
                <th><b>Rework Qty</b></th>
                <th><b>Revert Qty</b></th>
            </tr>
            </thead>
            <tbody >
                @forelse ($pts_datas as $item)
                <tr class="order">
                    <input type="hidden" name="id[]" id="id"  class="id" value="{{$item->id}}">
                    <td><select name="stricker_rc_id[]" class="form-control bg-light stricker_rc_id" id="stricker_rc_id"><option value="{{$item->rcmaster->id}}">{{$item->rcmaster->rc_id}}</option></select></td>
                    <td><select name="stricker_part_id[]" class="form-control bg-light stricker_part_id" id="stricker_part_id"><option value="{{$item->partmaster->id}}">{{$item->partmaster->child_part_no}}</option></select></td>
                    <td><select name="stricker_id[]" class="form-control bg-light stricker_id" id="stricker_id"><option value="{{$item->id}}">{{$item->cover_order_id}}</option></select></td>
                    <td><input type="number" name="cover_total_qty[]" @readonly(true) class="form-control bg-light cover_total_qty"  id="cover_total_qty" value="{{$item->total_cover_qty}}"></td>
                    <td><input type="number" name="rework_qty[]" readonly  class="form-control bg-light rework_qty" id="rework_qty" min="0" value="{{$item->rework_packed_qty}}" ></td>
                    <td><input type="number" name="filled[]" readonly  class="form-control bg-light filled" id="filled" min="0" ></td>
                    {{-- <td><input type="submit" value="Add"  class="form-control btn btn-primary add" name="add"></td> --}}
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

<script>
        function fillCardsUpToX() {
        let remaining = parseFloat($('#rework_revert_qty').val()) || 0;
            $('.order').each(function () {
                const originalVal = parseFloat($(this).find('.rework_qty').val()) || 0;
                let fill = 0;

                if (remaining >= originalVal) {
                    fill = originalVal;
                    remaining -= originalVal;
                } else if (remaining > 0) {
                    fill = remaining;
                    remaining = 0;
                }
                $(this).find('.filled').val(fill.toFixed(2));
            });
    }
    $(document).ready(function() {
        $('#rework_revert_qty').on('input', fillCardsUpToX);
    // Optionally trigger once on page load
    fillCardsUpToX();
});
</script>
