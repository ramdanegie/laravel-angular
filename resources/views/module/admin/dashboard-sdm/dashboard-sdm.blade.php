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
                    <h5>Dashboard SDM</h5>
                </div>
                <div class="card-block tab-icon">
                        <div class="row" style="margin-top:10px ">
                            <!-- order  start -->
                            <div class="col-md-12 col-lg-4"  style="cursor: pointer" onclick="detailPegawai('Aktif')">
                                <div class="card bg-c-yellow order-card">
                                    <div class="card-block">
                                        <h6>AKTIF</h6>
                                        <h2>{{$res['pegawai']['aktif']}}</h2>
                                        <p class="m-b-0"> <i class="feather icon-arrow-up"></i></p>
                                        <i class="card-icon feather icon-filter"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12 col-lg-4" style="cursor: pointer" onclick="detailPegawai('Non Aktif')">
                                <div class="card bg-c-blue order-card">
                                    <div class="card-block">
                                        <h6>NON AKTIF</h6>
                                        <h2>{{ $res['pegawai']['nonaktif']}}</h2>
                                        <p class="m-b-0"> <i class="feather icon-arrow-up"></i></p>
                                        <i class="card-icon feather icon-radio"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12 col-lg-4" >
                                <div class="card bg-c-green order-card">
                                    <div class="card-block">
                                        <h6>TOTAL PEGAWAI</h6>
                                        <h2>{{$res['pegawai']['aktif']+ $res['pegawai']['nonaktif']}}</h2>
                                        <p class="m-b-0"> <i class="feather icon-arrow-up"></i></p>
                                        <i class="card-icon feather icon-users"></i>
                                    </div>
                                </div>
                            </div>
                            <!-- order  end -->
                            <div class="col-lg-6 col-md-12">
                                <div class="card">
                                    <div class="panel panel-warning">
                                        <div class="panel-heading bg-warning">
                                          Jenis Pegawai
                                        </div>
                                        <div class="panel-body">
                                            <div id="chartStatusPegawai"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6 col-md-12">
                                <div class="card">
                                    <div class="panel panel-info">
                                        <div class="panel-heading bg-info">
                                            Jenis Kelamin
                                        </div>
                                        <div class="panel-body">
                                            <div id="chartJK"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6 col-md-12">
                                <div class="card">
                                    <div class="panel panel-primary">
                                        <div class="panel-heading bg-primary">
                                            Unit Kerja
                                        </div>
                                        <div class="panel-body">
                                            <div id="chartUnitKerja"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6 col-md-12">
                                <div class="card">
                                    <div class="panel panel-danger">
                                        <div class="panel-heading bg-danger">
                                            Kelompok Jabatan
                                        </div>
                                        <div class="panel-body">
                                            <div id="chartKelompok"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-12 col-md-12">
                                <div class="card">
                                    <div class="panel panel-dark">
                                        <div class="panel-heading bg-dark">
                                            Usia
                                        </div>
                                        <div class="panel-body">
                                            <div id="chartUsia"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6 col-md-12">
                                <div class="card">
                                    <div class="panel panel-warning">
                                        <div class="panel-heading  bg-c-kuning">
                                            Layananan Dokter
                                        </div>
                                        @php

                                            $sama = false;
                                            $resultSumDep = [];
                                            $array =   App\Http\Controllers\MainController::getLaporanLayanan();
                                           // dd($array);
                                            for ($i = 0; $i < count($array); $i++) {
                                                $sama = false;
                                                if(count($resultSumDep) > 0){
                                                   for ($x = 0; $x < count($resultSumDep); $x++) {
                                                        if ($resultSumDep[$x]['dokter'] == $array[$i]->dokter) {
                                                            $sama = true;
                                                            $resultSumDep[$x]['count'] = (float) $resultSumDep[$x]['count']+  (float)$array[$i]->count;
                                                        }
                                                    }
                                                }
                                                if ($sama == false) {
                                                    $resultSumDep []= [
                                                        'iddokter' => $array[$i]->iddokter,
                                                        'dokter' =>  $array[$i]->dokter,
                                                        'count' =>  $array[$i]->count,
                                                    ];
                                                }
                                            }

                                        @endphp
                                        <div class="panel-body" >
                                            <div class="row" style="padding:10px 20px;">
                                                <div class="col-md-12">
                                                    <div class="table-responsive" >
                                                        <table class="table  table-striped table-sm table-styling" id="t_Layanan"
                                                               style="width:100%">
                                                            <thead class="table-default">
                                                            <tr>
                                                                <th style="color:black;width: 10%">NO </th>
                                                                <th style="color:black">DOKTER </th>
                                                                <th style="color:black">JUMLAH LAYANAN </th>
                                                            </tr>
                                                            </thead>
                                                            <tbody>

                                                            @forelse($resultSumDep as $i => $d)

                                                                <tr>
                                                                    <td >{{ $i +1 }}</td>
                                                                    <td >{{ $d['dokter'] }}</td>
                                                                    <td >{{ $d['count'] }}</td>
                                                                </tr>
                                                            @empty
                                                                <tr>
                                                                    <td colspan="3" style="text-align: center">Data Tidak ada</td>
                                                                </tr>
                                                            @endforelse

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
                                    <div class="panel panel-aqua">
                                        <div class="panel-heading bg-aqua">
                                            Pendidikan
                                        </div>
                                        <div class="panel-body">
                                            <div id="chartPdd"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

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
        })
        var APP_URL = {!! json_encode(url('/')) !!}
        setChartJenis()
        setChartJK()
        setChartUnitKerja()
        setChartKel()
        setChartUsia()
        setChartPdd()
        function setChartJenis(){
            let jumlahKatPegawai = @json($res['pegawai']['jenispegawai']);
            let seriesKatPegawai = []
            let slice = true
            let totalAll = 0
            for (let z in jumlahKatPegawai) {
                if (jumlahKatPegawai[z].jenis == null)
                    jumlahKatPegawai[z].jenis = '-'
                totalAll = totalAll + parseFloat(jumlahKatPegawai[z].total)
                let datana = [];
                datana.push({
                    y: parseFloat(jumlahKatPegawai[z].total),
                    color: this.colors[z],
                    drilldown: jumlahKatPegawai[z].jenis
                });
                seriesKatPegawai.push({
                    name: jumlahKatPegawai[z].jenis,
                    y: parseFloat(jumlahKatPegawai[z].total),
                    sliced: slice,
                    selected: slice
                });
                slice = false;
            }
            Highcharts.chart('chartStatusPegawai', {
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
                            s = this.percentage.toFixed(2) + ' %';//this.key + ': ' + Highcharts.numberFormat(this.y, 0, '.', ',') + ' <br/>';
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
                                return this.key + ': ' + Highcharts.numberFormat(this.y, 0, '.', ',') + ' <br/>';// this.percentage.toFixed(2) + ' %';
                            }
                        },
                        showInLegend: true
                    },

                },
                credits: {
                    text: 'Total : ' + totalAll
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
                    data: seriesKatPegawai

                }]
            })
        }
        function setChartJK(){
            let jumlahKatPegawai = @json($res['pegawai']['jeniskelamin']);
            let seriesKatPegawai = []
            let slice = true
            let totalAll = 0
            for (let z in jumlahKatPegawai) {
                if (jumlahKatPegawai[z].jeniskelamin == null)
                    jumlahKatPegawai[z].jeniskelamin = '-'
                totalAll = totalAll + parseFloat(jumlahKatPegawai[z].total)
                let datana = [];
                datana.push({
                    y: parseFloat(jumlahKatPegawai[z].total),
                    color: this.colors[z],
                    drilldown: jumlahKatPegawai[z].jeniskelamin
                });
                seriesKatPegawai.push({
                    name: jumlahKatPegawai[z].jeniskelamin,
                    y: parseFloat(jumlahKatPegawai[z].total),
                    sliced: slice,
                    selected: slice
                });
                slice = false;
            }
            Highcharts.chart('chartJK', {
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
                            s = this.percentage.toFixed(2) + ' %';//this.key + ': ' + Highcharts.numberFormat(this.y, 0, '.', ',') + ' <br/>';
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
                                return this.key + ': ' + Highcharts.numberFormat(this.y, 0, '.', ',') + ' <br/>';// this.percentage.toFixed(2) + ' %';
                            }
                        },
                        showInLegend: true
                    },

                },
                credits: {
                    text: 'Total : ' + totalAll
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
                    data: seriesKatPegawai

                }]
            })
        }
        function setChartUnitKerja(){
            let jumlahKatPegawai = @json($res['pegawai']['unitkerja2']);
            let seriesKatPegawai = []
            let slice = true
            let totalAll = 0
            for (let z in jumlahKatPegawai) {
                if (jumlahKatPegawai[z].unitkerja == null)
                    jumlahKatPegawai[z].unitkerja = '-'
                totalAll = totalAll + parseFloat(jumlahKatPegawai[z].total)
                let datana = [];
                datana.push({
                    y: parseFloat(jumlahKatPegawai[z].total),
                    color: this.colors[z],
                    drilldown: jumlahKatPegawai[z].unitkerja
                });
                seriesKatPegawai.push({
                    name: jumlahKatPegawai[z].unitkerja,
                    y: parseFloat(jumlahKatPegawai[z].total),
                    sliced: slice,
                    selected: slice
                });
                slice = false;
            }
            Highcharts.chart('chartUnitKerja', {
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
                            s = this.percentage.toFixed(2) + ' %';//this.key + ': ' + Highcharts.numberFormat(this.y, 0, '.', ',') + ' <br/>';
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
                                return this.key + ': ' + Highcharts.numberFormat(this.y, 0, '.', ',') + ' <br/>';// this.percentage.toFixed(2) + ' %';
                            }
                        },
                        showInLegend: true
                    },

                },
                credits: {
                    text: 'Total : ' + totalAll
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
                    data: seriesKatPegawai

                }]
            })
        }
        function setChartUsia(){
            let jumlahUsia = @json($res['pegawai']['usia']);
            let seriesUsia = []
            for (let z in jumlahUsia) {
                let datana = [];
                datana.push({
                    y: parseFloat(jumlahUsia[z].total),
                    color: this.colors[z],
                });

                seriesUsia.push({
                    name: jumlahUsia[z].usia,
                    data: datana
                });
            }
            Highcharts.chart('chartUsia', {
                chart: {
                    type: 'column',
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
                        text: 'Usia'
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
                                return Highcharts.numberFormat(this.y, 0, '.', ',') + ' Pegawai';
                            }
                        },
                        showInLegend: true
                    }
                },
                tooltip: {
                    formatter: function () {
                        let point = this.point,
                            s = this.x + ':' + Highcharts.numberFormat(this.y, 0, '.', ',') + ' Pegawai <br/>';
                        return s;

                    }
                    // headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
                    // pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
                    //     '<td style="padding:0"><b>{point.y:.1f} </b></td></tr>',
                    // footerFormat: '</table>',
                    // shared: true,
                    // useHTML: true
                },
                series: seriesUsia,
                exporting: {
                    enabled: false
                },
                credits: {
                    enabled: false
                },
                legend: {
                    enabled: true,
                    borderRadius: 5,
                    borderWidth: 1
                },

            })
        }
        function setChartKel(){
            let jumlahKatPegawai = @json($res['pegawai']['kelompokjabatan']);
            let seriesKatPegawai = []
            let slice = true
            let totalAll = 0
            for (let z in jumlahKatPegawai) {
                if (jumlahKatPegawai[z].namakelompokjabatan == null)
                    jumlahKatPegawai[z].namakelompokjabatan = '-'
                totalAll = totalAll + parseFloat(jumlahKatPegawai[z].total)
                let datana = [];
                datana.push({
                    y: parseFloat(jumlahKatPegawai[z].total),
                    color: this.colors[z],
                    drilldown: jumlahKatPegawai[z].namakelompokjabatan
                });
                seriesKatPegawai.push({
                    name: jumlahKatPegawai[z].namakelompokjabatan,
                    y: parseFloat(jumlahKatPegawai[z].total),
                    sliced: slice,
                    selected: slice
                });
                slice = false;
            }
            Highcharts.chart('chartKelompok', {
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
                            s = this.percentage.toFixed(2) + ' %';//this.key + ': ' + Highcharts.numberFormat(this.y, 0, '.', ',') + ' <br/>';
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
                                return this.key + ': ' + Highcharts.numberFormat(this.y, 0, '.', ',') + ' <br/>';// this.percentage.toFixed(2) + ' %';
                            }
                        },
                        showInLegend: true
                    },

                },
                credits: {
                    text: 'Total : ' + totalAll
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
                    data: seriesKatPegawai

                }]
            })
        }
        function setChartPdd(){
            let jumlahKatPegawai = @json($res['pegawai']['pendidikan']);
            let seriesKatPegawai = []
            let slice = true
            let totalAll = 0
            for (let z in jumlahKatPegawai) {
                if (jumlahKatPegawai[z].pendidikan == null)
                    jumlahKatPegawai[z].pendidikan = '-'
                totalAll = totalAll + parseFloat(jumlahKatPegawai[z].total)
                let datana = [];
                datana.push({
                    y: parseFloat(jumlahKatPegawai[z].total),
                    color: this.colors[z],
                    drilldown: jumlahKatPegawai[z].pendidikan
                });
                seriesKatPegawai.push({
                    name: jumlahKatPegawai[z].pendidikan,
                    y: parseFloat(jumlahKatPegawai[z].total),
                    sliced: slice,
                    selected: slice
                });
                slice = false;
            }
            Highcharts.chart('chartPdd', {
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
                            s = this.percentage.toFixed(2) + ' %';//this.key + ': ' + Highcharts.numberFormat(this.y, 0, '.', ',') + ' <br/>';
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
                                return this.key + ': ' + Highcharts.numberFormat(this.y, 0, '.', ',') + ' <br/>';// this.percentage.toFixed(2) + ' %';
                            }
                        },
                        showInLegend: true
                    },

                },
                credits: {
                    text: 'Total : ' + totalAll
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
                    data: seriesKatPegawai

                }]
            })
        }
        function detailPegawai(namess) {
            $.ajax({
                type    : 'GET',
                url     : APP_URL+'/get-detail-pegawai',
                data    : {jenis:namess},
                success : function(respond){
                    document.getElementById("titleModalKun").innerHTML =   namess
                    $('#modalKunjungan').modal("show");
                    $("#load_kunjungan").html(respond);
                }
            })
        }
    </script>

@endsection
