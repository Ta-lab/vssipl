<input type="hidden" name="req_qty2" class="req_qty2" id="req_qty2" value="{{$req_qty}}">
<span class="me-auto mb-3 mt-3"><button class="btn btn-secondary text-white">STEP 2-RC Details</button></span>
<div class="col-md-12 mt-3">
    <div class="table-responsive">
        <table class='table table-bordered table-striped table-responsive' id="">
            <thead>
            <tr>
                <th><b>RC No</b></th>
                <th><b>Part No</b></th>
                <th><b>Available Quantity In KGS</b></th>
                <th><b>Available Quantity In Nos</b></th>
                <th><b>Action</b></th>
            </tr>
            </thead>
            <tbody >
                @forelse ($avl_checks as $item)
                <tr class="order">
                    <input type="hidden" name="id[]" id="id"  class="id" value="{{$item->id}}">
                    <td><select name="rc_id[]" class="form-control bg-light rc_id" id="rc_id"><option value="{{$item->rcmaster->id}}">{{$item->rcmaster->rc_id}}</option></select></td>
                    <td><select name="part_id[]" class="form-control bg-light part_id" id="part_id"><option value="{{$item->partmaster->id}}">{{$item->partmaster->child_part_no}}</option></select></td>
                    <td><input type="number" name="issue_avl_kg[]" @readonly(true) class="form-control bg-light issue_avl_kg"  id="issue_avl_kg" value="{{$item->t_avl_qty}}"></td>
                    <td><input type="number" name="issue_avl_qty[]" readonly  class="form-control bg-light issue_avl_qty" id="issue_avl_qty" min="0" max="{{round(($item->t_avl_qty)/($bom))}}" value="{{round(($item->t_avl_qty)/($bom))}}" ></td>
                    <td><input type="number" name="filled[]" readonly  class="form-control bg-light filled" id="filled" min="0" max="{{round(($item->t_avl_qty)/($bom))}}" ></td>
                    {{-- <td><input type="submit" value="Add"  class="form-control btn btn-primary add" name="add"></td> --}}
                    </tr>
                @empty
                <tr>
                    <td colspan="6" align="center">No Records Found!</td>
                </tr>
                @endforelse

                {{-- @if ($count>0)
                <tr class="order">
                    <input type="hidden" name="id[]" id="id"  class="id" value="{{$avl_checks->id}}">
                    <td><select name="rc_id[]" class="form-control bg-light rc_id" id="rc_id"><option value="{{$avl_checks->rcmaster->id}}">{{$avl_checks->rcmaster->rc_id}}</option></select></td>
                    <td><select name="part_id[]" class="form-control bg-light part_id" id="part_id"><option value="{{$avl_checks->partmaster->id}}">{{$avl_checks->partmaster->child_part_no}}</option></select></td>
                    <td><input type="number" name="issue_avl_kg[]" @readonly(true) class="form-control bg-light issue_avl_kg"  id="issue_avl_kg" value="{{$avl_checks->t_avl_qty}}"></td>
                    <td><input type="number" name="issue_avl_qty[]" readonly  class="form-control bg-light issue_avl_qty" id="issue_avl_qty" min="0" max="{{round(($avl_checks->t_avl_qty)/($bom))}}" value="{{round(($avl_checks->t_avl_qty)/($bom))}}" ></td>
                    <td><input type="number" name="filled[]" readonly  class="form-control bg-light filled" id="filled" min="0" max="{{round(($avl_checks->t_avl_qty)/($bom))}}" ></td>
                    <td><input type="submit" value="Add"  class="form-control btn btn-primary add" name="add"></td>
                    </tr>
                @else
                <tr>
                    <td colspan="6" align="center">No Records Found!</td>
                </tr>
                @endif --}}
            </tbody>
        </table>
    </div>
</div>

<script>
        function fillCardsUpToX() {
        let remaining = parseFloat($('#req_qty').val()) || 0;
            $('.order').each(function () {
                const originalVal = parseFloat($(this).find('.issue_avl_qty').val()) || 0;
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
        $('#req_qty').on('input', fillCardsUpToX);
    // Optionally trigger once on page load
    fillCardsUpToX();
});

    $('.add').click(function (e) {
        e.preventDefault();
        var part_id=$('#part_id').val();
        var machine_id=$('#machine_id').val();
        var req_type_id=$('.req_type_id').val();
        var req_qty=$('.req_qty').val();
        var req_kg=$('.req_kg').val();
        var bom=$('.bom').val();
        var rm_id=$('.rm_id').val();
        var avl_kg=$('.avl_kg').val();
        var avl_qty=$('.avl_qty').val();
        var group_id=$('.group_id').val();
        var grn_id=$('.grn_id').val();
        var grn_qc_id=$('.grn_qc_id').val();
        var heat_id=$('.heat_id').val();
        var issue_avl_kg=$('.issue_avl_kg').val();
        var issue_avl_qty=$('.issue_avl_qty').val();
        $('.req_issue_kg').val(issue_avl_kg);
        $('.req_issue_qty').val(issue_avl_qty);
        $.ajax({
            type: "POST",
            url: "{{ route('rmrequistionstore') }}",
            data:$("form").serialize(),
            success: function (response) {
                $('.req_id').val(response.req_rc_id);
                if (response.success) {
                    $('#data').html(response.html);
                    setTimeout(function() {
                        location.reload(true);
                    }, 3000);
                    $('#btn').hide();
                } else {
                    $.ajax({
                        type: "POST",
                        url: "{{ route('rmrequistionfetchdata2') }}",
                        data:$("form").serialize(),
                        success: function (response) {
                            $('#new_mydiv').html(response.html);

                        }
                    });
                }
            }
        });
    });
</script>
