<table style="border: 5px solid black;">
    <thead>
        <tr>
            <th><b>S.No</b></th>
            <th><b>Category</b></th>
            <th><b>Status</b></th>
        </tr>
    </thead>
    <tbody>
        @forelse ($categorydatas as $department)
        <tr>
            <td>{{$loop->iteration}}</td>
            <td>{{$department->name}}</td>
            <td>@if ($department->status==1)
                <span>Active</span>
                @else
                <span>Inactive</span>
            @endif</td>
        </tr>
        @empty
        <tr>
            <td colspan="3" align="center">No Records Found!</td>
        </tr>
        @endforelse
    </tbody>
</table>
