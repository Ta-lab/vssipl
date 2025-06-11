<span class="me-auto mb-3"><button class="btn btn-secondary text-white">STEP 2-RM GRN Details</button></span>
<div class="col-md-12 mt-3">
    <div class="table-responsive">
        <table class='table table-bordered table-striped table-responsive' id="">
            <thead>
            <tr>
                <th><b>GRN No</b></th>
                <th><b>Coil No</b></th>
                <th><b>Heat No</b></th>
                <th><b>Available Quantity In KGS</b></th>
                <th><b>Available Quantity In Nos</b></th>
                <th><b>Action</b></th>
            </tr>
            </thead>
            <tbody >

                {{-- @forelse ($rmcheckDatas as $rmcheckDatas) --}}
                @if ($count>0)
                <tr class="order_{{$rmcheckDatas->id}}">
                    <input type="hidden" name="grn_qc_id" id="grn_qc_id"  class="grn_qc_id" value="{{$rmcheckDatas->id}}">
                    <td><select name="grn_id" class="form-control bg-light grn_id" id="grn_id"><option value="{{$rmcheckDatas->grn_data->id}}">{{$rmcheckDatas->grn_data->rcmaster->rc_id}}</option></select></td>
                    <td><input type="number" name="coil_no" @readonly(true) class="form-control bg-light coil_no"  id="coil_no" value="{{$rmcheckDatas->heat_no_data->coil_no}}"></td>
                    <td><select name="heat_id" class="form-control bg-light heat_id" id="heat_id"><option value="{{$rmcheckDatas->heat_no_data->id}}">{{$rmcheckDatas->heat_no_data->heatnumber}}</option></select></td>
                    <td><input type="number" name="issue_avl_kg" @readonly(true) class="form-control bg-light issue_avl_kg"  id="issue_avl_kg" value="{{$rmcheckDatas->avl_kg}}"></td>
                    <td><input type="number" name="issue_avl_qty" @readonly(true)  class="form-control bg-light issue_avl_qty" id="issue_avl_qty" min="0" max="{{round(($rmcheckDatas->avl_kg)/($bom))}}" value="{{round(($rmcheckDatas->avl_kg)/($bom))}}" ></td>
                    <td><input type="submit" value="Add"  class="form-control btn btn-primary add" name="add"></td>
                    </tr>
                {{-- @empty --}}
                @else

                <tr>
                    <td colspan="6" align="center">No Records Found!</td>
                </tr>
                @endif

                {{-- @endforelse --}}
            </tbody>
        </table>
    </div>
</div>

<script>
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
