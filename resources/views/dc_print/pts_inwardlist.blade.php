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
            <div class="card-header d-flex" style="justify-content:space-between"><span><b>Paintshop Inward Register List</b>  </span>
                <a class="btn btn-md btn-primary" href="{{route('ptsmultidcreceive')}}"><b><i class='bx bx-plus bx-flashing' style='color:white;' ></i>&nbsp;&nbsp; New Multi Inward</b></a>
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
                                    <th>VSS Unit-1 DC Issue Qty</th>
                                    <th>PTS Stores DC Receive Qty</th>
                                    <th>PTS Production DC Receive Qty</th>
                                    <th>PTS Stores Receive From PTS Production DC Receive Qty</th>
                                    <th>PTS Stores Receive From PTS Production DC Reject Qty</th>
                                    <th>PTS Stores Receive From PTS Production DC Rework Qty</th>
                                    {{-- <th>PTS DC Reject Qty</th>
                                    <th>PTS DC Rework Qty</th> --}}
                                    <th>Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($d12Datas as $d12Data)
                                <tr>
                                    <td>{{$loop->iteration}}</td>
                                    <td>{{date('d-m-Y', strtotime($d12Data->open_date))}}</td>
                                    <td>{{$d12Data->partmaster->child_part_no}}</td>
                                    <td>{{$d12Data->rcmaster->rc_id}}</td>
                                    <td>{{$d12Data->u1_dc_issue_qty}}</td>
                                    <td>{{$d12Data->pts_store_dc_receive_qty}}</td>
                                    {{-- <td>{{$d12Data->pts_store_dc_reject_qty}}</td>
                                    <td>{{$d12Data->pts_store_dc_rework_qty}}</td> --}}
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
