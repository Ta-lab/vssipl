@extends('layouts.app')
@push('styles')

@endpush
@section('content')

<div class="row d-flex justify-content-center">
    <div id="data"></div>
        <div class="row col-md-3"id="res"></div>
        <div class="card">
            <div class="card-header d-flex" style="justify-content:space-between"><span> <b>Traceability Report</b></span><button onclick="exportToPDF()">Download PDF</button>
            </div>
        <div id="content">
            <div class="card-body">
                        <div class="row d-flex justify-content-center">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="invoice_id"><b>Invoice No. *</b></label>
                                    <select name="invoice_id" class="form-control invoice_id @error('invoice_id') is-invalid @enderror" id="invoice_id">
                                        <option value="" selected></option>
                                        @foreach ($invoiceDatas as $invoiceData)
                                            <option value="{{$invoiceData->id}}">{{$invoiceData->rcmaster->rc_id}}</option>
                                        @endforeach
                                    </select>
                                    @error('invoice_id')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            {{-- <div class="col-md-3">
                                <div class="form-group">
                                    <button class="btn btn-primary mt-4" id="proceed">Check</button>
                                </div>
                            </div> --}}
                        </div>
                <button onclick="exportToExcel()" >Download Excel</button>

                    <div id="myTable2"  border="1">

                        <div class="row mt-3" id="table1">
                        </div>
                        <div class="row mt-3" id="table2">
                        </div>
                        <div class="row mt-3" id="table3">
                        </div>
                        <div class="row mt-3" id="table4">
                        </div>
                        <div class="row mt-3" id="table5">
                        </div>
                        <div class="row mt-3" id="table6">
                        </div>
                        <div class="row mt-3" id="table7">
                        </div>
                        <div class="row mt-3" id="table8">
                        </div>
                    </div>
                    </div>
                </div>
            </div>
        </div>

        </div>
@endsection
@push('scripts')
<script>
        $('.invoice_id').select2({
            placeholder: 'Select The Invoice No',
            allowClear: true
        });
        $('.invoice_id').change(function (e) {
            e.preventDefault();
            var id=$(this).val();
            // alert(id);
            $.ajax({
                type: "POST",
                url: "{{route('rcinvoice_data')}}",
                data: {"id":id},
                success: function (response) {
                    $('#table1').html(response.html);
                    $('#table2').html(response.html2);
                    $('#table3').html(response.html3);
                    $('#table4').html(response.html4);
                    $('#table5').html(response.html5);
                    $('#table6').html(response.html6);
                    $('#table7').html(response.html7);
                    $('#table8').html(response.html8);
                }
            });
        });
        function exportToPDF() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();

            // Get content from the div
            let content = document.getElementById("content").innerText;

            // Add content to PDF
            doc.text(content, 10, 10);

            // Save the PDF
            doc.save("download.pdf");
        }
        function exportToExcel() {
            let table = document.getElementById("myTable");
            // alert (table.length);
            let wb = XLSX.utils.book_new();
            let ws = XLSX.utils.table_to_sheet(table);
            // Apply styles to header cells
            let range = XLSX.utils.decode_range(ws['!ref']);
            for (let col = range.s.c; col <= range.e.c; col++) {
                let cellRef = XLSX.utils.encode_cell({ r: 0, c: col }); // Header row
                if (ws[cellRef]) {
                    ws[cellRef].s = {
                        font: { bold: true, color: { rgb: "FFFFFF" } }, // White text
                        fill: { fgColor: { rgb: "4F81BD" } }, // Blue background
                        alignment: { horizontal: "center" }
                    };
                }
            }

            // Apply border and alignment to all cells
            for (let row = range.s.r; row <= range.e.r; row++) {
                for (let col = range.s.c; col <= range.e.c; col++) {
                    let cellRef = XLSX.utils.encode_cell({ r: row, c: col });
                    if (ws[cellRef]) {
                        ws[cellRef].s = {
                            border: {
                                top: { style: "thin", color: { rgb: "000000" } },
                                bottom: { style: "thin", color: { rgb: "000000" } },
                                left: { style: "thin", color: { rgb: "000000" } },
                                right: { style: "thin", color: { rgb: "000000" } }
                            },
                            alignment: { horizontal: "center" }
                        };
                    }
                }
            }

            // Append sheet and save file
            XLSX.utils.book_append_sheet(wb, ws, "StyledSheet");
            XLSX.writeFile(wb, "styled_export.xlsx");
        }


</script>
@endpush

