<table>
    <thead>
        <tr>
            <th><b>S.No</b></th>
            <th><b>Invoice Number</b></th>
            <th><b>Invoice Date</b></th>
            <th><b>Customer Code</b></th>
            <th><b>Customer Name</b></th>
            <th><b>Customer Billing Address</b></th>
            <th><b>Customer Billing Address2</b></th>
            <th><b>Customer Billing City</b></th>
            <th><b>Customer Billing State</b></th>
            <th><b>Customer Billing Country</b></th>
            <th><b>Customer Billing Pincode</b></th>
            <th><b>Customer Billing GST No</b></th>
            <th><b>Customer Billing PAN No</b></th>
            <th><b>Customer Name</b></th>
            <th><b>Customer Shipping Address</b></th>
            <th><b>Customer Shipping Address2</b></th>
            <th><b>Customer Shipping City</b></th>
            <th><b>Customer Shipping State</b></th>
            <th><b>Customer Shipping Country</b></th>
            <th><b>Customer Shipping Pincode</b></th>
            <th><b>Customer Shipping GST No</b></th>
            <th><b>Customer Shipping PAN No</b></th>
            <th><b>Part No</b></th>
            <th><b>Part Desc</b></th>
            <th><b>HSN/SAC Code</b></th>
            <th><b>Customer PO NO</b></th>
            <th><b>Customer LINE NO</b></th>
            <th><b>Quantity</b></th>
            <th><b>UOM</b></th>
            <th><b>Unit Rate</b></th>
            <th><b>Packing Charge (%)</b></th>
            <th><b>CGST (%)</b></th>
            <th><b>SGST (%)</b></th>
            <th><b>IGST (%)</b></th>
            <th><b>TCS (%)</b></th>
            <th><b>Packing Charge Amount</b></th>
            <th><b>CGST Amount</b></th>
            <th><b>SGST Amount</b></th>
            <th><b>IGST Amount</b></th>
            <th><b>TCS Amount</b></th>
            <th><b>Basic Amount</b></th>
            <th><b>Total Amount(Rs)</b></th>
            <th><b>Total Weight(KG)</b></th>
        </tr>
    </thead>
    <tbody>
        @forelse ($invoicedatas as $invoiceData)
        <tr>

            <td>{{$loop->iteration}}</td>
            <td>{{$invoiceData->rcmaster->rc_id}}</td>
            <td>{{date('d-m-Y', strtotime($invoiceData->invoice_date))}}</td>
            <td>{{$invoiceData->customerproductmaster->customermaster->cus_code}}</td>
            <td>{{$invoiceData->customerproductmaster->customermaster->cus_name}}</td>
            <td>{{$invoiceData->customerproductmaster->customermaster->cus_address}}</td>
            <td>{{$invoiceData->customerproductmaster->customermaster->cus_address1}}</td>
            <td>{{$invoiceData->customerproductmaster->customermaster->cus_city}}</td>
            <td>{{$invoiceData->customerproductmaster->customermaster->cus_state}}</td>
            <td>{{$invoiceData->customerproductmaster->customermaster->cus_country}}</td>
            <td>{{$invoiceData->customerproductmaster->customermaster->cus_pincode}}</td>
            <td>{{$invoiceData->customerproductmaster->customermaster->cus_gst_number}}</td>
            <td>{{$invoiceData->customerproductmaster->customermaster->cus_pan_no}}</td>
            <td>{{$invoiceData->customerproductmaster->customermaster->delivery_cus_name}}</td>
            <td>{{$invoiceData->customerproductmaster->customermaster->delivery_cus_address}}</td>
            <td>{{$invoiceData->customerproductmaster->customermaster->delivery_cus_address1}}</td>
            <td>{{$invoiceData->customerproductmaster->customermaster->delivery_cus_city}}</td>
            <td>{{$invoiceData->customerproductmaster->customermaster->delivery_cus_state}}</td>
            <td>{{$invoiceData->customerproductmaster->customermaster->delivery_cus_country}}</td>
            <td>{{$invoiceData->customerproductmaster->customermaster->delivery_cus_pincode}}</td>
            <td>{{$invoiceData->customerproductmaster->customermaster->delivery_cus_gst_number}}</td>
            <td>{{$invoiceData->customerproductmaster->customermaster->delivery_cus_pan_no}}</td>
            <td>{{$invoiceData->productmaster->part_no}}</td>
            <td>{{$invoiceData->productmaster->part_desc}}</td>
            <td>{{$invoiceData->customerproductmaster->part_hsnc}}</td>
            <td>{{$invoiceData->customerpomaster->cus_po_no}}</td>
            <td>{{$invoiceData->customerpomaster->cus_po_item_no}}</td>
            <td>{{$invoiceData->qty}}</td>
            <td>{{$invoiceData->uom_masters->name}}</td>
            <td>{{($invoiceData->customerpomaster->rate)/($invoiceData->customerpomaster->part_per)}}</td>
            <td>{{$invoiceData->packing_charge}}</td>
            <td>{{$invoiceData->cgst}}</td>
            <td>{{$invoiceData->sgst}}</td>
            <td>{{$invoiceData->igst}}</td>
            <td>{{$invoiceData->tcs}}</td>
            <td>{{$invoiceData->packing_charge_amt}}</td>
            <td>{{$invoiceData->cgstamt}}</td>
            <td>{{$invoiceData->sgstamt}}</td>
            <td>{{$invoiceData->igstamt}}</td>
            <td>{{$invoiceData->tcsamt}}</td>
            <td>{{$invoiceData->basic_value}}</td>
            <td>{{$invoiceData->invtotal}}</td>
            <td></td>
        </tr>
        @empty
        <tr>
            <td colspan="43" align="left"><b>No Records Found!</b></td>
        </tr>
        @endforelse

    </tbody>
</table>
