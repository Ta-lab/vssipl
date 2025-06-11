@extends('layouts.app')
@section('content')
<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>

<form action="{{route('po.store')}}" id="po_formdata" method="POST">
    @csrf
    @method('POST')

<div class="row d-flex justify-content-center">
    <div id="data"></div>
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex" style="justify-content:space-between"><span> <b>Create Purchase Order</b></span><a class="btn btn-sm btn-primary" href="{{route('po.index')}}">Supplier Products List</a>
            </div>
            <div class="card-body">
                        <div class="row d-flex justify-content-center">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="ponumber">PO Number *</label>
                                    <input type="text" name="ponumber" id="ponumber" value="{{$new_ponumber}}" readonly class="form-control bg-light @error('ponumber') is-invalid @enderror" >
                                    @error('ponumber')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="podate">PO Date *</label>
                                    <input type="date" name="podate" id="podate" value="{{$current_date}}" readonly class="form-control bg-light @error('podate') is-invalid @enderror" >
                                    @error('podate')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="supplier_id">Supplier Code *</label>
                                    <select name="supplier_id" id="supplier_id" class="form-control @error('supplier_id') is-invalid @enderror">
                                    <option value=""></option>
                                    @forelse ($suppliers as $code)
                                        <option value="{{$code->id}}">{{$code->supplier_code}}</option>
                                    @empty
                                    @endforelse
                                    </select>
                                    @error('supplier_id')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">Name *</label>
                                    <input type="text" name="name" id="name" readonly class="form-control bg-light @error('name') is-invalid @enderror" >
                                    @error('name')
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
                                    <label for="contact_person">Contact Person*</label>
                                    <input type="text" name="contact_person" id="contact_person"  readonly class="form-control bg-light @error('contact_person') is-invalid @enderror" >
                                    @error('contact_person')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">Contact No*</label>
                                    <input type="text" name="contact_number" id="contact_number" class="form-control @error('contact_number') is-invalid @enderror" >
                                    @error('contact_number')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">GST Number *</label>
                                    <input type="text" name="gst_number" maxlength="15" minlength="15" id="gst_number" class="form-control @error('gst_number') is-invalid @enderror" >
                                    @error('gst_number')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">Address *</label>
                                    <textarea name="address" id="address" class="form-control @error('address') is-invalid @enderror" id="" cols="20" rows="3"></textarea>
                                    @error('address')
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
                                    <label for="trans_mode">Mode Of Transaction*</label>
                                    <select name="trans_mode" id="trans_mode" class="form-control @error('trans_mode') is-invalid @enderror">
                                        <option value="BY ROAD">BY ROAD</option>
                                        <option value="BY COURIER">BY COURIER</option>
                                    </select>
                                    @error('trans_mode')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="cgst">CGST (%) *</label>
                                    <input type="number" name="cgst" id="cgst" class="form-control @error('cgst') is-invalid @enderror" >
                                    @error('cgst')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="sgst">SGST (%) *</label>
                                    <input type="number" name="sgst" id="sgst" min="0" class="form-control @error('sgst') is-invalid @enderror" >
                                    @error('sgst')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="igst">IGST (%) *</label>
                                    <input type="number" name="igst" id="igst" class="form-control @error('igst') is-invalid @enderror" >
                                    @error('igst')
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
                                    <label for="packing_charges">Packing Charge (%) *</label>
                                    <input type="number" name="packing_charges" id="packing_charges" class="form-control @error('packing_charges') is-invalid @enderror" >
                                    @error('packing_charges')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="currency_id">Mode Of Currency*</label>
                                    <select name="currency_id" id="currency_id" class="form-control @error('currency_id') is-invalid @enderror">
                                        <option value="0">INR</option>
                                        <option value="1">USD</option>
                                    </select>
                                    @error('currency_id')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="purchasetype">Purchase Type *</label>
                                    <input type="text" name="purchasetype" id="purchasetype" class="form-control @error('purchasetype') is-invalid @enderror" >
                                    @error('purchasetype')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="payment_terms">Payment Terms *</label>
                                    <input type="text" name="payment_terms" id="payment_terms" class="form-control @error('payment_terms') is-invalid @enderror" >
                                    @error('payment_terms')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="indentno">Indent No *</label>
                                    <input type="text" name="indentno" id="indentno" class="form-control @error('indentno') is-invalid @enderror" >
                                    @error('indentno')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="indentdate">Indent Date *</label>
                                    <input type="date" name="indentdate" id="indentdate" class="form-control @error('indentdate') is-invalid @enderror" >
                                    @error('indentdate')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="indentno">Indent No *</label>
                                    <input type="text" name="indentno" id="indentno" class="form-control @error('indentno') is-invalid @enderror" >
                                    @error('indentno')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="quotdt">Quote Date *</label>
                                    <input type="date" name="quotdt" id="quotdt" class="form-control @error('quotdt') is-invalid @enderror" >
                                    @error('quotdt')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="remarks1">Remarks 1 *</label>
                                    <textarea name="remarks1" id="remarks1" class="form-control @error('remarks1') is-invalid @enderror" id="" cols="20" rows="3"></textarea>
                                    @error('remarks1')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="remarks2">Remarks 2 *</label>
                                    <textarea name="remarks2" id="remarks2" class="form-control @error('remarks2') is-invalid @enderror" id="" cols="20" rows="3"></textarea>
                                    @error('remarks2')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="remarks3">Remarks 3 *</label>
                                    <textarea name="remarks3" id="remarks3" class="form-control @error('remarks3') is-invalid @enderror" id="" cols="20" rows="3"></textarea>
                                    @error('remarks3')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="remarks4">Remarks 4 *</label>
                                    <textarea name="remarks4" id="remarks4" class="form-control @error('remarks4') is-invalid @enderror" id="" cols="20" rows="3"></textarea>
                                    @error('remarks4')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                    <div class="row clearfix mt-3">
                        <div class="col-md-12">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-responsive" id="tab_logic">
                                    <thead>
                                    <tr>
                                        <th>S.No</th>
                                        <th>Material Category</th>
                                        <th>Material Description</th>
                                        <th>Material HSN Code</th>
                                        <th>Due Date</th>
                                        <th>UOM</th>
                                        <th>Rate</th>
                                        <th>Quantity</th>
                                        <th>Total Cost</th>
                                        <th><button type="button" name="add" class="btn btn-success btn-border-radius-sm btn-xs add"><i class='bx bx-plus-circle' style="color: white" ></i></button></th>
                                    </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                        <div class="row d-flex justify-content-center">
                            <div class="col-md-2 mt-4">
                                <button class="btn btn-sm btn-primary" id="submit_btn" type="submit">Submit</button>
                            </div>
                        </div>
            </div>
        </div>
    </div>
