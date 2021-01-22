<!DOCTYPE html>
<!--
* CoreUI - Free Bootstrap Admin Template
* @version v3.0.0-alpha.1
* @link https://coreui.io
* Copyright (c) 2019 creativeLabs Łukasz Holeczek
* Licensed under MIT (https://coreui.io/license)
-->

<html lang="en" ng-app="myApp">
<head>
    <!--    <base href="./">-->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <meta name="description" content="UI Template By EpicTeam@Transmedic">

    <meta name="keyword" content="Bootstrap,Admin,Template,Open,Source,jQuery,CSS,HTML,RWD,Dashboard">
    <title>EIS - Login</title>

    <meta name="theme-color" content="#ffffff">
    <!-- Icons-->
    <script src="{{ asset('js/jquery/jquery-3.5.1.min.js') }}"></script>
    <link href="{{ asset('coreui/css/free.min.css') }}" rel="stylesheet"> <!-- icons -->
<!--    <link href="{{ asset('coreui/css/flag-icon.min.css') }}" rel="stylesheet">-->
    <!-- Main styles for this application-->
    <link href="{{ asset('coreui/css/style.css') }}" rel="stylesheet">
    <!--    <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.6.9/angular.min.js" type="text/javascript"></script> -->
<!-- <script src="{{ asset('node_modules/angular/angular.js') }}" type="text/javascript"></script> -->
    <script src="{{ asset('node_modules/angular/angular.min.js') }}" type="text/javascript"></script>

    <link rel="stylesheet" type="text/css" href="https://gitcdn.xyz/cdn/angular/bower-material/v1.2.2/angular-material.css">

    <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.8.2/angular-route.min.js" type="text/javascript"></script>
    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/angularjs/1.8.2/angular-animate.min.js"></script>
    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/angularjs/1.8.2/angular-aria.min.js"></script>
    <script src="https://gitcdn.xyz/cdn/angular/bower-material/v1.2.2/angular-material.js" type="text/javascript"></script>
    <script type="text/javascript" src="https://s3-us-west-2.amazonaws.com/s.cdpn.io/t-114/svg-assets-cache.js"></script>
<!-- <script src="{{ asset('node_modules/angular/index.js') }}" type="text/javascript"></script> -->
    <!-- Global site tag (gtag.js) - Google Analytics-->
    <!--    <script async="" src="https://www.googletagmanager.com/gtag/js?id=UA-118965717-3"></script>-->
    @toastr_css
    @toastr_js
    <script>
        var angular = angular.module('myApp', ['ngMaterial']);


        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }
        gtag('js', new Date());
        // Shared ID
        gtag('config', 'UA-118965717-3');
        // Bootstrap ID
        gtag('config', 'UA-118965717-5');
    </script>
    <script src="{{ asset('js/public.js') }}" defer></script>
<!--    <link href="{{ asset('coreui/css/coreui-chartjs.css') }}" rel="stylesheet">-->

</head>
<style>
    .bg-primary {
        background-color: #00bcd4 !important;
    }
    .btn-info.disabled, .btn-info:disabled {
        color: #fff;
        background-color: #8C489F;
        border-color: #8C489F;
    }
    .bg-primary {
        background-color: #8C489F !important;
    }
    .btn-info {
        color: #fff;
        background-color: #8C489F;
        border-color: #8C489F;
    }
</style>
<body ng-controller="LoginCtrl" class="c-app flex-row align-items-center" style="
    background: url({!! asset('images/cover1.jpg') !!}) top/cover no-repeat fixed;
    display: flex;
    flex-direction: row;
    padding: 0;
    height: 100vh;">
<div id="toast"></div>
@toastr_render
@yield('content')

<!-- CoreUI and necessary plugins-->
<script src="{{ asset('coreui/js/coreui.bundle.min.js') }}"></script>
<!-- <script src="https://www.google.com/recaptcha/api.js" async="" defer=""></script> -->
@yield('javascript')

</body>
</html>


