@extends('layouts.app')
@section('content')
<div class="row d-flex justify-content-center">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><b>Open Route Card Report</b></span>
                <a href="{{ route('open_route_card.export') }}" class="btn btn-primary">Export Excel</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-responsive">
                        <thead>
                            <tr>
                                <th>S.No</th>
                                <th>Route Card No</th>
                                <th>Operation</th>
                                <th>Issue Date</th>
                                <th>Raw Material</th>
                                <th>Issued Qty</th>
                                <th>UOM</th>
                                <th>Part Number</th>
                                <th>OK Qty</th>
                                <th>Rejected Qty</th>
                                <th>Rework Qty</th>
                                <th>Process</th>
                                <th>BOM</th>
                                <th>Used Qty</th>
                                <th>Qty In Process</th>
                                <th>Used UOM</th>
                                <th>No. Days</th>
                                <th>RSP</th>
                                <th>Group</th>
                                <th>Machine</th>
                                <th>RM Requisition No</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($routeCards as $i => $rc)
                            <tr>
                                <td>{{ $i+1 }}</td>
                                <td>{{ $rc->current_rcmaster->rc_id ?? '' }}</td>
                                <td>{{ $rc->currentprocessmaster->operation ?? '' }}</td>
                                <td>{{ $rc->open_date }}</td>
                                <td>{{ $rc->rm_master->name ?? '' }}</td>
                                <td>{{ $rc->rm_issue_qty }}</td>
                                <td>{{ $rc->rm_master->category->name ?? '' }}</td>
                                <td>{{ $rc->partmaster->child_part_no ?? '' }}</td>
                                <td>{{ $rc->receive_qty }}</td>
                                <td>{{ $rc->reject_qty }}</td>
                                <td>{{ $rc->rework_qty }}</td>
                                <td>{{ $rc->currentproductprocessmaster->processMaster->operation ?? '' }}</td>
                                <td>{{ optional(\App\Models\BomMaster::where('child_part_id', $rc->part_id)->first())->manual_usage }}</td>
                                <td>{{ $rc->issue_qty }}</td>
                                <td>{{ $rc->receive_qty - $rc->issue_qty }}</td>
                                <td>{{ $rc->rm_master->category->name ?? '' }}</td>
                                <td>{{ $rc->no_days ?? '' }}</td>
                                <td>{{ $rc->receiver->name ?? '' }}</td>
                                <td>{{ optional($rc->partmaster)->group_id ? optional(\App\Models\GroupMaster::find(optional($rc->partmaster)->group_id))->name : '' }}</td>
                                <td>{{ optional($rc->partmaster)->machine_id ? optional(\App\Models\MachineMaster::find(optional($rc->partmaster)->machine_id))->machine_name : '' }}</td>
                                <td>{{ $rc->rm_requisition_no ?? '' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="d-flex justify-content-center mt-3">
    {{ $routeCards->links() }}
</div>
@endsection 