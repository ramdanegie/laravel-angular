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
    <div class="page-wrapper pad" >
        <div class="page-body">
            <div class="card">
                <div class="card-header">
                    <h5>Dashboard Pendapatan</h5>
                </div>
                <div class="card-block tab-icon">
                    <form action="{!! route("show_page", ["role" => $_SESSION['role'], "pages" => $r->pages ]) !!}" method="get">
                        <div class="row">
                            <div class="col-lg-7">
                            </div>
                            <div class="col-lg-2" style="margin-top: 5px">
                                <div class="input-group" >
                                    <span class="input-group-addon" id="basic-addon1"><i class="ti-calendar"></i></span>
                                    <input type="text" id="tglawal" name="tglawal" class="date-custom form-control" value="{{request()->get("tglawal")}}" >
                                </div>

                            </div>
                            <div class="col-lg-2" style="margin-top: 5px">
                                <div class="input-group">
                                    <span class="input-group-addon" id="basic-addon1"><i class="ti-calendar"></i></span>
                                    <input type="text" id="tglakhir" name="tglakhir"  class="date-custom form-control" value="{{request()->get("tglakhir")}}" >
                                </div>
                            </div>
                            <div class="col-lg-1" style="margin-top: 5px" >
                                <button class="btn btn-success  btn-outline-success"  type="submit">
                                    <i class="icofont icofont-search"></i>Search</button>
                            </div>
                        </div>
                        <div class="row" style="margin-top:10px ">
                            <div class="col-lg-12 col-xl-12">
                                <div class="card">
                                    <div class="panel panel-warning">
                                        <div class="panel-heading bg-warning">
                                            Pendapatan Rumah Sakit
                                        </div>
                                        <div class="panel-body">
                                            <div id="chartPendapatan"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-12 col-md-12">
                                <div class="card">
                                    <div class="panel panel-warning">
                                        <div class="panel-heading  bg-c-kuning">
                                            Pendapatan PerInstalasi
                                        </div>
                                        @php

                                            $sama = false;
                                            $resultSumDep = [];
                                            $array = $res['pendapatan']['data'];
                                           // dd($array);
                                            for ($i = 0; $i < count($array); $i++) {
                                                $sama = false;
                                                if(count($resultSumDep) > 0){
                                                   for ($x = 0; $x < count($resultSumDep); $x++) {
                                                        if ($resultSumDep[$x]['namadepartemen'] == $array[$i]->namadepartemen) {
                                                            $sama = true;
                                                            $resultSumDep[$x]['total'] = (float) $resultSumDep[$x]['total']+  (float)$array[$i]->total;
                                                        }
                                                    }
                                                }
                                                if ($sama == false) {
                                                    $resultSumDep []= [
                                                        'namadepartemen' =>  $array[$i]->namadepartemen,
                                                        'total' =>  $array[$i]->total,
                                                    ];
                                                }
                                            }

                                        @endphp
                                        <div class="panel-body" >
                                            <div class="row" style="padding:10px 20px;">
                                                <div class="col-md-12">
                                                    <div class="table-responsive" >
                                                        <table class="table  table-striped table-sm table-styling"
                                                               style="width:100%">
                                                           <thead class="table-default">
                                                            <tr>
                                                                <th style="color:black;width: 10%">NO </th>
                                                                <th style="color:black">NAMA INSTALASI </th>
                                                                <th style="text-align: right;color:black">JUMLAH </th>
                                                            </tr>
                                                            </thead>
                                                            <tbody>
                                                            @php
                                                                $total = 0;
                                                            @endphp
                                                            @forelse($resultSumDep as $i => $d)
                                                                @php
                                                                    $total = $total+$d['total']
                                                                @endphp
                                                                <tr>
                                                                    <td >{{ $i +1 }}</td>
                                                                    <td >{{ $d['namadepartemen'] }}</td>
                                                                    <td  style="text-align: right"> {{ App\Http\Controllers\MainController::formatRp($d['total']) }}</td>
                                                                </tr>
                                                            @empty
                                                                <tr>
                                                                    <td colspan="3" style="text-align: center">Data Tidak ada</td>
                                                                </tr>
                                                            @endforelse
                                                            <tr style="background:rgba(0,0,0,.3);">
                                                                <td colspan="2">TOTAL</td>
                                                                <td style="text-align: right">{{App\Http\Controllers\MainController::formatRp($total)}}</td>
                                                            </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6 col-md-12">
                                <div class="card">
                                    <div class="panel panel-success">
                                        <div class="panel-heading bg-success">
                                            Pendapatan PerJenis Pasien
                                        </div>
                                        <div class="panel-body">
                                            <div class="tab-content card-block">
                                                <div class="tab-pane active" id="home1" role="tabpanel">
                                                    <div id="chartPerKelompokPasienPie"></div>
                                                </div>
                                                <div class="tab-pane" id="profile1" role="tabpanel">
                                                    <div id="chartPerKelompokPasien"></div>
                                                </div>
                                            </div>
                                            <ul class="nav nav-tabs tabs" role="tablist">
                                                <li class="nav-item">
                                                    <a class="nav-link active" data-toggle="tab"
                                                       href="#home1" role="tab">Pie Chart</a>
                                                </li>
                                                <li class="nav-item">
                                                    <a class="nav-link" data-toggle="tab" href="#profile1" role="tab">
                                                        Bar Chart</a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6 col-md-12">
                                <div class="card">
                                    <div class="panel panel-danger">
                                        <div class="panel-heading bg-danger">
                                            Pendapatan PerJenis Pasien
                                        </div>
                                        @php
                                            $res['penerimaan'] = App\Http\Controllers\MainController::getPenerimaanKasir(request()->get("tglawal"),request()->get("tglakhir"),$_SESSION['kdProfile']);
                                        @endphp
                                        <div class="panel-body">
                                          <!--   <span style="float: right;font-weight:bold">
                                                <span id="totalPenerimaanKasir"></span>&nbsp;&nbsp;</span> -->


                                            <div class="tab-content card-block">
                                                <div class="tab-pane active" id="home2" role="tabpanel">
                                                    <div id="chartPenerimaan"></div>
                                                </div>
                                                <div class="tab-pane" id="profile2" role="tabpanel">
                                                    <div id="chartPenerimaanNonLayanan"></div>
                                                </div>
                                            </div>
                                            <ul class="nav nav-tabs tabs" role="tablist">
                                                <li class="nav-item">
                                                    <a class="nav-link active" data-toggle="tab"
                                                       href="#home2" role="tab">Layanan</a>
                                                </li>
                                                <li class="nav-item">
                                                    <a class="nav-link" data-toggle="tab" href="#profile2" role="tab">
                                                        Non Layanan</a>
                                                </li>
                                            </ul>

                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-12 col-xl-12">
                                <div class="card">
                                    <div class="panel panel-primary">
                                        <div class="panel-heading bg-primary">
                                            Trend Pendapatan Rumah Sakit
                                        </div>
                                        <div class="panel-body">
                                            @php
                                                $res['pendapatan_mg'] = App\Http\Controllers\MainController::getPendapatanRumahSakit(request()->get("tglawal"),request()->get("tglakhir"),$_SESSION['kdProfile'],'seminggu');
                                            @endphp
                                            <div id="chartTrendPendapatan"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div >
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalKunjungan" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg"  role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title"><span id="titleModalKun"></span></h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="load_kunjungan">
                </div>

                <div class="modal-footer">
                </div>
            </div>
        </div>
    </div>
