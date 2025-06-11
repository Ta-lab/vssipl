<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <meta name="description" content="VSSIPL-ERP">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <link rel="icon" type="image/png" sizes="192x192" href="{{asset('assets/favicon/android-icon-192x192.png')}}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{asset('assets/favicon/favicon-32x32.png')}}">
    <link rel="icon" type="image/png" sizes="96x96" href="{{asset('assets/favicon/favicon-96x96.png')}}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{asset('assets/favicon/favicon-16x16.png')}}">
    <link rel="manifest" href="{{asset('assets/favicon/manifest.json')}}">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="{{asset('assets/favicon/ms-icon-144x144.png')}}">
    <meta name="theme-color" content="#ffffff">
    <!-- Vendors styles-->
    <link rel="stylesheet" href="{{asset('vendors/simplebar/css/simplebar.css')}}">
    <link rel="stylesheet" href="{{asset('css/vendors/simplebar.css')}}">
    <link  rel="stylesheet" href="{{asset('css/select2.min.css')}}" />
    <link  rel="stylesheet" href="{{asset('css/boxicons.min.css')}}" />

	{{-- <link rel="stylesheet" type="text/css" href="{{asset('css/select2.min.css')}}"> --}}
    <!-- Main styles for this application-->
    <link href="{{asset('css/style.css')}}" rel="stylesheet">
    <!-- We use those styles to show code examples, you should remove them in your application.-->
    <link href="{{asset('css/examples.css')}}" rel="stylesheet">
    <link rel="stylesheet" href="{{asset('css/sweetalert2.min.css')}}">
    <link rel="stylesheet" href="{{asset('css/toaster.min.css')}}" />
    <style>
        body{
            font-family:Arial !important;
            color: black !important;
        }
        a{
            text-decoration:none !important;
        }
        #page-size {
            page-break-after: always;
        }
        @page {
            size: 7.6cm*10cm !important;
        }
        .grnprint{
            /* outline: 10px solid rgb(17, 0, 255); */
            padding:1px solid black;

        }
        table {
            border-collapse: collapse;
            border-spacing:0px;
            min-height: 2.46276in !important;
            min-width: 3.52301in !important;
            margin-left: 0.1in;
        }   th, td {
            text-align:center;
            border:1px solid black;
            padding:0px;
        }
    </style>
    @stack('styles')
