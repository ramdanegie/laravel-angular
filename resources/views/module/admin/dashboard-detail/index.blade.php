
@extends('template.template')

@section('content-body')
<style>
    #modal-demografi-penyakit > .modal-lg{
        max-width:1140px;
    }

    .demografi-content{

    }

    .demografi-content__header{
        display: block;
    }

    .demografi-content__title{
        display:flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
    }

    .demografi-content__title > div:first-child{
        display:flex;
        align-items: center;
        font-weight: bold;
        flex-wrap: wrap;
    }

    .demografi-content__body{
        display: grid;
        grid-template-areas: 'chart table';
        grid-template-columns: 1fr 1fr;
        gap:1rem;
        height:550px;
        overflow-x: hidden;
    }

    .demografi-content__body > div{
        width:100%;
        position: relative;
        height: 100%;
        transition:all ease 0.3s;
    }

    .demografi-content__body > .demografi-content__full{
        grid-column: chart / table;
        display: flex;
        justify-content: center;
    }

    .demografi-content__body > .demografi-content__full + div{
        display: none;
    }
</style>
<div id="isLoading">
    @include('template.loader2')
</div>
<div class="page-wrapper">
    <!-- Page-header start -->
    <div class="page-header">
        <div class="row align-items-end">
            <div class="col-lg-10">
                <div class="page-header-title">
                    <div class="d-inline">
                        <h4>Detail Demografi </h4>
                        <!--                        <span>lorem ipsum dolor sit amet, consectetur adipisicing elit</span>-->
                    </div>
                </div>
            </div>
            <div class="col-lg-2" style="float: right;">
                <button style="float:right" class="btn btn-inverse btn-outline-inverse" onclick="back()"><i class="icofont icofont-arrow-left"></i>Back</button>
              <!--   <div class="page-header-breadcrumb">
                    <ul class="breadcrumb-title">
                        <li class="breadcrumb-item">
                            <a href="#"> <i class="feather icon-home"></i> </a>
                        </li>
                        <li class="breadcrumb-item"><a href="{!! route("show_page",["role"=>"admin","pages"=>'dashboard-v2'])!!}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item"><a href="#!">Detail</a>
                        </li>
                    </ul>
                </div> -->
            </div>
        </div>
    </div>
    <!-- Page-header end -->

    <!-- Page-body start -->
    <div class="page-body">
        <div class="row">
            <!--            <div class="col-12">-->
            <div class="col-md-12 col-xs-12">
                <div class="card">
                    <div class="card-header">
                        @php

                        if(count($map) == 0){
                        $namadiagnosa = '-';
                        }else{
                        $namadiagnosa =$map[0]->namadiagnosa;
                        }
                        @endphp
                        <h5>Provinsi {{ $namawilayah }} - <b style="font-weight: 500"> {{ ' Diagnosa [ '. $kddiagnosa.' '. $namadiagnosa .' ]' }}</b> {{ ' Periode : '.$tglawal.' - '.$tglakhir }} </h5>

                    </div>
                    <div class="card-block">
                        @php
                        $dataMap = [];
                        foreach($map as $m){
                             $dataMap [$m->kdmap] = (float) $m->jumlah;
                        }
                        @endphp
                        <div id="map-prov" style="width: 100%; height: 600px"></div>

                    </div>
                </div>
            </div>


        </div>
        <!--        </div>-->
    </div>
    <!-- Page-body end -->
</div>
<div class="modal fade" id="modalDetail" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg"  role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title"><span id="titleMap"></span></h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="loaddetailtable">
            </div>

            <div class="modal-footer">
            </div>
        </div>
    </div>
