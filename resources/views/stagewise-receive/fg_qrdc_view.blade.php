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
            <div class="card-header d-flex" style="justify-content:space-between"><span><b>FG DC Receive Pending List</b></span>
                <a class="btn btn-md btn-primary" href="{{route('fgqrreceive.create')}}"><b><i class='bx bx-plus bx-flashing' style='color:white;' ></i>&nbsp;&nbsp; New</b></a>
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
                                    <th>DC Number</th>
                                    <th>Route Card Number</th>
                                    <th>Cover ID</th>
                                    <th>PTS Store Issue Qty</th>
                                    <th>Cover Qty</th>
                                    <th>To Be Receive Qty</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($coverStrickerDetails as $coverStrickerDetail)
                                <tr>
                                    <td>{{$loop->iteration}}</td>
                                    <td>{{$coverStrickerDetail->rcmaster->create_date}}</td>
                                    <td>{{$coverStrickerDetail->stickermaster->partmaster->child_part_no}}</td>
                                    <td>{{$coverStrickerDetail->rcmaster->rc_id}}</td>
                                    <td>{{$coverStrickerDetail->stickermaster->rcmaster->rc_id}}</td>
                                    <td>{{$coverStrickerDetail->stickermaster->cover_order_id}}</td>
                                    <td>{{$coverStrickerDetail->stickermaster->pts_dc_issue_qty}}</td>
                                    <td>{{$coverStrickerDetail->stickermaster->total_cover_qty}}</td>
                                    <td>{{($coverStrickerDetail->stickermaster->pts_dc_issue_qty)-($coverStrickerDetail->stickermaster->u1_dc_receive_qty)}}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" align="center">No Records Found!</td>
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
