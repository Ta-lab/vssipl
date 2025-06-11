<table style="border: 5px solid black;">
    <thead>
        <tr>
                                    <th><b>S.No</b></th>
                                    <th><b>Date</b></th>
                                    <th><b>Part Number</b></th>
                                    <th><b>Route Card Number</b></th>
                                    <th><b>VSS UNIT-1 Issue DC Qty</b></th>
                                    <th><b>PTS Store DC Receive Available Qty</b></th>
                                    <th><b>PTS Production Available Qty</b></th>
                                    <th><b>PTS Store Issue To CLE Available Qty</b></th>
                                    <th><b>CLE Issue To PTS Store Available Qty</b></th>
                                    <th><b>PTS Store Issue DC Available Qty</b></th>
                                    {{-- <th><b>FG DC Receive Available Qty</b></th> --}}
        </tr>
    </thead>
    <tbody>
        @forelse ($d12Datas as $d12Data)
        <tr>
            <td>{{$loop->iteration}}</td>
            <td>{{$d12Data->open_date}}</td>
            <td>{{$d12Data->partmaster->child_part_no}}</td>
            <td>{{$d12Data->rcmaster->rc_id}}</td>
            <td>{{$d12Data->u1_dc_issue_qty}}</td>
            <td>{{$d12Data->u1_avl_qty}}</td>
            <td>{{$d12Data->pts_store_avl_qty}}</td>
            <td>{{$d12Data->pts_production_avl_qty}}</td>
            <td>{{$d12Data->cle_avl_qty}}</td>
            <td>{{$d12Data->pts_dc_avl_qty}}</td>
            {{-- <td>{{$d12Data->fg_dc_avl_qty}}</td> --}}
        </tr>
        @empty
        <tr>
            <td colspan="9" align="center">No Records Found!</td>
        </tr>
        @endforelse
    </tbody>
</table>
