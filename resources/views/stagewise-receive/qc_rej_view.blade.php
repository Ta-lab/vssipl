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
            <div class="card-header d-flex" style="justify-content:space-between"><span><b>QC Rejection Register List</b>  </span>
                <a class="btn btn-md btn-primary" href="{{route('qcrejection')}}"><b><i class='bx bx-plus bx-flashing' style='color:white;' ></i>&nbsp;&nbsp; New</b></a>
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
                                    <th>Area</th>
                                    <th>Operation</th>
                                    <th>Rejected Qty (Nos)</th>
                                    <th>Rejected Weight (KGS)</th>
                                    <th>Reason</th>
                                    <th>Rejected By</th>
                                    <th>Rejected Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($transDataD12Datas as $transDataD12Data)
                                <tr>
                                    <td>{{$loop->iteration}}</td>
                                    <td>{{$transDataD12Data->open_date}}</td>
                                    <td>{{$transDataD12Data->partmaster->child_part_no}}</td>
                                    <td>{{$transDataD12Data->current_rcmaster->rc_id}}</td>
                                    <td>{{$transDataD12Data->area}}</td>
                                    <td>{{$transDataD12Data->operation}}</td>
                                    <td>{{$transDataD12Data->reject_qty}}</td>
                                    <td>{{$transDataD12Data->reject_wt}}</td>
                                    <td>{{$transDataD12Data->remarks}}</td>
                                    <td>{{$transDataD12Data->receiver->name}}</td>
                                    <td>{{$transDataD12Data->created_at}}</td>
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