</div>
<input type="hidden" value="" id="kodekota" >
<div class="modal fade" id="modal-demografi-penyakit" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg"  role="document">
        <div class="modal-content demografi-content">
            <div class="modal-header">
                <h4 class="modal-title"><span id="titleModal"></span></h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <main class="modal-body demografi-content__body">
                <div class="demografi-content__full">
                    <canvas id="chartRS_ctx" ></canvas>
                </div>
                <div style="overflow: auto">
                    <span style="font-weight: bold" id="titleProfile"></span>
                    <div class="table-responsive">
                        <table class="table table-striped table-sm table-styling" id="table-rs" >
                            <thead>
                            <tr class="table-inverse">
                                <th>No </th>
                                <th>Tgl Registrasi </th>
                                <th>No Registrasi </th>
                                <th>No RM</th>
                                <th>Nama Pasien</th>
                                <th>Diagnosa </th>
                                <th>Alamat </th>
                                <th>Desa/Kelurahan </th>
                                <th>Kecamatan </th>
                                <th>Kota/Kab </th>
                                <th>Provinsi </th>
                            </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
            <div class="modal-footer"  >
            </div>
        </div>
    </div>
</div>
@endsection

@section('javascript')
<script src="{{ asset('js/jvectormap/indonesia-adm2-'.$kodewilayah.'.js') }}"></script>
<script src="{{ asset('js/public.js') }}"></script>