@endsection

@section('javascript')
    <script>
        $(document).ready(function() {
            $('.js-example-basic-single').select2()
        })
        var APP_URL = {!! json_encode(url('/')) !!}
        setChartPendapatan()
        setChartNonLayanan()
        setChartTren()
        function setChartPendapatan() {
            this.dataChartPendapatan =  @json($res['pendapatan']);
            let array = this.dataChartPendapatan.data
            let series = [];

            // totalkeun hela
            for (let i in array) {
                array[i].total = parseFloat(array[i].total)//total
            }
            // looping nu sarua deapartemena na jumlahkeun
            let samateuuu = false
            let resultSumRuangan = [];
            for (let i in array) {
                samateuuu = false
                for (let x in resultSumRuangan) {
                    if (resultSumRuangan[x].namaruangan == array[i].namaruangan) {
                        resultSumRuangan[x].total = parseFloat(resultSumRuangan[x].total) + parseFloat(array[i].total)
                        // resultSumRuangan[x].namaruangan = array[i].namaruangan
                        samateuuu = true;
                    }
                }
                if (samateuuu == false) {
                    let result = {
                        namaruangan: array[i].namaruangan,
                        total: array[i].total,
                        namadepartemen: array[i].namadepartemen,
                        kelompokpasien: array[i].kelompokpasien,
                    }
                    resultSumRuangan.push(result)
                }
            }


            let sama = false
            let resultSumDep = [];
            for (let i in array) {
                sama = false
                for (let x in resultSumDep) {
                    if (resultSumDep[x].namadepartemen == array[i].namadepartemen) {
                        sama = true;
                        resultSumDep[x].total = parseFloat(resultSumDep[x].total) + parseFloat(array[i].total)
                        // resultSumDep[x].namadepartemen = array[i].namadepartemen
                    }
                }
                // let resultGroupingRuangan = []
                if (sama == false) {
                    var dataDetail0 = [];
                    for (var f = 0; f < resultSumRuangan.length; f++) {
                        if (array[i].namadepartemen == resultSumRuangan[f].namadepartemen) {
                            dataDetail0.push([resultSumRuangan[f].namaruangan, resultSumRuangan[f].total]);
                        };
                    }
                    let result = {
                        id: array[i].namadepartemen,
                        name: array[i].namadepartemen,
                        namadepartemen: array[i].namadepartemen,
                        total: array[i].total,
                        data: dataDetail0
                    }
                    resultSumDep.push(result)
                }
            }

            // console.log(resultSumDep)
            // drilldown ruangan
            // asupkeun kana series data di CHART
            let totalAll = 0;
            for (let z in resultSumDep) {
                totalAll = totalAll + parseFloat(resultSumDep[z].total)
                let datana = [];
                datana.push({
                    y: parseFloat(resultSumDep[z].total),
                    color: this.colors[z],
                    drilldown: resultSumDep[z].namadepartemen
                });
                series.push({
                    name: resultSumDep[z].namadepartemen,
                    data: datana
                });
            }
            this.totalPerinstalasi = 0
            for (let j = 0; j < resultSumDep.length; j++) {
                const element = resultSumDep[j];
                this.totalPerinstalasi = this.totalPerinstalasi + element.total
            }
            this.totalPerinstalasi = 'Rp. ' + Highcharts.numberFormat(this.totalPerinstalasi, 0, '.', ',');
            var dataKanggeGrid = resultSumDep
            for (let i = 0; i < dataKanggeGrid.length; i++) {
                const element = dataKanggeGrid[i];
                element.total = 'Rp. ' + Highcharts.numberFormat(element.total, 0, '.', ',');
            }

            this.dataSouceInstalasiPend = dataKanggeGrid
            var tglAwal = $("#tglawal").val()
            var tglAkhir = $("#tglakhir").val()
            this.isShowTrend = false;
            Highcharts.chart('chartPendapatan', {

                chart: {
                    type: 'column'
                },
                title: {
                    text: ''
                },
                subtitle: {
                    text: ''
                },
                xAxis: {
                    // categories: [" "],
                    // labels: {
                    //     align: 'center',
                    //     style: {
                    //         fontSize: '7px',
                    //         fontFamily: 'Verdana, sans-serif'
                    //     }
                    // },
                    type: 'category'
                },
                yAxis: {
                    title: {
                        text: 'Realisasi Pendapatan'
                    }
                },
                legend: {
                    enabled: true,
                    borderRadius: 5,
                    borderWidth: 1
                },
                // legend: {
                //     enabled: false
                // },
                plotOptions: {
                    column: {
                        // url:"#",
                        cursor: 'pointer',

                        dataLabels: {
                            enabled: true,
                            color: this.colors[1],

                            formatter: function () {
                                return 'Rp. ' + Highcharts.numberFormat(this.y, 0, '.', ',');
                            }
                        },
                        showInLegend: true
                    },
                    series: {
                        cursor: 'pointer',
                        borderWidth: 0,
                        // dataLabels: {
                        //     enabled: true,
                        //     format: '{point.y:.1f}%'
                        // },
                        point: {
                            events: {
                                click: function () {
                                    if (this.name == undefined) return;
                                    getDetailChartPen(event.point.name, tglAwal, tglAkhir)
                                    // alert( "point this.name = [" + this.name + tglAwal+ "], this.category = [" + this.category + "]")
                                }
                                // click: function (event) {
                                //     if (event.point.name == undefined) return;
                                //      getDetailChartPen(event.point.name, tglAwal, tglAkhir)
                                // }.bind(this)
                            },
                            // }
                        }
                    },

                },
                // tooltip: {
                //     formatter: function () {
                //         let point = this.point,
                //             s = this.series.name + ': Rp. ' + Highcharts.numberFormat(this.y, 0, '.', ',') + ' <br/>';
                //         return s;

                //     }

                // },
                credits: {
                    text: 'Total : Rp. ' + Highcharts.numberFormat(totalAll, 0, '.', ','),
                    style: {
                        color:"black",
                        cursor: "pointer",
                        fontSize: "12px"
                    }
                },
                series: series,
                drilldown: {
                    series: resultSumDep,

                }

            })


            //   kelompok pasien
            let samakan = false
            let resultSumKelPasien = [];
            for (let i in array) {
                samakan = false
                for (let x in resultSumKelPasien) {
                    if (resultSumKelPasien[x].namaruangan == array[i].namaruangan &&
                        resultSumKelPasien[x].kelompokpasien == array[i].kelompokpasien) {
                        samakan = true;
                        resultSumKelPasien[x].total = parseFloat(resultSumKelPasien[x].total) + parseFloat(array[i].total)
                        // resultSumKelPasien[x].namaruangan = array[i].namaruangan
                    }
                }
                if (samakan == false) {
                    let result = {
                        namaruangan: array[i].namaruangan,
                        total: array[i].total,
                        namadepartemen: array[i].namadepartemen,
                        kelompokpasien: array[i].kelompokpasien,
                    }
                    resultSumKelPasien.push(result)
                }
            }
            let seriesKelPasien = []
            let sarua = false
            let resultSumKelompokPasien = [];
            for (let i in array) {
                sarua = false
                for (let x in resultSumKelompokPasien) {
                    if (resultSumKelompokPasien[x].kelompokpasien == array[i].kelompokpasien) {
                        sarua = true;
                        resultSumKelompokPasien[x].total = parseFloat(resultSumKelompokPasien[x].total) + parseFloat(array[i].total)
                        // resultSumKelompokPasien[x].kelompokpasien = array[i].kelompokpasien
                    }
                }
                if (sarua == false) {
                    var details = [];
                    var rinci = [];
                    for (var f = 0; f < resultSumKelPasien.length; f++) {
                        if (array[i].kelompokpasien == resultSumKelPasien[f].kelompokpasien) {
                            details.push([resultSumKelPasien[f].namaruangan, resultSumKelPasien[f].total]);
                            rinci.push(resultSumKelPasien[f]);
                        };
                    }
                    let result = {
                        kelompokpasien: array[i].kelompokpasien,
                        total: array[i].total,
                        id: array[i].kelompokpasien,
                        name: array[i].kelompokpasien,
                        data: details,
                        rincian: rinci
                    }
                    resultSumKelompokPasien.push(result)
                }
            }
            //  console.log(resultSumKelompokPasien)
            // asupkeun kana series data di CHART
            let dataKelPasienPie = []
            let slice = true
            for (let z in resultSumKelompokPasien) {
                let datana = [];
                datana.push({
                    y: parseFloat(resultSumKelompokPasien[z].total),
                    color: this.colors[z],
                    drilldown: resultSumKelompokPasien[z].kelompokpasien
                });

                seriesKelPasien.push({
                    name: resultSumKelompokPasien[z].kelompokpasien,
                    data: datana
                });
                dataKelPasienPie.push({
                    name: resultSumKelompokPasien[z].kelompokpasien,
                    y: parseFloat(resultSumKelompokPasien[z].total),
                    sliced: slice,
                    selected: slice
                });
                slice = false;
            }
            Highcharts.chart('chartPerKelompokPasien', {
                chart: {
                    type: 'column',

                },

                title: {
                    text: ''
                },
                xAxis: {
                    type: 'category',
                    // categories: ["Jumlah "],
                    // labels: {
                    //     align: 'center',
                    //     style: {
                    //         fontSize: '13px',
                    //         fontFamily: 'Verdana, sans-serif'
                    //     }
                    // }
                },
                yAxis: {
                    title: {
                        text: 'Realisasi Pendapatan'
                    }
                },
                plotOptions: {
                    column: {
                        // url:"#",
                        cursor: 'pointer',

                        dataLabels: {
                            enabled: true,
                            color: this.colors[1],

                            formatter: function () {
                                return 'Rp. ' + Highcharts.numberFormat(this.y, 0, '.', ',');
                            }
                        },
                        showInLegend: true
                    },
                    series: {
                        cursor: 'pointer',
                    }
                },
                // tooltip: {
                //     formatter: function () {
                //         let point = this.point,
                //             s = this.series.name + ': Rp. ' + Highcharts.numberFormat(this.y, 0, '.', ',') + ' <br/>';
                //         return s;

                //     }

                // },
                series: seriesKelPasien,
                drilldown: {
                    series: resultSumKelompokPasien
                },
                exporting: {
                    enabled: false
                },
                credits: {
                    // enabled: false
                    text: 'Total : Rp. ' + Highcharts.numberFormat(totalAll, 0, '.', ','),
                    style: {
                        color:"black",
                        cursor: "pointer",
                        fontSize: "12px"
                    }
                },
                legend: {
                    enabled: true,
                    borderRadius: 5,
                    borderWidth: 1
                },

            })

            Highcharts.chart('chartPerKelompokPasienPie', {
                chart: {
                    plotBackgroundColor: null,
                    plotBorderWidth: null,
                    plotShadow: false,
                    type: 'pie'
                },
                title: {
                    text: '',

                },
                tooltip: {
                    formatter: function (e) {
                        let point = this.point,
                            s = this.series.name + ': Rp. ' + Highcharts.numberFormat(this.y, 0, '.', ',') + ' <br/>';
                        return s;

                    }

                },
                plotOptions: {
                    pie: {
                        allowPointSelect: true,
                        cursor: 'pointer',
                        dataLabels: {
                            enabled: true,
                            color: '#000000',
                            connectorColor: '#000000',
                            formatter: function () {
                                return this.percentage.toFixed(2) + ' %';
                            }
                        },
                        showInLegend: true
                    },

                },
                credits: {
                    text: 'Total : Rp. ' + Highcharts.numberFormat(totalAll, 0, '.', ','),
                    style: {
                        color:"black",
                        cursor: "pointer",
                        fontSize: "12px"
                    }
                    // enabled: false
                },
                legend: {
                    enabled: true,
                    borderRadius: 5,
                    borderWidth: 1
                },
                series: [{
                    type: 'pie',
                    name: 'Total',
                    // colorByPoint: true,
                    data: dataKelPasienPie

                }]
            })
        }
        function setChartNonLayanan(){

            this.dataChartPenerimaan = @json($res['penerimaan']);
            // this.loading = false;
            this.dataGrid = this.dataChartPenerimaan.data
            let array = this.dataChartPenerimaan.data
            let seriesTerima = [];
            let seriesTerimaNonLayanan = [];

            // looping nu sarua deapartemena na jumlahkeun
            let sama = false
            let resultSum = [];
            for (let i in array) {

                sama = false
                for (let x in resultSum) {
                    if (resultSum[x].namadepartemen == array[i].namadepartemen) {
                        sama = true;
                        resultSum[x].totaldibayar = parseFloat(resultSum[x].totaldibayar) + parseFloat(array[i].totaldibayar)
                        resultSum[x].namadepartemen = array[i].namadepartemen
                    }
                }
                if (sama == false) {
                    let result = {
                        namadepartemen: array[i].namadepartemen,
                        totaldibayar: array[i].totaldibayar,
                    }
                    resultSum.push(result)
                }
            }
            // asupkeun kana series data di CHART
            let totalAll = 0;
            let totalNonLayanan = 0
            for (let z in resultSum) {
                if (resultSum[z].namadepartemen != null) {
                    totalAll = totalAll + parseFloat(resultSum[z].totaldibayar)
                    let datana = [];
                    datana.push({
                        y: parseFloat(resultSum[z].totaldibayar),
                        color: this.colors[z],
                        drilldown: true
                    });

                    seriesTerima.push({
                        type: 'column',
                        name: resultSum[z].namadepartemen,
                        data: datana
                    });
                } else {
                    totalNonLayanan = totalNonLayanan + parseFloat(resultSum[z].totaldibayar)
                    let datana = [];
                    datana.push({
                        y: parseFloat(resultSum[z].totaldibayar),
                        color: this.colors[0],
                        drilldown: true
                    });

                    seriesTerimaNonLayanan.push({
                        type: 'column',
                        name: 'Non Layanan',
                        data: datana
                    });
                }

            }
            // document.getElementById("totalPenerimaanKasir").innerHTML =   'Rp. ' + Highcharts.numberFormat((totalAll + totalNonLayanan), 0, '.', ',');

            Highcharts.chart('chartPenerimaan', {
                chart: {
                    // type: 'column',
                    events: {
                        drilldown: function (e) {
                            if (!e.seriesOptions) {

                                var chart = this,
                                    drilldowns = {
                                        'Animals': {
                                            name: 'Animals',
                                            data: [
                                                ['Cows', 2],
                                                ['Sheep', 3]
                                            ]
                                        },
                                        'Fruits': {
                                            name: 'Fruits',
                                            data: [
                                                ['Apples', 5],
                                                ['Oranges', 7],
                                                ['Bananas', 2]
                                            ]
                                        },
                                        'Cars': {
                                            name: 'Cars',
                                            data: [
                                                ['Toyota', 1],
                                                ['Volkswagen', 2],
                                                ['Opel', 5]
                                            ]
                                        }
                                    },
                                    series = drilldowns[e.point.name];

                                // Show the loading label
                                // chart.showLoading('Simulating Ajax ...');

                                setTimeout(function () {
                                    chart.hideLoading();
                                    chart.addSeriesAsDrilldown(e.point, series);
                                }, 1000);
                            }

                        }
                    }
                },

                title: {
                    text: ''
                },
                xAxis: {
                    categories: ["Jumlah "],
                    labels: {
                        align: 'center',
                        style: {
                            fontSize: '13px',
                            fontFamily: 'Verdana, sans-serif'
                        }
                    }
                },
                yAxis: {
                    title: {
                        text: 'Penerimaan'
                    }
                },
                plotOptions: {
                    column: {
                        // url:"#",
                        cursor: 'pointer',

                        dataLabels: {
                            enabled: true,
                            color: this.colors[1],

                            formatter: function () {
                                return 'Rp. ' + Highcharts.numberFormat(this.y, 0, '.', ',');
                            }
                        },
                        showInLegend: true
                    },
                    series: {
                        cursor: 'pointer',
                    }
                },
                tooltip: {
                    formatter: function () {
                        let point = this.point,
                            s = this.series.name + ': Rp. ' + Highcharts.numberFormat(this.y, 0, '.', ',') + ' <br/>';
                        return s;

                    }

                },
                series: seriesTerima,
                drilldown: {
                    series: []
                },
                exporting: {
                    enabled: false
                },
                credits: {
                    // enabled: false
                    text: 'Total : Rp. ' + Highcharts.numberFormat(totalAll, 0, '.', ','),
                    style: {
                        color:"black",
                        cursor: "pointer",
                        fontSize: "12px"
                    }
                },
                legend: {
                    enabled: true,
                    borderRadius: 5,
                    borderWidth: 1
                },

            })
            // chart Penerimaan Non Layanan
            Highcharts.chart('chartPenerimaanNonLayanan', {
                chart: {
                    // type: 'column',
                    events: {
                        drilldown: function (e) {
                            if (!e.seriesOptions) {

                                var chart = this,
                                    drilldowns = {
                                        'Animals': {
                                            name: 'Animals',
                                            data: [
                                                ['Cows', 2],
                                                ['Sheep', 3]
                                            ]
                                        },
                                        'Fruits': {
                                            name: 'Fruits',
                                            data: [
                                                ['Apples', 5],
                                                ['Oranges', 7],
                                                ['Bananas', 2]
                                            ]
                                        },
                                        'Cars': {
                                            name: 'Cars',
                                            data: [
                                                ['Toyota', 1],
                                                ['Volkswagen', 2],
                                                ['Opel', 5]
                                            ]
                                        }
                                    },
                                    series = drilldowns[e.point.name];

                                // Show the loading label
                                // chart.showLoading('Simulating Ajax ...');

                                setTimeout(function () {
                                    chart.hideLoading();
                                    chart.addSeriesAsDrilldown(e.point, series);
                                }, 1000);
                            }

                        }
                    }
                },

                title: {
                    text: ''
                },
                xAxis: {
                    categories: ["Jumlah "],
                    labels: {
                        align: 'center',
                        style: {
                            fontSize: '13px',
                            fontFamily: 'Verdana, sans-serif'
                        }
                    }
                },
                yAxis: {
                    title: {
                        text: 'Penerimaan'
                    }
                },
                plotOptions: {
                    column: {
                        // url:"#",
                        cursor: 'pointer',

                        dataLabels: {
                            enabled: true,
                            color: this.colors[1],

                            formatter: function () {
                                return 'Rp. ' + Highcharts.numberFormat(this.y, 0, '.', ',');
                            }
                        },
                        showInLegend: true
                    },
                    series: {
                        cursor: 'pointer',
                    }
                },
                tooltip: {
                    formatter: function () {
                        let point = this.point,
                            s = this.series.name + ': Rp. ' + Highcharts.numberFormat(this.y, 0, '.', ',') + ' <br/>';
                        return s;

                    }

                },
                series: seriesTerimaNonLayanan,
                drilldown: {
                    series: []
                },
                exporting: {
                    enabled: false
                },
                credits: {
                    // enabled: false
                    text: 'Total : Rp. ' + Highcharts.numberFormat(totalNonLayanan, 0, '.', ','),
                    style: {
                        color:"black",
                        cursor: "pointer",
                        fontSize: "12px"
                    }
                },
                legend: {
                    enabled: true,
                    borderRadius: 5,
                    borderWidth: 1
                },

            })
        }
        function setChartTren(){
            this.dataChartTend =@json($res['pendapatan_mg']);
            let array = this.dataChartTend.data
            let categories = []
            let periodeCatego = []
            // totalkeun hela
            for (let i in array) {
                array[i].tgl = new Date(array[i].tglpencarian).toDateString()//.substring(4, 10)
                array[i].total = parseFloat(array[i].total)
            }
            let samateuuu = false
            let sumKeun = [];
            for (let i in array) {
                samateuuu = false
                for (let x in sumKeun) {
                    if (sumKeun[x].tgl == array[i].tgl) {
                        sumKeun[x].total = parseFloat(sumKeun[x].total) + parseFloat(array[i].total)
                        sumKeun[x].tgl = array[i].tgl
                        samateuuu = true;
                    }
                }
                if (samateuuu == false) {
                    let result = {
                        tgl: array[i].tgl,
                        total: array[i].total,
                    }
                    sumKeun.push(result)
                }
            }
            let dataSeries = []
            for (let i in sumKeun) {
                dataSeries.push(sumKeun[i].total
                );
                categories.push(sumKeun[i].tgl.substring(4, 10))
                periodeCatego.push(sumKeun[i].tgl)
            }
            this.isShowTrendPen = false
            //console.log(sumKeun)
            Highcharts.chart('chartTrendPendapatan',{
                chart: {
                    type: 'area',
                    spacingBottom: 30
                },
                title: {
                    text: ''
                },

                subtitle: {
                    text: ''
                },
                xAxis: {
                    categories: categories,
                },
                yAxis: {
                    title: {
                        text: 'Jumlah'
                    }
                },

                legend: {
                    layout: 'horizontal',
                    // align: 'right',
                    borderRadius: 5,
                    borderWidth: 1,
                    // verticalAlign: 'middle'
                },
                plotOptions: {
                    // area: {
                    //     stacking: 'normal',
                    //     lineColor: '#666666',
                    //     lineWidth: 1,
                    //     marker: {
                    //         lineWidth: 1,
                    //         lineColor: '#666666'
                    //     }
                    // },
                    // line: {
                    //     dataLabels: {
                    //         enabled: true,
                    //         color: this.colors[1],

                    //         formatter: function () {
                    //             return 'Rp. ' + Highcharts.numberFormat(this.y, 0, '.', ',');
                    //         }
                    //     },
                    //     enableMouseTracking: false
                    // },
                    area: {
                        // url:"#",
                        cursor: 'pointer',

                        dataLabels: {
                            enabled: true,
                            color: this.colors[1],

                            formatter: function () {
                                return 'Rp. ' + Highcharts.numberFormat(this.y, 0, '.', ',');
                            }
                        },
                        showInLegend: true
                    },
                    series: {
                        cursor: 'pointer',
                    }
                },
                tooltip: {
                    formatter: function () {
                        let point = this.point,
                            s = this.series.name + ': Rp. ' + Highcharts.numberFormat(this.y, 0, '.', ',') + ' <br/>';
                        return s;

                    }

                },
                // plotOptions: {
                //     series: {
                //         label: {
                //             connectorAllowed: false
                //         },
                //         pointStart: 2010
                //     }
                // },

                series: [{
                    name: 'Trend Pendapatan Rumah Sakit Per-Minggu',
                    data: dataSeries,
                    color: '#00c0ef'
                }],
                credits: {
                    enabled: false
                },

                responsive: {
                    rules: [{
                        condition: {
                            maxWidth: 500
                        },
                        chartOptions: {
                            legend: {
                                layout: 'horizontal',
                                align: 'center',
                                verticalAlign: 'bottom'
                            }
                        }
                    }]
                }
            })
        }
        function getDetailChartPen(namess, tglawal, tglakhir) {
            $.ajax({
                type    : 'GET',
                url     : APP_URL+'/get-detail-pendapatan',
                data    : {tglawal:tglawal,tglakhir:tglakhir,ruangan:namess},
                success : function(respond){
                    document.getElementById("titleModalKun").innerHTML =   namess
                    $('#modalKunjungan').modal("show");
                    $("#load_kunjungan").html(respond);
                }
            })
        }

    </script>

@endsection
