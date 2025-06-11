@extends('layouts.app')
@section('content')
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
            <div class="card-header d-flex" style="justify-content:space-between"><span> <b> Sales Despatch FG Plan List</b> </span>
                {{-- <a class="btn btn-sm btn-primary" href="{{route('salesdespatchplansummary.create')}}">Add Sales Despatch Plan</a> --}}
            </div>
            <div class="card-body">
                <div class="table">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-responsive">
                            <thead>
                                <tr>
                                    <th>S.No</th>
                                    <th>Plan ID</th>
                                    <th>Plan Date</th>
                                    <th>Customer Code</th>
                                    <th>Customer Name</th>
                                    <th>Customer Type</th>
                                    <th>Part Number</th>
                                    <th>Cover Quantity</th>
                                    <th>Require Quantity</th>
                                    <th>Require No Of Cover</th>
                                    <th>Actual FG Quantity</th>
                                    <th>Actual FG No Of Cover</th>
                                    <th>Confirmed Invoice Quantity</th>
                                    <th>Invoiced Quantity</th>
                                    <th>Remarks</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($salesDespatchPlanSummaries as $salesDespatchPlanSummary)
                                <tr>
                                    <td>{{$loop->iteration}}</td>
                                    <td>{{$salesDespatchPlanSummary->plan_no}}</td>
                                    <td>{{$salesDespatchPlanSummary->open_date}}</td>
                                    <td>{{$salesDespatchPlanSummary->customermaster->cus_code}}</td>
                                    <td>{{$salesDespatchPlanSummary->customermaster->cus_name}}</td>
                                    <td>{{$salesDespatchPlanSummary->customermaster->cus_type_name}}</td>
                                    <td>{{$salesDespatchPlanSummary->productmaster->part_no}}</td>
                                    <td>{{$salesDespatchPlanSummary->packingmaster->cover_qty}}</td>
                                    <td>{{$salesDespatchPlanSummary->cus_req_qty}}</td>
                                    <td>{{($salesDespatchPlanSummary->cus_req_qty)/($salesDespatchPlanSummary->packingmaster->cover_qty)}}</td>
                                    <td>{{$salesDespatchPlanSummary->actual_fg_qty}}</td>
                                    <td>{{($salesDespatchPlanSummary->actual_fg_qty)/($salesDespatchPlanSummary->packingmaster->cover_qty)}}</td>
                                    <td>{{$salesDespatchPlanSummary->to_confirm_qty}}</td>
                                    <td>{{$salesDespatchPlanSummary->invoiced_qty}}</td>
                                    <td>{{$salesDespatchPlanSummary->remarks}}</td>
                                    <td>@if ($salesDespatchPlanSummary->status==1)
                                        <span class="btn btn-sm text-white btn-warning">Pending</span>
                                        @elseif($salesDespatchPlanSummary->status==0)
                                        <span class="btn btn-sm text-white btn-danger">Reject</span>
                                        @else
                                        <span class="btn btn-sm text-white btn-success">Completed</span>
                                    @endif</td>
                                    <td>
                                        {{-- @if ($salesDespatchPlanSummary->cus_req_qty!=$salesDespatchPlanSummary->actual_fg_qty) --}}
                                        <a href="{{route('salesdespatchplansummary.show',$salesDespatchPlanSummary->id)}}" class="btn btn-sm btn-info"><img  src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAB4AAAAeCAYAAAA7MK6iAAAAAXNSR0IArs4c6QAAAUtJREFUSEvdltFJBDEQhr8D3y3CBpR7ljvENqxBOzgtQLCEa+RAxQK0A1uwANEbSWBvnST/hIUVAwt32cl8M38mk10w01jMxGUIXgP2lMYd8CUEeluxedwz7DkA24JNZdER8CmAa8FZ8D+BDTP+E+Cp9z6r0MzYwA/AaUHaV+AmvVPsQmArglUB/DQoRMWuC/wBWIY2TIFjwAPX7LrAHiQ6VwWPVc0SRiHmx1t74L9WvXnxFFL/KhUF7NVXtLhCYOWYmEPVTpZa6I79JlN3KDmSGliV8B5YAm/AdSJ7c7LUSkcyZzvgAnjZ/z5P3r25MLh1nHJ278A2eb8CTkYqhMGtBpIdWrbP6c9lUqK456X72ObVzqWAu3p1S+oMtsvjrCB1F7jVubz34+IKgdXj5IHHxykElpuBYCiDPV//7ytztg96YaumM5ntdvoGkGmUH1CCdycAAAAASUVORK5CYII="/></a></td>
                                    {{-- @else
                                        <a href="{{route('salesdespatchplansummary.show',$salesDespatchPlanSummary->id)}}" class="btn btn-sm btn-danger" style="pointer-events: none"><img  src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAB4AAAAeCAYAAAA7MK6iAAAAAXNSR0IArs4c6QAAAUtJREFUSEvdltFJBDEQhr8D3y3CBpR7ljvENqxBOzgtQLCEa+RAxQK0A1uwANEbSWBvnST/hIUVAwt32cl8M38mk10w01jMxGUIXgP2lMYd8CUEeluxedwz7DkA24JNZdER8CmAa8FZ8D+BDTP+E+Cp9z6r0MzYwA/AaUHaV+AmvVPsQmArglUB/DQoRMWuC/wBWIY2TIFjwAPX7LrAHiQ6VwWPVc0SRiHmx1t74L9WvXnxFFL/KhUF7NVXtLhCYOWYmEPVTpZa6I79JlN3KDmSGliV8B5YAm/AdSJ7c7LUSkcyZzvgAnjZ/z5P3r25MLh1nHJ278A2eb8CTkYqhMGtBpIdWrbP6c9lUqK456X72ObVzqWAu3p1S+oMtsvjrCB1F7jVubz34+IKgdXj5IHHxykElpuBYCiDPV//7ytztg96YaumM5ntdvoGkGmUH1CCdycAAAAASUVORK5CYII="/></a></td>
                                    @endif --}}
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="17" align="center">No Records Found!</td>
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
@endsection
