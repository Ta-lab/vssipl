<table>
    <thead>
        <tr>
            <th>S.No</th>
            <th>DC Number</th>
            <th>DC Date</th>
            <th>Code</th>
            <th>Supplier Name</th>
            <th>Part No</th>
            <th>Issued Qty</th>
            <th>Received Qty</th>
            <th>Available Qty</th>
            <th>UOM</th>
            <th>Unit Rate</th>
            <th>Total Value</th>
        </tr>
    </thead>
    <tbody>
        @foreach($dcReports as $report)
        <tr>
            <td>{{$loop->iteration}}</td>
            <td>{{$report->rcmaster->rc_id ?? 'N/A'}}</td>
            <td>{{date('d-m-Y', strtotime($report->issue_date))}}</td>
            <td>{{$report->dcmaster->supplier->supplier_code ?? 'N/A'}}</td>
            <td>{{$report->dcmaster->supplier->name ?? 'N/A'}}</td>
            <td>{{$report->dcmaster->invoicepart->part_no ?? 'N/A'}}</td>
            <td>{{$report->issue_qty}}</td>
            <td>{{$report->receive_qty ?? 0}}</td>
            <td>{{$report->issue_qty - ($report->receive_qty ?? 0)}}</td>
            <td>{{$report->uom->name ?? 'N/A'}}</td>
            <td>{{$report->unit_rate}}</td>
            <td>{{number_format(($report->unit_rate * 0.7) * ($report->issue_qty - ($report->receive_qty ?? 0)), 2)}}</td>
        </tr>
        @endforeach
    </tbody>
</table> 