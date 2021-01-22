<!DOCTYPE html>
<html lang="en" ng-app="angularApp">

<head>
    <title> Home - EIS </title>
    <!-- HTML5 Shim and Respond.js IE10 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->

    <!--    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>-->
    <!--    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>-->
    <!--   -->
    <!-- Meta -->

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="Template By ER@Epic Transmedic">
    <meta name="keywords" content="flat ui, admin Admin , Responsive, Landing, Bootstrap, App, Template, Mobile, iOS, Android, apple, creative app">
    <meta name="author" content="#">
    <!-- Favicon icon -->
    <link rel="icon" href="{!! asset('favicon.ico') !!}" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,600,800" rel="stylesheet">
    <!-- Required Fremwork -->
    <!-- <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" integrity="sha384-JcKb8q3iqJ61gNV9KGb8thSsNjpSL0n8PARn9HuZOnIxN0hoP+VmmDGMN5t9UJ0Z" crossorigin="anonymous"> -->
    <link rel="stylesheet" type="text/css" href="{{ asset('comp\bower_components\bootstrap\css\bootstrap.min.css') }}">
    <!-- themify-icons line icon -->
    <link rel="stylesheet" type="text/css" href="{{ asset('comp\assets\icon\themify-icons\themify-icons.css') }}">
    <!-- ico font -->
    <link rel="stylesheet" type="text/css" href="{{ asset('comp\assets\icon\icofont\css\icofont.css') }}">
    <!-- feather Awesome -->
    <link rel="stylesheet" type="text/css" href="{{ asset('comp\assets\icon\feather\css\feather.css') }}">
    <!-- Select 2 css -->
    <link rel="stylesheet" href="{{ asset('comp\bower_components\select2\css\select2.min.css') }}">
    <!-- Font Awesome -->
    <link rel="stylesheet" type="text/css" href="{{ asset('comp\assets\icon\font-awesome\css\font-awesome.min.css') }}">
    <!-- Data Table Css -->
    <link rel="stylesheet" type="text/css" href="{{ asset('comp\bower_components\datatables.net-bs4\css\dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('comp\assets\pages\data-table\css\buttons.dataTables.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('comp\bower_components\datatables.net-responsive-bs4\css\responsive.bootstrap4.min.css') }}">

    <!-- Syntax highlighter Prism css -->
    <link rel="stylesheet" type="text/css" href="{{ asset('comp\assets\pages\prism\prism.css') }}">
    <!-- jpro forms css -->
    <link rel="stylesheet" type="text/css" href="{{ asset('comp\assets\pages\j-pro\css\demo.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('comp\assets\pages\j-pro\css\font-awesome.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('comp\assets\pages\j-pro\css\j-pro-modern.css') }}">
    <!-- Sweetalert Css -->
    <link href="{{ asset('comp/assets/vendors/sweetalert/sweetalert.css') }}" rel="stylesheet" />
    <!-- Date-time picker css -->
    <link rel="stylesheet" type="text/css" href="{{ asset('comp\assets\pages\advance-elements\css\bootstrap-datetimepicker.css') }}">
    <!-- Date-range picker css  -->
    <link rel="stylesheet" type="text/css" href="{{ asset('comp\bower_components\bootstrap-daterangepicker\css\daterangepicker.css') }}">
    <!-- Date-Dropper css -->
    <link rel="stylesheet" type="text/css" href="{{ asset('comp\bower_components\datedropper\css\datedropper.min.css') }}">
    <!-- Color Picker css -->
    <link rel="stylesheet" type="text/css" href="{{ asset('comp\bower_components\spectrum\css\spectrum.css') }}">
    <!-- Mini-color css -->
    <link rel="stylesheet" type="text/css" href="{{ asset('comp\bower_components\jquery-minicolors\css\jquery.minicolors.css') }}">
    <!-- radial chart -->
    <link rel="stylesheet" href="{{ asset('comp\assets\pages\chart\radial\css\radial.css') }}" type="text/css" media="all">
    <!-- Style.css -->
    <link rel="stylesheet" type="text/css" href="{{ asset('comp\assets\pages\j-pro\css\j-forms.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('comp\assets\css\style.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('comp\assets\css\jquery.mCustomScrollbar.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('comp\assets\css\pcoded-horizontal.min.css') }}">
    <link rel="stylesheet" href="{{ asset('comp\assets\css\bootstrapValidator.min.css') }}" />
    <!-- ion icon css -->
    <link rel="stylesheet" type="text/css" href="{{ asset('comp\assets\icon\ion-icon\css\ionicons.min.css') }}">
{{--    <link rel="stylesheet" type="text/css" href="{{ asset('comp\assets\css\styleCustom.css') }}">--}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-material-design/0.5.10/css/ripples.min.css" />
{{--    <link rel="stylesheet" href="http://t00rk.github.io/bootstrap-material-datetimepicker/css/bootstrap-material-datetimepicker.cs" />--}}

{{--    <link href='http://fonts.googleapis.com/css?family=Roboto:400,500' rel='stylesheet' type='text/css'>--}}
{{--    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">--}}
    <link href="{!! asset('css/font-roboto.css') !!}" rel='stylesheet' type='text/css'>
    <link href="{!! asset('css/font-material.css') !!}" rel='stylesheet' type='text/css'>
    <link rel="stylesheet" type="text/css" href="{!! asset('comp\bower_components\animate.css\css\animate.css') !!}">
    <link rel="stylesheet" href="{{ asset('css/styleCustom.css') }}">
    <link rel="stylesheet" href="{{ asset('css/styleAdminLte.css') }}">
    <link href="{{ asset('css/jquery-jvectormap-2.0.5.css') }}" rel="stylesheet">
{{--    adminlte--}}
{{--    <link rel="stylesheet" href="https://adminlte.io/themes/AdminLTE/dist/css/AdminLTE.min.css" />--}}
    <style>
        .overlay{
            display: none;
            position: fixed;
            width: 100%;
            height: 100%;
            top: 0;
            right: 0;
            bottom: 0;
            left: 0;
            outline: 0;
            z-index: 1051;
            background: rgba(255,255,255,0.8) url("{{ URL::asset('load2.gif') }}") center no-repeat;
            background-size: 100px;
        }
        /* Turn off scrollbar when body element has the loading class */
        body.loading{
            overflow: hidden;
        }
        /* Make spinner image visible when body element has the loading class */
        body.loading .overlay{
            display: block;
        }
        #notifikasi {
            cursor: pointer;
            position: fixed;
            right: 0px;
            z-index: 9999;
            bottom: 0px;
            margin-bottom: 22px;
            margin-right: 15px;
            min-width: 300px;
            max-width: 800px;
        }
        .pcoded .pcoded-header .navbar-logo[logo-theme="theme1"] {
            background-color: #8C489F;
        }
        .pcoded .pcoded-header[header-theme="theme6"] {
            background: #8C489F;
        }
        .right {
            text-align: right;
        }

        .input-group {
            margin-bottom: 0px;
        }

        fieldset {
            border: 1px solid #ddd !important;
            margin: 0;
            padding: 10px;
            position: relative;
            border-radius: 4px;
            padding-left: 10px !important;
        }

        legend {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 0px;
            width: 35%;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 5px 5px 5px 10px;
            background-color: #ffffff;
        }

        @media (min-width: 992px) {
            .modal-lg {
                max-width: 1200px;
            }
        }
        .modal {
            z-index: 1999;
        }
    </style>
    <script src="{{ asset('js/jquery/jquery-3.5.1.min.js') }}"></script>
    <script type="text/javascript" src="{!! asset('comp\bower_components\moment\js\moment.js') !!}"></script>

    <script type="text/javascript" src="{!! asset('js/bootstrap-material-datetimepicker.js') !!}"></script>
{{--    <script type="text/javascript" src="{{ asset('comp\bower_components\jquery\js\jquery.min.js') }}"></script>--}}
{{--    <script type="text/javascript" src="{{ asset('comp\bower_components\jquery-ui\js\jquery-ui.min.js') }}"></script>--}}
    <script type="text/javascript" src="{{ asset('comp\bower_components\popper.js\js\popper.min.js') }}"></script>
    <!-- <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script> -->
<!-- <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script> -->
<!-- <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js" integrity="sha384-B4gt1jrGC7Jh4AgTPSdUtOBvfO8shuf57BaghqFfPlYxofvL8/KUEfYiJOMMV+rV" crossorigin="anonymous"></script> -->
    <script type="text/javascript" src="{{ asset('comp\bower_components\bootstrap\js\bootstrap.min.js') }}"></script>
    <!-- j-pro js -->
    <script type="text/javascript" src="{{ asset('comp\assets\pages\j-pro\js\jquery.ui.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('comp\assets\pages\j-pro\js\jquery.maskedinput.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('comp\assets\pages\j-pro\js\jquery.j-pro.js') }}"></script>

    <!-- jquery slimscroll js -->
    <script type="text/javascript" src="{{ asset('comp\bower_components\jquery-slimscroll\js\jquery.slimscroll.js') }}"></script>

    <!-- Bootstrap date-time-picker js -->
    <script type="text/javascript" src="{{ asset('comp\assets\pages\advance-elements\moment-with-locales.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('comp\bower_components\bootstrap-datepicker\js\bootstrap-datepicker.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('comp\assets\pages\advance-elements\bootstrap-datetimepicker.min.js') }}"></script>


    <!-- Date-range picker js -->
    <script type="text/javascript" src="{{ asset('comp\bower_components\bootstrap-daterangepicker\js\daterangepicker.js') }}"></script>


    <!-- Date-dropper js -->
    <script type="text/javascript" src="{{ asset('comp\bower_components\datedropper\js\datedropper.min.js') }}"></script>

    <!-- Color picker js -->
    <script type="text/javascript" src="{{ asset('comp\bower_components\spectrum\js\spectrum.js') }}"></script>
    <script type="text/javascript" src="{{ asset('comp\bower_components\jscolor\js\jscolor.js') }}"></script>

    <!-- Mini-color js -->
    <script type="text/javascript" src="{{ asset('comp\bower_components\jquery-minicolors\js\jquery.minicolors.min.js') }}"></script>

    <!-- modernizr js -->
    <script type="text/javascript" src="{{ asset('comp\bower_components\modernizr\js\modernizr.js') }}"></script>
    <script type="text/javascript" src="{{ asset('comp\bower_components\modernizr\js\css-scrollbars.js') }}"></script>

    <script src="{{ asset('comp\bower_components\datatables.net\js\jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('comp\bower_components\datatables.net-buttons\js\dataTables.buttons.min.js') }}"></script>
    <script src="{{ asset('comp\bower_components\datatables.net-bs4\js\dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('comp\bower_components\datatables.net-responsive\js\dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('comp\bower_components\datatables.net-responsive-bs4\js\responsive.bootstrap4.min.js') }}"></script>

    <!-- i18next.min.js -->
    <script type="text/javascript" src="{{ asset('comp\bower_components\i18next\js\i18next.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('comp\bower_components\i18next-xhr-backend\js\i18nextXHRBackend.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('comp\bower_components\i18next-browser-languagedetector\js\i18nextBrowserLanguageDetector.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('comp\bower_components\jquery-i18next\js\jquery-i18next.min.js') }}"></script>

    <script src="https://cdn.ckeditor.com/ckeditor5/15.0.0/classic/ckeditor.js"></script>

    <!-- SweetAlert Plugin Js -->
    <script src="{{ asset('comp\assets/vendors/sweetalert/sweetalert.min.js') }}"></script>

    <!-- Select 2 js -->
    <script type="text/javascript" src="{{ asset('comp\bower_components\select2\js\select2.full.min.js') }}"></script>

    <!-- Chart js -->
    <script type="text/javascript" src="{{ asset('comp\bower_components\chart.js\js\Chart.js') }}"></script>
    <!-- amchart js -->
    <script src="{{ asset('comp\assets\pages\widget\amchart\amcharts.js') }}"></script>
    <script src="{{ asset('comp\assets\pages\widget\amchart\serial.js') }}"></script>
    <script src="{{ asset('comp\assets\pages\widget\amchart\light.js') }}"></script>

{{--    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-material-design/0.5.10/js/ripples.min.js"></script>--}}
{{--    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-material-design/0.5.10/js/material.min.js"></script>--}}
{{--    <script type="text/javascript" src="http://momentjs.com/downloads/moment-with-locales.min.js"></script>--}}
{{--    <script type="text/javascript" src="http://t00rk.github.io/bootstrap-material-datetimepicker/js/bootstrap-material-datetimepicker.js"></script>--}}
    <!-- Custom js -->
    <link rel="stylesheet" href="{!! asset('css/bootstrap-material-datetimepicker.css') !!}" />
    <script src="{{ asset('comp\assets\js\pcoded.min.js') }}"></script>
    <script src="{{ asset('comp\assets\js\menu\menu-hori-fixed.js') }}"></script>
    <script src="{{ asset('comp\assets\js\jquery.mCustomScrollbar.concat.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('comp\assets\js\script.js') }}"></script>

    <script src="{{ asset('js/public.js') }}"></script>
    <script src="{{ asset('js/highchart.js') }}"></script>
    <!--    <script src="https://code.highcharts.com/highcharts.js"></script>-->
    <!--    <script src="https://code.highcharts.com/modules/drilldown.js"></script>-->
    <script src="{{ asset('js/drilldown.js') }}"></script>
    <script src="{{ asset('js/toastr/toastr.js') }}"></script>
    <script src="{{ asset('js/jvectormap/jquery-jvectormap-2.0.5.min.js') }}"></script>
    <script src="{{ asset('js/jvectormap/jquery-jvectormap-world-mill-en.js') }}"></script>
{{--    <script type="text/javascript" src="{{ asset('adminty\files\assets\js\script.js') }}"></script>--}}

<!-- angular -->
    <script src="{{ asset('node_modules/angular/angular.min.js') }}" type="text/javascript"></script>
    <link rel="stylesheet" type="text/css" href="https://gitcdn.xyz/cdn/angular/bower-material/v1.2.2/angular-material.css">

    <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.8.2/angular-route.min.js" type="text/javascript"></script>
    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/angularjs/1.8.2/angular-animate.min.js"></script>
    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/angularjs/1.8.2/angular-aria.min.js"></script>
    <script src="https://gitcdn.xyz/cdn/angular/bower-material/v1.2.2/angular-material.js" type="text/javascript"></script>
    <script type="text/javascript" src="https://s3-us-west-2.amazonaws.com/s.cdpn.io/t-114/svg-assets-cache.js"></script>
    @toastr_css
    @toastr_js


    @yield('css')
    <script type="text/javascript" src="{{ asset('comp\assets\js\customHelper.js') }}"></script>
    <script type="text/javascript">
        $("#notifikasi").slideDown('slow').delay(3000).slideUp('slow');
        $(document).ready(function() {
            $('.date-custom').bootstrapMaterialDatePicker({
                time: false,
                clearButton: false,
                switchOnClick: true,
                nowButton: true,
                // format :'YY MMM YYYY'
            });
            $('.datetime-custom').bootstrapMaterialDatePicker({
                time: true,
                clearButton: false,
                switchOnClick: true,
                nowButton: true,
                // format :'YY MMM YYYY'
            });

            $(".js-example-basic-single").select2();
            $(".cbo-custom").select2();
            $("#t_Kedatangan").dataTable();
            $("#t_Bed").dataTable();
            $("#t_Layanan").dataTable();
            $("#t_Pemakaian2").dataTable();
            $("#t_Pemakaian").dataTable();
            $("#t_Stok").dataTable();
            $("#t_ObatTren").dataTable();
            $("#t_Pengeluaran").dataTable();
            $("#t_Penerimaan").dataTable();
        })

    </script>

</head>

<body >
    <div id="toast"></div>
    @toastr_render
    @include('template.toast')
    <div id="showLoading" style="z-index:1051;position: fixed;left: 0;
    right: 0;
    bottom: 40%;
    text-align: center;" class="animated loading" >
        <img  height="100" src="{!! asset('load2.gif') !!}"/>
    </div>
    <!-- Pre-loader start -->
    <div class="theme-loader">
        <div class="ball-scale">
            <div class='contain'>
                <div class="ring">
                    <div class="frame"></div>
                </div>

            </div>
        </div>
    </div>
    <!-- Pre-loader end -->
    <div id="pcoded" class="pcoded">
        <div class="pcoded-container">
            <!-- Menu header start -->
            <nav class="navbar header-navbar pcoded-header">
                <div class="navbar-wrapper">
                    <div class="navbar-logo">

                        <a class="mobile-menu" id="mobile-collapse">
                            <i class="feather icon-menu"></i>
                        </a>
                        <a href="{!! route("show_page", ["role" => $_SESSION['role'], "pages" => $r->pages ]) !!}">
                            <span style="font-size: 20px; margin-left: 30px;">
                               {!! 'Insurance' !!}<span style="color: red"></span>
                            </span>
                        </a>
                        <a class="mobile-options">
                            <i class="feather icon-more-horizontal"></i>
                        </a>
                    </div>
                    <div class="navbar-container container-fluid">
                        <ul class="nav-left">
                            <li class="header-search">
                                <div class="main-search morphsearch-search">
                                    <div class="input-group">
                                        <span class="input-group-addon search-close"><i class="feather icon-x"></i></span>
                                        <input type="text" class="form-control">
                                        <span class="input-group-addon search-btn"><i class="feather icon-search"></i></span>
                                    </div>
                                </div>
                            </li>
                            <li>
                                <a href="#!" onclick="javascript:toggleFullScreen()">
                                    <i class="feather icon-maximize full-screen"></i>
                                </a>
                            </li>
                        </ul>
                        <ul class="nav-right">
                            <!-- <li class="header-notification">
                                <div class="dropdown-primary dropdown">
                                <div class="dropdown-toggle" data-toggle="dropdown">
                                    <i class="feather icon-bell"></i>
                                    <span class="badge bg-c-pink">5</span>
                                </div>
                                </div>
                            </li>
                            <li class="header-notification">
                                <div class="dropdown-primary dropdown">
                                <div class="displayChatbox dropdown-toggle" data-toggle="dropdown">
                                    <i class="feather icon-message-square"></i>
                                    <span class="badge bg-c-green">3</span>
                                </div>
                                </div>
                            </li> -->
                            <li class="user-profile header-notification">
                                <div class="dropdown-primary dropdown">
                                    <div class="dropdown-toggle" data-toggle="dropdown">
                                        <i class="feather icon-user" style="font-size: 20px;"></i>
                                        <span>{{ isset($_SESSION['namaLengkap']) ?$_SESSION['namaLengkap'] : 'Administrator' }}</span>
                                        <i class="feather icon-chevron-down"></i>
                                    </div>
                                    <ul class="show-notification profile-notification dropdown-menu" data-dropdown-in="fadeIn" data-dropdown-out="fadeOut">
                                        <li>
                                            <a href="{{ route('logout') }}">
                                                <i class="feather icon-log-out"></i> Logout
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>
            <!-- Menu header end -->
            <div class="pcoded-main-container">
                <nav class="pcoded-navbar">
                    <div class="pcoded-inner-navbar">
                        <ul class="pcoded-item pcoded-left-item">
                            @include('template.menutop')
                        </ul>
                    </div>
                </nav>
                <div class="pcoded-wrapper">
                    <div class="pcoded-content">
                        <div class="pcoded-inner-content">
                            <!-- Main-body start -->
                            <div class="main-body">
                                <div id="notifikasi"></div>
                                @yield('content-body')
                            </div>
                            <!-- Main-body end -->
                            <div id="styleSelector"> </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>

<script>
    /*
    * angular initialize
    */
    var baseUrl = {!! json_encode(url('/')) !!}
    
    var angular = angular.module('angularApp', [ 'ngMaterial'], function($interpolateProvider) {
        $interpolateProvider.startSymbol('@{{');
        $interpolateProvider.endSymbol('}}');
    }).factory('httpService', function ($http,$q) {
            return {
                get: function (url) {
                    $("#showLoading").show()
                    var deffer = $q.defer();
                    $http.get(baseUrl + '/'+ url , {
                        headers: {
                            'Content-Type': 'application/json',
                        }
                    }).then(function successCallback(response) {
                        deffer.resolve(response);
                        $("#showLoading").hide()
                    }, function errorCallback(response) {
                        deffer.reject(response);
                        $("#showLoading").hide()
                    });
                    return deffer.promise;
                },
                post: function (url, data) {
                    $("#showLoading").show()
                    var deffer = $q.defer();
                    var req = {
                        method: 'POST',
                        url: baseUrl + '/'+ url,
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        data: data
                    }
                    $http(req).then(function successCallback(response, a, b) {
                        $("#showLoading").hide()
                        if(response.data.message != undefined){
                            add_toast(response.data.message,'success')
                        }
                        deffer.resolve(response);
                    }, function errorCallback(response) {
                        $("#showLoading").hide()
                        if(response.data.message!= undefined){
                            add_toast(response.data.message,'error')
                        }
                        deffer.reject(response);

                    });
                    return deffer.promise;
                },
                put: function (url, data) {
                    $("#showLoading").show()
                    var deffer = $q.defer();
                    var req = {
                        method: 'PUT',
                        url: baseUrl + '/'+ url,
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        data: data
                    }
                    $http(req).then(function successCallback(response, a, b) {
                        deffer.resolve(response);
                        $("#showLoading").hide()
                    }, function errorCallback(response) {
                        deffer.reject(response);
                        $("#showLoading").hide()
                    });
                    return deffer.promise;
                },
                delete: function (url) {
                    var deffer = $q.defer();
                    var req = {
                        method: 'DELETE',
                        url: baseUrl + '/'+ url,
                        headers: {
                            'Content-Type': 'application/json',
                        }
                    }
                    $http(req).then(function successCallback(response, a, b) {
                        deffer.resolve(response);
                        $("#showLoading").hide()
                    }, function errorCallback(response) {
                        deffer.reject(response);
                        $("#showLoading").hide()
                    });
                    return deffer.promise;
                },
            }
    });

    $("#showLoading").hide()
    // $("body").addClass("loading");
    $(document).on({
        ajaxStart: function(){
            $("#showLoading").show()
            // $("body").addClass("loading");
        },
        ajaxStop: function(){
            $("#showLoading").hide()
            // $("body").removeClass("loading");
        }

    });
</script>
@yield('javascript')


</html>
