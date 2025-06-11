<?php
    $whole = floor($totalData[0]->sum_rate);
    $fraction = $totalData[0]->sum_rate - $whole;
    $fraction=$fraction*100;
    $rup=ROUND($fraction);
    $pai=floor($totalData[0]->sum_rate);
    $digit = new NumberFormatter("en", NumberFormatter::SPELLOUT);
    if($digit->format($rup)==""){
        $a=strtoupper($digit->format($pai))." ONLY";
        $amtstr=$a;
    }else{
        $a=strtoupper($digit->format($pai))." AND PAISE ".strtoupper($digit->format($rup)." ONLY");
    }
?>
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

    <!-- Main styles for this application-->
    <link href="{{asset('css/style.css')}}" rel="stylesheet">
    <!-- We use those styles to show code examples, you should remove them in your application.-->
    <link href="{{asset('css/examples.css')}}" rel="stylesheet">
    <link rel="stylesheet" href="{{asset('css/sweetalert2.min.css')}}">
    <link rel="stylesheet" href="{{asset('css/toaster.min.css')}}" />
    <style>
        a{
            text-decoration:none !important;
        }
        footer{
            font-family:Arial, sans-serif;
            font-size:8px!important;
            font-weight:normal;
        }
        .header-sticky{
            background-color:currentColor !important;
        }
        table, td, th {
        border: 1px solid black;
        text-align:left;
        }
        table, td {
        font-family:Arial, sans-serif;
        font-size:11px;padding:3px 3px;
        border-style:solid;
        border-width:1px;
        overflow:hidden;
        word-break:normal;
        }
        table, th {
        font-family:Arial, sans-serif;
        font-size:16px;font-weight:normal;
        padding:1px 3px;
        border-style:solid;
        border-width:1px;
        overflow:hidden;
        word-break:normal;
        }
        @page {
            size: A4;
        }
        table {
        border-collapse: collapse;
        width: 100%;
        border-spacing:0;margin:0px auto;
        }


        @media screen and (max-width: 767px) {.tg {width: auto !important;}.tg col {width: auto !important;}.tg-wrap {overflow-x: auto;-webkit-overflow-scrolling: touch;margin: auto 0px;}}
    </style>
    @stack('styles')

  </head>
