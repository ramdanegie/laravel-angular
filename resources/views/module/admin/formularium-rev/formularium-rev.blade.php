@extends('template.template3')
@section('css')
    <style typ[>
        .pcoded .pcoded-inner-content {
            padding: 10px 0 10px 0;
        }

        .class-icon {
            font-size: 50px;

            padding: 5px;
            border-radius: 5px;
            background: #bcbcbc8a;
        }

        .modal-lg .kons {
            width: 1140px;
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

        .jstree-default .jstree-search {
            font-style: italic;
            /* color: beige; */
            font-weight: bold;
            border-radius: 5px;
            background-color: #e4f974;
        }

        md-tabs {
            display: block;
            margin: 0;
            border-radius: 5px;
            overflow: hidden;
            position: relative;
            flex-shrink: 0;
            border: 1px solid #77777726;
        }

        md-tabs.md-default-theme.md-primary > md-tabs-wrapper, md-tabs.md-primary > md-tabs-wrapper {
            background-color: #777777bf;
        }

        md-tabs.md-default-theme.md-primary > md-tabs-wrapper > md-tabs-canvas > md-pagination-wrapper > md-ink-bar, md-tabs.md-primary > md-tabs-wrapper > md-tabs-canvas > md-pagination-wrapper > md-ink-bar {
            color: #6c63ffb8;
            background: #6c63ffb8;
        }

        md-ink-bar {
            position: absolute;
            left: auto;
            right: auto;
            bottom: 0;
            height: 5px;
        }

        md-content h1:first-child {
            margin-top: 0;
        }

        md-content md-tabs .demo-tab {
            padding: 25px;
            text-align: center;
        }

        md-content md-tabs .demo-tab > div > div {
            padding: 25px;
            box-sizing: border-box;
        }

        md-content md-tabs md-tab[disabled] {
            opacity: 0.5;
        }

        md-content form {
            padding-top: 0;
        }

        md-content form div[flex] {
            position: relative;
        }

        md-content form div[flex] h2.md-subhead {
            position: absolute;
            bottom: 0;
            left: 0;
            margin: 0;
            font-weight: 500;
            text-transform: uppercase;
            line-height: 35px;
            white-space: nowrap;
        }

        md-content form md-button.add-tab {
            margin-right: 0;
            transform: translateY(5px);
        }

        md-content form md-input-container {
            padding-bottom: 0;
        }


        md-tabs {
            display: block;
            margin: 0;
            border-radius: 5px;
            overflow: hidden;
            position: relative;
            flex-shrink: 0;
            border: 1px solid #77777726;
            height: 450px;
        }

        media-left i {
            margin-right: 0;
        }

        .class-icon {
            font-size: 50px;
            padding: 5px;
            border-radius: 5px;
            background: #bcbcbc8a;
        }

        .tab-icon i {
            padding-right: 0;
            padding-left: 0;
        }
    </style>
@endsection
@push('head')
    <script src="{{ asset('module/formulariumCtrl.js')}}"></script>
@endpush
@section('content-body')

    <div class="page-wrapper" ng-controller="formulariumCtrl">
        <div class="page-body m-t-50">
            <div class="row">
                <div class="col-sm-12 col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h5>Formularium</h5>
                            <div class="card-header-right">
                                <ul class="list-unstyled card-option">
                                    <li><i class="feather icon-maximize full-card"></i></li>
                                    {{--                                    <li><i class="feather icon-minus minimize-card"></i></li>--}}
                                    <li><i ng-click="addData()" class="feather icon-plus">
                                            <md-tooltip md-delay="10">
                                                Tambah Kelompok
                                            </md-tooltip>
                                        </i></li>
                                </ul>
                            </div>
                        </div>

                        <div class="card-block ">
                            <div class="row" id="isShowBtn">
                                <div class="col-12 col-md-3" style="margin-top: 5px">
                                    <button ng-click="edit()" class="btn btn-success btn-outline-success"
                                            style="width: 100%"><i
                                            class="icofont icofont-check-circled"></i>Edit
                                    </button>
                                </div>
                                <div class="col-12 col-md-3" style="margin-top: 5px">
                                    <button ng-click="addChild()" class="btn btn-primary btn-outline-primary"
                                            style="width: 100%"><i
                                            class="icofont  icofont-chart-bar-graph"></i>Child
                                    </button>
                                </div>
                                <div class="col-12 col-md-3" style="margin-top: 5px">
                                    <button ng-click="hapus()" class="btn btn-warning btn-outline-warning"
                                            style="width: 100%"><i
                                            class="icofont  icofont-trash"></i>Hapus
                                    </button>
                                </div>
                                <div class="col-12 col-md-3" style="margin-top: 5px">
                                    <button ng-click="details()" class="btn btn-danger btn-outline-danger"
                                            style="width: 100%"><i
                                            class="icofont  icofont-edit-alt"></i>Details
                                    </button>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12 col-md-10">
                                    <md-input-container class="md-block" flex-gt-sm style="margin-bottom: 0">
                                        <label>Search Data</label>
                                        <input ng-model="item.filter"
                                               ng-keypress="($event.which == 13)? filterData():item.filter">
                                    </md-input-container>
                                </div>
                                <div class="col-12 col-md-1" style="margin-top: 5px">
                                    <button ng-click="clearFilter()" class="btn btn-success btn-outline-success">
                                        <i class="icofont  icofont-refresh"></i>
                                        <md-tooltip md-delay="10">
                                            Clear Search
                                        </md-tooltip>
                                    </button>
                                </div>
                                <div class="col-12 ">
                                    <div class="card-block tree-view" style="overflow: auto;display: block;    position: relative;    background-color: rgb(250,250,250);">
                                        <div id="treeFormula" class="jstree jstree-1 jstree-default" role="tree"
                                             aria-multiselectable="true" tabindex="0" aria-activedescendant="j1_5"
                                             aria-busy="false">
                                            {{--                                        <div id="treeFormula" class="jstree jstree-1 jstree-default" role="tree"--}}
                                            {{--                                             aria-multiselectable="true" tabindex="0" aria-activedescendant="j1_3"--}}
                                            {{--                                             aria-busy="false">--}}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-12 col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h5>Details @{{ nodeText }}</h5>
                        </div>
                        <div class="card-block tab-icon">
                            <div ng-cloak>
                                <md-content>
                                    <md-tabs class="md-primary"  >
                                        <md-tab label="@{{ items.namagenerik }}" ng-repeat="items in listObat" >
                                            <md-content class="md-padding">
                                                <div class="row">
                                                    <div class="col-md-4 col-xs-12"  ng-repeat="details in items.details" >
                                                        <ul class="list-view">
                                                            <li>
                                                                <div class="card list-view-media">
                                                                    <div class="card-block">
                                                                        <div class="media">
                                                                            <a class="media-left" href="#">
                                                                                <i style="padding-left: 10px;padding-right: 10px"
                                                                                   class="icofont icofont-drug class-icon"></i>
                                                                            </a>
                                                                            <div class="media-body">
                                                                                <div class="col-xs-12">
                                                                                    <h6 class="d-inline-block">
                                                                                       @{{ details.namadagang }} </h6>
                                                                                     <label class="label label-warning ">   @{{ details.fnf }}  </label>
                                                                                </div>
                                                                                <div class="f-13 text-muted m-b-10">  @{{ details.kekuatan }}   @{{ details.bentuksediaan }}</div>
                                                                                <div class="f-12 text-muted ">  @{{ details.dosis }}</div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </md-content>
                                        </md-tab  ng-repeat="data in listData.kolom4">

                                    </md-tabs>
                                </md-content>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade md-effect-2" id="modalParent" tabindex="-1" role="dialog">
            <div class="modal-dialog " role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title"><span id="titleModalKun"></span></h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-sm-12 col-lg-12">
                                <md-input-container class="md-block" flex-gt-sm
                                                    style="margin-top: 18px;margin-bottom: 0">
                                    <label>Caption</label>
                                    <input ng-model="item.kelompok">
                                </md-input-container>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a ng-click="saveKel(item.kelompok)" class="btn btn-success btn-outline-success"><i
                                class="icofont icofont-check-circled"></i>Save </a>
                        <a data-dismiss="modal" class="btn btn-danger btn-outline-danger"><i
                                class="icofont  icofont-not-allowed"></i>Close</a>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection
@section('javascript')
    <script>

    </script>
@endsection

