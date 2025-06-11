@extends('layouts.app')
@section('content')
<div class="row d-flex justify-content-center">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex" style="justify-content:space-between"><span> Master Product Process Creation</span><a class="btn btn-sm btn-primary" href="{{route('productprocessmaster.index')}}">Product Process List</a>
            </div>
            <div class="card-body">
                    <form action="{{route('productprocessmaster.store')}}" id="part_formdata" method="POST">
                    @csrf
                    @method('POST')
                    <div class="row col-md-3"id="res"></div>
                        <div class="row d-flex justify-content-center">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="part_id">Part No *</label>
                                    <select name="part_id" id="part_id" class="form-control @error('part_id') is-invalid @enderror">
                                        <option value=""></option>
                                        @forelse ($productmasters as $productmaster)
                                            <option value="{{$productmaster->id}}">{{$productmaster->child_part_no}}</option>
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
                        </div>
                        <div class="row processtable mt-3"></div>
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


<script src="{{asset('vendors/simplebar/js/simplebar.min.js')}}"></script>
<script src="{{asset('vendors/@coreui/coreui/js/coreui.bundle.min.js')}}"></script>
<script src="{{asset('js/jquery.min.js')}}" ></script>
<script src="{{asset('js/select2.min.js')}}"></script>
<script>
    $(document).ready(function(){
        $("#part_id").select2();

        $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });

        $('#part_id').change(function (e) {
            e.preventDefault();
            var part_id=$(this).val();
            if (part_id!="") {
                $.ajax({
                    type: "POST",
                    url: "{{ route('processpartcheck') }}",
                    data:{
                        "_token": "{{ csrf_token() }}",
                        "part_id":part_id,
                    },
                    success: function (response) {
                        $('.processtable').html(response.table);
                    }
                });
            }
        });

        $('.status').change(function (e) {
            e.preventDefault();
            alert('ok');
        });

        $("#reset").click(function (e) {
            e.preventDefault();
            location.reload(true);
        });
    });
    </script>
    @endsection

