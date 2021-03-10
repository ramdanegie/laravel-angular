<!DOCTYPE html>
<html lang="en" ng-app="angularApp">

<head>
    <title> Home || Transmedic </title>


    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="Template By ER@Epic Transmedic">
    <meta name="keywords"
          content="flat ui, admin Admin , Responsive, Landing, Bootstrap, App, Template, Mobile, iOS, Android, apple, creative app">
    <meta name="author" content="#">
    <!-- Favicon icon -->
    <link rel="icon" href="{!! asset('favicon.ico') !!}" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,600,800" rel="stylesheet">
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

    <link rel="stylesheet" type="text/css" href="{{ asset('comp\assets\css\style.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('comp\assets\css\jquery.mCustomScrollbar.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('comp\assets\css\pcoded-horizontal.min.css') }}">

    <!-- ion icon css -->
    <link rel="stylesheet" type="text/css" href="{{ asset('comp\assets\icon\ion-icon\css\ionicons.min.css') }}">

    <link rel="stylesheet" type="text/css" href="{!! asset('comp\bower_components\animate.css\css\animate.css') !!}">
    <link rel="stylesheet" href="{{ asset('css/styleCustom.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('comp/assets/pages/treeview/treeview.css') }}">
    <script src="{{ asset('js/jquery/jquery-3.5.1.min.js') }}"></script>
    <script type="text/javascript" src="{!! asset('comp/bower_components/moment/js/moment.js') !!}"></script>
    <script type="text/javascript" src="{{ asset('comp/bower_components/popper.js/js/popper.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('comp/bower_components/bootstrap/js/bootstrap.min.js') }}"></script>

    <!-- jquery slimscroll js -->
    <script type="text/javascript" src="{{ asset('comp/bower_components/jquery-slimscroll/js/jquery.slimscroll.js') }}"></script>
    <!-- Bootstrap date-time-picker js -->

    <script type="text/javascript" src="{{ asset('comp/bower_components/bootstrap-datepicker/js/bootstrap-datepicker.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('comp/assets/pages/advance-elements/bootstrap-datetimepicker.min.js') }}"></script>
    <!-- Select 2 js -->
    <script type="text/javascript" src="{{ asset('comp/bower_components/select2/js/select2.full.min.js') }}"></script>
<!-- Custom js -->
    <link rel="stylesheet" href="{!! asset('css/bootstrap-material-datetimepicker.css') !!}"/>
    <script src="{{ asset('comp/assets/js/pcoded.min.js') }}"></script>
    <script src="{{ asset('comp/assets/js/menu/menu-hori-fixed.js') }}"></script>
    <script src="{{ asset('comp/assets/js/jquery.mCustomScrollbar.concat.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('comp/assets/js/script.js') }}"></script>
    <script src="{{ asset('js/public.js') }}"></script>
    <script src="{{ asset('js/toastr/toastr.js') }}"></script>

<!-- angular -->
    <script src="{{ asset('node_modules/angular/angular.min.js') }}" type="text/javascript"></script>
    <link rel="stylesheet" type="text/css" href="{{ asset('node_modules/angular-material/angular-material.css') }}">
    <script src="{{ asset('node_modules/angular/angular-route.min.js') }}" type="text/javascript"></script>
    <script type="text/javascript" src="{{ asset('node_modules/angular/angular-animate.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('node_modules/angular/angular-aria.min.js') }}"></script>
    <script src="{{ asset('node_modules/angular/angular-material.js') }}" type="text/javascript"></script>
    <script type="text/javascript" src="{{ asset('comp/bower_components/jstree/js/jstree.min.js') }}"></script>

    @toastr_css
    @toastr_js
    @yield('css')
    <script type="text/javascript" src="{{ asset('comp\assets\js\customHelper.js') }}"></script>
    <script type="text/javascript">
        $("#notifikasi").slideDown('slow').delay(3000).slideUp('slow');
        $(document).ready(function () {
            // $('.date-custom').bootstrapMaterialDatePicker({
            //     time: false,
            //     clearButton: false,
            //     switchOnClick: true,
            //     nowButton: true,
            //     // format :'YY MMM YYYY'
            // });
            // $('.datetime-custom').bootstrapMaterialDatePicker({
            //     time: true,
            //     clearButton: false,
            //     switchOnClick: true,
            //     nowButton: true,
            //     // format :'YY MMM YYYY'
            // });


            $(".cbo-custom").select2();
        })

    </script>

</head>

