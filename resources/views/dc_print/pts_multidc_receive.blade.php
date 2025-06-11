@extends('layouts.app')
@section('content')
<div class="row d-flex justify-content-center">

    <div class="col-12">
        <div class="col-12" id="res"></div>

        <div class="card">
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
            @if (session()->has('message'))
                <div class="alert alert-danger mt-4">
                {{ session()->get('message')}}
                </div>
            @endif

            <form action="{{route('ptsdcmultidcstore')}}" method="post">
                {{-- <form action="#" method="post"> --}}
                @csrf
                @method('POST')
                <div class="card-header d-flex" style="justify-content:space-between"><span> <b>PTS Multi Delivery challan Receive Entry</b> </span>
                <a class="btn btn-sm btn-primary text-white" href="{{route('ptsmultidchandoverlist')}}">Material Handover</a>
                <a class="btn btn-sm btn-info text-white" href="{{route('ptsinwarddata')}}">PTS Multi Delivery challan Receive List</a>
                </div>
                <div class="card-body">
                    <div class="row d-flex justify-content-center">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="s_no">DC S.No *</label>
                                <select name="s_no" id="s_no" class="form-control s_no  @error('s_no') is-invalid @enderror">
                                    <option value="" selected>Select DC S.No</option>
                                    @foreach ($multiDCDatas as $multiDCData)
                                    <option value="{{$multiDCData->s_no}}">{{'DC-U1-'.$multiDCData->s_no}}</option>
                                    @endforeach
                                </select>
                                @error('s_no')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <input type="submit" value="Receive All" id="receive_all" name="receive_all" class="form-control btn btn-primary text-white mt-4 receive_all  @error('receive_all') is-invalid @enderror" style="display: none">
                                {{-- <button class="btn btn-primary text-white mt-4 downloadpdf" id="downloadpdf" style="display: none">Receive All</button> --}}
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <input type="submit" value="Handover" id="handover" name="handover" class="form-control btn btn-info text-white mt-4 handover  @error('handover') is-invalid @enderror" style="display: none">
                                {{-- <button class="btn btn-info text-white mt-4 handover" id="handover" style="display: none">Handover</button> --}}
                            </div>
                        </div>
                    </div>
                    <br>

                    <div class="table">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-responsive">
                                <thead>
                                    <tr>
                                        <th><input type="checkbox" class="form-check-input select_all" name="select_all" id="select_all"></th>
                                        <th>DC Number</th>
                                        <th>DC Date</th>
                                        <th style="width: 150px">Part No</th>
                                        <th>Current Process</th>
                                        <th>Next Process</th>
                                        <th>Issue Quantity</th>
                                        <th>Received Quantity</th>
                                        <th>To Be Receive Quantity</th>
                                        <th>Balance</th>
                                        <th>UOM</th>
                                        <th>Status</th>
                                        <th>Remarks</th>
                                    </tr>
                                </thead>
                                <tbody id="table_logic">
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
        $("#s_no").select2();
        $(".status").select2({
            placeholder:"Select Status",
            allowedClear:true
        });
        $("#s_no").change(function (e) {
            e.preventDefault();
            var s_no=$(this).val();
            // alert(s_no);
            $.ajax({
                type: "POST",
                url: "{{route('ptsdcmultireceivedata')}}",
                data: {"s_no":s_no},
                success: function (response) {
                    if (response.sno_msg) {
                    $('#table_logic').html(response.table);
                    $('#receive_all').show();
                    } else {
                        $('#res').html(response.table);
                    setTimeout(function() {
                            location.reload(true);
                            }, 6000);
                    }
                    // if (response.dc_print_count>0) {
                    //     $('#handover').show();
                    // } else {
                    //     $('#receive_all').show();
                    // }
                }
            });
        });


        $('.select_all').on('click', function(e) {
            if($(this).is(':checked',true))
            {
                $(".sub_id").prop('checked', true);
            } else {
                $(".sub_id").prop('checked',false);
            }
        });

        $('.status').change(function (event) {
            $(this).closest("tr").find('td .sub_id').prop('checked', true);
            var status=$(this).val();
            alert(status);
        });
        $('.receive_all').on('click', function(e) {
            var allVals = [];
            $(".sub_id:checked").each(function() {
                allVals.push($(this).attr('data-id'));
            });
            if(allVals.length <=0)
            {
                alert("Please select row.");
                return false;
            }  else {
                var check = confirm("Are you sure you want to submit inventory data this row?");
                if(check == true){
                    var join_selected_values = allVals.join(",");
                    // alert(join_selected_values);
                }else{
                    return false;
                }
            }
        });
        $('.handover').on('click', function(e) {
            var allVals = [];
            $(".sub_id:checked").each(function() {
                allVals.push($(this).attr('data-id'));
            });
            if(allVals.length <=0)
            {
                alert("Please select row.");
                return false;
            }  else {
                var check = confirm("Are you sure you want to submit inventory data this row?");
                if(check == true){
                    var join_selected_values = allVals.join(",");
                    // alert(join_selected_values);
                }else{
                    return false;
                }
            }
        });
        $('.downloadpdf2').on('click', function(e) {
            var s_no=$('#s_no').val();
            const sub_id = [];
            const dc_id = [];
            const issue_date = [];
            const part_id = [];
            const issue_qty = [];
            const receive_qty = [];
            const balance_qty = [];
            const uom_id = [];
            const status = [];
            const reason = [];
            $(".sub_id:checked").each(function() {
                if ($(this).is(":checked")) {
                    sub_id.push($(this).val());
                    dc_id.push($(this).closest("tr").find('td .dc_id').val());
                    issue_date.push($(this).closest("tr").find('td .issue_date').val());
                    part_id.push($(this).closest("tr").find('td .part_id').val());
                    issue_qty.push($(this).closest("tr").find('td .issue_qty').val());
                    receive_qty.push($(this).closest("tr").find('td .receive_qty').val());
                    balance_qty.push($(this).closest("tr").find('td .balance_qty').val());
                    uom_id.push($(this).closest("tr").find('td .uom_id').val());
                    status.push($(this).closest("tr").find('td .status').val());
                    reason.push($(this).closest("tr").find('td .reason').val());
                }
            });
            if (sub_id.length <=0) {
                alert("Please select row.");
                return false;
            }else {
                var check = confirm("Are you sure you want to submit inventory data this row?");
                if(check == true){
                    $.ajax({
                        url: this.action,
                        type:"POST",
                        data: {
                            "_token":"{{csrf_token()}}",
                            "s_no" :s_no,
                            "sub_id" :sub_id,
                            "dc_id" :dc_id,
                            "issue_date" :issue_date,
                            "part_id" :part_id,
                            "issue_qty" :issue_qty,
                            "receive_qty" :receive_qty,
                            "balance_qty" :balance_qty,
                            "uom_id" :uom_id,
                            "status" :status,
                            "reason" :reason
                        },
                        cache:false,
                        processData:false,
                        contentType:false,
                        success: function (response) {
                            console.log(response);
                        }
                    });

                }else{
                    return false;
                }
            }
        });
</script>
@endpush
