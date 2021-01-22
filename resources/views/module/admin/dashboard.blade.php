
@extends('template.template')

@section('content-body')

<div class="page-wrapper" id="id_template">
    <!-- Page-header start -->
    <div class="page-header">
        <div class="row align-items-end">
            <div class="col-lg-8">
                <div class="page-header-title">
                    <div class="d-inline">
                        <h4>Dashboard </h4>
<!--                        <span>lorem ipsum dolor sit amet, consectetur adipisicing elit</span>-->
                    </div>
                </div>
            </div>
            <div class="col-lg-2">
                <input type="date" value="{{ $tglawal }}" name="tglawal" id="tglawal" class="form-control">
<!--                &nbsp; sd-->
            </div>
            <div class="col-lg-2">
                <input type="date" value="{!! $tglakhir !!}" name="tglakhir" id="tglakhir"  class="form-control">
<!--                <div class="page-header-breadcrumb">-->
<!--                    <ul class="breadcrumb-title">-->
<!--                        <li class="breadcrumb-item">-->
<!--                            <a href="#"> <i class="feather icon-home"></i> </a>-->
<!--                        </li>-->
<!--                        <li class="breadcrumb-item"><a href="#!">Dashboard</a>-->
<!--                        </li>-->

<!--                    </ul>-->
<!--                </div>-->
            </div>
        </div>
    </div>
    <!-- Page-header end -->

    <!-- Page-body start -->
    <div class="page-body">
        <div class="row">
<!--            <div class="col-12">-->
            <div class="col-md-6 col-xs-12">
                <div class="card">
                <div class="card-header">
                    <h5>10 Besar Diagnosa</h5>

                </div>
                <div class="card-block"  style="overflow: auto;height: 700px;">
                        <div class="dt-responsive table-responsive">
                            <table id="simpletable" class="table table-striped table-bordered nowrap">
                            <thead>
                            <tr>
                                <th>No</th>
                                <th>Diagnosa</th>
                                <th>Jumlah</th>
                                <th style="width: 80px;text-align: center;"># </th>
                            </tr>
                            </thead>
                            <tbody>
                            @php
                            $labelDiag= [];
                            $jmlDiag=[];
                            $data = $listdiagnosa;
                            $labelUmur=[] ;
                            $jmlUmur=[] ;
                            @endphp

                            @foreach($listdiagnosa as $key => $item)
                            @php
                                $labelDiag[] = $item->kddiagnosa;//.' - '.$item->namadiagnosa;
                                $jmlDiag[] = (float) $item->jumlah;
                            @endphp
                            <tr>
                                <td style="background-color: {{ $item->color }}"> {{$key + 1}}</td>
                                <td style="background-color: {{ $item->color }}"> {{$item->kddiagnosa .' - '. $item->namadiagnosa}}</td>
                                <td style="background-color: {{ $item->color }}"> {{$item->jumlah}}</td>
                                <td style="background-color: {{ $item->color }}">

                                    <a href="#" class="btn btn-primary btn-outline-primary btn-mini diagnosmap" data-kodediagnosa="<?php echo $item->kddiagnosa; ?>"
                                       class="diagnosmap"><i class="fa fa-search"></i></a>
                                </td>
                            </tr>
                            @endforeach

                            </tbody>

                        </table>
                    </div>
                </div>
            </div>
            </div>
            <div class="col-md-6 col-xs-12">
                <div class="card">
                    <div class="card-header">
                        <h5>Chart 10 Besar Diagnosa</h5>

                    </div>
                    <div class="card-block" style="overflow: auto;height: 700px;">
<!--                        <canvas id="myChart" width="400" height="400"></canvas>-->
                        <canvas id="chartDonut" ></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-12 col-xs-12">
                <div class="card">
                    <div class="card-header">
                        <h5>Demografi 10 Besar Diagnosa </h5>
                        <div class="card-header-right">
                            <ul class="list-unstyled card-option">
                                <li><i class="feather icon-maximize full-card"></i></li>
                                <li><i class="feather icon-minus minimize-card"></i></li>
                                <li><i class="feather icon-trash-2 close-card"></i></li>
                            </ul>
                        </div>
                    </div>
                    <div class="card-block">
                        @php
                        $dataMap =[];
                        foreach($map as $m){
                          $dataMap [$m->kdmap] = (float) $m->jumlah;
                        }
                        @endphp
                        <div id="map-indo" style="width: 100%; height: 650px"></div>
                    </div>
                </div>
            </div>
         </div>
