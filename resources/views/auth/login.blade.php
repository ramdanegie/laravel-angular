<!doctype html>
<html lang="en" ng-app="myApp">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href="https://fonts.googleapis.com/css?family=Roboto:300,400&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('logins/fonts/icomoon/style.css') }}">
    <link rel="stylesheet" href="{{ asset('logins/css/owl.carousel.min.css')}}">
    <link rel="stylesheet" href="{{ asset('logins/css/bootstrap.min.css')}}">

    <link rel="stylesheet" href="{{ asset('logins/css/style.css')}}">
    <link rel="stylesheet" type="text/css" href="{{asset('css/toastr.css')}}">

    <title>Login || Transmedic</title>
</head>
<style>
    @font-face {
        font-family: 'Comfortaa';
        font-style: normal;
        font-weight: 400;
        font-display: swap;
        src: {{ URL::asset('fonts/Comporta_web_latin.woff2') }}  format("woff");
        unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+2000-206F, U+2074, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;
        /* font-family: 'Comfortaa';
        src: url("../font/Comporta_web.woff2") format("woff"); */
    }

    body {
        background-color: #f8fafb;
        font-size: .875em;
        overflow-x: hidden;
        color: #353c4e;
        /*font-family: 'Comfortaa', cursive, 'OpenSans-Regular';*/
        -webkit-font-smoothing: antialiased;
        background-attachment: fixed;
    }

    /*.form-control {*/
    /*    font-family: 'Comfortaa', cursive, 'OpenSans-Regular';*/
    /*}*/

    /*.btn {*/
    /*    font-family: 'Comfortaa', cursive, 'OpenSans-Regular';*/
    /*}*/

    /*button, select, html, textarea, input {*/
    /*    font-family: 'Comfortaa', cursive, 'OpenSans-Regular';*/
    /*}*/

    @media (max-width: 658px) {
        /*.img-left {*/
        /*    flex: 1 1 0%;*/
        /*    height: 100vh;*/
        /*    position: sticky;*/
        /*    top: 0px;*/
        /*}*/

        .class-logo {
            width: 120px;
            margin-bottom: 10px;
        }
        .justify-content-center {
            text-align: center;
        }
        .img-left{
            display: none;
        }
    }

    @media (min-width: 1280px) {
        .class-logo {
            width: 220px;
            /* margin-bottom: 10px; */
        }
        .justify-content-center {
            text-align: center;
        }
    }
</style>
<body>
<div class="content">
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <img src="{{ asset('logins/images/undraw_remotely_2j6y.svg')}}" alt="Image" class="img-left img-fluid">
            </div>
            <div class="col-md-6 contents">
                <div class="row justify-content-center">
                    <div class="col-md-8">
                        <img src="{{ asset('images/Transmedic HIS.png')}}" alt="Image" class="img-fluid class-logo">
                        <hr/>
                        <div class="mb-4">
                            <h3>RSUD V-3</h3>
                            <h5 class="mb-4" style="color:#b3b3b3;">Hospital Information System</h5>
                            <!-- <h5 class="mb-4">RS<span style="color: red">D</span>C</h5> -->
                        </div>
                        <form method="POST" action="{{ route('login_validation') }}">
                            <div class="form-group first">
                                <label for="username">Nama User</label>
                                <input type="text" name="username" class="form-control" id="username" value="{{request()->input('username')}}">
                            </div>
                            <div class="form-group last mb-4">
                                <label for="password">Kata Sandi</label>
                                <input type="password" class="form-control" name="password" id="password" required>
                            </div>
                            <!-- <span id="captcha" hidden></span>
                            <div class="form-group last mb-4">
                                <div class="g-recaptcha" data-callback="onReturnCallback" data-sitekey="6LdwosIZAAAAALF5LATNjFt7raOiBmI_37rB0bVe" ></div>
                               
                            </div> -->
                            <div class="d-flex mb-5 align-items-center">
                                <label class="control control--checkbox mb-0"><span class="caption">Remember me</span>
                                    <input type="checkbox" checked="checked"/>
                                    <div class="control__indicator"></div>
                                </label>
                                <!-- <span class="ml-auto"><a href="#" class="forgot-pass">Forgot Password</a></span> -->
                            </div>

                            <button type="submit" class="btn btn-block btn-primary">Log In</button>


                            {{--                            <span class="d-block text-left my-4 text-muted">&mdash; or login with &mdash;</span>--}}
                            {{--                            <div class="social-login">--}}
                            {{--                                <a href="#" class="facebook">--}}
                            {{--                                    <span class="icon-facebook mr-3"></span>--}}
                            {{--                                </a>--}}
                            {{--                                <a href="#" class="twitter">--}}
                            {{--                                    <span class="icon-twitter mr-3"></span>--}}
                            {{--                                </a>--}}
                            {{--                                <a href="#" class="google">--}}
                            {{--                                    <span class="icon-google mr-3"></span>--}}
                            {{--                                </a>--}}
                            {{--                            </div>--}}
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<script src="{{ asset('logins/js/jquery-3.3.1.min.js')}}"></script>
<script src="{{ asset('logins/js/popper.min.js')}}"></script>
<script src="{{ asset('logins/js/bootstrap.min.js')}}"></script>
<script src="{{ asset('logins/js/main.js')}}"></script>
<script src="{{asset('vendors/toastr/toastr.min.js')}}"></script>


<script src="https://www.google.com/recaptcha/api.js" async="" defer=""></script>
{{--   end  angular --}}
<script >
    var onReturnCallback = function(response) {
        var captchaResponse = response
        document.getElementById("btnMasuk").disabled = false;
        document.getElementById("captcha").innerHTML = captchaResponse;
    };
    // var width = $('.g-recaptcha').parent().width();
    // if (width < 302) {
    //     var scale = width / 302;
    //     $('.g-recaptcha').css('transform', 'scale(' + scale + ')');
    //     $('.g-recaptcha').css('-webkit-transform', 'scale(' + scale + ')');
    //     $('.g-recaptcha').css('transform-origin', '0 0');
    //     $('.g-recaptcha').css('-webkit-transform-origin', '0 0');
    // }

    var baseUrl = {!! json_encode(url('/')) !!}

    // var angular = angular.module('myApp', [ 'ngMaterial'], function($interpolateProvider) {
    //     $interpolateProvider.startSymbol('@{{');
    //     $interpolateProvider.endSymbol('}}');
    //
    // }).factory('httpService', function ($http,$q) {
    //
    // })
    @if(Session::has('message'))
    var type = "{{ Session::get('alert-type', 'info') }}";
    switch (type) {
        case 'info':
            toastr.info("{{ Session::get('message') }}","Info");
            break;

        case 'warning':
            toastr.warning("{{ Session::get('message') }}","Info");
            break;

        case 'success':
            toastr.success("{{ Session::get('message') }}","Info");
            break;

        case 'error':
            toastr.error("{{ Session::get('message') }}","Info");
            break;
    }
    @endif

</script>
</body>
</html>
