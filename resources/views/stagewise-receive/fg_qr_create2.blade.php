@extends('layouts.app')
@push('styles')

@endpush
@section('content')

<div class="row d-flex justify-content-center">
    <div id="data"></div>
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
        <div class="row col-md-3"id="res"></div>

        <div class="card">
            <div class="card-header d-flex" style="justify-content:space-between"><span> <b>Finished Goods Part Receive Register</b></span><a class="btn btn-sm btn-primary" href="{{route('fgreceive')}}">Finished Goods Part Receive List</a>
            </div>
            <div class="card-body">
                <form action="{{route('fgqrreceive.store')}}" id="sf_receive_formdata" method="POST">
                    @csrf
                    @method('POST')
                    <div class="row">
                        <div class="col-md-3 mx-auto">
                            <main>
                                <div id="reader"></div>
                                <div id="result"></div>
                            </main>
                        </div>
                    </div>
                    <div class="row d-flex justify-content-center">
                        <input type="hidden" name="current_rc_id" id="current_rc_id">
                        <input type="hidden" name="previous_product_process_id" id="previous_product_process_id">
                        <input type="hidden" name="next_productprocess_id" id="next_productprocess_id">
                        <input type="hidden" name="fqc_count" id="fqc_count">
                        <input type="hidden" name="qrcodes_count" id="qrcodes_count" value="{{$qrCodes_count}}">
                        <input type="hidden" name="qr_rc_id" id="qr_rc_id">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="rc_no">Route Card Number *</label>
                                <select name="rc_no" class="form-control @error('rc_no') is-invalid @enderror" @if ($qrCodes_count!=0)
                                @readonly(true)
                            @else
                                @readonly(false)
                            @endif  id="rc_no">
                                    <option value="" selected></option>
                                    @foreach ($d11Datas as $d11Data)
                                        <option value="{{$d11Data->rcmaster->id}}" >{{$d11Data->rcmaster->rc_id}}</option>
                                    @endforeach
                                </select>
                                @error('rc_no')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="rc_date">Route Card Date *</label>
                                <input type="date" name="rc_date" id="rc_date" value="{{$current_date}}" readonly class="form-control bg-light @error('rc_date') is-invalid @enderror" >
                                @error('rc_date')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="part_id">Part Number *</label>
                                <select name="part_id" id="part_id" class="form-control bg-light @error('part_id') is-invalid @enderror" @readonly(true)>
                                </select>
                                @error('part_id')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="next_process_id">Stocking Point *</label>
                                <select name="next_process_id" id="next_process_id" class="form-control bg-light @error('next_process_id') is-invalid @enderror" @readonly(true)>
                                </select>
                                @error('next_process_id')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row d-flex justify-content-center">
                        {{-- <div class="col-md-3">
                            <div class="form-group">
                                <label for="avl_kg">Available Stock (In KG) *</label>
                                <input type="number" name="avl_kg" id="avl_kg"  class="form-control bg-light @error('avl_kg') is-invalid @enderror" @readonly(true)>
                                @error('avl_kg')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div> --}}
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="avl_qty">Available Stock (In Numbers) *</label>
                                <input type="number" name="avl_qty" id="avl_qty"  class="form-control bg-light @error('avl_qty') is-invalid @enderror" @readonly(true)>
                                @error('avl_qty')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>
                        {{-- <div class="col-md-3">
                            <div class="form-group">
                                <label for="receive_kg">Receive Quantity IN KG *</label>
                                <input type="number" name="receive_kg" id="receive_kg" required min="0" step="0.0000000000000001" class="form-control @error('receive_kg') is-invalid @enderror">
                                @error('receive_kg')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div> --}}
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="cover_order_id">Cover Id*</label>
                                <input type="number" name="cover_order_id" id="cover_order_id" required min="0" class="form-control bg-light @error('cover_order_id') is-invalid @enderror">
                                @error('cover_order_id')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="cover_qty">Cover Quantity*</label>
                                <input type="number" name="cover_qty" id="cover_qty" required min="0" class="form-control bg-light @error('cover_qty') is-invalid @enderror">
                                @error('cover_qty')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="receive_qty">Receive Quantity In Numbers*</label>
                                <input type="number" name="receive_qty" id="receive_qty" required min="0" class="form-control bg-light @error('receive_qty') is-invalid @enderror">
                                @error('receive_qty')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- <div class="row d-flex justify-content-center mt-3">
                        <div class="col-md-3">
                            <p><b>Route Card Close :</b></p>
                              <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="rc_close" id="inlineRadio1" value="yes">
                                <label class="form-check-label" for="inlineRadio1">Yes</label>
                              </div>
                              <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="rc_close" id="inlineRadio2" checked value="no">
                                <label class="form-check-label" for="inlineRadio2">No</label>
                              </div>
                        </div>
                    </div> --}}

                    <div class="row d-flex justify-content-center ">
                        <div class="col-md-2 mt-4">
                            <input type="submit" class="btn btn-success  text-white align-center" id="btn" value="Save">
                            <input class="btn btn-danger text-white" id="reset" type="reset" value="Reset">
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const scanner = new Html5QrcodeScanner('reader', {
qrbox: {
width: 250,
height: 250,
},
fps: 20,
});
scanner.render(success, error);
function success(result) {
// document.getElementById('result').innerHTML = `
// <h2>Success!</h2>
// `;
let rc=result.split("-");
var stricker_id=rc[8];
if (stricker_id!='') {
            $.ajax({
                type: "POST",
                url: "{{ route('fgqrpartfetchdata') }}",
                data:{
                    "_token": "{{ csrf_token()}}",
                    "stricker_id":stricker_id,
                },
                success: function (response) {
                    // console.log(response);
                    if(response.message){
                        if (response.success) {
                            $('#part_id').html(response.part_data);
                                $('#rc_no').html(response.rc_data);
                                // $('#avl_kg').val(response.avl_kg);
                                $('#avl_qty').val(response.avl_qty);
                                $('#receive_qty').val(response.avl_qty);
                                $('#receive_qty').attr('max', response.avl_qty);
                                $('#receive_qty').attr('min', 0);
                                $('#bom').val(response.bom);
                                $('#cover_order_id').val(response.cover_order_id);
                                $('#cover_qty').val(response.cover_qty);
                                // $('#previous_product_process_id').val(response.product_process_id);
                                // $('#next_process_id').val(response.next_process_id);
                                $('#next_process_id').html(response.operation);
                                // $('#rc_no').html(response.rc_data);
                                $('#scan_rc_id').val(response.stricker_data);
                                $('#qr_rc_id').val(response.stricker_data);
                                $('#current_rc_id').val(response.current_rc_id);
                        } else {
                        //    alert('Sorry This Cover Not Properly Handover From Prevovious Stage...');
                           var msg='This Cover No is '+response.cover_rc+' Already Received by FG Team';
                           alert(msg);
                        }

                    }else{
                        alert('Sorry This Cover Not Properly Handover From Prevovious Stage...');

                        // var msg='This Cover No is '+response.cover_rc+' Already Received by FG Team';
                        // alert(msg);
                    }
                }
            });
        }
// alert(result);
scanner.clear();
document.getElementById('reader').remove();
}
function error(err) {
    // alert(err);
console.error(err);
}
  </script>

@endpush