<!--        </div>-->
    </div>
    <!-- Page-body end -->
</div>

@endsection

@section('javascript')
<script src="{{ asset('js/jvectormap/indonesia-adm1.js') }}"></script>
<script>
    "use strict";
    $(document).ready(function(){

    // $('#tglawal').bootstrapMaterialDatePicker
    // ({
    //     time: false,
    //     clearButton: false,
    //     dateFormat: 'yyyy-mm-dd'
    // });

    $("#simpletable").dataTable();

    var APP_URL = {!! json_encode(url('/')) !!}
    var kddiagnosaaa = '{!! $kddiagnosa !!}';

    showMapData(null,kddiagnosaaa);
    function loadData(){
        var tglawal = $("#tglawal").val();
        var tglakhir = $("#tglakhir").val();

        $.ajax({
            type    : 'GET',
            url     : APP_URL+'/get-data-dashboard',
            data    : {tglawal:tglawal,tglakhir:tglakhir},
            cache   : false,
            success : function(respond){
                console.log(respond)
                // $("#id_template").html(respond);
            }
        });
    }

        // $("#tglawal").change(function(e){
        //     // debugger
        //     e.preventDefault();
        //     loadData();
        // });
        // $("#tglakhir").change(function(e){
        //     // debugger
        //     e.preventDefault();
        //     loadData();
        // });


        $(".diagnosmap").click(function(e){
       // debugger
        e.preventDefault();
        var kddiagnosa = $(this).attr("data-kodediagnosa");

        $.ajax({
            type   : 'GET',
            url    : APP_URL+'/get-diagnosa-bykode-byrsaddress/'+kddiagnosa,
            cache  : false,
            success: function(respond)
            {
                console.log(respond)
                // $("#default-Modal").modal("show");
                showMapData(respond,kddiagnosa);
                // $("#modal-body").html(respond);
            }
        });
    });


    function showMapData(e,kddiagnosa) {
        if(e ==null){
            var gdpData2 = <?php echo json_encode($dataMap); ?>;
        }else{
            var gdpData2 = e
        }

        $('#map-indo').empty();
        $('#map-indo').vectorMap({
            map: 'indonesia-adm1_merc',
            backgroundColor:'#cccccc',
            onRegionClick: function(e, code){
                if(gdpData2[code] != undefined){
                    // console.log(e)
                    //  var myModal = new coreui.Modal(document.getElementById('exampleModalLong'),{});
                    $.ajax({
                        type   : 'GET',
                        url    : APP_URL+'/get-name-prov/'+code,
                        cache  : false,
                        success: function(responsd)
                        {

                            if(responsd.length> 0){
                                window.location.href= "{!! route("show_page",["role"=>"admin","pages"=>'dashboard-detail']) !!}?kddiagnosa="
                                + kddiagnosa +
                                "&code="+code
                                +"&namawilayah="+ responsd[0].provinsi;

                                // var url = "{{URL::to('pelayanan-detail/:code/:nama/:kddiagnosa')}}"
                                // url = url.replace(':code', code);
                                // url = url.replace(':kddiagnosa', kddiagnosa);
                                // url = url.replace(':nama',  responsd[0].provinsi);
                                // window.location.href=url;
                                // document.getElementById("titleMap").innerHTML = responsd[0].provinsi;
                            }else{
                                // document.getElementById("titleMap").innerHTML = '-';
                            }
                            // getMapDetail()
                            //  myModal.show()

                        }
                    });
                }else{
                    alert('Data Tidak ada')
                    // return
                }

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
                    scale: ['#c7b3b3','#c99f9f','#c98585','#fcacac','#fa9393','#fa6969','#fa4848','#f72323', '#990a00'],
                    normalizeFunction: 'polynomial'
                }]
            },
            onRegionTipShow: function(e, el, code){
                var jml = 0
                if(gdpData2[code] != undefined){
                    jml =gdpData2[code]
                }
                el.html(el.html()+' ('+kddiagnosa+' : '+jml+')');
            }
        });
    }
    function getMapDetail(){
        var gdpData = {
            "83": 16.63,
            "87": 11.58,
            "92": 158.97,

        };
        $('#map-prov').empty();
        $('#map-prov').vectorMap({
            map: 'indonesia-adm2-10_merc',
            backgroundColor:'#cccccc',
            onRegionClick: function(e, code){
                alert(code);
                console.log(e)
            },
            regionStyle: {
                initial: {
                    fill: '#128da7'
                },
                hover: {
                    fill: "#A0D1DC"
                }
            },
            series: {
                regions: [{
                    values: gdpData,
                    scale: ['#C8EEFF', '#0071A4'],
                    normalizeFunction: 'polynomial'
                }]
            },
            onRegionTipShow: function(e, el, code){
                el.html(el.html()+' (GDP - '+gdpData[code]+')');
            }
        });
    }

    });
    //    https://github.com/nsetyo/jvectormap-indonesia/tree/master/maps/origin
    var colors = [
        "#FF6384",
        "#4BC0C0",
        "#FFCE56",
        "#E7E9ED",
        "#36A2EB", '#7cb5ec', '#75b2a3', '#9ebfcc', '#acdda8', '#d7f4d2', '#ccf2e8',
        '#468499', '#088da5', '#00ced1', '#3399ff', '#00ff7f',
        '#b4eeb4', '#a0db8e', '#999999', '#6897bb', '#0099cc', '#3b5998',
        '#000080', '#191970', '#8a2be2', '#31698a', '#87ff8a', '#49e334',
        '#13ec30', '#7faf7a', '#408055', '#09790e'
    ]
    getChartDiagnosDonut();
    // getChartumur()
    function getChartDiagnosDonut(){
        var config = {
            type: 'doughnut',
            data: {
                datasets: [{
                    data: <?php echo json_encode($jmlDiag); ?>,
                    backgroundColor: colors,
                    label: 'Dataset 1'
                }],
                labels:<?php echo json_encode($labelDiag); ?>
            },

            options: {
                maintainAspectRatio:false,

                responsive: true,
                // legend:false,
                legend:{
                    position:'top'
                },
                tooltips: {
                    enabled: true,
                    callbacks: {
                        label: function(tooltipItem, data) {
                            var da = <?php echo json_encode($data) ?>;
                            // debugger;
                            var label = da[tooltipItem.index].kddiagnosa+' - ' + da[tooltipItem.index].namadiagnosa;
                            var val = data.datasets[tooltipItem.datasetIndex].data[tooltipItem.index];
                            var lenn= <?php echo json_encode($jmlDiag); ?>;
                            var tot = 0
                            for (var i = lenn.length - 1; i >= 0; i--) {
                                tot= tot+  lenn[i]
                            }
                            // debugger
                            // return label + ':' + val + ' (' + (100 * val / 130).toFixed(2) + '%)';
                            return label + ' :' +' (' + (val / tot *100).toFixed(2) + '%)';
                        }
                    }

                },
                animation: {
                    animateScale: true,
                    animateRotate: true
                }
            }
        };

        var ctx = document.getElementById('chartDonut').getContext('2d');
        var myDoughnut = new Chart(ctx, config);
    }
    function getChartumur(){
        var config = {
            type: 'pie',
            data: {
                datasets: [{
                    data: <?php echo json_encode($jmlUmur); ?>,
                    backgroundColor: colors,
                    label: 'Dataset 1'
                }],
                labels:<?php echo json_encode($labelUmur); ?>
            },
            options: {
                responsive: true,
                legend: {
                    position: 'top',
                },

                animation: {
                    animateScale: true,
                    animateRotate: true
                }
            }
        };

        var ctx = document.getElementById('chartUmur').getContext('2d');
        var myDoughnut = new Chart(ctx, config);
    }

</script>
@endsection