</head>
<body>
    @foreach ($packingStrickerDetails as $packingStrickerDetail)

    <div class="grnprint">
        <div class="container-fluid">
            <div class="col-12">
                <div class="row" >
                    <div class="col-12">
                        <h6 style="font-size:12px!important;font-color:black;text-align:center;"><b>VENKATESWARA STEELS & SPRINGS (I) PVT LTD (UNIT-I)</b></h6>
                        {{-- <h6 style="font-size:10px!important;font-color:black;text-align:center;" class="mx-auto"><b>SF RECEIPT</b></h6> --}}
                    </div>
                </div>
                <div class="row">
                    <div class="col-11">
                        <div class="table">
                            <table >
                                <tr>
                                    <th style="font-size:12px!important;font-color:black;text-align:left;padding-left:10px;">RC Number</th>
                                    <th colspan="2" style="font-size:14px!important;font-color:black;">{{$packingStrickerDetail->rcmaster->rc_id}}-{{$packingStrickerDetail->cover_order_id}}</th>
                                </tr>
                                <tr>
                                    <th style="font-size:12px!important;font-color:black;text-align:left;padding-left:10px;">Part Number</th>
                                    <th colspan="2" style="font-size:14px!important;font-color:black;">{{$packingStrickerDetail->partmaster->child_part_no}}</th>
                                </tr>
                                <tr>
                                    <th style="font-size:12px!important;font-color:black;text-align:left;padding-left:10px;">Part Name</th>
                                    <th  colspan="2" style="font-size:12px!important;font-color:black;">{{mb_strimwidth($packingStrickerDetail->partmaster->invoicepart->part_desc, 0, 11, "")}}</th>
                                    {{-- <th  colspan="2" style="font-size:12px!important;font-color:black;">{{$packingStrickerDetail->partmaster->invoicepart->part_desc}}</th> --}}
                                </tr>
                                <tr>
                                    <th style="font-size:12px!important;font-color:black;text-align:left;padding-left:10px;">Cover Quantity</th>
                                    <th style="font-size:14px!important;font-color:black;">{{$packingStrickerDetail->total_cover_qty}}</th>
                                    @if ($c_process_id==3)
                                    <th rowspan="3" style="font-size:10px!important;font-color:black;margin:auto;text-align:center;">{{QrCode::size(125)->style('round')->generate($packingStrickerDetail->cus_type_name."---".$packingStrickerDetail->rcmaster->rc_id."-".$packingStrickerDetail->cover_order_id."-".$packingStrickerDetail->partmaster->child_part_no."-".$packingStrickerDetail->partmaster->invoicepart->part_desc."-".$packingStrickerDetail->total_cover_qty."-".$packingStrickerDetail->id)}}</th>

                                    @else
                                        <th rowspan="3" style="font-size:10px!important;font-color:black;margin:auto;text-align:center;">{{QrCode::size(125)->style('round')->generate($packingStrickerDetail->cus_type_name."-".$packingStrickerDetail->rcmaster->rc_id."-".$packingStrickerDetail->cover_order_id."-".$packingStrickerDetail->partmaster->child_part_no."-".$packingStrickerDetail->partmaster->invoicepart->part_desc."-".$packingStrickerDetail->total_cover_qty."-".$packingStrickerDetail->id)}}</th>
                                    @endif
                                </tr>
                                <tr>
                                    <th style="font-size:12px!important;font-color:black;text-align:left;padding-left:10px;">Inspected By</th>
                                    @if (($packingStrickerDetail->inspect_by)==0)
                                    <th style="font-size:10px!important;font-color:black;"></th>
                                    @else
                                    <th style="font-size:10px!important;font-color:black;">{{mb_strimwidth($packingStrickerDetail->inspectedby->name, 0, 11, "")}}</th>
                                    @endif
                                </tr>
                                <tr>
                                    <th style="font-size:12px!important;font-color:black;text-align:left;padding-left:10px;">Inspected Date</th>
                                    @if ($packingStrickerDetail->inspect_at!='')
                                    <th style="font-size:12px!important;font-color:black;">{{date('d-m-Y', strtotime($packingStrickerDetail->inspect_at))}}</th>

                                    @else
                                    <th style="font-size:12px!important;font-color:black;"></th>
                                    @endif
                                </tr>

                                {{-- <tr>
                                    <th style="font-size:17px!important;font-color:black;margin:auto;text-align:center;">DC</th>
                                    <th style="font-size:10px!important;font-color:black;margin:auto;text-align:center;">{{QrCode::size(50)->style('round')->generate($packingStrickerDetail->cus_type_name."-".$packingStrickerDetail->rcmaster->rc_id."-".$packingStrickerDetail->cover_order_id."-".$packingStrickerDetail->partmaster->child_part_no."-".$packingStrickerDetail->partmaster->invoicepart->part_desc."-".$packingStrickerDetail->total_cover_qty."-".$packingStrickerDetail->id)}}</th>
                                </tr> --}}
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endforeach

</body>
<script src="{{asset('js/jquery.min.js')}}" ></script>
<script src="{{asset('vendors/simplebar/js/simplebar.min.js')}}"></script>
<script src="{{asset('vendors/@coreui/coreui/js/coreui.bundle.min.js')}}"></script>
<script src="{{asset('js/select2.min.js')}}"></script>
<script src="{{asset('js/sweetalert2.all.min.js')}}"></script>
<script src="{{asset('js/boxicons.js')}}"></script>
<script src="{{asset('js/toaster.min.js')}}" ></script>
<script>
setTimeout(() => {
$('.alert').alert('close');
}, 2000);
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});
</script>
@stack('scripts')
</html>
