@extends('layouts.app')
@section('content')
<div class="row d-flex justify-content-center">

    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex" style="justify-content:space-between"><span> <b>RawMaterialCategory List</b> </span>
                <a class="btn btn-sm btn-primary" href="{{route('raw_material_category.create')}}">Add Category</a>
            </div>
            <div class="card-body">
                <form action="" method="get">
                    @csrf
                    <div class="row mb-3 mt-3">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="raw_material_category_id"><b>Raw Material Category*</b></label>
                                <select name="raw_material_category_id" id="raw_material_category_id" class="form-control @error('raw_material_category_id') is-invalid @enderror">
                                <option value=""></option>
                                @forelse ($categories as $category)
                                    <option value="{{$category->id}}" {{Request::get('raw_material_category_id')==$category->id ? 'selected':''}}>{{$category->name}}</option>
                                @empty
                                @endforelse
                                </select>
                                @error('raw_material_category_id')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-3 mt-4">
                            <button type="submit" class="btn btn-sm btn-success text-white" name="submit">Submit</button>
                            <a class="btn btn btn-sm btn-danger text-white" id="reset" href="{{ route('raw_material_category.index') }}">clear</a>
                            <a class="btn btn-sm btn-success text-white" id="export_excel_btn1" href="{{ route('raw_material_category_export', ['_token'=>csrf_token(),'raw_material_category_id' => Request::get('raw_material_category_id')]) }}">Export To EXCEL</a>
                        </div>
                    </div>
                </form>
                <div class="table">
                    <table class="table table-bordered table-responsive">
                        <thead>
                            <tr>
                                <th>S.No</th>
                                <th>Name</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($categories as $department)
                            <tr>
                                <td>{{$loop->iteration}}</td>
                                <td>{{$department->name}}</td>
                                <td>@if ($department->status==1)
                                    <span class="btn btn-sm btn-success">Active</span>
                                    @else
                                    <span class="btn btn-sm btn-danger">Inactive</span>
                                @endif</td>
                                <td><a href="{{route('raw_material_category.edit',$department->id)}}" class="btn btn-sm btn-primary">Edit</a></td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" align="center">No Records Found!</td>
                            </tr>
                            @endforelse

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
    <script>
            $("#raw_material_category_id").select2({
                placeholder:"Select The Raw Material Category",
                allowedClear:true
            });
    </script>
@endpush
