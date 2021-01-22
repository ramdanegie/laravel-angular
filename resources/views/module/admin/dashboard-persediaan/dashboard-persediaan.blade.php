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
                    <h5>Dashboard Persediaan</h5>
                </div>
                <div class="card-block tab-icon">
                    <div class="row" style="margin-top:10px ">

                        <div class="col-lg-12 col-md-12">
                            <div class="card">
                                <div class="panel panel-warning">
                                    <div class="panel-heading bg-warning">
                                        Pemakiaan Obat
                                    </div>
                                    <div class="panel-body">
                                        <div class="row" style="padding:10px 20px;">
                                            <div class="col-md-12">
                                                <div class="table-responsive" >
                                                    <table id="t_ObatTren" class="table  table-striped table-sm table-styling"
                                                           style="width:100%">
                                                        <thead class="table-default">
                                                        <tr>
                                                            <th style="color:black;width: 10%">NO </th>
                                                            <th style="color:black">NAMA PRODUK </th>
                                                            <th style="color:black">JUMLAH  </th>
                                                            <th style="color:black">TOTAL  </th>
                                                        </tr>
                                                        </thead>
                                                        <tbody>
                                                        @php
                                                            $total=0;
                                                        @endphp
                                                        @forelse($res['obat']['data'] as $i => $d)
                                                            @php
                                                                $total=$total+ (float)$d->total;
                                                            @endphp
                                                            <tr>
                                                                <td >{{ $i +1 }}</td>
                                                                <td >{{ $d->namaproduk }}</td>
                                                                <td >{{ $d->jumlah}}</td>
                                                                <td>{{App\Http\Controllers\MainController::formatRp($d->total)}}</td>
                                                            </tr>
                                                        @empty
                                                            <tr>
                                                                <td colspan="4" style="text-align: center">Data Tidak ada</td>
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
                                <div class="panel panel-danger">
                                    <div class="panel-heading bg-danger">
                                        Stok Rumah Sakit
                                    </div>
                                    <div class="panel-body">
                                        <div class="row" style="padding:10px 20px;">
                                            <div class="col-md-12">
                                                <div class="table-responsive" >
                                                    <table id="t_Stok" class="table  table-striped table-sm table-styling"
                                                           style="width:100%">
                                                        <thead class="table-default">
                                                        <tr>
                                                            <th style="color:black;width: 10%">NO </th>
                                                            <th style="color:black">NAMA PRODUK </th>
                                                            <th style="color:black">SATUAN  </th>
                                                            <th style="color:black">STOK </th>
                                                            <th style="color:black"># </th>
                                                        </tr>
                                                        </thead>
                                                        <tbody>
                                                        @php
                                                            $total=0;
                                                            $sama = false;
                                                            $array=$res['stok'];
                                                            $resultSumDep = [];
                                                          for ($i = 0; $i < count($array); $i++) {
                                                            $sama = false;
                                                            if(count($resultSumDep) > 0){
                                                               for ($x = 0; $x < count($resultSumDep); $x++) {
                                                                    if ($resultSumDep[$x]['namaproduk'] == $array[$i]->namaproduk) {
                                                                        $sama = true;
                                                                        $resultSumDep[$x]['qtyproduk'] = (float) $resultSumDep[$x]['qtyproduk']+  (float)$array[$i]->qtyproduk;
                                                                    }
                                                                }
                                                            }
                                                            if ($sama == false) {
                                                                $resultSumDep []= [
                                                                    'namaproduk' =>  $array[$i]->namaproduk,
                                                                    'satuanstandar' =>  $array[$i]->satuanstandar,
                                                                    'qtyproduk' =>  $array[$i]->qtyproduk,
                                                                ];
                                                            }
                                                        }
                                                        @endphp
                                                        @forelse($resultSumDep as $i => $d)

                                                            <tr>
                                                                <td >{{ $i +1 }}</td>
                                                                <td >{{ $d['namaproduk'] }}</td>
                                                                <td >{{ $d['satuanstandar']}}</td>
                                                                <td>{{number_format((float) $d['qtyproduk'] ,2,".",",")}}</td>
                                                                <td ><a href='#'   class="btn btn-primary btn-outline-primary btn-mini click-stok"
                                                                        data-namaproduk="{{ $d['namaproduk'] }}" >
                                                                    <i class='icofont icofont-search'></i></a>
                                                                </td>
                                                            </tr>
                                                        @empty
                                                            <tr>
                                                                <td colspan="4" style="text-align: center">Data Tidak ada</td>
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
                                <div class="panel panel-info">
                                    <div class="panel-heading bg-info">
                                        10 Besar Pemakaian Obat
                                    </div>
                                    <div class="panel-body">
                                        <div id="chart10PemakaianObat"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-12 col-md-12">
                            <div class="card">
                                <div class="panel panel-primary">
                                    <div class="panel-heading bg-primary">
                                        Penerimaan Barang Harian
                                    </div>
                                    <div class="panel-body">
                                        <div class="row" style="padding:10px 20px;">
                                            <div class="col-md-12">
                                                <div class="table-responsive" >
                                                    @php
                                                       $pen =  App\Http\Controllers\MainController::getDaftarPenerimaanSuplier(request()->get("tglawal"),request()->get("tglakhir"),request());
                                                    @endphp
                                                    <table id="t_Penerimaan" class="table  table-striped table-sm table-styling"
                                                           style="width:100%">
                                                        <thead class="table-default">
                                                        <tr>
                                                            <th style="color:black;width: 10%">No </th>
                                                            <th style="color:black">No Dokumen </th>
                                                            <th style="color:black">No PO  </th>
                                                            <th style="color:black">Tanggal  </th>
                                                            <th style="color:black">Supplier  </th>
                                                            <th style="color:black">Jumlah Item  </th>
                                                            <th style="color:black">Total Tagihan  </th>
                                                        </tr>
                                                        </thead>
                                                        <tbody>
                                                        @php
                                                            $total=0;
                                                        @endphp
                                                     
                                                       @forelse($pen as $i => $d)

                                                            <tr>
                                                                <td >{{ $i +1 }}</td>
                                                                <td >{{ $d['nofaktur'] }}</td>
                                                                <td >{{ $d['nosppb']}}</td>
                                                                <td >{{ $d['tglstruk']}}</td>
                                                                <td >{{ $d['namarekanan']}}</td>
                                                                <td >{{ $d['jmlitem']}}</td>
                                                                <td>{{App\Http\Controllers\MainController::formatRp($d['totalharusdibayar'])}}</td>
                                                            </tr>
                                                        @empty
                                                            <tr>
                                                                <td colspan="7" style="text-align: center">Data Tidak ada</td>
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
                        <div class="col-lg-12 col-md-12">
                            <div class="card">
                                <div class="panel panel-info">
                                    <div class="panel-heading bg-info">
                                        Pengeluaran Barang Harian
                                    </div>
                                    <div class="panel-body">
                                        <div class="row" style="padding:10px 20px;">
                                            <div class="col-md-12">
                                                @php
                                                    $penge =  App\Http\Controllers\MainController::getDaftarDistribusiBarangPerUnit(request()->get("tglawal"),request()->get("tglakhir"),request());
                                                @endphp
                                                <div class="table-responsive" >
                                                    <table id="t_Pengeluaran" class="table  table-striped table-sm table-styling"
                                                           style="width:100%">
                                                        <thead class="table-default">
                                                        <tr>
                                                            <th style="color:black;width: 10%">No </th>
                                                            <th style="color:black">Tanggal </th>
                                                            <th style="color:black">No Pengeluaran  </th>
                                                            <th style="color:black">Ruang Asal  </th>
                                                            <th style="color:black">Ruang Tujuan  </th>
                                                            <th style="color:black">Nama Barang  </th>
                                                            <th style="color:black">Satuan  </th>
                                                            <th style="color:black">Jenis  </th>
                                                            <th style="color:black">Qty   </th>
                                                            <th style="color:black">Harga Satuan   </th>
                                                            <th style="color:black">Total   </th>
                                                        </tr>
                                                        </thead>
                                                        <tbody>
                                                        @php
                                                            $total=0;
                                                        @endphp
                                                        @forelse($penge as $i => $d)
                                                            <tr>
                                                                <td >{{ $i +1 }}</td>
                                                                <td >{{ $d->tglkirim }}</td>
                                                                <td >{{ $d->nokirim}}</td>
                                                                <td >{{ $d->ruanganasal}}</td>
                                                                <td >{{ $d->ruangantujuan}}</td>
                                                                <td >{{ $d->namaproduk}}</td>
                                                                <td >{{ $d->satuanstandar}}</td>
                                                                <td >{{ $d->jenisproduk}}</td>
                                                                <td >{{ $d->qtyproduk}}</td>
                                                                <td>{{App\Http\Controllers\MainController::formatRp($d->hargasatuan)}}</td>
                                                                <td>{{App\Http\Controllers\MainController::formatRp($d->total)}}</td>
                                                            </tr>
                                                        @empty
                                                            <tr>
                                                                <td colspan="11" style="text-align: center">Data Tidak ada</td>
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
                    <table id="tStokD" class="table  table-striped table-sm table-styling"
                           style="width:100%">
                        <thead class="table-default">
                        <tr>
                            <th style="color:black;width: 10%">No </th>
                            <th style="color:black">Nama Ruangan </th>
                            <th style="color:black">Produk  </th>
                            <th style="color:black">Satuan  </th>
                            <th style="color:black">Stok  </th>
                        </tr>
                        </thead>
                        <tbody id="tBodyStokD">
                        </tbody>
                    </table>
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
        setChart()
        let datazz = @json($res['stok']);
        $(".click-stok").click(function(e) {
            e.preventDefault();
            var namaproduk = $(this).attr("data-namaproduk");
            var datas =[];

            for (let i in datazz) {
                if (namaproduk == datazz[i].namaproduk) {
                    datazz[i].qtyproduk = parseFloat(datazz[i].qtyproduk)
                    datas.push(datazz[i])
                }
            }
            $('#tBodyStokD').empty();
            var trHTML = "";

            $.each(datas, function (i, item) {
                trHTML += "<tr><td style='width:40px' >" + (i+1)
                    + "</td><td  >" + item.namaruangan
                    + "</td><td  >" + item.namaproduk
                    + "</td><td  >" + item.satuanstandar
                    + "</td><td  >" + item.qtyproduk
                    + "</td></tr> ";
            });
            $('#tStokD').append(trHTML);
            $('#modalKunjungan').modal("show");
        })
        function setChart(){
            var result =@json($res['trend']);
            let series = [];
            let categories = [];
            let loopIndex = 0;
            for (let i in result.chart) {
                categories.push(result.chart[i].namaproduk);
                let dataz2 = [];
                dataz2.push({
                    y: parseFloat(result.chart[i].jumlah),
                    color: this.colors[i]
                });
                if (loopIndex < 10)
                    series.push({
                        name: result.chart[i].namaproduk,
                        data: dataz2
                    });
                loopIndex++;

            }
            Highcharts.chart('chart10PemakaianObat', {
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
                        text: 'Pemakaian Obat'
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
                                return Highcharts.numberFormat(this.y, 0, '.', ',');
                            }
                        },
                        showInLegend: true
                    }
                },
                tooltip: {
                    formatter: function () {
                        let point = this.point,
                            s = this.x + ':' + Highcharts.numberFormat(this.y, 0, '.', ',')
                        return s;
                    }
                },
                series: series,
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
