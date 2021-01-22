@extends('template.template3')
@section('css')
    <style>
        .pcoded .pcoded-inner-content {
            padding: 10px 0 10px 0;
        }

        .class-icon {
            font-size: 50px;

            padding: 5px;
            border-radius: 5px;
            background: #bcbcbc8a;
        }
        .pad{
            padding-top: 3rem;
        }
        @media (min-width: 992px) {
            .pad {
                padding-top: 1.8rem;
            }
        }
        .modal-lg .kons{
            width:1140px;
        }
        @media only screen and (max-width: 575px) {
            .latest-update-card .card-block .latest-update-box .update-meta {
                z-index: 2;
                min-width: 0;
                text-align: left !important;
                margin-bottom: 15px;
                border-top: 1px solid #f1f1f1;
                padding-top: 15px;
            }
        }
    </style>
@endsection
@section('content-body')
    <div class="page-wrapper pad" ng-controller="RegistrasiCtrl" >
        <div class="page-body">
            <div class="card">
                <div class="card-header">
                    <h5>Registrasi</h5>
                </div>
                <div class="card-block tab-icon">
                    <form action="{!! route("show_page", ["role" => $_SESSION['role'], "pages" => $r->pages ]) !!}" method="get">
                        <div class="row">
                        <div class="col-lg-2" >
                            <md-input-container class="md-block" flex-gt-sm>
                                <label>First name</label>
                                <input ng-model="user.firstName">
                            </md-input-container>
                        </div>
                        <div class="col-lg-2" >
                            <md-input-container class="md-block" flex-gt-sm>
                                <label>State</label>
                                <md-select ng-model="user.state">
                                    <md-option ng-repeat="state in states" value="@{{state.abbrev}}">
                                        @{{state.abbrev}}
                                    </md-option>
                                </md-select>
                            </md-input-container>
                        </div>
                        <div class="col-lg-2" >
                            <md-input-container class="md-block" flex-gt-sm>
                                <label>First name</label>
                                <input ng-model="user.firstName">
                            </md-input-container>
                        </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('javascript')
    <script>
        angular.controller('RegistrasiCtrl',function ($scope, $http, httpService) {
            $scope.states = ('AL AK AZ AR CA CO CT DE FL GA HI ID IL IN IA KS KY LA ME MD MA MI MN MS ' +
                'MO MT NE NV NH NJ NM NY NC ND OH OK OR PA RI SC SD TN TX UT VT VA WA WV WI ' +
                'WY').split(' ').map(function(state) {
                return {abbrev: state};
            });
            // httpService.get('get-count-pegawai').then(function (e) {
            // })
            // httpService.post('tes-post',{}).then(function (e) {
            // })
        });
    </script>
@endsection