<script>
    function back(){
        window.history.back()
    }
    $(function(){

        var APP_URL = {!! json_encode(url('/')) !!}
        var gdpData2 = <?php echo json_encode($dataMap); ?>;;
        var map ='indonesia-adm2-'+{{ $kodewilayah }}+'_merc'
        let tableDemografiPenyakit = $('#table-rs').DataTable({
            dom: 'tp'
        });

        setMapProv()
        let chartDemografiPenyakit = new Chart(document.querySelector('#chartRS_ctx').getContext('2d'),{
            type:'pie',
            options:{
                maintainAspectRatio:false,
                responsive:true,
                events:['click','mousemove'],
                onClick:function(evt){
                    let chartInstance = this
                    let selectedChartArea = chartInstance.getElementsAtEvent(evt)

                    if (selectedChartArea.length > 0) {
                        demografiPenyakitDataDepthHandler(this,evt)
                    }
                }
            },
            data:{
                labels:[],
                datasets:[{
                    data:[],
                    borderWidth:0
                }]
            }
        });
        function   demografiPenyakitDataDepthHandler(chartInstance,evt){
            $('#isLoading').show()
            let selectedChartArea = chartInstance.getElementsAtEvent(evt)
             let namaProfile = chartInstance.data.labels[selectedChartArea[0]._index]
             // tableDemografiPenyakit.clear()
            $.ajax({
                type   : 'GET',
                url    : APP_URL+'/get-detail-rs-table?kddiagnosa='
                    +'{{$kddiagnosa}}'+'&kodekota='+ $('#kodekota').val()//paramChart.kodekota
                    +'&namaprofile='+namaProfile+'&tglawal={{$tglawal}}&tglakhir={{$tglakhir}}',
                cache  : false,
                success: function(response)
                {
                    $('#isLoading').hide()
                    tableDemografiPenyakit.clear()

                    document.getElementById("titleProfile").innerHTML = namaProfile

                    response.forEach(function(item, index){
                        tableDemografiPenyakit.row.add([
                            index+1,item.tglregistrasi,item.noregistrasi,
                            item.norm,  item.namapasien,  item.kddiagnosa +' - '+ item.namadiagnosa,
                            item.alamatlengkap, item.desakelurahan, item.kecamatan,
                            item.kotakabupaten, item.provinsi
                        ])
                    })

                    tableDemografiPenyakit.draw()
                    let chartWrapper = $('.demografi-content__body > div:first-child')
                    chartWrapper.removeClass('demografi-content__full')
                    let width = chartWrapper.parent().width() / 2
                    chartWrapper.width(width)

                }
            });
             chartInstance.update()

         }
          function chartDemografiPenyakitHandler(labels, data){
            chartDemografiPenyakit.data.labels = labels
            chartDemografiPenyakit.data.datasets[0].data = data
            chartDemografiPenyakit.data.datasets[0].backgroundColor= getColorChart()

            chartDemografiPenyakit.update()
        }
 
        function setMapProv() {
            $('#map-prov').vectorMap({
                map:map ,
                backgroundColor:'#cccccc',
                onRegionClick: function(e, code){
                    if(gdpData2[code] == undefined){
                        alert('Data tidak ada')
                        return
                    }
                    $('#isLoading').show()
                    $.ajax({
                        type   : 'GET',
                        // url    : APP_URL+'/get-name-kota/'+code+'/'+'{{ $kddiagnosa}}',
                        url    : APP_URL+'/get-chart-by-rs?kodekota='+code+'&kddiagnosa='+'{{ $kddiagnosa}}'+'&tglawal={{$tglawal}}&tglakhir={{$tglakhir}}',
                        cache  : false,
                        success: function(responsd)
                        {
                             $('#isLoading').hide()
                            document.getElementById("titleModal").innerHTML = responsd.kota.kotakabupaten +' Diagnosa [ {{ $kddiagnosa }} - {{ $namadiagnosa }} ]' ;
                             // document.getElementById("code").innerHTML
                              $('#kodekota').val(code);
                            let paramChart = {
                                'kddiagnosa' : '{{ $kddiagnosa }}',
                                'kodekota' : code
                            }
                            setChartRS(responsd.chart,paramChart)
                            tableDetailVisibilityHandler()
                            tableDemografiPenyakit.clear()
                            $('#modal-demografi-penyakit').modal("show");
                            // $.ajax({
                            //     type   : 'GET',
                            //     url    : APP_URL+'/get-detail-table-diagnosa?kddiagnosa={{$kddiagnosa}}&code='+code,
                            //     cache  : false,
                            //     success: function(res)
                            //     {
                            //         $("#loaddetailtable").html(res);
                            //         // $('#records_table').append(trHTML);
                            //         $('#modalDetail').modal("show");
                            //
                            //     }
                            // });
                        }
                    });


                },
                regionStyle: {
                    initial: {
                        // fill: '#128da7'
                    },
                    hover: {
                        fill: "#A0D1DC"
                    }
                },
                series: {
                    regions: [{
                        values: gdpData2,
                        // scale: ['#C8EEFF', '#0071A4'],
                        scale: ['#c7b3b3','#c99f9f','#c98585','#fcacac','#fa9393','#fa6969','#fa4848','#f72323', '#990a00'],
                        normalizeFunction: 'polynomial'
                    }]
                },
                onRegionTipShow: function(e, el, code){
                    var jml = 0
                    if(gdpData2[code] != undefined){
                        jml =gdpData2[code]
                    }
                    el.html(el.html()+' : '+jml);

                }
            });
        }


        function setChartRS(dataChart,paramChart) {
            let labels = []
            let datas = []
            for (let i = 0; i < dataChart.length; i++) {
                const element = dataChart[i]
                labels.push(element.namaprofile)
                datas.push(parseFloat(element.jumlah))
            }
            chartDemografiPenyakitHandler(labels,datas)
            // var config = {
            //     type: 'pie',
            //     data: {
            //         datasets: [{
            //             data: datas,
            //             backgroundColor: getColorChart(),
            //             label: 'Dataset 1'
            //         }],
            //         labels:labels
            //     },

            //     options: {
            //         maintainAspectRatio:false,
            //         responsive: true,
            //         events:['click','mousemove'],
            //         onClick:function(evt){
            //             let chartInstance = this
            //             let selectedChartArea = chartInstance.getElementsAtEvent(evt)
            //             if (selectedChartArea.length > 0) {


            //             }
            //         },
            //         legend:{
            //             position:'top'
            //         },
            //         tooltips: {
            //             enabled: true,

            //         },
            //         animation: {
            //             animateScale: true,
            //             animateRotate: true
            //         }
            //     }
            // };

            // var canvasRS = document.getElementById('chartRS_ctx').getContext('2d');
            // var chartRS = new Chart(canvasRS, config);

        }
        function tableDetailVisibilityHandler(){
            let chartWrapper = document.querySelector('.demografi-content__body > div:first-child')
            if(!chartWrapper.classList.contains("demografi-content__full")){
                chartWrapper.classList.add('demografi-content__full')
                chartWrapper.style.width = '100%'

            }
        }
    });
    //    https://github.com/nsetyo/jvectormap-indonesia/tree/master/maps/origin
</script>
@endsection
