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
                    <!-- Filter Form -->
                    <form action="{{ route('dc-report.index') }}" method="GET" class="mb-4">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="supplier_id">Supplier</label>
                                    <select name="supplier_id" id="supplier_id" class="form-control select2">
                                        <option value="">Select Supplier</option>
                                        @foreach($suppliers as $supplier)
                                            <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                                {{ $supplier->supplier_code }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="rm_id">Raw Material</label>
                                    <select name="rm_id" id="rm_id" class="form-control select2">
                                        <option value="">Select Raw Material</option>
                                        @foreach($rawMaterials as $rm)
                                            <option value="{{ $rm->id }}" {{ request('rm_id') == $rm->id ? 'selected' : '' }}>
                                                {{ $rm->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="part_no">Part Number</label>
                                    <input type="text" name="part_no" id="part_no" class="form-control" value="{{ request('part_no') }}" placeholder="Enter Part Number">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="from_date">From Date</label>
                                    <input type="date" name="from_date" id="from_date" class="form-control" value="{{ request('from_date', date('Y-m-d', strtotime('-1 day'))) }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="to_date">To Date</label>
                                    <input type="date" name="to_date" id="to_date" class="form-control" value="{{ request('to_date', date('Y-m-d')) }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <div>
                                        <button type="submit" class="btn btn-primary">Filter</button>
                                        <a href="{{ route('dc-report.index') }}" class="btn btn-secondary">Reset</a>
                                        <a href="{{ route('dc-report.export', request()->query()) }}" class="btn btn-success">Export to Excel</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>

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
                                        <th>Issued Qty</th>
                                        <th>Received Qty</th>
                                        <th>Available Qty</th>
                                        <th>UOM</th>
                                        <th>Unit Rate</th>
                                        <th>Age (Days)</th>
                                        <th>Total Value</th>                                        
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
                                        <td>{{$report->receive_qty ?? 0}}</td>
                                        <td>{{$report->issue_qty - ($report->receive_qty ?? 0)}}</td>
                                        <td>{{$report->uom->name ?? 'N/A'}}</td>
                                        <td>{{$report->unit_rate}}</td>
                                        <td>{{$report->issue_date ? (new DateTime($report->issue_date))->diff(new DateTime())->days : 'N/A'}}</td>
                                        <td>{{number_format(($report->unit_rate * 0.7) * ($report->issue_qty - ($report->receive_qty ?? 0)), 2)}}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="13" align="center">No Records Found!</td>
                                    </tr>
                                    @endforelse
                                    @if(count($dcReports) > 0)
                                    <tr class="font-weight-bold">
                                        <td colspan="6" align="right">Total:</td>
                                        <td>{{$dcReports->sum('issue_qty')}}</td>
                                        <td>{{$dcReports->sum('receive_qty')}}</td>
                                        <td>{{$dcReports->sum('issue_qty') - $dcReports->sum('receive_qty')}}</td>
                                        <td></td>
                                        <td></td>
                                        <td>{{number_format($dcReports->sum(function($report) {
                                            return ($report->unit_rate * 0.7) * ($report->issue_qty - ($report->receive_qty ?? 0));
                                        }), 2)}}</td>
                                        <td></td>
                                    </tr>
                                    @endif
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
@push('scripts')
    <script>
        $(document).ready(function() {
            $('.select2').select2({
                placeholder: "Select an option",
                allowClear: true
            });
        });
    </script>
@endpush
