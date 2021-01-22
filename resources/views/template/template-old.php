<!DOCTYPE html>
<html lang="en">

<head>
    <title>Executive Information System </title>
    <!-- HTML5 Shim and Respond.js IE10 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->

    <!--    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>-->
    <!--    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>-->

    <!-- Meta -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="#">
    <meta name="keywords" content="Admin , Responsive, Landing, Bootstrap, App, Template, Mobile, iOS, Android, apple, creative app">
    <meta name="author" content="#">
    <!-- Favicon icon -->

    <link rel="icon" href="{{ asset('adminty/files/assets/image/favicon.ico') }}" type="image/x-icon">
    <!-- Google font-->
    <link href="{!! asset('fonts/opensans.css') !!}" rel="stylesheet">
    <!-- Required Fremwork -->
    <link rel="stylesheet" type="text/css" href="{{ asset('adminty\files\bower_components\bootstrap\css\bootstrap.min.css') }}">
    <!-- feather Awesome -->
    <link rel="stylesheet" type="text/css" href="{{ asset('adminty\files\assets\icon\feather\css\feather.css') }}">
    <!-- Style.css -->
    <link rel="stylesheet" type="text/css" href="{{ asset('fontawesome.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('adminty\files\assets\css\style.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('adminty\files\assets\css\jquery.mCustomScrollbar.css') }}">
    <!-- Font Awesome -->
    <link rel="stylesheet" type="text/css" href="{{ asset('adminty\icon\font-awesome\css\font-awesome.min.css') }}">
    <script src="{{ asset('js/jquery/jquery-3.5.1.min.js') }}"></script>
    <link href="{{ asset('css/jquery-jvectormap-2.0.5.css') }}" rel="stylesheet">

    @toastr_css


    <!--    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.4/toastr.min.js"></script>-->
    <!--    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.4/toastr.min.css">-->
</head>

<body>

<!-- Pre-loader start -->
<div class="theme-loader">
    <div class="ball-scale">
        <div class='contain'>
            <div class="ring">
                <div class="frame"></div>
            </div>
            <div class="ring">
                <div class="frame"></div>
            </div>
            <div class="ring">
                <div class="frame"></div>
            </div>
            <div class="ring">
                <div class="frame"></div>
            </div>
            <div class="ring">
                <div class="frame"></div>
            </div>
            <div class="ring">
                <div class="frame"></div>
            </div>
            <div class="ring">
                <div class="frame"></div>
            </div>
            <div class="ring">
                <div class="frame"></div>
            </div>
            <div class="ring">
                <div class="frame"></div>
            </div>
            <div class="ring">
                <div class="frame"></div>
            </div>
        </div>
    </div>
</div>
<!-- Pre-loader end -->
<div id="pcoded" class="pcoded">
    <div class="pcoded-overlay-box"></div>
    <div class="pcoded-container navbar-wrapper">

        @include('template.header')

        @include('template.sidebar')

        <div class="pcoded-main-container">
            <div class="pcoded-wrapper">
                @include('template.navbar')
                <div class="pcoded-content">
                    <div class="pcoded-inner-content">
                        <div class="main-body">
                            <div id="notifikasi">

                            </div>
                            @yield('content-body')

                            <div id="styleSelector"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Warning Section Ends -->
<!-- Required Jquery -->
<script type="text/javascript" src="{{ asset('adminty\files\bower_components\jquery\js\jquery.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('adminty\files\bower_components\jquery-ui\js\jquery-ui.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('adminty\files\bower_components\popper.js\js\popper.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('adminty\files\bower_components\bootstrap\js\bootstrap.min.js') }}"></script>
<!-- jquery slimscroll js -->
<script type="text/javascript" src="{{ asset('adminty\files\bower_components\jquery-slimscroll\js\jquery.slimscroll.js') }}"></script>
<!-- modernizr js -->
<script type="text/javascript" src="{{ asset('adminty\files\bower_components\modernizr\js\modernizr.js') }}"></script>
<!-- Chart js -->
<script type="text/javascript" src="{{ asset('adminty/files/bower_components/chart.js/js/Chart.js') }}"></script>
<!-- amchart js -->
<script src="{{ asset('adminty\files\assets\pages\widget\amchart\amcharts.js') }}"></script>
<script src="{{ asset('adminty\files\assets\pages\widget\amchart\serial.js') }}"></script>
<script src="{{ asset('adminty\files\assets\pages\widget\amchart\light.js') }}"></script>
<script src="{{ asset('adminty\files\assets\js\jquery.mCustomScrollbar.concat.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('adminty/files/assets/js/SmoothScroll.js') }}"></script>
<script src="{{ asset('adminty\files\assets\js\pcoded.min.js') }}"></script>
<!-- custom js -->
<script src="{{ asset('adminty\files\assets\js\vartical-layout.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('adminty\files\assets\pages\dashboard\custom-dashboard.js') }}"></script>
<script type="text/javascript" src="{{ asset('adminty\files\assets\js\script.min.js') }}"></script>
<script src="{{ asset('js/jvectormap/jquery-jvectormap-2.0.5.min.js') }}"></script>
<script src="{{ asset('js/jvectormap/jquery-jvectormap-world-mill-en.js') }}"></script>
<script type="text/javascript" src="{!! asset('adminty/files/assets/pages/chart/chartjs/chartjs-custom.js') !!}"></script>
<script type="text/javascript" src="{!! asset('adminty/files/bower_components/chart.js/js/Chart.js') !!}"></script>
<!-- Global site tag (gtag.js) - Google Analytics -->
<script async="" src="https://www.googletagmanager.com/gtag/js?id=UA-23581568-13"></script>
<script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());

    gtag('config', 'UA-23581568-13');
</script>

@yield('javascript')
@toastr_js
@toastr_render
</body>

</html>
