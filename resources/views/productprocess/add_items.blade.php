<input type="hidden" name="no_of_order" id="no_of_order" value="0">
<div class="table-responsive">
    <table class="table table-bordered table-striped" id="tab_logic">
        <thead>
            <tr>
            <th>Process</th>
            <th>Order</th>
            <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <tr id="saved">
                @foreach ($processdatas as $processdata)
                <tr>
                    <td style="display:none"><input type="hidden" name="id[{{$processdata->id}}]" value="{{$processdata->id}}"></td>
                    <td><select name="operation[{{$processdata->id}}]" class="form-control operation" id="operation">
                        <option value="{{$processdata->id}}">{{$processdata->operation}}</option>
                    </select></td>
                    <td><input type="number" name="process_order_id[{{$processdata->id}}]" min="0" value="0" class="form-control process_order_id" id="process_order_id"></td>
                    <td><div class="form-check form-switch"><input class="form-check-input status"  data-id="{{ $processdata->id }}" name="status[{{$processdata->id}}]" type="checkbox" id="status"></div></td>
                </tr>
                @endforeach
            </tr>
        </tbody>
    </table>
</div>
<div class="col-md-3 mt-3">
    <div class="form-group">
        <label for="machine_id">Machine No *</label>
        <select name="machine_id" id="machine_id" class="form-control @error('machine_id') is-invalid @enderror">
            <option value=""></option>
            @forelse ($machinedatas as $machinedata)
                <option value="{{$machinedata->id}}">{{$machinedata->machine_name}}</option>
            @empty
            @endforelse
        </select>
        @error('machine_id')
        <span class="invalid-feedback" role="alert">
            <strong>{{ $message }}</strong>
        </span>
        @enderror
    </div>
</div>
<div class="col-md-3 mt-3">
    <div class="form-group">
        <label for="foreman_id">Foreman Name *</label>
        <select name="foreman_id" id="foreman_id" class="form-control @error('foreman_id') is-invalid @enderror">
            <option value=""></option>
            @forelse ($foremandatas as $foremandata)
                <option value="{{$foremandata->id}}">{{$foremandata->name}}</option>
            @empty
            @endforelse
        </select>
        @error('foreman_id')
        <span class="invalid-feedback" role="alert">
            <strong>{{ $message }}</strong>
        </span>
        @enderror
    </div>
</div>
<div class="col-md-3 mt-3">
    <div class="form-group">
        <label for="cell_id">Cell No *</label>
        <select name="cell_id" id="cell_id" class="form-control @error('cell_id') is-invalid @enderror">
            <option value=""></option>
        </select>
        @error('cell_id')
        <span class="invalid-feedback" role="alert">
            <strong>{{ $message }}</strong>
        </span>
        @enderror
    </div>
</div>
<div class="col-md-3 mt-3">
    <div class="form-group">
        <label for="group_id">Group *</label>
        <select name="group_id" id="group_id" class="form-control @error('group_id') is-invalid @enderror">
            <option value=""></option>
        </select>
        @error('group_id')
        <span class="invalid-feedback" role="alert">
            <strong>{{ $message }}</strong>
        </span>
        @enderror
    </div>
</div>
<div class="col-md-3 mt-3">
    <div class="form-group">
        <label for="rm_id">RM Desc *</label>
        <select name="rm_id" id="rm_id" class="form-control @error('rm_id') is-invalid @enderror">
            <option value=""></option>
        </select>
        @error('rm_id')
        <span class="invalid-feedback" role="alert">
            <strong>{{ $message }}</strong>
        </span>
        @enderror
    </div>
</div>
<div class="col-md-3 mt-3">
    <div class="form-group">
        <label for="cnc_bom">BOM *</label>
        <input type="number" name="cnc_bom" id="cnc_bom" min="0" step="0.0000001" class="form-control cnc_bom @error('cnc_bom') is-invalid @enderror">
        @error('cnc_bom')
        <span class="invalid-feedback" role="alert">
            <strong>{{ $message }}</strong>
        </span>
        @enderror
    </div>
</div>
<script>
$(document).ready(function () {
    $('.process_order_id').prop('readonly', true);
    $('.process_order_id').addClass('bg-light');
});
$("#machine_id").select2();
$("#rm_id").select2();
$('#machine_id').change(function (e) {
    e.preventDefault();
    var machine_id=$(this).val();
    if (machine_id!="") {
        $.ajax({
            type: "POST",
            url: "{{ route('processpartmachinecheck') }}",
            data:{
                    "_token": "{{ csrf_token() }}",
                    "machine_id":machine_id,
                },
            success: function (response) {
                $('#cell_id').html(response.cell_id);
                $('#group_id').html(response.group_id);
                $('#rm_id').html(response.rm_id);
            }
        });
    }
});
$("#foreman_id").select2();
$('#tab_logic .status').on('change', function(e) {
    e.preventDefault();
    let isChecked = $(this).is(':checked');
    if (isChecked) {
        var old_order=$("#no_of_order").val();
        var current=parseInt(old_order)+1;
            // alert(current);
        $("#no_of_order").val(current);
            // Go to the parent row
        let row = $(this).closest('tr');
        // Find the .action-column in the same row and update it
        row.find('.process_order_id').val(isChecked ? current : '0');
        $('.process_order_id').prop('readonly', true);
        $('.process_order_id').addClass('bg-light');

    }else{
        var old_order=$("#no_of_order").val();
        var current=parseInt(old_order)-1;
        let row = $(this).closest('tr');
        var current_order=row.find('.process_order_id').val();
            // alert(current);
        if (old_order==current_order) {
            $("#no_of_order").val(current);
            row.find('.process_order_id').val(0);
            $('.process_order_id').prop('readonly', true);
            $('.process_order_id').addClass('bg-light');
        }if (old_order>current_order) {
            $("#tab_logic > tbody  > tr").each(function(index, row2) {
                var value = ($(row2).find('.process_order_id').val());
                if (value>current_order) {
                    var newvalue=parseInt(value)-1;
                    $(row2).find('.process_order_id').val(newvalue);
                        $("#no_of_order").val(current);
                        row.find('.process_order_id').val(0);
                        $('.process_order_id').prop('readonly', false);
                        $('.process_order_id').removeClass('bg-light');
                }

            });
        }
    }
});

</script>
