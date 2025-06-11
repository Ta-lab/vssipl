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
            <div class="card-header d-flex" style="justify-content:space-between"><span><b>CNC Production Entry List</b>  </span>
                <a class="btn btn-md btn-info text-white" href="{{route('cncproductionfqc')}}"><b>&nbsp;&nbsp; FQC Pending</b></a>
                <a class="btn btn-md btn-success text-white" href="{{route('cncproductioncoverwiselist')}}"><b>&nbsp;&nbsp; Assign Cover</b></a>
                <a class="btn btn-md btn-primary" href="{{route('productioncoverwiseentry')}}"><b><i class='bx bx-plus bx-flashing' style='color:white;' ></i>&nbsp;&nbsp; New</b></a>
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
                                    <th>Accepted Qty</th>
                                    <th>Rejected Qty</th>
                                    <th>Rework Qty</th>
                                    <th>QC Pending Inspect Qty</th>
                                    <th>Received By</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($cnc_production_datas as $cnc_production_data)
                                <tr>
                                    <td>{{$loop->iteration}}</td>
                                    <td>{{$cnc_production_data->open_date}}</td>
                                    <td>{{$cnc_production_data->partmaster->child_part_no}}</td>
                                    <td>{{$cnc_production_data->rcmaster->rc_id}}</td>
                                    <td>{{$cnc_production_data->cnc_ok_qty}}</td>
                                    <td>{{$cnc_production_data->cnc_rej_qty}}</td>
                                    <td>{{$cnc_production_data->cnc_rework_qty}}</td>
                                    <td>{{$cnc_production_data->qc_pending_qty}}</td>
                                    <td>{{$cnc_production_data->prepareuserdetails->name}}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" align="center">No Records Found!</td>
                                </tr>
                                @endforelse

                                {{-- <tr>
                                    <td colspan="18" align="center">No Records Found!</td>
                                </tr> --}}
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
