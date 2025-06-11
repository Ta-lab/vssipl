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
            <div class="card-header d-flex" style="justify-content:space-between"><span><b>PTS Store Material Issue To CLE Inspection List</b>  </span>
                <a class="btn btn-md btn-primary" href="{{route('ptsclereceive.create')}}"><b><i class='bx bx-plus bx-flashing' style='color:white;' ></i>&nbsp;&nbsp; New</b></a>
            </div>
            <div class="card-body">
                <div class="table">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-responsive">
                            <thead>
                                <tr>
                                    <th>S.No</th>
                                    <th>Date</th>
                                    <th>Operation</th>
                                    <th>Part Number</th>
                                    <th>Route Card Number</th>
                                    <th>Cover ID</th>
                                    <th>Received Qty</th>
                                    <th>Accepted Qty</th>
                                    <th>Rejected Qty</th>
                                    <th>Rework Qty</th>
                                    <th>Return Issued Qty</th>
                                    <th>Reason</th>
                                    <th>Inspected By</th>
                                    <th>Inspected Date</th>
                                    <th>Issued By</th>
                                    <th>Issued Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($ptsStockDatas as $ptsStockData)
                                <tr>
                                    <td>{{$loop->iteration}}</td>
                                    <td>{{$ptsStockData->open_date}}</td>
                                    <td>{{$ptsStockData->process}}</td>
                                    <td>{{$ptsStockData->partmaster->child_part_no}}</td>
                                    <td>{{$ptsStockData->rcmaster->rc_id}}</td>
                                    <td>{{$ptsStockData->strickermaster->cover_order_id}}</td>
                                    <td>{{($ptsStockData->receive_qty)+($ptsStockData->reject_qty)+($ptsStockData->rework_qty)+($ptsStockData->return_issue_qty)}}</td>
                                    <td>{{$ptsStockData->receive_qty}}</td>
                                    <td>{{$ptsStockData->reject_qty}}</td>
                                    <td>{{$ptsStockData->rework_qty}}</td>
                                    <td>{{$ptsStockData->return_issue_qty}}</td>
                                    <td>{{$ptsStockData->remarks}}</td>
                                    <td>{{$ptsStockData->strickermaster->inspectedby->name}}</td>
                                    <td>{{$ptsStockData->strickermaster->inspect_at}}</td>
                                    <td>{{$ptsStockData->prepareuserdetails->name}}</td>
                                    <td>{{$ptsStockData->created_at}}</td>
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
