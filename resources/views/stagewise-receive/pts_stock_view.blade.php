@extends('layouts.app')
@section('content')
<link  rel="stylesheet" href="{{asset('node_modules/boxicons/css/boxicons.min.css')}}" />

<div class="row d-flex justify-content-center">
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
        <div class="card">
            <div class="card-header d-flex" style="justify-content:space-between"><span><b>PTS DC Stock List</b>  </span>
            </div>
            <div class="card-body">
                <form action="" method="get">
                    @csrf
                    <div class="row mb-3 mt-3">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="part_id"><b>Part Number*</b></label>
                                <select name="part_id" id="part_id" class="form-control @error('part_id') is-invalid @enderror">
                                <option value=""></option>
                                @forelse ($partDatas as $partData)
                                    <option value="{{$partData->partmaster->id}}" {{Request::get('part_id')==$partData->partmaster->id ? 'selected':''}}>{{$partData->partmaster->child_part_no}}</option>
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
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="rc_id"><b>Route Card No*</b></label>
                                <select name="rc_id" id="rc_id" class="form-control @error('rc_id') is-invalid @enderror">
                                <option value=""></option>
                                @forelse ($rcDatas as $rcData)
                                    <option value="{{$rcData->rcmaster->id}}" {{Request::get('rc_id')==$rcData->rcmaster->id ? 'selected':''}}>{{$rcData->rcmaster->rc_id}}</option>
                                @empty
                                @endforelse
                                </select>
                                @error('rc_id')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-3 mt-4">
                            <button type="submit" class="btn btn-sm btn-success text-white" name="submit">Submit</button>
                            <a class="btn btn btn-sm btn-danger text-white" id="reset" href="{{ route('ptsstocklist') }}">clear</a>
                            <a class="btn btn-sm btn-success text-white" id="export_excel_btn1" href="{{ route('pts_export', ['_token'=>csrf_token(),'part_id' => Request::get('part_id'),'rc_id' => Request::get('rc_id')]) }}">Export To EXCEL</a>
                        </div>
                    </div>
                </form>
                <div class="table">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-responsive">
                            <thead>
                                <tr>
                                    <th>S.No</th>
                                    <th>Date</th>
                                    <th>Part Number</th>
                                    <th>Route Card Number</th>
                                    <th>VSS UNIT-1 Issue DC Qty</th>
                                    <th>PTS Store DC Receive Available Qty</th>
                                    <th>PTS Production Available Qty</th>
                                    <th>PTS Store Issue To CLE Available Qty</th>
                                    <th>CLE Issue To PTS Store Available Qty</th>
                                    <th>PTS Store Issue DC Available Qty</th>
                                    {{-- <th>FG DC Receive Available Qty</th> --}}
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($d12Datas as $d12Data)
                                <tr>
                                    <td>{{$loop->iteration}}</td>
                                    <td>{{$d12Data->open_date}}</td>
                                    <td>{{$d12Data->partmaster->child_part_no}}</td>
                                    <td>{{$d12Data->rcmaster->rc_id}}</td>
                                    <td>{{$d12Data->u1_dc_issue_qty}}</td>
                                    <td>{{$d12Data->u1_avl_qty}}</td>
                                    <td>{{$d12Data->pts_store_avl_qty}}</td>
                                    <td>{{$d12Data->pts_production_avl_qty}}</td>
                                    <td>{{$d12Data->cle_avl_qty}}</td>
                                    <td>{{$d12Data->pts_dc_avl_qty}}</td>
                                    {{-- <td>{{$d12Data->fg_dc_avl_qty}}</td> --}}
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="11" align="center">No Records Found!</td>
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

<script src="{{asset('js/boxicons.js')}}"></script>
@endsection
@push('scripts')
    <script>
            $("#rc_id").select2({
                placeholder:"Select The Route Card No",
                allowedClear:true
            });
            $("#part_id").select2({
                placeholder:"Select The Part No",
                allowedClear:true
            });
            $('#raw_material_category_id').change(function (e) {
                e.preventDefault();
                var raw_material_category_id=$('#raw_material_category_id').val();
                var rm_id=$('#rm_id').val();
                if (raw_material_category_id!='') {
                    if (rm_id!='') {

                    }else{
                        $.ajax({
                        type: "GET",
                        url: "{{route('rawfetchdata')}}",
                        data: {'raw_material_category_id':raw_material_category_id},
                        success: function (response) {
                            // console.log(response);
                            $('#rm_id').html(response.html);
                        }
                    });
                    }
                }
            });
            $('#rm_id').change(function (e) {
                e.preventDefault();
                var raw_material_category_id=$('#raw_material_category_id').val();
                var rm_id=$('#rm_id').val();
                // alert(raw_material_category_id);
                if (rm_id!='') {
                    if (raw_material_category_id!='') {

                    }else{
                        $.ajax({
                        type: "GET",
                        url: "{{route('rawcategoryfetchdata')}}",
                        data: {'rm_id':rm_id},
                        success: function (response) {
                            // console.log(response);
                            $('#raw_material_category_id').html(response.html);
                        }
                        });
                    }
                }
            });
    </script>
@endpush
