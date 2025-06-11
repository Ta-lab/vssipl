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
            <div class="card-header d-flex" style="justify-content:space-between"><span><b>PTS Store Material Receive From Pts Production List</b>  </span>
                <a class="btn btn-md btn-primary" href="{{route('ptsproductionreceive.create')}}"><b><i class='bx bx-plus bx-flashing' style='color:white;' ></i>&nbsp;&nbsp; New</b></a>
            </div>
            <div class="card-body">
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
                                    <th>PTS Store DC Receive Qty</th>
                                    <th>PTS Production Receive Qty</th>
                                    <th>PTS Production Issue Qty</th>
                                    <th>PTS Production Reject Qty</th>
                                    <th>PTS Production Rework Qty</th>
                                    <th>Remarks</th>
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
                                    <td>{{$d12Data->pts_store_dc_receive_qty}}</td>
                                    <td>{{$d12Data->pts_production_receive_qty}}</td>
                                    <td>{{$d12Data->pts_production_issue_qty}}</td>
                                    <td>{{$d12Data->pts_production_reject_qty}}</td>
                                    <td>{{$d12Data->pts_production_rework_qty}}</td>
                                    <td>{{$d12Data->remarks}}</td>
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
