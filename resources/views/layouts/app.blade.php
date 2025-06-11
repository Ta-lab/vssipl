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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
    <link rel="stylesheet" href="{{asset('css/sweetalert2.min.css')}}">
    <link rel="stylesheet" href="{{asset('css/toaster.min.css')}}" />
    <link rel="stylesheet" href="https://cdn.datatables.net/2.0.3/css/dataTables.dataTables.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/3.0.1/css/buttons.dataTables.css" />
    <style>
        * {
        padding: 0;
        margin: 0;
        box-sizing: border-box;
        }
        html{
            scroll-behavior: smooth;
        }
        /* #scroll-top{
            height: 50px;
            width: 50px;
            background-color: orange;
            position:fixed;
            bottom: 5px;
            right: 5px;
            border-radius: 50%;
            transition: background-color 1s;
        } */
        /* #scroll-top:hover{
            background-color: black;
            cursor: pointer;
        } */
        .scroll-top {
        height: 60px;
        width: 60px;
        background-color: rgb(45, 146, 214);
        position: fixed;
        bottom: 5px;
        right: 10px;
        cursor: pointer;
        border-radius: 6px;
        box-shadow: 0px 0px 20px white;
        /* hover effect default */
        transform: scale(0.9);
        transition: 0.3s ease-in-out transform;
        /* for centering the icon */
        display: flex;
        align-items: center;
        justify-content: center;
        }
        .scroll-top svg {
        width: 50%;
        color: white;
        pointer-events: none;
        }

        .scroll-top:hover {
        transform: scale(1);
        }
        a{
            text-decoration:none !important;
        }
        .header-sticky{
            background-color:currentColor !important;
        }
    </style>
    @stack('styles')
  </head>
<body>
    <div id="scroll-top" class="scroll-top">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 11l7-7 7 7M5 19l7-7 7 7" />
          </svg>
    </div>
    <div class="wrapper d-flex flex-column min-vh-100 bg-light">
      <header class="header header-sticky mb-4">
            <div class="container-fluid">
                <a class="header-brand d-xl-none" href="{{route('home')}}">
                    <img src="{{asset('image/logo.png')}}" alt="logo">
                </a>
                <ul class="header-nav d-none d-md-flex" style="margin-right:30% !important;">
                    <li class="nav-item" ><a href="{{route('home')}}"><img src="{{asset('image/logo.png')}}" alt="logo"></li>
                    <!-- <li class="nav-item"><a class="nav-link" href="#">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">{{ Auth::user()->name }}</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Settings</a></li> -->
                </ul>
                <ul class="header-nav ms-auto">

                    <li class="nav-item text-white font-bold">Welcome ! {{ Auth::user()->name }}</li>
                </ul>
                <ul class="header-nav ms-auto ">
                    <li class="nav-item"><a class="nav-link" href="#">
                        {{-- <svg class="icon icon-lg text-white">
                        <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-bell"></use>
                        </svg> --}}
                    </a></li>
                </ul>
                <ul class="header-nav ms-3">
                    <li class="nav-item dropdown"><a class="nav-link py-0" data-coreui-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
                        <div class="avatar avatar-md"><img class="avatar-img" src="{{asset('assets/img/avatars/10.jpg')}}" alt=""></div>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end pt-0">
                        <div class="dropdown-header bg-light py-2">
                        <div class="fw-semibold">Settings</div>
                        </div>
                        <a class="dropdown-item" href="#">
                        <svg class="icon me-2">
                            <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-user"></use>
                        </svg> Profile</a>
                        <div class="dropdown-divider"></div>
                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
                            <a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault();document.getElementById('logout-form').submit();" >
                            <svg class="icon me-2">
                                <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-account-logout"></use>
                            </svg> Logout</a>
                        </div>
                    </li>
                </ul>
            </div>
        </header>
        <div class="body flex-grow-1 px-3">

            <div class="container-fluid">
                    @yield('content')
            </div>
        </div>
    </div>
</body>
{{-- <script src="{{asset('./node_modules/html5-qrcode/html5-qrcode.min.js')}}"></script> --}}
{{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/html5-qrcode/2.3.4/html5-qrcode.min.js" integrity="sha512-k/KAe4Yff9EUdYI5/IAHlwUswqeipP+Cp5qnrsUjTPCgl51La2/JhyyjNciztD7mWNKLSXci48m7cctATKfLlQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script> --}}
<script src="{{asset('js/html5-qrcode.min.js')}}"></script>
    <script src="{{asset('js/jquery.min.js')}}" ></script>
    <script src="{{asset('vendors/simplebar/js/simplebar.min.js')}}"></script>
    <script src="{{asset('vendors/@coreui/coreui/js/coreui.bundle.min.js')}}"></script>
    <script src="{{asset('js/select2.min.js')}}"></script>
    <script src="{{asset('js/sweetalert2.all.min.js')}}"></script>
    <script src="{{asset('js/boxicons.js')}}"></script>
    <script src="{{asset('js/toaster.min.js')}}" ></script>
    <script src="{{asset('js/excelreport.min.js')}}" ></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script src="https://cdn.datatables.net/2.0.3/js/dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.0.1/js/dataTables.buttons.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.0.1/js/buttons.dataTables.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
    <script>
        $(document).ready(function () {
            $(window).scroll(function () {
                var x=$(document).scrollTop();
                // alert(x);
                if (x>310) {
                    $('#scroll-top').show('slow');
                } else {
                    $('#scroll-top').hide('slow');
                }
            });
            $('#scroll-top').click(function () {
                // e.preventDefault();
                $(window).scrollTop(0,0);
            });
        });
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
