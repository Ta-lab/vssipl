    <span class="btn btn-primary col-3 mb-3">STEP-1 INVOICE DETAILS</span>
        <div class="col-md-12">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-responsive myTable"  id="myTable" border="1">
                    <thead>
                    <tr>
                        <th><b>INVOICE NO</b></th>
                        <th><b>INVOICE DATE</b></th>
                        <th><b>CUSTOMER CODE</b></th>
                        <th><b>CUSTOMER NAME</b></th>
                        <th><b>PART NO</b></th>
                        <th><b>QTY</b></th>
                        <th><b>RATE/PER</b></th>
                        <th><b>BASIC VALUE</b></th>
                        <th><b>ISSUED BY</b></th>
                    </tr>
                    </thead>
                    <tbody  id="table_logic1">
                        <tr>
                            <td>{{$rc_no}}</td>
                            <td>{{date('d-m-Y', strtotime($invoiceDatas->invoice_date))}}</td>
                            <td>{{$invoiceDatas->customerproductmaster->customermaster->cus_code}}</td>
                            <td>{{$invoiceDatas->customerproductmaster->customermaster->cus_name}}</td>
                            <td>{{$invoiceDatas->productmaster->part_no}}</td>
                            <td>{{$invoiceDatas->qty}}</td>
                            <td>{{($invoiceDatas->part_rate)/($invoiceDatas->part_per)}}</td>
                            <td>{{$invoiceDatas->basic_value}}</td>
                            <td>{{$invoiceDatas->prepared_user->name}}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    <span class="btn btn-primary col-3 mb-3">STEP-2 SALES DESPATCH PLAN DETAILS</span>
        <div class="col-md-12">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-responsive myTable"  id="myTable" border="1">
                    <thead>
                    <tr>
                        <th><b>S NO</b></th>
                        <th><b>PLAN NO</b></th>
                        <th><b>PLAN DATE</b></th>
                        <th><b>CUSTOMER CODE</b></th>
                        {{-- <th><b>CUSTOMER NAME</b></th> --}}
                        <th><b>PART NO</b></th>
                        <th><b>RC NO</b></th>
                        <th><b>COVER NO</b></th>
                        <th><b>COVER QTY</b></th>
                        <th><b>ISSUED BY</b></th>
                    </tr>
                    </thead>
                    <tbody  id="table_logic2">
                        @foreach ($desaptchplandatas as $desaptchplandata)
                        <tr>
                            <td>{{$loop->iteration}}</td>
                            <td>{{$desaptchplandata->salesplanmaster->plan_no}}</td>
                            <td>{{date('d-m-Y', strtotime($desaptchplandata->salesplanmaster->open_date))}}</td>
                            <td>{{$desaptchplandata->customermaster->cus_code}}</td>
                            {{-- <td>{{$desaptchplandata->customermaster->cus_name}}</td> --}}
                            <td>{{$desaptchplandata->productmaster->part_no}}</td>
                            <td>{{$desaptchplandata->rcmaster->rc_id}}</td>
                            <td>{{($desaptchplandata->packingstrickerdetails->rcmaster->rc_id)}}-{{($desaptchplandata->packingstrickerdetails->cover_order_id)}}</td>
                            <td>{{$desaptchplandata->invoiced_qty}}</td>
                            <td>{{$desaptchplandata->prepared_user->name}}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        {{-- {{$html2}} --}}
