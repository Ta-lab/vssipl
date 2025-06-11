@extends('layouts.app')
@push('styles')

@endpush
@section('content')
<form action="{{route('invoicestickerprint')}}" target="_blank" id="delivery_challan_formdata" method="POST">
    @csrf
    @method('POST')

<div class="row d-flex justify-content-center">
    <div id="data"></div>
        <div class="row col-md-3"id="res"></div>
        <div class="card">
            <div class="card-header d-flex" style="justify-content:space-between"><span> <b>Print Invoice Box Sticker</b></span><a class="btn btn-sm btn-primary" href="{{route('invoicedetails.index')}}">Invoice List</a>
            </div>
                    <div class="card-body">
                        <div class="col-md-12">
                            <div class="row d-flex justify-content-center">
                                <span class="me-auto mb-3"><button class="btn btn-primary">STEP 1-Invoice Details</button></span>
                                <input type="hidden" name="invoice_rc_id" class="form-control invoice_rc_id" id="invoice_rc_id">
                                <input type="hidden" name="invoice_rc_no" class="form-control invoice_rc_no" id="invoice_rc_no">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="invoice_id">Invoice No. *</label>
                                        <select name="invoice_id" id="invoice_id" class="form-control invoice_id @error('invoice_id') is-invalid @enderror">
                                            <option value="" selected>Select The Invoice No</option>
                                            @forelse ($invoicedatas as $invoicedata)
                                                <option value="{{$invoicedata->id}}">{{$invoicedata->rcmaster->rc_id}}</option>
                                            @empty
                                            @endforelse
                                            </select>
                                        @error('invoice_id')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="invoice_date">Invoice Date *</label>
                                        <input type="date" name="invoice_date" id="invoice_date" max="{{date('Y-m-d')}}" readonly class="form-control invoice_date bg-light @error('invoice_date') is-invalid @enderror" >
                                        @error('invoice_date')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="cus_id">Customer Code *</label>
                                        <input type="text" name="cus_id" id="cus_id" readonly class="form-control cus_id bg-light @error('cus_id') is-invalid @enderror" >
                                        @error('cus_id')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="part_id">Part Number *</label>
                                        <input type="text" name="part_id" id="part_id" readonly class="form-control part_id bg-light @error('part_id') is-invalid @enderror" >
                                        @error('part_id')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="part_desc">Part Description *</label>
                                        <input type="text" name="part_desc" id="part_desc" readonly class="form-control part_desc bg-light @error('part_desc') is-invalid @enderror" >
                                        @error('part_desc')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="invoice_qty">Invoice Quantity *</label>
                                        <input type="number" name="invoice_qty" id="invoice_qty" min="0" readonly class="form-control invoice_qty bg-light @error('invoice_qty') is-invalid @enderror" >
                                        @error('invoice_qty')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="box_qty">Box Quantity *</label>
                                        <input type="number" name="box_qty" id="box_qty" min="0"  class="form-control box_qty @error('box_qty') is-invalid @enderror" >
                                        @error('box_qty')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="no_of_box">No Of Box *</label>
                                        <input type="number" name="no_of_box" id="no_of_box" min="0" readonly class="form-control no_of_box bg-light @error('no_of_box') is-invalid @enderror" >
                                        @error('no_of_box')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row d-flex justify-content-center">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <button class="btn btn-success text-white mt-4" id="print">Print Invoice</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
</form>
@endsection
@push('scripts')
    <script>
        $("#invoice_id").select2();
        $('.invoice_id').change(function (e) {
            e.preventDefault();
            var invoice_id=$('.invoice_id').val();
            $.ajax({
                type: "get",
                url: "{{route('invoicestickerfetch')}}",
                data: {"invoice_id":invoice_id},
                success: function (response) {
                    $('.invoice_rc_id').val(response.invoice_rc_id);
                    $('.invoice_rc_no').val(response.invoice_rc_no);
                    $('.invoice_date').val(response.invoce_date);
                    $('.invoice_qty').val(response.invoce_qty);
                    $('.part_desc').val(response.part_desc);
                    $('.cus_id').val(response.cus_id);
                    $('.part_id').val(response.part_id);
                }
            });
        });
        $('.box_qty').change(function (e) {
            e.preventDefault();
            var box_qty=$('.box_qty').val();
            var invoice_qty=$('.invoice_qty').val();
            if (box_qty!='') {
                var no_of_box=Math.ceil((invoice_qty)/(box_qty));
                $('.no_of_box').val(no_of_box);
            } else {
                $('.no_of_box').val(0);
            }
        });
    </script>
@endpush
