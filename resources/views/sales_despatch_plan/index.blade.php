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
            <div class="card-header d-flex" style="justify-content:space-between"><span> <b> Sales Despatch Plan List</b> </span>
                <a class="btn btn-sm btn-primary" href="{{route('salesdespatchplansummary.create')}}">Add Sales Despatch Plan</a>
                <a class="btn btn-sm btn-primary" href="{{route('salesplanconfirm')}}">Sales Despatch Plan Confirm List</a>
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
                                    <td>@if ($salesDespatchPlanSummary->status==3)
                                        <span class="btn btn-sm text-white btn-success">Completed</span>
                                        @elseif ($salesDespatchPlanSummary->status==4)
                                        <span class="btn btn-sm text-white btn-danger">Rejected</span>
                                        @elseif ($salesDespatchPlanSummary->status==2)
                                        <span class="btn btn-sm text-white btn-info">Waiting For Confirmation</span>
                                        @else
                                        <span class="btn btn-sm text-white btn-warning">Pending</span>
                                    @endif</td>
                                    <td><a href="{{route('salesdespatchplansummary.edit',$salesDespatchPlanSummary->id)}}" class="btn btn-sm btn-primary">Edit</a></td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="15" align="center">No Records Found!</td>
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
