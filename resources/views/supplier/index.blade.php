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
            <div class="card-header d-flex" style="justify-content:space-between"><span> <b>Supplier List</b> </span>
                <a class="btn btn-sm btn-primary" href="{{route('supplier.create')}}">Add Supplier</a>
            </div>
            <div class="card-body">
                <form action="" method="get">
                    @csrf
                    <div class="row mb-3 mt-3">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="supplier_id"><b>Supplier Code*</b></label>
                                <select name="supplier_id" id="supplier_id" class="form-control @error('supplier_id') is-invalid @enderror">
                                <option value=""></option>
                                @forelse ($suppliers as $supplier)
                                    <option value="{{$supplier->id}}" {{Request::get('supplier_id')==$supplier->id ? 'selected':''}}>{{$supplier->supplier_code}}</option>
                                @empty
                                @endforelse
                                </select>
                                @error('supplier_id')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-3 mt-4">
                            <button type="submit" class="btn btn-sm btn-success text-white" name="submit">Submit</button>
                            <a class="btn btn btn-sm btn-danger text-white" id="reset" href="{{ route('supplier.index') }}">clear</a>
                            <a class="btn btn-sm btn-success text-white" id="export_excel_btn1" href="{{ route('supplier_export', ['_token'=>csrf_token(),'supplier_id' => Request::get('supplier_id')]) }}">Export To EXCEL</a>
                        </div>
                    </div>
                </form>
                <div class="table">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-responsive">
                            <thead>
                                <tr>
                                    <th>S.No</th>
                                    <th>Code</th>
                                    <th>Supplier Name</th>
                                    <th>Contact Person</th>
                                    <th>Contact Number</th>
                                    <th>Email</th>
                                    <th>Location</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($suppliers as $supplier)
                                <tr>
                                    <td>{{$loop->iteration}}</td>
                                    <td>{{$supplier->supplier_code}}</td>
                                    <td>{{$supplier->name}}</td>
                                    <td>{{$supplier->contact_person}}</td>
                                    <td>{{$supplier->contact_number}}</td>
                                    <td>{{$supplier->email}}</td>
                                    <td>{{$supplier->address}}</td>
                                    <td>@if ($supplier->status==1)
                                        <span class="btn btn-sm text-white btn-success">Active</span>
                                        @else
                                        <span class="btn btn-sm text-white btn-danger">Inactive</span>
                                    @endif</td>
                                    <td><a href="{{route('supplier.edit',$supplier->id)}}" class="btn btn-sm btn-primary">Edit</a></td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" align="center">No Records Found!</td>
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
@push('scripts')
    <script>
            $("#supplier_id").select2({
                placeholder:"Select The Supplier Code",
                allowedClear:true
            });
    </script>
@endpush
