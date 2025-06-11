<table style="border: 5px solid black;">
    <thead>
        <tr>
            <th><b>S.No</b></th>
            <th><b>Category</b></th>
            <th><b>Material Code</b></th>
            <th><b>Material Description</b></th>
            <th><b>Minimum Stock</b></th>
            <th><b>Maximum Stock</b></th>
            <th><b>Available Stock</b></th>
            <th><b>Stock Level</b></th>
            <th><b>Status</b></th>
        </tr>
    </thead>
    <tbody>
        @forelse ($raw_materialdatas as $department)
        <tr>
            <td>{{$loop->iteration}}</td>
            <td>{{$department->category->name}}</td>
            <td>{{$department->material_code}}</td>
            <td>{{$department->name}}</td>
            <td>{{$department->minimum_stock}}</td>
            <td>{{$department->maximum_stock}}</td>
            <td>{{$department->avl_stock}}</td>
            @if(($department->minimum_stock)>($department->avl_stock))
                <td style="background-color: red"><b>Low</b></td>
            @elseif(($department->maximum_stock)<($department->avl_stock))
                <td style="background-color: yellow"><b>High</b></td>
            @else
                <td style="background-color: green"><b>Available</b></td>
            @endif
            <td>@if ($department->status==1)
                <span>Active</span>
                @else
                <span>Inactive</span>
            @endif</td>
        </tr>
        @empty
        <tr>
            <td colspan="9" align="center">No Records Found!</td>
        </tr>
        @endforelse
        <tr>
            <td colspan="6" align="center"><b>Total Availble Stock (In KGS)</b></td>
            <td colspan="3"><b>{{$total_avl_kg}}</b></td>
        </tr>
    </tbody>
</table>
