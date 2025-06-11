@extends('layouts.app')
@section('content')
<div class="row d-flex justify-content-center">

    <div class="col-12">
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
            <div class="card-header d-flex" style="justify-content:space-between"><span> <b>Invoice List</b> </span>
                <a class="btn btn-sm btn-warning text-white" href="{{route('invoicecorrectionmaster.create')}}">Correction Request</a>
                <a class="btn btn-sm btn-success text-white" href="{{route('traceability')}}">Traceability</a>
                <a class="btn btn-sm btn-success text-white" href="{{route('invoiceprint')}}">Print</a>
                <a class="btn btn-sm btn-warning text-white" href="{{route('invoicereprint')}}">Re-Print</a>
                <a class="btn btn-sm btn-primary text-white" href="{{route('invoicedetails.create')}}">New</a>
            </div>
            <div class="card-body">
                <form action="" id="invoice_report_formdata" method="GET">
                    @csrf
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label for=""><b>Date From</b></label>
                        <input type="date" class="form-control" name="date_from" value="{{Request::get('date_from') ?? date('Y-m-d',strtotime("-1 days"))}}" id="date_from" max="{{$current_date}}" >
                    </div>
                    <div class="col-md-3">
                        <label for=""><b>Date To</b></label>
                        <input type="date" class="form-control" name="date_to" value="{{Request::get('date_to') ?? date('Y-m-d')}}" max="{{$current_date}}" id="date_to" >
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="cus_id"><b>Customer Code *</b></label>
                            <select name="cus_id" id="cus_id" class="form-control @error('cus_id') is-invalid @enderror">
                            <option value=""></option>
                            @forelse ($customerMasterDetails as $customerMasterDetail)
                                <option value="{{$customerMasterDetail->id}}" {{Request::get('cus_id')==$customerMasterDetail->id ? 'selected':''}}>{{$customerMasterDetail->cus_code}}</option>
                            @empty
                            @endforelse
                            </select>
                            @error('cus_id')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="part_id"><b>Part Number*</b></label>
                            <select name="part_id" id="part_id" class="form-control @error('part_id') is-invalid @enderror">
                            <option value=""></option>
                            @forelse ($productMasterDetails as $productMasterDetail)
                                <option value="{{$productMasterDetail->id}}" {{Request::get('part_id')==$productMasterDetail->id ? 'selected':''}}>{{$productMasterDetail->part_no}}</option>
                            @empty
                            @endforelse
                            </select>
                            @error('part_id')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-3 mt-4">
                        <button type="submit" class="btn btn-sm btn-success text-white" name="submit">Submit</button>
                        <button type="button" class="btn btn-sm btn-danger text-white" id="reset" name="reset">clear</button>

                        <a class="btn btn-sm btn-success text-white" id="export_excel_btn1" href="{{ route('invoice_report_export', ['_token'=>csrf_token(),'date_from' => Request::get('date_from'),'date_to' => Request::get('date_to'),'cus_id' => Request::get('cus_id'),'part_id' => Request::get('part_id')]) }}">Export To EXCEL</a>
                        {{-- <a class="btn btn-sm btn-success text-white" id="export_excel_btn1" href="{{ route('invoice_report_export'}}">Export To EXCEL</a> --}}

                    </div>
                </div>
                </form>
                <div class="row">
                    <div class="col-md-3">
                        {{-- <a class="btn btn-sm btn-success text-white" id="export_excel_btn1" href="{{route('invoice_report_export1')}}">Export To EXCEL</a> --}}
                    </div>
                </div>
                <div class="table">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-responsive">
                            <thead>
                                <tr>
                                    <th>S.No</th>
                                    <th>Invoice Number</th>
                                    <th>Invoice Date</th>
                                    <th>Customer Code</th>
                                    <th>Customer Name</th>
                                    <th>Customer Billing Address</th>
                                    <th>Customer Billing Address2</th>
                                    <th>Customer Billing City</th>
                                    <th>Customer Billing State</th>
                                    <th>Customer Billing Country</th>
                                    <th>Customer Billing Pincode</th>
                                    <th>Customer Billing GST No</th>
                                    <th>Customer Billing PAN No</th>
                                    <th>Customer Name</th>
                                    <th>Customer Shipping Address</th>
                                    <th>Customer Shipping Address2</th>
                                    <th>Customer Shipping City</th>
                                    <th>Customer Shipping State</th>
                                    <th>Customer Shipping Country</th>
                                    <th>Customer Shipping Pincode</th>
                                    <th>Customer Shipping GST No</th>
                                    <th>Customer Shipping PAN No</th>
                                    <th>Part No</th>
                                    <th>Part Desc</th>
                                    <th>HSN/SAC Code</th>
                                    <th>Customer PO NO</th>
                                    <th>Customer LINE NO</th>
                                    <th>Quantity</th>
                                    <th>UOM</th>
                                    <th>Unit Rate</th>
                                    <th>Packing Charge (%)</th>
                                    <th>CGST (%)</th>
                                    <th>SGST (%)</th>
                                    <th>IGST (%)</th>
                                    <th>TCS (%)</th>
                                    <th>Packing Charge Amount</th>
                                    <th>CGST Amount</th>
                                    <th>SGST Amount</th>
                                    <th>IGST Amount</th>
                                    <th>TCS Amount</th>
                                    <th>Basic Amount</th>
                                    <th>Total Amount(Rs)</th>
                                    <th>Total Weight(KG)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($invoiceDatas as $invoiceData)
                                <tr>

                                    <td>{{$loop->iteration}}</td>
                                    <td>{{$invoiceData->rcmaster->rc_id}}</td>
                                    <td>{{date('d-m-Y', strtotime($invoiceData->invoice_date))}}</td>
                                    <td>{{$invoiceData->customerproductmaster->customermaster->cus_code}}</td>
                                    <td>{{$invoiceData->customerproductmaster->customermaster->cus_name}}</td>
                                    <td>{{$invoiceData->customerproductmaster->customermaster->cus_address}}</td>
                                    <td>{{$invoiceData->customerproductmaster->customermaster->cus_address1}}</td>
                                    <td>{{$invoiceData->customerproductmaster->customermaster->cus_city}}</td>
                                    <td>{{$invoiceData->customerproductmaster->customermaster->cus_state}}</td>
                                    <td>{{$invoiceData->customerproductmaster->customermaster->cus_country}}</td>
                                    <td>{{$invoiceData->customerproductmaster->customermaster->cus_pincode}}</td>
                                    <td>{{$invoiceData->customerproductmaster->customermaster->cus_gst_number}}</td>
                                    <td>{{$invoiceData->customerproductmaster->customermaster->cus_pan_no}}</td>
                                    <td>{{$invoiceData->customerproductmaster->customermaster->delivery_cus_name}}</td>
                                    <td>{{$invoiceData->customerproductmaster->customermaster->delivery_cus_address}}</td>
                                    <td>{{$invoiceData->customerproductmaster->customermaster->delivery_cus_address1}}</td>
                                    <td>{{$invoiceData->customerproductmaster->customermaster->delivery_cus_city}}</td>
                                    <td>{{$invoiceData->customerproductmaster->customermaster->delivery_cus_state}}</td>
                                    <td>{{$invoiceData->customerproductmaster->customermaster->delivery_cus_country}}</td>
                                    <td>{{$invoiceData->customerproductmaster->customermaster->delivery_cus_pincode}}</td>
                                    <td>{{$invoiceData->customerproductmaster->customermaster->delivery_cus_gst_number}}</td>
                                    <td>{{$invoiceData->customerproductmaster->customermaster->delivery_cus_pan_no}}</td>
                                    <td>{{$invoiceData->productmaster->part_no}}</td>
                                    <td>{{$invoiceData->productmaster->part_desc}}</td>
                                    <td>{{$invoiceData->customerproductmaster->part_hsnc}}</td>
                                    <td>{{$invoiceData->customerpomaster->cus_po_no}}</td>
                                    <td>{{$invoiceData->customerpomaster->cus_po_item_no}}</td>
                                    <td>{{$invoiceData->qty}}</td>
                                    <td>{{$invoiceData->uom_masters->name}}</td>
                                    <td>{{($invoiceData->customerpomaster->rate)/($invoiceData->customerpomaster->part_per)}}</td>
                                    <td>{{$invoiceData->packing_charge}}</td>
                                    <td>{{$invoiceData->cgst}}</td>
                                    <td>{{$invoiceData->sgst}}</td>
                                    <td>{{$invoiceData->igst}}</td>
                                    <td>{{$invoiceData->tcs}}</td>
                                    <td>{{$invoiceData->packing_charge_amt}}</td>
                                    <td>{{$invoiceData->cgstamt}}</td>
                                    <td>{{$invoiceData->sgstamt}}</td>
                                    <td>{{$invoiceData->igstamt}}</td>
                                    <td>{{$invoiceData->tcsamt}}</td>
                                    <td>{{$invoiceData->basic_value}}</td>
                                    <td>{{$invoiceData->invtotal}}</td>
                                    <td></td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="43" align="left"><b>No Records Found!</b></td>
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
@endsection
@push('scripts')
<script>

    $("#cus_id").select2({
        placeholder:"Select The Customer Code",
        allowedClear:true
    });
    $("#part_id").select2({
        placeholder:"Select The Part Number",
        allowedClear:true
    });
                //     window.location.href = '/invoice-report/export';
    $('#reset').click(function (e) {
        e.preventDefault();
        // location.reload(true);
        window.location.href = "{{route('invoicedetails.index')}}";
    });
    $('#cus_id').change(function (e) {
        e.preventDefault();
        var cus_id=$(this).val();
        var part_id=$('#part_id').val();
        if (cus_id!='') {
            $.ajax({
                type: "GET",
                url: "{{route('invoicecustomerpartdata')}}",
                data: {'cus_id':cus_id},
                success: function (response) {
                    // console.log(response);
                    $('#part_id').html(response.html);
                }
            });
        }
    });
    $('#part_id').change(function (e) {
        e.preventDefault();
        var part_id=$(this).val();
        var cus_id=$('#cus_id').val();
        if (part_id!='') {
            $.ajax({
                type: "GET",
                url: "{{route('invoicepartcustomerdata')}}",
                data: {'part_id':part_id},
                success: function (response) {
                    // console.log(response);
                    $('#cus_id').html(response.html);
                }
            });
        }
    });
</script>
@endpush