</div>
</form>

<script src="{{asset('js/jquery.min.js')}}"></script>
<script src="https://unpkg.com/boxicons@2.1.4/dist/boxicons.js"></script>
<script src="{{asset('js/select2.min.js')}}"></script>

<script>
$(document).ready(function(){
    $("#raw_material_category_id").select2();

    $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
    $("#supplier_id").change(function(e){
        e.preventDefault();
        var supplier_id=$(this).val();
        $.ajax({
            url: "{{ route('posuppliersdata') }}?id=" + $(this).val(),
            method: 'GET',
            cache:false,
            processData:false,
            contentType:false,
            success : function(result){
                // console.log(result);
                if (result.count > 0) {
                    $('#name').val(result.name);
                    $('#name'). attr('readonly','true');
                    $('#contact_person').val(result.contact_person);
                    $('#contact_number').val(result.contact_number);
                    $('#gst_number').val(result.gst_number);
                    $('#address').val(result.address);
                    $('#trans_mode').html(result.trans_mode);
                    $('#cgst').val(result.cgst);
                    $('#sgst').val(result.sgst);
                    $('#igst').val(result.igst);
                    $('#packing_charges').val(result.packing_charges);
                    $('#currency_id').html(result.currency_id);
                    $('#remarks').val(result.remarks);
                    $('#raw_material_category_id').html(result.category);

                }else{
                    // location.reload(true);
                    // $('#supplier_code').val('abc');
                    $('#name').val('');
                    $('#contact_number').val('');
                    $('#gst_number').val('');
                    $('#address').val('');
                    $('#trans_mode').html(result.trans_mode);
                    $('#cgst').val('');
                    $('#sgst').val('');
                    $('#igst').val('');
                    $('#packing_charges').val('');
                    $('#currency_id').html(result.currency_id);
                    $('#remarks').val('');
                    $("#submit_btn").attr('disabled',false);

                }
            }
        });
	});
    $("#raw_material_category_id").change(function(e){
        // e.preventDefault();
        var supplier_id=$("#supplier_id").val();
        var raw_material_category_id=$(this).val();
        // var sub_category_id = $(this).data('supplier_product_id');
        // alert(sub_category_id);
        alert(raw_material_category_id);
        $.ajax({
            url: "{{ route('posuppliersrmdata') }}",
            method: 'POST',
            data:{
                "_token": "{{ csrf_token() }}",
                "raw_material_category_id":raw_material_category_id,
                "supplier_id":supplier_id
            },
            success : function(result){
                // console.log(result);
                $("#supplier_product_id").html(result);
                // console.log(result.data);
                // console.log(result.code);
            }
        });
	});
    $("#supplier_product_id").change(function(e){
        // e.preventDefault();
        var supplier_id=$("#supplier_id").val();
        var raw_material_category_id=$("#raw_material_category_id").val();
        var supplier_product_id=$(this).val();
        // alert(raw_material_category_id);
        // alert(supplier_id);

        $.ajax({
            url: "{{ route('posuppliersproductdata') }}",
            method: 'POST',
            data:{
                "_token": "{{ csrf_token() }}",
                "raw_material_category_id":raw_material_category_id,
                "supplier_id":supplier_id,
                "supplier_product_id":supplier_product_id,
            },
            success : function(result){
                // console.log(result);
                $("#uom_id").html(result.html);
                $("#products_hsnc").val(result.products_hsnc);
                $("#products_rate").val(result.products_rate);
                // console.log(result.data);
                // console.log(result.code);
            }
        });
	});
    $('#qty').blur(function (e) {
        e.preventDefault();
        var products_rate=$("#products_rate").val();
        var qty=$("#qty").val();
        if(products_rate!=''&&qty!=''){
            var total_cost=products_rate * qty;
            $("#rate").val(total_cost);
        }
    });

    var i=1;
    $("#add_row").click(function(){b=i-1;
      	$('#addr'+i).html($('#addr'+b).html()).find('td:first-child').html(i+1);
      	$('#tab_logic').append('<tr id="addr'+(i+1)+'"></tr>');
      	i++;
  	});
      $("#delete_row").click(function(){
    	if(i>1){
		$("#addr"+(i-1)).html('');
		i--;
		}
	});
    var count = 0;
    $(document).on('click', '.add', function(){
        count++;
        var html = '';
        html += '<tr>';
        html += '<td>'+count+'</td>';
        html += '<td><select name="raw_material_category_id[]" id="raw_material_category_id" class="form-control"></select></td>';
        html += '<td><select name="supplier_product_id[]" id="supplier_product_id" class="form-control"></select></td>';
        html += '<td><input type="text" class="form-control" id="products_hsnc" name="products_hsnc[]"></td>';
        html += '<td><input type="date" class="form-control" id="duedate" name="duedate[]"></td>';
        html += '<td><select name="uom_id[]" id="uom_id" class="form-control bg-white"></td>';
        html += '<td><input type="number" id="products_rate" class="form-control" name="products_rate[]" readonly></td>';
        html += '<td><input type="text" id="qty" class="form-control" name="qty[]"></td>';
        html += '<td><input type="number" id="rate" class="form-control" name="rate[]" readonly></td>';
        html += '<td><button type="button" name="remove" class="btn btn-danger btn-xs remove"><i class="bx bx-minus-circle"></i></button></td>';
        $('tbody').append(html);
      });
      $(document).on('click', '.remove', function(){
        $(this).closest('tr').remove();
      });
});
</script>
@endsection
