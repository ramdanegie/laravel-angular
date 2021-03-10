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
        // httpService.get('emr/get-cbo-sediaan').then(function (e) {
        //     $scope.listSediaan = e.data
        // })
        nomorEMR = '-'
        // httpService.get('emr/get-emr-data').then(function (e) {
        //     if (e.data.data.length > 0) {
        //         nomorEMR = e.data.data[0].noemr
        //     }
        // })

    }

    $('#treeFormula')
        // .bind("dblclick.jstree", function (event) {
        //     $("#isShowBtn").show()
        //     var tree = $(this).jstree();
        //     var node = tree.get_node(event.target);
        //     $scope.item.id = ''
        //     $scope.item.id = node.id
        //     $scope.item.headfk = null
        //     $scope.nodeText = node.text
        //     if (node.parent != '#')
        //         $scope.item.headfk = node.parent
        //     $scope.listData = []
        //     add_toast(node.text +' Selected',"info");
        //     // toastr.info(node.text, 'Selected')
        //     // $scope.details()
        // })
        .bind("click.jstree", function (event) {
            var tree = $(this).jstree();
            var node = tree.get_node(event.target);
            $scope.nodeText =''
            if(node.children.length == 0){
                add_toast(node.text +' Selected !',"info");
                $scope.nodeText = node.text
                loadDetailObat(node.id)
            }

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
        httpService.get('emr/get-treeview-formularium?namaemr=' + namaEMR).then(function (e) {
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

    function loadDetailObat(id) {
        $scope.listObat = []
        httpService.get('emr/get-detail-formularium?id=' + id).then(function (e) {

            $scope.listObat = e.data.data
        })
    }

    $scope.filterData = function () {
        $("#treeFormula").jstree("search", $scope.item.filter);
    }
    $scope.clearFilter = function () {
        debugger
        delete $scope.item.filter
        $("#treeFormula").jstree("clear_search");
        $scope.show()
        $scope.listData = []
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

                for (var i = 0; i < $scope.listData.kolom4.length; i++) {
                    const elem = $scope.listData.kolom4[i]
                    if (elem.caption == 'Nama Generik') {
                        $scope.item.obj[elem.id] = $scope.nodeText
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
                if (dataLoad.length > 0) {
                    $scope.item.obj = []
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
                            if (res.length > 1) {
                                $scope.item.obj[dataLoad[i].emrdfk] = {value: res[0], text: res[1]}
                            } else {
                                $scope.item.obj[dataLoad[i].emrdfk] = res[0]
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
