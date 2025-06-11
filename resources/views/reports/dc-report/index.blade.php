@extends('layouts.app')
@section('content')
<div class="row d-flex justify-content-center">
    <div class="col-12">
        <div class="card">
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
                @if (session()->has('message'))
                <div class="alert alert-danger mt-4">
                {{ session()->get('message')}}
                </div>
                @endif
                <div class="card-header">
                    <span><b>DC Report</b></span>
                </div>
                <div class="card-body">
                    <div class="table">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-responsive">
                                <thead>
                                    <tr>
                                        <th>S.No</th>
                                        <th>DC Number</th>
                                        <th>DC Date</th>
                                        <th>Code</th>
                                        <th>Supplier Name</th>
                                        <th>Part No</th>
                                        <th>Quantity</th>
                                        <th>UOM</th>
                                        <th>Unit Rate</th>
                                        <th>Total Value</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($dcReports as $report)
                                    <tr>
                                        <td>{{$loop->iteration}}</td>
                                        <td>{{$report->rcmaster->rc_id ?? 'N/A'}}</td>
                                        <td>{{date('d-m-Y', strtotime($report->issue_date))}}</td>
                                        <td>{{$report->dcmaster->supplier->supplier_code ?? 'N/A'}}</td>
                                        <td>{{$report->dcmaster->supplier->name ?? 'N/A'}}</td>
                                        <td>{{$report->dcmaster->invoicepart->part_no ?? 'N/A'}}</td>
                                        <td>{{$report->issue_qty}}</td>
                                        <td>{{$report->uom->name ?? 'N/A'}}</td>
                                        <td>{{$report->unit_rate}}</td>
                                        <td>{{$report->basic_rate}}</td>
                                        <td>
                                            @if ($report->status == 0)
                                                <span class="btn btn-sm text-white btn-danger">OPEN</span>
                                            @else
                                                <span class="btn btn-sm text-white btn-success">CLOSE</span>
                                            @endif
                                        </td>
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
</div>
@endsection 