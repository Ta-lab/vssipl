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
            <div class="card-header d-flex" style="justify-content:space-between"><span><b>RM Issuance Register List</b>  </span>
                <a class="btn btn-md btn-primary" href="{{route('rmrequistion.index')}}"><b><i class='bx bx-plus bx-flashing' style='color:white;' ></i>&nbsp;&nbsp; New</b></a>
                {{-- <a class="btn btn-md btn-primary" href="{{route('rmissuance.create')}}"><b><i class='bx bx-plus bx-flashing' style='color:white;' ></i>&nbsp;&nbsp; New</b></a> --}}
            </div>
            <div class="card-body">
                <div class="table">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-responsive">
                            <thead>
                                <tr>
                                    <th>S.No</th>
                                    <th>Route Card Number</th>
                                    <th>Route Card Date</th>
                                    <th>Route Card Id</th>
                                    <th>Part Number</th>
                                    <th>GRN Number</th>
                                    <th>RM Desc</th>
                                    <th>Heat Number</th>
                                    <th>Coil Number</th>
                                    <th>Test Certificate Number</th>
                                    <th>Issue Qty</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($d12Datas as $d12Data)
                                <tr>
                                    <td>{{$loop->iteration}}</td>
                                    {{-- <td>{{$d12Data->rc_no}}</td>
                                    <td>{{$d12Data->open_date}}</td>
                                    <td>{{$d12Data->previous_rc_no}}</td>
                                    <td>{{$d12Data->heatnumber}}</td>
                                    <td>{{$d12Data->coil_no}}</td>
                                    <td>{{$d12Data->tc_no}}</td>
                                    <td>{{$d12Data->rm_desc}}</td>
                                    <td>{{$d12Data->rm_issue_qty}}</td> --}}
                                    <td>{{$d12Data->current_rcmaster->rc_id}}</td>
                                    <td>{{$d12Data->open_date}}</td>
                                    <td>{{$d12Data->current_rcmaster->id}}</td>
                                    <td>{{$d12Data->partmaster->child_part_no}}</td>
                                    <td>{{$d12Data->grndata->rcmaster->rc_id}}</td>
                                    <td>{{$d12Data->rm_master->name}}</td>
                                    <td>{{$d12Data->heat_nomaster->heatnumber}}</td>
                                    <td>{{$d12Data->heat_nomaster->coil_no}}</td>
                                    <td>{{$d12Data->heat_nomaster->tc_no}}</td>
                                    <td>{{$d12Data->rm_issue_qty}}</td>
                                    <td>@if ($d12Data->status==1)
                                        <span class="btn btn-sm btn-success text-white">Active</span>
                                        @else
                                        <span class="btn btn-sm btn-danger text-white">Inactive</span>
                                    @endif</td>
                                    <td>
                                        @if ($d12Data->status==1)
                                        <a href="{{route('rmissuance.show',$d12Data->current_rcmaster->id)}}" class="btn btn-sm btn-success" target="_blank"><i class='bx bx-printer' style='color:white;'>&nbsp;</i></a>
                                        @else

                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="18" align="center">No Records Found!</td>
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
