@extends('layouts.app')
@section('content')
<link  rel="stylesheet" href="{{asset('node_modules/boxicons/css/boxicons.min.css')}}" />

<div class="row d-flex justify-content-center">
    <div class="col-12">
        <div class="card">
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
            <div class="card-header d-flex" style="justify-content:space-between"><span><b>RM Requistion List</b>  </span>
                <a class="btn btn-md btn-primary" href="{{route('rmrequistion.create')}}"><b><i class='bx bx-plus bx-flashing' style='color:white;' ></i>&nbsp;&nbsp; New</b></a>
            </div>
            <div class="card-body">
                <div class="table">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-responsive">
                            <thead>
                                <tr>
                                    <th>S.No</th>
                                    <th>Requistion No</th>
                                    <th>Requistion Date</th>
                                    <th>Part No</th>
                                    {{-- <th>Machine ID</th> --}}
                                    <th>RM Description</th>
                                    {{-- <th>Group</th> --}}
                                    <th>Requirement Quantity (KGS)</th>
                                    <th>Requirement Quantity (NOS)</th>
                                    {{-- <th>Issue Quantity (KGS)</th>
                                    <th>Issue Quantity (NOS)</th> --}}
                                    {{-- <th>To Be Return Quantity (KGS)</th>
                                    <th>To Be Return Quantity (NOS)</th>
                                    <th>Return Quantity (KGS)</th>
                                    <th>Return Quantity (NOS)</th> --}}
                                    {{-- <th>Issue Type</th> --}}
                                    {{-- <th>Requested By</th>
                                    <th>Issued By</th> --}}
                                    {{-- <th>Requistion Status</th>
                                    <th>Status</th> --}}
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($rmrequistionDatas as $rmrequistionData)
                                <tr>
                                    <td>{{$loop->iteration}}</td>
                                    <td>{{$rmrequistionData->rc_master->rc_id}}</td>
                                    <td>{{$rmrequistionData->open_date}}</td>
                                    <td>{{$rmrequistionData->partmaster->child_part_no}}</td>
                                    {{-- <td>{{$rmrequistionData->machine_master->machine_name}}</td> --}}
                                    <td>{{$rmrequistionData->rm_master->name}}</td>
                                    {{-- <td>{{$rmrequistionData->group_master->name}}</td> --}}
                                    <td>{{$rmrequistionData->req_kg}}</td>
                                    <td>{{$rmrequistionData->req_qty}}</td>
                                    {{-- <td>{{$rmrequistionData->issue_kg}}</td>
                                    <td>{{$rmrequistionData->issue_qty}}</td> --}}
                                    {{-- <td>{{$rmrequistionData->to_be_return_kg}}</td>
                                    <td>{{$rmrequistionData->to_be_return_qty}}</td>
                                    <td>{{$rmrequistionData->return_kg}}</td>
                                    <td>{{$rmrequistionData->return_qty}}</td> --}}
                                    {{-- <td>@if ($rmrequistionData->req_type_id==1)
                                        New Raw Material Request
                                    @else
                                        Existing Raw Material Return
                                    @endif</td> --}}
                                    {{-- <td>{{$rmrequistionData->request_user->name}}</td>
                                    <td>@if ($rmrequistionData->approve_by!='')
                                        {{$rmrequistionData->approved_user->name}}
                                    @else

                                    @endif</td> --}}
                                    {{-- <td>@if ($rmrequistionData->status==1)
                                        <span class="btn btn-sm btn-success text-white">Coil Issued</span>
                                        @elseif ($rmrequistionData->status==2)
                                        <span class="btn btn-sm btn-success text-white">Reject</span>
                                        @else
                                        <span class="btn btn-sm btn-warning text-white">Pending</span>
                                    @endif</td>
                                    <td>@if ($rmrequistionData->rc_status==0)
                                        <span class="btn btn-sm btn-success text-white">Closed</span>
                                        @else
                                        <span class="btn btn-sm btn-warning text-white">Pending</span>
                                    @endif</td> --}}
                                    <td>@if ($rmrequistionData->status==0)
                                        <a href="{{route('rmrequistion.show',$rmrequistionData->id)}}" class="btn btn-sm btn-primary"><i class='bx bxs-edit'>&nbsp; RM Issue
                                        </i></a>
                                        @endif
                                        @if(($rmrequistionData->status!=0)&&($rmrequistionData->rc_status!=0))
                                        <a class="btn btn-sm btn-info" href="{{route('rmrequistion.edit',$rmrequistionData->id)}}" ><i class='bx bxs-edit'>&nbsp; Edit</i></a>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="16" align="center">No Records Found!</td>
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
