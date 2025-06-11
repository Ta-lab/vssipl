<table style="border: 5px solid black;">
    <thead>
        <tr>
            <th><b>S.No</b></th>
            <th><b>Code</b></th>
            <th><b>Supplier Name</b></th>
            <th><b>Contact Person</b></th>
            <th><b>Contact Number</b></th>
            <th><b>Email</b></th>
            <th><b>Location</b></th>
            <th><b>Status</b></th>
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
        </tr>
        @empty
        <tr>
            <td colspan="7" align="center">No Records Found!</td>
        </tr>
        @endforelse
    </tbody>
</table>
