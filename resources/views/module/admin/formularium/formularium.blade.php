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
    </style>
@endsection
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
                                    <div class="card-block tree-view">
                                        <div id="treeFormula" class="jstree jstree-1 jstree-default" role="tree" aria-multiselectable="true" tabindex="0" aria-activedescendant="j1_5" aria-busy="false">
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
                            <h5>Details @{{ item.title }}</h5>
                        </div>
                        <div class="card-block tab-icon">
                            <div class="row" ng-repeat="data in listData.kolom4">
                                <div ng-switch on="data.caption" class="col-sm-12 col-lg-12">
                                    <div ng-switch-when="Nama Generik">
                                        <md-input-container class="md-block">
                                            <label>Nama Generik</label>
                                            <textarea ng-model="item.obj[data.id]" md-maxlength="150" rows="2"
                                                      md-select-on-focus></textarea>
                                        </md-input-container>
                                    </div>
                                    <div ng-switch-when="Formularium">
                                        <md-radio-group ng-model="item.obj[data.id]">
                                            <div class="row">
                                                <div class="col-sm-12 col-lg-12">
                                                    <md-radio-button value="F" class="md-primary">Formularium
                                                    </md-radio-button>
                                                </div>
                                                <div class="col-sm-12 col-lg-12">
                                                    <md-radio-button value="NF" class="md-primary">Non Formularium
                                                    </md-radio-button>
                                                </div>
                                            </div>
                                        </md-radio-group>
                                    </div>
                                    <div ng-switch-when="Bentuk Sediaan">
                                        <md-input-container class="md-block" flex-gt-sm>
                                            <label>Bentuk Sediaan</label>
                                            <md-select ng-model="item.obj[data.id]">
                                                <md-option ng-repeat="it in listSediaan" value="@{{it.value}}">
                                                    @{{it.text}}
                                                </md-option>
                                            </md-select>
                                        </md-input-container>
                                    </div>
                                    <div ng-switch-when="Kekuatan">
                                        <md-input-container class="md-block" flex-gt-sm>
                                            <label>Kekuatan</label>
                                            <input ng-model="item.obj[data.id]">
                                        </md-input-container>
                                    </div>
                                    <div ng-switch-when="Nama Dagang">
                                        <md-input-container class="md-block" flex-gt-sm>
                                            <label>Nama Dagang</label>
                                            <input ng-model="item.obj[data.id]">
                                        </md-input-container>
                                    </div>
                                    <div ng-switch-when="Dosis Penggunaan">
                                        <md-input-container class="md-block">
                                            <label>Dosis Penggunaan</label>
                                            <textarea ng-model="item.obj[data.id]" md-maxlength="150"
                                                      rows="2"></textarea>
                                        </md-input-container>
                                    </div>
                                </div>
                            </div>


                            <div class="row">
                                <div class="col-12 col-md-2 col-md-offset-8"  style="margin-top: 5px">
                                    <button ng-click="saveEMR()" class="btn btn-success btn-outline-success"
                                            style="width: 100%"><i
                                            class="icofont icofont-check-circled"></i>Save
                                    </button>
                                </div>
                                <div class="col-md-2 col-12"  style="margin-top: 5px">
                                    <button ng-click="cancelDetails()" class="btn btn-danger btn-outline-danger"
                                            style="width: 100%"><i
                                            class="icofont icofont-exchange"></i>Cancel
                                    </button>
                                </div>
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
        angular.controller('formulariumCtrl', function ($scope, $http, httpService) {
            let namaEMR = 'formularium';
            let nomorEMR = '-'
            $scope.item = {};
            $scope.cc = {}
            $scope.isShowBtn = false
            $("#isShowBtn").hide()
            $scope.nodeID = '';
            loadCbo()

            function loadCbo() {
                httpService.get('emr/get-cbo-sediaan').then(function (e) {
                    $scope.listSediaan = e.data
                })
                nomorEMR = '-'
                httpService.get('emr/get-emr-data').then(function (e) {
                    if (e.data.data.length > 0) {
                        nomorEMR = e.data.data[0].noemr
                    }
                })

            }

            $('#treeFormula')
                .bind("dblclick.jstree", function (event) {
                    $("#isShowBtn").show()
                    var tree = $(this).jstree();
                    var node = tree.get_node(event.target);
                    $scope.item.id = ''
                    $scope.item.id = node.id
                    $scope.item.headfk = null
                    $scope.nodeText = node.text
                    if (node.parent != '#')
                        $scope.item.headfk = node.parent
                    $scope.listData = []
                    add_toast(node.text +' Selected',"info");
                    // toastr.info(node.text, 'Selected')
                    // $scope.details()
                })
                .bind("click.jstree", function (event) {
                    var tree = $(this).jstree();
                    var node = tree.get_node(event.target);
                    // $scope.item.id= ''
                    $scope.listData = []
                })
                .jstree({
                    'core': {
                        'themes': {
                            'responsive': true
                        },
                        'data': []
                    },
                    'types': {
                        'default': {
                            'icon': 'icofont icofont-folder'
                        },
                        'file': {
                            'icon': 'icofont icofont-file-alt'
                        }
                    },
                    'plugins': ['types', "themes", "html_data", "search", "adv_search"],

                })

            // listen for event;
            onload()

            function onload() {
                let jsonData = []
                httpService.get('emr/get-menu-rekam-medis-dynamic?namaemr=' + namaEMR).then(function (e) {
                    jsonData = e.data.data
                    $('#treeFormula').jstree(true).settings.core.data = jsonData;
                    $('#treeFormula').jstree(true).refresh();
                    // $('#treeFormula').jstree({
                    //     core: {
                    //         data: jsonData
                    //     }
                    // });
                })
            }

            $scope.filterData = function () {
                $("#treeFormula").jstree("search", $scope.item.filter);
            }
            $scope.clearFilter = function () {
                delete $scope.item.filter
                $("#treeFormula").jstree("clear_search");
                $scope.show()
                $scope.listData =[]
                $scope.item.title = ''
            }
            $scope.show = function () {
                // $scope.isShowBtn = false
                $("#isShowBtn").hide()
                $scope.item.headfk = null
                $scope.item.id = ''
                $scope.item.kelompok = ''
            }

            $scope.addData = function () {
                $scope.item.id = ''
                $scope.item.headfk = null
                $('#modalParent').modal("show");
            }
            $scope.edit = function () {
                $scope.item.kelompok = $scope.nodeText;
                $('#modalParent').modal("show");
            }
            $scope.addChild = function () {
                $scope.item.headfk = $scope.item.id
                $scope.item.id = ''
                $('#modalParent').modal("show");
            }
            $scope.hapus = function () {
                if (confirm('Yakin mau hapus?')) {
                    let json = {
                        'id': $scope.item.id
                    };
                    httpService.post('emr/delete-navigasi', json).then(function (e) {
                        $scope.show()
                        onload()
                    });
                } else {
                }
            }
            $scope.saveKel = function (kel) {
                let json = {
                    'namaemr': namaEMR,
                    'caption': kel,
                    'headfk': $scope.item.headfk,
                    'id': $scope.item.id
                };
                httpService.post('emr/save-navigasi', json).then(function (e) {
                    $('#modalParent').modal("hide");
                    $scope.show()
                    onload()
                });
            }
            $scope.details = function () {
                httpService.get("emr/get-rekam-medis-dynamic?emrid=" + $scope.item.id).then(function (e) {
                    if (e.data.title == '') {
                        $scope.saveDetails()
                    } else {
                        $scope.item.obj = []
                        $scope.item.obj2 = []
                        $scope.listData = e.data
                        $scope.item.title = e.data.title
                        $scope.item.classgrid = e.data.classgrid

                        for (var i = 0; i <  $scope.listData.kolom4.length; i++) {
                            const elem =  $scope.listData.kolom4[i]
                            if(elem.caption == 'Nama Generik'){
                                $scope.item.obj[elem.id] =  $scope.nodeText
                                break;
                            }
                        }
                        onloadDetail()
                    }
                })
            }
            $scope.saveDetails = function () {
                let data = [
                    {caption: 'Nama Generik', type: 'textbox', satuan: null, cbotable: null},
                    {caption: 'Formularium', type: 'radio', satuan: null, cbotable: null},
                    {caption: 'Bentuk Sediaan', type: 'combobox', satuan: null, cbotable: 'emr/get-cbo-sediaan'},
                    {caption: 'Kekuatan', type: 'textbox', satuan: null, cbotable: null},
                    {caption: 'Nama Dagang', type: 'textbox', satuan: null, cbotable: null},
                    {caption: 'Dosis Penggunaan', type: 'textbox', satuan: null, cbotable: null}
                ];
                let json = []
                for (var i = 0; i < data.length; i++) {
                    const elem = data[i]
                    json.push({
                        'emrfk': $scope.item.id,
                        'caption': elem.caption,
                        'headfk': null,
                        'type': elem.type,
                        'satuan': elem.satuan,
                        'cbotable': elem.cbotable,
                    })
                }
                httpService.post('emr/save-emr-d', {'data': json}).then(function (e) {
                    // onloadDetail()
                    $scope.details()
                });
            }

            function onloadDetail() {
                if (nomorEMR != '-') {
                    httpService.get("emr/get-emr-transaksi-detail?noemr=" + nomorEMR + "&emrfk=" + $scope.item.id, true).then(function (dat) {


                        var dataLoad = dat.data.data
                        if(dataLoad.length >0){
                            $scope.item.obj =[]
                        }

                        for (var i = 0; i <= dataLoad.length - 1; i++) {
                            if (parseFloat($scope.item.id) == dataLoad[i].emrfk) {
                                if (dataLoad[i].type == "textbox") {
                                    $scope.item.obj[dataLoad[i].emrdfk] = dataLoad[i].value
                                }
                                if (dataLoad[i].type == "checkbox") {
                                    chekedd = false
                                    if (dataLoad[i].value == '1') {
                                        chekedd = true
                                    }
                                    $scope.item.obj[dataLoad[i].emrdfk] = chekedd
                                }

                                if (dataLoad[i].type == "datetime") {
                                    $scope.item.obj[dataLoad[i].emrdfk] = new Date(dataLoad[i].value)
                                }
                                if (dataLoad[i].type == "time") {
                                    $scope.item.obj[dataLoad[i].emrdfk] = new Date(dataLoad[i].value)
                                }
                                if (dataLoad[i].type == "date") {
                                    $scope.item.obj[dataLoad[i].emrdfk] = new Date(dataLoad[i].value)
                                }

                                if (dataLoad[i].type == "checkboxtextbox") {
                                    $scope.item.obj[dataLoad[i].emrdfk] = dataLoad[i].value
                                    $scope.item.obj2[dataLoad[i].emrdfk] = true
                                }
                                if (dataLoad[i].type == "textarea") {
                                    $scope.item.obj[dataLoad[i].emrdfk] = dataLoad[i].value
                                }
                                if (dataLoad[i].type == "radio") {
                                    $scope.item.obj[dataLoad[i].emrdfk] = dataLoad[i].value
                                }
                                if (dataLoad[i].type == "combobox") {

                                    var str = dataLoad[i].value
                                    var res = str.split("~");
                                    if(res.length > 1){
                                        $scope.item.obj[dataLoad[i].emrdfk] = {value: res[0], text: res[1]}
                                    }else{
                                        $scope.item.obj[dataLoad[i].emrdfk] =  res[0]
                                    }


                                }
                            }
                        }
                    })
                }

            }

            $scope.cancelDetails = function () {
                $scope.show()
            }
            $scope.saveEMR = function () {
                // debugger
                var arrobj = Object.keys($scope.item.obj)
                var arrSave = []
                for (var i = arrobj.length - 1; i >= 0; i--) {
                    if ($scope.item.obj[parseInt(arrobj[i])] instanceof Date)
                        $scope.item.obj[parseInt(arrobj[i])] = moment($scope.item.obj[parseInt(arrobj[i])]).format('YYYY-MM-DD HH:mm')
                    arrSave.push({id: arrobj[i], values: $scope.item.obj[parseInt(arrobj[i])]})
                }
                $scope.cc.norec_emr = nomorEMR
                $scope.cc.emrfk = $scope.item.id
                $scope.cc.norec_pd = null
                $scope.cc.nocm = $scope.item.id
                $scope.cc.namapasien = null
                $scope.cc.jeniskelamin = null
                $scope.cc.noregistrasi = 'formularium'
                $scope.cc.umur = null
                $scope.cc.tglregistrasi = moment(new Date()).format('YYYY-MM-DD HH:mm:ss')
                $scope.cc.norec = null
                $scope.cc.namaruangan = 'Formularium'
                $scope.cc.jenis = namaEMR
                $scope.cc.jenisemr = namaEMR
                var jsonSave = {
                    head: $scope.cc,
                    data: arrSave
                }
                httpService.post('emr/save-emr-dinamis', jsonSave).then(function (e) {
                    nomorEMR = e.data.data.noemr
                    $scope.listData = []
                });
            }

        });
    </script>
@endsection

