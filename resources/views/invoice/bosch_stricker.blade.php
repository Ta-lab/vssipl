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
            size: 10.1cm*12.5cm !important;
        }

        table {
            border-collapse: collapse;
            border-spacing:0px;
            min-height: 2.46276in !important;
            min-width: 3.52301in !important;
            margin-left: 0.1in;
        }   th, td {
            border:1px solid black;
            text-align:center;
            padding:0px;
        }

    </style>
    @stack('styles')
</head>
<body>
    {{-- @foreach ($packingStrickerDetails as $packingStrickerDetail) --}}
    <div class="grnprint">
        <div class="container-fluid">
            <div class="col-12">
                <div class="row">
                    <div class="col-11">
                        <div class="table">
                            <table style="margin-top:1.25rem">
                                <tr>
                                    {{-- <th rowspan="5" style="font-size:17px!important;font-color:black;margin:auto;text-align:center;"></th> --}}
                                    <th rowspan="5" colspan="2" style="font-size:10px!important;font-color:black;margin:auto;text-align:center;">{{QrCode::size(90)->style('round', 0.8)->generate($code)}}</th>
                                    <th style="font-size:12px!important;font-color:black;">Part No:</th>
                                    <th style="font-size:12px!important;font-color:black;">{{$manufacturing_part_no}}</th>
                                    <th style="font-size:12px!important;font-color:black;">Qty:</th>
                                    <th style="font-size:12px!important;font-color:black;">{{$qty}}</th>
                                </tr>
                                <tr>
                                    <th style="font-size:12px!important;font-color:black;">Ordering Code:</th>
                                    <th style="font-size:12px!important;font-color:black;">{{$orgin}}</th>
                                    <th style="font-size:12px!important;font-color:black;">Index:</th>
                                    <th style="font-size:12px!important;font-color:black;">{{$index}}</th>
                                    {{-- <th style="font-size:12px!important;font-color:black;">CoO</th>
                                    <th style="font-size:12px!important;font-color:black;">My</th> --}}
                                </tr>
                                <tr>
                                    <th style="font-size:12px!important;font-color:black;">Man. Date:</th>
                                    <th style="font-size:12px!important;font-color:black;">{{$manufacturing_date}}</th>
                                    <th style="font-size:12px!important;font-color:black;">1.Batch:</th>
                                    <th style="font-size:12px!important;font-color:black;">{{$batch1}}</th>
                                </tr>
                                <tr>
                                    <th style="font-size:12px!important;font-color:black;">Exp. Date:</th>
                                    <th style="font-size:12px!important;font-color:black;">{{$expiry_date}}</th>
                                    <th style="font-size:12px!important;font-color:black;">2.Batch:</th>
                                    <th style="font-size:12px!important;font-color:black;">{{$batch2}}</th>
                                </tr>
                                <tr>
                                    <th style="font-size:12px!important;font-color:black;">Add.Info:</th>
                                    <th style="font-size:12px!important;font-color:black;">{{$add_info}}</th>
                                    <th style="font-size:12px!important;font-color:black;">MS-Level:</th>
                                    <th style="font-size:12px!important;font-color:black;">{{$ms_level}}</th>
                                </tr>
                                <tr>
                                    <th style="font-size:12px!important;font-color:black;">CoO:</th>
                                    <th style="font-size:12px!important;font-color:black;">{{$coo}}</th>
                                    <th style="font-size:12px!important;font-color:black;">Supplier-ID:</th>
                                    <th style="font-size:12px!important;font-color:black;">{{$supplier_id}}</th>
                                    <th style="font-size:12px!important;font-color:black;">Package-ID:</th>
                                    <th style="font-size:12px!important;font-color:black;">{{$packing_id}}</th>
                                </tr>
                                <tr>
                                    <th colspan="2" style="font-size:12px!important;font-color:black;">Supplier:</th>
                                    <th style="font-size:12px!important;font-color:black;">{{$supplier}}</th>
                                    <th style="font-size:12px!important;font-color:black;">Purchase:</th>
                                    <th  colspan="2" style="font-size:12px!important;font-color:black;">{{$purchase_no}}</th>
                                </tr>
                                <tr>
                                    <th colspan="1" style="font-size:12px!important;font-color:black;">Man.Loc</th>
                                    <th style="font-size:12px!important;font-color:black;">{{$manufacturing_location}}</th>
                                    <th style="font-size:12px!important;font-color:black;">Shipping Note:</th>
                                    <th style="font-size:12px!important;font-color:black;">{{$shipping_note}}</th>
                                    <th style="font-size:12px!important;font-color:black;">Part Name:</th>
                                    <th style="font-size:12px!important;font-color:black;">{{$part_name}}</th>
                                </tr>
                                <tr>
                                    <th colspan="1" style="font-size:12px!important;font-color:black;">Man.Part No</th>
                                    <th style="font-size:12px!important;font-color:black;">{{$manufacturing_part_no}}</th>
                                    <th style="font-size:12px!important;font-color:black;">Supplier Data:</th>
                                    <th style="font-size:12px!important;font-color:black;">{{$supplier_data}}</th>
                                    <th style="font-size:12px!important;font-color:black;">RoHS:</th>
                                    <th style="font-size:12px!important;font-color:black;">{{$rohs}}</th>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- @endforeach --}}

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