<body>
<div id="toast"></div>
@toastr_render
@include('template.toast')
<div id="showLoading" style="z-index:1051;position: fixed;left: 0;
    right: 0;
    bottom: 40%;
    text-align: center;" class="animated loading">
    <img height="100" src="{!! asset('load2.gif') !!}"/>
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
                <div class="navbar-logo nav-atas" style="width: 300px;">

                    <a class="mobile-menu" id="mobile-collapse">
                        <i class="feather icon-menu"></i>
                    </a>
                    <a href="{!! route("show_page", ["role" => $_SESSION['role'], "pages" => $r->pages ]) !!}">
                            <span class="span-namaprofile">
                               {!! $_SESSION['namaProfile'] !!}<span style="color: red"></span>
                            </span>
                        <p class="subtitle-2">TRANS<span style="color: red">M</span>EDIC</p>
                    </a>
                    <a class="mobile-options">
                        <i class="feather icon-more-horizontal"></i>
                    </a>
                </div>
                <div class="navbar-container container-fluid">
                    <ul class="nav-left">
                        <li class="header-search" ng-controller="globalCtrl">
                            <div class="main-search morphsearch-search">
                                <div class="input-group">
                                    <span class="input-group-addon search-close"><i class="feather icon-x"></i></span>
                                    <input type="text" class="form-control ui-autocomplete-input" id="globalSearch" ng-model="globalSearch"  ng-change="changeGlobal(globalSearch)">
                                    <!-- <autocomplete class="form-control" ng-model="globalSearch" data="listMenu" on-type="changeGlobal"></autocomplete> -->
                                    <span class="input-group-addon search-btn"><i
                                            class="feather icon-search"></i></span>
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
                                <ul class="show-notification profile-notification dropdown-menu"
                                    data-dropdown-in="fadeIn" data-dropdown-out="fadeOut">
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
                        <div id="styleSelector"></div>
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
    var baseUrl = {!! json_encode(url('/')) !!};
        baseUrl = baseUrl +'/service/transmedic';
    var angular = angular.module('angularApp', ['ngMaterial'], function ($interpolateProvider) {
        // debugger
        // $scope.$watch('globalSearch', function (newValue, oldValue) {
        //     alert(newValue)
        // })


        $interpolateProvider.startSymbol('@{{');
        $interpolateProvider.endSymbol('}}');
    }).factory('httpService', function ($http, $q) {
        return {
            get: function (url) {
                $("#showLoading").show()
                var deffer = $q.defer();
                $http.get(baseUrl + '/' + url, {
                    headers: {
                        'Content-Type': 'application/json',
                        'X-AUTH-TOKEN': '{{ $_SESSION["tokenLogin"] }}'
                    }
                }).then(function successCallback(response) {
                    deffer.resolve(response);
                    $("#showLoading").hide()
                }, function errorCallback(response) {
                    add_toast(response.data.message, 'error')
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
                    url: baseUrl + '/' + url,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-AUTH-TOKEN': '{{ $_SESSION["tokenLogin"] }}'
                    },
                    data: data
                }
                $http(req).then(function successCallback(response, a, b) {
                    $("#showLoading").hide()
                    if (response.data.message != undefined) {
                        add_toast(response.data.message, 'success')
                    } else {
                        if (response.data.messages != undefined) {
                            add_toast(response.data.messages, 'success')
                        }
                    }

                    deffer.resolve(response);
                }, function errorCallback(response) {
                    $("#showLoading").hide()
                    if (response.data.message != undefined) {
                        add_toast(response.data.message, 'error')
                    } else {
                        if (response.data.messages != undefined) {
                            add_toast(response.data.messages, 'success')
                        }
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
                    url: baseUrl + '/' + url,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-AUTH-TOKEN': '{{ $_SESSION["tokenLogin"] }}'
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
                    url: baseUrl + '/' + url,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-AUTH-TOKEN': '{{ $_SESSION["tokenLogin"] }}'
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
    }).controller('globalCtrl', ['$scope','httpService', function($scope,httpService) {
        //
        // $("#globalSearch").autocomplete({
        //     source: function (request, response) {
        //           httpService.get('global/get-menu?nama='+ request.term).then(function (e) {
        //             //   debugger
        //                 if(e.data.length> 0){
        //                     response($.map(e.data, function (item) {
        //                         return {
        //                             label: item.objekmodulaplikasi,
        //                             value: item.alamaturlform.replace('#/',''),
        //                             kode: item.alamaturlform.replace('#/','')
        //                         };
        //                     }))
        //                 }else { $('#globalSearch').val(''); }
        //          })
        //
        //     },
        //     minLength: 3,
        //     messages: {
        //         noResults: '',
        //         results: function (resultsCount) { }
        //     },
        //     select: function (event, ui) {
        //         $('#globalSearch').val(ui.item.kode);
        //
        //      }
        //     });

            $scope.changeGlobal = function(typed){
                $scope.listMenu = []
                httpService.get('global/get-menu?nama='+ typed).then(function (e) {
                    if(e.data.length> 0){
                        $scope.listMenu  =[]
                        for (let i = 0; i < e.data.length; i++) {
                            const element = e.data[i];
                            $scope.listMenu .push(element.objekmodulaplikasi)
                        }
                        // $scope.listMenu = e.data
                    }
                })

            }
    //   $scope.changeGlobal = function(a) {
    //       alert(a)

    //   };
    }]);;

    $("#showLoading").hide()
    // $("body").addClass("loading");
    $(document).on({
        ajaxStart: function () {
            $("#showLoading").show()
            // $("body").addClass("loading");
        },
        ajaxStop: function () {
            $("#showLoading").hide()
            // $("body").removeClass("loading");
        }

    });
</script>
@yield('javascript')
@stack('head')
</html>
