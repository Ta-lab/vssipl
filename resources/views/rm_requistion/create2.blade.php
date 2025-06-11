@extends('layouts.app')
@push('styles')

@endpush
@section('content')
<form action="{{route('rmrequistionstore')}}" id="rmrequistion_formdata" method="POST">
    @csrf
    @method('POST')

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
            <div class="card-header d-flex" style="justify-content:space-between"><span> <b>Create RM Requistion</b></span><a class="btn btn-sm btn-primary" href="{{route('rmrequistion.index')}}">RM Requistion List</a>
            </div>
            <div class="card-body">
                <div class="row d-flex justify-content-center">
                    <input type="hidden" name="bom" class="bom" id="bom">
                    <input type="hidden" name="req_id" class="req_id" id="req_id">
                    <input type="hidden" name="req_issue_kg" class="req_issue_kg" id="req_issue_kg">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="open_date">Date *</label>
                            <input type="date" name="open_date" id="open_date" value="{{$current_date}}" readonly class="form-control open_date bg-light @error('open_date') is-invalid @enderror" >
                            @error('open_date')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="part_id">Part Number *</label>
                            <select name="part_id" id="part_id" class="form-control part_id bg-light @error('part_id') is-invalid @enderror">
                                <option value="" selected></option>
                                @foreach ($partDatas as $partData)
                                <option value="{{($partData->id)}}">{{($partData->child_part_no)}}</option>
                                @endforeach
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
                            <label for="rm_id">RM Description *</label>
                            <select name="rm_id" class="form-control bg-light rm_id  @error('rm_id') is-invalid @enderror" id="rm_id">
                                <option value="" selected></option>
                            </select>
                            @error('rm_id')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="machine_id">Machine No *</label>
                            <select name="machine_id" id="machine_id" class="form-control machine_id bg-light @error('machine_id') is-invalid @enderror">
                            </select>
                            @error('machine_id')
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
                            <label for="group_id">Group *</label>
                            <select name="group_id" id="group_id" class="form-control group_id bg-light @error('group_id') is-invalid @enderror">
                            </select>
                            @error('group_id')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="avl_qty">Available Quantity (In Nos) *</label>
                            <input type="number" name="avl_qty" id="avl_qty"  class="form-control bg-light avl_qty @error('avl_qty') is-invalid @enderror" min="0" step="0.001" @readonly(true)>
                            @error('avl_qty')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="avl_kg">Available Quantity (In KGS) *</label>
                            <input type="number" name="avl_kg" id="avl_kg"  class="form-control bg-light avl_kg @error('avl_kg') is-invalid @enderror" min="0" step="0.001" @readonly(true)>
                            @error('avl_kg')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="req_qty">Requirement Quantity (In NOS) *</label>
                            <input type="number" name="req_qty" id="req_qty"  class="form-control req_qty @error('req_qty') is-invalid @enderror" min="0" step="1">
                            @error('req_qty')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="row" id="dc_details">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="req_kg">Requirement Quantity (In KGS) *</label>
                            <input type="number" name="req_kg" id="req_kg"  class="form-control bg-light req_kg @error('req_kg') is-invalid @enderror" min="0" step="0.001" @readonly(true)>
                            @error('req_kg')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="req_issue_qty">Request Issue Quantity (In NOS) *</label>
                            <input type="number" name="req_issue_qty" id="req_issue_qty"  class="form-control bg-light req_issue_qty @error('req_issue_qty') is-invalid @enderror" min="0" step="0.001" @readonly(true)>
                            @error('req_issue_qty')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="req_type_id">Requistion Type *</label>
                            <select name="req_type_id" id="req_type_id" class="form-control req_type_id @error('req_type_id') is-invalid @enderror">
                                <option value="">Select The Type</option>
                                <option value="1" selected>New RM Receive</option>
                                <option value="0">Existing RM Return</option>
                            </select>
                            @error('req_type_id')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="remarks">Remarks *</label>
                            <textarea name="remarks" id="remarks" cols="20" rows="5" class="form-control remarks @error('remarks') is-invalid @enderror"></textarea>
                            @error('remarks')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                </div>
                <div id="new_mydiv"></div>
                <div class="row d-flex justify-content-center ">
                    <div class="col-md-2 mt-4">
                        <input type="submit" class="btn btn-success  text-white align-center" id="btn" value="Save" style="display: none;">
                        <input class="btn btn-danger text-white" id="reset" type="reset" value="Reset">
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
$(document).ready(function(){
    $('input').on('input', function() {
        var inputId = $(this).attr('id');
        $('#' + inputId + '-error').remove();
    });
    $('#inlineRadio1').hide();
    $('#btn').hide();

});

    $("#part_id").select2({
        placeholder:"Select The Part Number",
        allowedClear:true
    });
    $("#req_type_id").select2({
        placeholder:"Select The Requistion Type",
        allowedClear:true
    });

    $("#reset").click(function (e) {
        e.preventDefault();
        location.reload(true);
    });

    $('#part_id').change(function (e) {
        e.preventDefault();
        var part_id=$(this).val();
        // alert(part_id);
        if (part_id!='') {
            $.ajax({
                type: "POST",
                url: "{{ route('partrmrequistionfetchdata') }}",
                data:{
                    "_token": "{{ csrf_token()}}",
                    "part_id":part_id,
                },
                success: function (response) {
                    // console.log(response);
                    // if (response.avl_msg) {
                    //     $('#rm_id').html(response.rm_id);
                    //         $('#group_id').html(response.group_id);
                    //         $('#machine_id').html(response.machine_id);
                    //         $('.req_qty').val(0);
                    //         $('.req_qty').attr('max', response.avl_qty);
                    //         $('.req_qty').attr('min', 0);
                    //         $('.avl_qty').val(response.avl_qty);
                    //         $('.avl_kg').val(response.avl_kg);
                    //         $('.req_kg').val(0);
                    //         $('.req_kg').attr('max', response.avl_kg);
                    //         $('.req_kg').attr('min', 0);
                    //         $('.bom').val(response.bom);
                    // } else {
                    //     $('#data').html(response.html);
                    //     setTimeout(function() {
                    //         location.reload(true);
                    //     }, 3000);
                    //     $('#btn').hide();
                    // }
                    if (response.rm_msg) {
                        if (response.avl_msg) {
                            if (response.process_msg) {
                                if (response.machine_msg) {
                                    if (response.returnrm_msg) {
                                        $('#rm_id').html(response.rm_id);
                                        $('#group_id').html(response.group_id);
                                        $('#machine_id').html(response.machine_id);
                                        $('.req_qty').val(0);
                                        $('.req_qty').attr('max', response.avl_qty);
                                        $('.req_qty').attr('min', 0);
                                        $('.avl_qty').val(response.avl_qty);
                                        $('.avl_kg').val(response.avl_kg);
                                        $('.req_kg').val(0);
                                        $('.req_kg').attr('max', response.avl_kg);
                                        $('.req_kg').attr('min', 0);
                                        $('.bom').val(response.bom);
                                    } else {
                                        $('#data').html(response.html);
                                        setTimeout(function() {
                                            location.reload(true);
                                        }, 3000);
                                        $('#btn').hide();
                                    }
                                } else {
                                    $('#data').html(response.html);
                                    setTimeout(function() {
                                        location.reload(true);
                                    }, 3000);
                                    $('#btn').hide();
                                }
                            } else {
                                $('#data').html(response.html);
                                setTimeout(function() {
                                    location.reload(true);
                                }, 3000);
                                $('#btn').hide();
                            }
                        } else {
                            $('#data').html(response.html);
                            setTimeout(function() {
                                location.reload(true);
                            }, 3000);
                            $('#btn').hide();
                        }
                    } else {
                        $('#data').html(response.html);
                        setTimeout(function() {
                            location.reload(true);
                        }, 3000);
                        $('#btn').hide();
                    }
                }
            });
        }
    });

    // $('.req_type_id').change(function (e) {
    //     e.preventDefault();
    //     var req_type_id=$(this).val();
    //     var req_kg=$('.req_qty').val();
    //     var part_id=$('.part_id').val();
    //     var rm_id=$('.rm_id').val();
    //     var group_id=$('.group_id').val();
    //     var machine_id=$('.machine_id').val();
    //     if (req_type_id!=''&& req_kg!='' && part_id!='' && rm_id!=''&& group_id!=''&& machine_id!='') {
    //         $.ajax({
    //             type: "POST",
    //             url: "{{ route('rmrequistioncheckdata') }}",
    //             data:{
    //                 "_token": "{{ csrf_token()}}",
    //                 "req_type_id":req_type_id,
    //                 "req_kg":req_kg,
    //                 "part_id":part_id,
    //                 "rm_id":rm_id,
    //                 "group_id":group_id,
    //                 "machine_id":machine_id,
    //             },
    //             success: function (response) {
    //                 console.log(response);
    //                 if (response.req_type_id==1) {
    //                     if (response.rc_msg) {
    //                         if (response.rm_msg) {
    //                             if (response.rm_avl_msg) {
    //                                 $('#btn').show();
    //                             } else {
    //                                 $('#data').html(response.html);
    //                                 setTimeout(function() {
    //                                     location.reload(true);
    //                                 }, 3000);
    //                                 $('#btn').hide();
    //                             }
    //                         } else {
    //                             $('#data').html(response.html);
    //                             setTimeout(function() {
    //                                     location.reload(true);
    //                                 }, 3000);
    //                                 $('#btn').hide();
    //                         }
    //                     }else{
    //                         $('#data').html(response.html);
    //                         setTimeout(function() {
    //                                 location.reload(true);
    //                                 }, 3000);
    //                                 $('#btn').hide();
    //                     }
    //                 }else{
    //                     $('#btn').show();
    //                 }
    //             }
    //         });
    //     } else {
    //         // alert('Not ok...');
    //         $('#btn').hide();
    //     }
    // });

    $('.req_qty').change(function (e) {
        e.preventDefault();
        var req_type_id=$('.req_type_id').val();
        var req_qty=$(this).val();
        var bom=$('.bom').val();
        var part_id=$('.part_id').val();
        var rm_id=$('.rm_id').val();
        var avl_kg=$('.avl_kg').val();
        var avl_qty=$('.avl_qty').val();
        var group_id=$('.group_id').val();
        var machine_id=$('.machine_id').val();
        // alert(bom);
        // alert(req_qty);
        var req=(bom*req_qty);
        // alert(req);
        var req_kg=req.toFixed(2);
        // alert(req_kg);
        if (avl_kg>=req) {
            $('.req_kg').val(req_kg);
            if (req_type_id!=''&& req_kg!='' && part_id!='' && rm_id!=''&& group_id!=''&& machine_id!='') {
                $.ajax({
                    type: "POST",
                    url: "{{ route('rmrequistionfetchdata') }}",
                    data:{
                        "_token": "{{ csrf_token()}}",
                        "req_type_id":req_type_id,
                        "req_kg":req_kg,
                        "part_id":part_id,
                        "rm_id":rm_id,
                        "group_id":group_id,
                        "machine_id":machine_id,
                        "bom":bom,
                    },
                    success: function (response) {
                        console.log(response);
                        if (response.count==1) {
                            $('#new_mydiv').html(response.html);
                        }else{
                            $('#data').html(response.html);
                            setTimeout(function() {
                            location.reload(true);
                            }, 3000);
                            $('#btn').hide();
                        }
                    }
                });
            } else {
                $('#btn').hide();
                // alert('Not ok...');
            }
        } else {

        }
        // alert(req_kg);

    });

    $('.rm_id').change(function (e) {
        e.preventDefault();
        var req_type_id=$('.req_type_id').val();
        var req_kg=$('.req_qty').val();
        var part_id=$('.part_id').val();
        var rm_id=$(this).val();
        var group_id=$('.group_id').val();
        var machine_id=$('.machine_id').val();
        if (req_type_id!=''&& req_kg!='' && part_id!='' && rm_id!=''&& group_id!=''&& machine_id!='') {
            $.ajax({
                type: "POST",
                url: "{{ route('rmrequistioncheckdata') }}",
                data:{
                    "_token": "{{ csrf_token()}}",
                    "req_type_id":req_type_id,
                    "req_kg":req_kg,
                    "part_id":part_id,
                    "rm_id":rm_id,
                    "group_id":group_id,
                    "machine_id":machine_id,
                },
                success: function (response) {
                    console.log(response);
                    if (response.req_type_id==1) {
                        if (response.rc_msg) {
                            if (response.rm_msg) {
                                if (response.rm_avl_msg) {
                                    $('#btn').show();
                                } else {
                                    $('#data').html(response.html);
                                    setTimeout(function() {
                                        location.reload(true);
                                    }, 3000);
                                    $('#btn').hide();
                                }
                            } else {
                                $('#data').html(response.html);
                                setTimeout(function() {
                                        location.reload(true);
                                    }, 3000);
                                    $('#btn').hide();
                            }
                        }else{
                            $('#data').html(response.html);
                            setTimeout(function() {
                                    location.reload(true);
                                    }, 3000);
                                    $('#btn').hide();
                        }
                    }else{
                        $('#btn').show();
                    }
                }
            });
        } else {
            // alert('Not ok...');
            $('#btn').hide();

        }
    });
</script>
@endpush

