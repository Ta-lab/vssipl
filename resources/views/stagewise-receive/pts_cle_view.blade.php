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
                <a class="btn btn-md btn-primary" href="{{route('ptscleissue.create')}}"><b><i class='bx bx-plus bx-flashing' style='color:white;' ></i>&nbsp;&nbsp; New</b></a>
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
                                    <th>Previous Route Card Number</th>
                                    <th>Issued Qty</th>
                                    <th>Issued By</th>
                                    <th>Issued Date</th>
                                    <th>Action</th>
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
                                    <td>{{$ptsStockData->previous_rcmaster->rc_id}}</td>
                                    <td>{{$ptsStockData->issue_qty}}</td>
                                    <td>{{$ptsStockData->prepareuserdetails->name}}</td>
                                    <td>{{$ptsStockData->created_at}}</td>
                                    <td><a href="{{route('ptsclepartqrcodeissue',$ptsStockData->rc_id)}}" class="btn btn-sm btn-success" target="_blank"><i class='bx bx-printer' style='color:white;'>&nbsp;</i></a></td>
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