<body>
    @for ($page = 0; $page < $page_count; $page++)
    <div class="row d-flex justify-content-center mt-2" >
        <div class="col-12">
            <div class="card">
                <div class="col-12">
                    <div class="card-body">
                        <div class="row d-flex justify-content-center">
                            <div class="table">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped table-responsive">
                                        <tr>
                                            <th class="tg-bn4o" colspan="16" style="font-weight:bold;font-size:14px;text-align:center!important;vertical-align:top"> DELIVERY CHALLAN</th>
                                          </tr>
                                          <tr>
                                            <td class="tg-3b15" colspan="2" rowspan="3"><img src="{{asset('image/logo.png')}}" alt="Mountain View" style="width:100%;height:8%;background-color:#010066;vertical-align:center"></img></td>
                                            <td class="tg-bn4o" colspan="14" style="font-weight:bold;font-size:14px;text-align:center!important;vertical-align:top">VENKATESWARA STEELS AND SPRINGS (INDIA) PVT LTD</td>
                                          </tr>
                                          <tr>
                                            <td class="tg-amwm" colspan="14" style="font-weight:bold;text-align:center;vertical-align:top">No:164-A,Chettipalayam Road,</td>
                                          </tr>
                                          <tr>
                                            <td class="tg-amwm" colspan="14" style="font-weight:bold;text-align:center;vertical-align:top">Pappampatti,Coimbatore - 641 016. Ph.No. 9095177955</td>
                                          </tr>
                                          <tr>
                                            <td class="tg-amwm" colspan="2" style="font-weight:bold;text-align:center;vertical-align:top">GSTIN: 33AACCV3065F1ZL</td>
                                            <td class="tg-l2oz" style="font-weight:bold;text-align:right;vertical-align:top" colspan="2">MODE OF TRANSPORT</td>
                                            <td class="tg-9hbo" style='font-weight:bold;vertical-align:top' colspan="12">{{$dc_transactionDatas[0]->trans_mode}}</td>
                                          </tr>
                                          <tr>
                                            <td class="tg-amwm" colspan="2" style="font-weight:bold;text-align:center;vertical-align:top">DC NUMBER</td>
                                            <td class="tg-l2oz" style="font-weight:bold;text-align:right;vertical-align:top" colspan="2">VEHICLE NUMBER</td>
                                            <td class="tg-yw4l" style="vertical-align:top" colspan="12">{{$dc_transactionDatas[0]->vehicle_no}}</td>
                                          </tr>
                                          <tr>
                                            <td class="tg-ujoh" style="font-weight:bold;font-size:22px;text-align:center;vertical-align:top" colspan="2">{{'DCU4-'.$dc_transactionDatas[0]->s_no}}</td>
                                            <td class="tg-l2oz" style="font-weight:bold;text-align:right;vertical-align:top" colspan="2">DATE AND TIME OF SUPPLY</td>
                                            <td class="tg-yw4l" style="vertical-align:top" colspan="12">{{date('d-m-Y  # H:i')}}</td>
                                          </tr>
                                          <tr>
                                            <td class="tg-amwm" style="font-weight:bold;text-align:center;vertical-align:top">DATE:</td>
                                            <td class="tg-baqh" style="text-align:center;vertical-align:top">{{date('d-m-Y')}}</td>
                                            <td class="tg-l2oz" style="font-weight:bold;text-align:right;vertical-align:top" colspan="2">PLACE OF SUPPLY</td>
                                            <td class="tg-yw4l" style="vertical-align:top" colspan="12">Coimbatore</td>
                                          </tr>
                                          <tr>
                                            <td class="tg-bn4o" colspan="16" style="font-weight:bold;font-size:14px;text-align:center!important;vertical-align:top">DETAILS OF THE RECEIVER</td>
                                          </tr>
                                          <tr>
                                            <td style='font-weight:bold;text-align:right;vertical-align:top' colspan="3">Name:</td>
                                            <td colspan="13">{{$dc_transactionDatas[0]->supplier_name}}</td>
                                          </tr>
                                          <tr>
                                            <td style='font-weight:bold;text-align:right;vertical-align:top' colspan="3">Address:</td>
                                            <td colspan="13">{{$dc_transactionDatas[0]->supplier_address.$dc_transactionDatas[0]->supplier_address1.$dc_transactionDatas[0]->supplier_city.'-'.$dc_transactionDatas[0]->supplier_pincode}}</td>
                                          </tr>
                                          <tr>
                                            <td style='font-weight:bold;text-align:right;vertical-align:top' colspan="3">State:</td>
                                            <td colspan="13">{{$dc_transactionDatas[0]->supplier_state}}</td>
                                          </tr>
                                          <tr>
                                            <td style='font-weight:bold;text-align:right;vertical-align:top' colspan="3">State code:</td>
                                            <td colspan="13">{{$dc_transactionDatas[0]->supplier_state_code}}</td>
                                          </tr>
                                          <tr>
                                            <td style='font-weight:bold;text-align:right;vertical-align:top' colspan="3">GST Unique ID:</td>
                                            <td colspan="13">{{$dc_transactionDatas[0]->supplier_gst_number}}</td>
                                          </tr>
                                          <tr>
                                            <td  class="tg-amwm" style="font-weight:bold;text-align:center;vertical-align:top;width:5px;">S No</td>
                                            <td  class="tg-amwm" style="font-weight:bold;text-align:center;vertical-align:top">DC Number</td>
                                            <td  class="tg-amwm" style="font-weight:bold;text-align:center;vertical-align:top">DC Date</td>
                                            <td  class="tg-amwm" style="font-weight:bold;text-align:center;vertical-align:top;width:150px;">Product/Part No</td>
                                            <td  class="tg-amwm" style="font-weight:bold;text-align:center;vertical-align:top">HSN Code</td>
                                            <td  class="tg-amwm" style="font-weight:bold;text-align:center;vertical-align:top">Cover Quantity</td>
                                            <td  class="tg-amwm" style="font-weight:bold;text-align:center;vertical-align:top">No Of Cover</td>
                                            <td  class="tg-amwm" style="font-weight:bold;text-align:center;vertical-align:top">Total Quantity</td>
                                            <td  class="tg-amwm" style="font-weight:bold;text-align:center;vertical-align:top">UOM</td>
                                            <td  class="tg-amwm" style="font-weight:bold;text-align:center;vertical-align:top">Unit Rate</td>
                                            <td  class="tg-amwm" style="font-weight:bold;text-align:center;vertical-align:top">Total Value</td>
                                            <td  class="tg-amwm" style="font-weight:bold;text-align:center;vertical-align:top" colspan="5">Remarks</td>
                                        </tr>
                                          <?php
                                            if ($page==0) {
                                                if ($count<=10) {
                                                    $count1=$count;
                                                    $diff=10-1;
                                                    $first=$count-1;
                                                    $xy=0;
                                                }else{
                                                    $count1=10;
                                                    $diff=0;
                                                    $first=0;
                                                    $xy=0;
                                                }
                                            }elseif ($page==1) {
                                                if ($count>10 && $count<=20) {
                                                    $count1=$count;
                                                    $diff=(20-1);
                                                    $first=20-(20-$count)-1;
                                                    $xy=10;
                                                }else{
                                                    $count1=20;
                                                    $diff=0;
                                                    $first=11;
                                                    $xy=10;
                                                }
                                            }elseif ($page==2) {
                                                if ($count>20 && $count<=30) {
                                                    $count1=$count;
                                                    $diff=(30-1);
                                                    $first=30-(30-$count)-1;
                                                    $xy=20;
                                                }else{
                                                    $count1=30;
                                                    $diff=0;
                                                    $first=21;
                                                    $xy=20;
                                                }
                                            }
                                          ?>
                                          @for ($i = $xy; $i < $count1; $i++)
                                          <tr>
                                              <td class="tg-baqh" style="text-align:center;font-size:10px;height: 28px;vertical-align:top;width:5px;">{{$i+1}}</td>
                                              <td class="tg-baqh" style="text-align:center;font-size:10px;height: 28px;vertical-align:top">{{$dc_transactionDatas[$i]->dc_no}}</td>
                                              <td class="tg-baqh" style="text-align:center;font-size:10px;height: 28px;vertical-align:top">{{$dc_transactionDatas[$i]->issue_date}}</td>
                                              <td class="tg-baqh" style="text-align:center;font-size:10px;height: 28px;vertical-align:top;width:150px;">{{$dc_transactionDatas[$i]->part_no}}</td>
                                              <td class="tg-baqh" style="text-align:center;font-size:10px;height: 28px;vertical-align:top">{{$dc_transactionDatas[$i]->hsnc}}</td>
                                              <td class="tg-baqh" style="text-align:center;font-size:10px;height: 28px;vertical-align:top">{{$dc_transactionDatas[$i]->cover_qty}}</td>
                                              <td class="tg-baqh" style="text-align:center;font-size:10px;height: 28px;vertical-align:top">{{$dc_transactionDatas[$i]->no_cover}}</td>
                                              <td class="tg-baqh" style="text-align:center;font-size:10px;height: 28px;vertical-align:top">{{$dc_transactionDatas[$i]->issue_qty}}</td>
                                              <td class="tg-baqh" style="text-align:center;font-size:10px;height: 28px;vertical-align:top">{{$dc_transactionDatas[$i]->uom}}</td>
                                              <td class="tg-baqh" style="text-align:center;font-size:10px;height: 28px;vertical-align:top">{{$dc_transactionDatas[$i]->unit_rate}}</td>
                                              <td class="tg-baqh" style="text-align:center;font-size:10px;height: 28px;vertical-align:top">{{$dc_transactionDatas[$i]->total_rate}}</td>
                                              <td class="tg-baqh" style="text-align:center;font-size:10px;height: 28px;vertical-align:top" colspan="5">{{$dc_transactionDatas[$i]->remarks}}</td>
                                          </tr>
                                          @endfor
                                            @if ($diff>0)
                                                @for ($x = $first; $x < (($diff)); $x++)
                                                    <tr >
                                                        <td class="tg-baqh" style="text-align:center;font-size:10px;height: 28px;vertical-align:top">{{$x+2}}</td>
                                                        <td class="tg-baqh" style="text-align:center;font-size:10px;height: 28px;vertical-align:top"></td>
                                                        <td class="tg-baqh" style="text-align:center;font-size:10px;height: 28px;vertical-align:top"></td>
                                                        <td class="tg-baqh" style="text-align:center;font-size:10px;height: 28px;vertical-align:top"></td>
                                                        <td class="tg-baqh" style="text-align:center;font-size:10px;height: 28px;vertical-align:top"></td>
                                                        <td class="tg-baqh" style="text-align:center;font-size:10px;height: 28px;vertical-align:top"></td>
                                                        <td class="tg-baqh" style="text-align:center;font-size:10px;height: 28px;vertical-align:top"></td>
                                                        <td class="tg-baqh" style="text-align:center;font-size:10px;height: 28px;vertical-align:top"></td>
                                                        <td class="tg-baqh" style="text-align:center;font-size:10px;height: 28px;vertical-align:top"></td>
                                                        <td class="tg-baqh" style="text-align:center;font-size:10px;height: 28px;vertical-align:top"></td>
                                                        <td class="tg-baqh" style="text-align:center;font-size:10px;height: 28px;vertical-align:top"></td>
                                                        <td class="tg-baqh" style="text-align:center;font-size:10px;height: 28px;vertical-align:top" colspan="5"></td>
                                                    </tr>
                                                @endfor
                                                @else
                                            @endif
                                            <?php
                                            if ($page==0) {
                                            if ($count<=10) {
                                            ?>
                                                    <tr>
                                                        <td class="tg-amwm" style="font-weight:bold;text-align:center;vertical-align:top" colspan="7">TOTAL</td>
                                                        <td class="tg-amwm" style="font-weight:bold;text-align:center;vertical-align:top">{{$totalData[0]->sum_qty}}</td>
                                                        <td class="tg-amwm" style="font-weight:bold;text-align:center;vertical-align:top" colspan="2"></td>
                                                        <td class="tg-amwm" style="font-weight:bold;text-align:center;vertical-align:top">{{$totalData[0]->sum_rate}}</td>';
                                                        <td class="tg-amwm" style="font-weight:bold;text-align:center;vertical-align:top" colspan="5"></td>
                                                    </tr>
                                                    <tr>
                                                        <td class="tg-amwm" style="font-weight:bold;text-align:center;vertical-align:top" colspan="16">TOTAL VALUE IN WORDS</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="tg-yw4l" style="vertical-align:top;text-align:center;" colspan="16">{{$a}}</td>
                                                    </tr>
                                            <?php
                                            }else{
                                                echo "<tr><td colspan='16'>";
                                                echo "<br>";
                                                echo "</td></tr>";
                                            }
                                        }elseif ($page==1) {
                                            if ($count<=20) {
                                                ?>
                                                    <tr>
                                                        <td class="tg-amwm" style="font-weight:bold;text-align:center;vertical-align:top" colspan="7">TOTAL</td>
                                                        <td class="tg-amwm" style="font-weight:bold;text-align:center;vertical-align:top">{{$totalData[0]->sum_qty}}</td>
                                                        <td class="tg-amwm" style="font-weight:bold;text-align:center;vertical-align:top" colspan="2"></td>
                                                        <td class="tg-amwm" style="font-weight:bold;text-align:center;vertical-align:top">{{$totalData[0]->sum_rate}}</td>';
                                                        <td class="tg-amwm" style="font-weight:bold;text-align:center;vertical-align:top" colspan="5"></td>
                                                    </tr>
                                                    <tr>
                                                        <td class="tg-amwm" style="font-weight:bold;text-align:center;vertical-align:top" colspan="16">TOTAL VALUE IN WORDS</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="tg-yw4l" style="vertical-align:top;text-align:center;" colspan="16">{{$a}}</td>
                                                    </tr>
                                            <?php
                                            }else{
                                                echo "<tr><td colspan='16'>";
                                                echo "<br>";
                                                echo "</td></tr>";
                                            }
                                        }elseif ($page==2) {
                                                if ($count<=30) {
                                                ?>
                                                    <tr>
                                                        <td class="tg-amwm" style="font-weight:bold;text-align:center;vertical-align:top" colspan="7">TOTAL</td>
                                                        <td class="tg-amwm" style="font-weight:bold;text-align:center;vertical-align:top">{{$totalData[0]->sum_qty}}</td>
                                                        <td class="tg-amwm" style="font-weight:bold;text-align:center;vertical-align:top" colspan="2"></td>
                                                        <td class="tg-amwm" style="font-weight:bold;text-align:center;vertical-align:top">{{$totalData[0]->sum_rate}}</td>';
                                                        <td class="tg-amwm" style="font-weight:bold;text-align:center;vertical-align:top" colspan="5"></td>
                                                    </tr>
                                                    <tr>
                                                        <td class="tg-amwm" style="font-weight:bold;text-align:center;vertical-align:top" colspan="16">TOTAL VALUE IN WORDS</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="tg-yw4l" style="vertical-align:top;text-align:center;" colspan="16">{{$a}}</td>
                                                    </tr>
                                            <?php
                                            }else{
                                                echo "<tr><td colspan='16'>";
                                                echo "<br>";
                                                echo "</td></tr>";
                                            }
                                        }
                                            ?>
                                            <tr>
                                                <td class="tg-9hbo" style='font-weight:bold;vertical-align:top' colspan="8">
                                                Terms & Conditions :
                                                    <ul>
                                                        <li>Not For Supply</li>
                                                        @if ($dc_transactionDatas[0]->operation=='FG For S/C')
                                                        <li>Inter Unit Transfer</li>
                                                        @else
                                                        <li>For Job Work</li>
                                                        @endif
                                                        <li>Material sent for {{$dc_transactionDatas[0]->operation_desc}} work only</li>
                                                    </ul>
                                                </td>
                                                <td class="tg-9hbo" style='font-weight:bold;vertical-align:top' colspan="8">For Venkateswara Steels &amp; Springs India Pvt Ltd            </td>
                                            </tr>
                                            <tr>
                                                <td class="tg-yw4l" style="vertical-align:top" colspan="16" rowspan="0"><br>RECEIVED,THE ABOVE GOODS<br><br>CUSTOMER SIGNATURE<br>.</td>
                                            </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
            </div>
            <div class="row">
                <footer style="font-weight:8px!important;"><center><b>Page {{$page+1}}/{{$page_count}}</b></center></footer>
            </div>
        </div>
    </div>
    <br>
    @endfor
</body>
</html>
