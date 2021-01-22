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
        .label-cus {
            display: inline;
            /* padding: .2em .6em .3em; */
            padding: .3em .5em;
            /* font-size: 75%; */
            /* font-weight: 700; */
            line-height: 2;
            color: #fff;
            text-align: center;
            white-space: nowrap;
            vertical-align: baseline;
            border-radius: 20%;

        }
        .label-cus .success{
            background: linear-gradient(to right, #0ac282, #0df3a3);
        }
        .label-cus .default{
            background: linear-gradient(to right, #e0e0e0, #e0e0e0);
        }
    </style>
@endsection
@section('content-body')
    <div class="page-wrapper pad" >
        <div class="page-body">
            <div class="card">
                <div class="card-header">
                    <h5>Daftar Pasien Rawat Jalan</h5>

                </div>
                <div class=" card-block">
                    <form action="{!! route("show_page", ["role" => "admin", "pages" => $r->pages ])!!}" method="get">
                        <div class="row">
                            <div class="col-md-2 col-xs-12" style="margin-top: 5px">
                                <div class="input-group">
                                    <span class="input-group-addon" id="basic-addon1"><i class="ti-calendar"></i></span>
                                    <input type="text" id="src_tglAwal" name="src_tglAwal"
                                           class="date-custom form-control" value="{{request()->get("src_tglAwal")}}" >
                                </div>
                            </div>
                            <div class="col-md-2 col-xs-12" style="margin-top: 5px">
                                <div class="input-group">
                                    <span class="input-group-addon" id="basic-addon1"><i class="ti-calendar"></i></span>
                                    <input type="text" id="src_tglAkhir" name="src_tglAkhir"
                                           class="date-custom form-control" value="{{request()->get("src_tglAkhir")}}" >
                                </div>
                            </div>
                            <div class="col-md-3 col-xs-12" style="margin-top: 5px">
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-search"></i></span>
                                    <input type="text" name="src_nocm" id="src_nocm" class="form-control" placeholder="No RM / Nama" value="{{request()->get("src_nocm")}}">

                                </div>
                            </div>

                            <div class="col-md-2 col-xs-12" style="margin-top: 5px">
                                <div class="input-group" >
                                    <span class="input-group-addon" id="basic-addon1"><i class="fa fa-search"></i></span>
                                    <select id="cboRuangan" class="form-control js-example-basic-single"
                                            name="src_idRuangan"  >
                                        <option value="">-- Ruangan --</option>
                                        @php
                                            @endphp
                                        @foreach($ruangan as $k)
                                            <option {!! request()->get('src_idRuangan') == $k->id ? 'selected' : '' !!}
                                                    value='{{$k->id}}'  > {{ $k->namaruangan }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2 col-xs-12" style="margin-top: 5px">
                                <div class="input-group" >
                                    <span class="input-group-addon" id="basic-addon1"><i class="fa fa-search"></i></span>
                                    <select id="cboDokter" class="form-control js-example-basic-single"
                                            name="src_idDokter"  >
                                        <option value="">-- Dokter --</option>

                                        @foreach($dokter as $k)
                                            <option {!!   $k->id == request()->get('src_idDokter') ?  'selected' : '' !!}
                                                    value='{{$k->id}}'  > {{ $k->namalengkap }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>


                            <div class="col-lg-1" style="margin-top: 5px" >
{{--                                onclick="cari()"--}}
                                <button class="btn btn-success  btn-outline-success"  type="submit"><i class="icofont icofont-search"></i>Search</button>
                            </div>
                        </div>
                        <div class="row"  style='margin-top: 10px;'>
                            @forelse($data as $key => $p)
                                @php
                                    $jkName = '';
                                     $jk = 'fa-genderless';
                                    if($p->jkid == 1){
                                    $jk = 'fa-mars';
                                    $jkName = 'L';
                                    }
                                    if($p->jkid == 2){
                                    $jk = 'fa-venus';
                                    $jkName = 'P';
                                    }
                                @endphp
                                <div class="col-md-4 col-xs-12">
                                    <ul class="list-view">
                                        <li>
                                            <div class="card list-view-media">
                                                <div class="card-block">
                                                    <div class="media">
                                                        <a class="media-left" href="#">
                                                            <i class="feather icon-user class-icon"></i>
                                                            <!-- <img class="media-object card-list-img" src="..\files\assets\images\avatar-1.jpg" alt="Generic placeholder image"> -->
                                                        </a>
                                                        <div class="media-body">
                                                            <div class="col-xs-12">
                                                                <h6 class="d-inline-block">
                                                                    {{ $p->namapasien }}</h6>
                                                                <label class="label label-info">{{ $p->nocm }}</label>
                                                                <label class="label label-{{ $jk == 'L' ? 'success' : 'warning'}}"><i class="fa {{ $jk }} f-15"></i> </label>
                                                            </div>
                                                            <div class="f-13 text-muted m-b-15">
                                                                {{ $p->namaruangan }}
                                                            </div>
                                                            <p style="margin-top: -10px;">{{ $p->umur }}</p>
                                                            <p style="margin-top: -10px;" class="label label-{{  $p->namadokter != null ?'primary':'danger' }}">{{ $p->namadokter  !=  null ?  $p->namadokter   :'-' }}</p>
                                                            <span class="f-13 text-muted m-b-15"> Diagnosis : </span>
                                                            @if(count($p->kddiagnosa) > 0)
                                                                @foreach($p->kddiagnosa as $dg)
                                                                <label class="label label-success">
                                                                    {{ $dg  }}
                                                                </label>
                                                                 @endforeach
{{--                                                                <p style="margin-top:5px" >Status : Sudah Diperiksa</p>--}}
                                                                    <span class="f-13 text-muted m-b-15">|| Status : Sudah Diperiksa </span>
                                                            @else
                                                                <label class="label label-default">- </label>
{{--                                                                <p style="margin-top:5px">Status : Belum Diperiksa</p>--}}
                                                                <span class="f-13 text-muted m-b-15">|| Status : Belum Diperiksa </span>
                                                            @endif
                                                            <p style="margin-top:5px" class="label label-{!! $p->noreservasi !=null ? 'warning':'success' !!}"> <i class="fa fa-{!! $p->noreservasi !=null ? 'bookmark':'bookmark-o' !!} m-r-10"></i> {!! $p->noreservasi !=null ? 'Online':'Onsite' !!}</p>

                                                              <div class="m-t-15">
                                                                <a type="button" href="{!! route('show_page',[ 'pages'=> 'detail-billing','role'=>$_SESSION['role'],'norec_pd'=>$p->norec_pd ]) !!}" data-toggle="tooltip" title="Pemeriksaan" class="btn btn-facebook btn-mini waves-effect waves-light">
                                                                    <span class="fa fa-heartbeat"></span>
                                                                </a>
                                                                <a type="button" href="{!! route('show_page',[ 'pages'=> 'detail-registrasi','role'=>$_SESSION['role'],'norec_pd'=>$p->norec_pd ]) !!}"data-toggle="tooltip" title="Riwayat Registrasi" class="btn btn-dribbble btn-mini waves-effect waves-light">
                                                                    <span class="fa fa-history"></span>
                                                                </a>

                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            @empty
                                <div class="col-md-6 col-xs-12">
                                    <label  class="label label-info"> Tidak Ada Data </label>
                                </div>
                            @endforelse
                        </div>
                        <div style='margin-top: 10px;'>

                            <div class="row">
                                <div class="col-md-6 col-xs-12">
                                    @if ($data->hasMorePages())
                                        {!!
                                        $data->appends(["src_tglAwal"=>request()->get("src_tglAwal"),
                                        "src_tglAkhir"=>request()->get("src_tglAkhir"),
                                        "src_nocm"=>request()->get("src_nocm"),
                                        "src_idRuangan"=>request()->get("src_idRuangan"),
                                         "src_idDokter"=>request()->get("src_idDokter")
                                        ])
                                        ->links()
                                        !!}
                                    @endif
                                </div>

                            </div>
                            <div class="row" style="margin-top:10px">

                                <!-- statustic-card start -->
                                <div class="col-xl-4 col-md-12">
                                    <div class="card bg-c-yellow text-white">
                                        <div class="card-block">
                                            <div class="row align-items-center">
                                                <div class="col">
                                                    <p class="m-b-5">Total Pasien</p>
                                                    <h4 class="m-b-0">{!! $total['total'] !!}</h4>
                                                </div>
                                                <div class="col col-auto text-right">
                                                    <i class="fa fa-users f-50 text-c-yellow"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-4 col-md-12">
                                    <div class="card bg-c-green text-white">
                                        <div class="card-block">
                                            <div class="row align-items-center">
                                                <div class="col">
                                                    <p class="m-b-5">Terlayani</p>
                                                    <h4 class="m-b-0">{!! $total['terlayani'] !!}</h4>
                                                </div>
                                                <div class="col col-auto text-right">
                                                    <i class="fa fa-stethoscope f-50 "></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-4 col-md-12">
                                    <div class="card bg-c-pink text-white">
                                        <div class="card-block">
                                            <div class="row align-items-center">
                                                <div class="col">
                                                    <p class="m-b-5">Belum Terlayani</p>
                                                    <h4 class="m-b-0">{!! $total['belumterlayani'] !!}</h4>
                                                </div>
                                                <div class="col col-auto text-right">
                                                    <i class="fa fa-list-ol f-50 text-c-pink"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>


                        </div>

                    </form>
                </div>
            </div>
        </div>
        <div class="modal fade bs-example" id="modaledit" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-lg" style="    max-width: 800px">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" >Pindah Ruangan </h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div id="modal-body">
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade bs-example" id="modalpulang" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-lg" style="    max-width: 800px">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" >Rencana Pulang </h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div id="modal-body-pulang">
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade bs-example" id="modalpulang2" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-lg" style="    max-width: 800px">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" >Proses Pulang </h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div id="modal-body-pulang2">
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade bs-example" id="modalkonsul"  role="dialog" aria-hidden="false">
            <div class="modal-dialog modal-lg kons" style="    max-width: 1280px">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" >Rujuk Poli </h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div id="modal-body-konsul" >
                    </div>
                </div>
            </div>
        </div>
    </div>


@endsection

@section('javascript')
    <script>

        var APP_URL = {!! json_encode(url('/')) !!}
        function showPopPindah(norec){
            var namapasien = $("#namapasien").val()
            var departemen = $("#comboDepartemen").val()
            var ruangan = $("#comboRuangan").val()
            var lamarawats = $("#lamarawats").val()
            $("#modaledit").modal("show");
            let url ="{!! route("showDetailPindah", ":norec") !!}";
            url = url.replace('?:norec', '?norec='+norec);
            if(namapasien != ''){
                namapasien =namapasien.replace(' ','%20')
            }

            let newUrl = "{!! route("showDetailPindah") !!}?norec="+norec +"&namapasien="
                +namapasien + '&objectdepartemenfk='+departemen + '&ruanganfk='+ruangan
                +'&lamarawats='+lamarawats
            $("#modal-body").load(newUrl);
        }
        function showPopPulang(norec){
            var namapasien = $("#namapasien").val()
            var departemen = $("#comboDepartemen").val()
            var ruangan = $("#comboRuangan").val()
            var lamarawats = $("#lamarawats").val()
            $("#modalpulang").modal("show");
            let url ="{!! route("showDetailPulang", ":norec") !!}";
            url = url.replace('?:norec', '?norec='+norec);
            if(namapasien != ''){
                namapasien =namapasien.replace(' ','%20')
            }
            let newUrl = "{!! route("showDetailPulang") !!}?norec="+norec +"&namapasien=" +namapasien + '&objectdepartemenfk='+departemen + '&ruanganfk='+ruangan
                +'&lamarawats='+lamarawats
            $("#modal-body-pulang").load(newUrl);
        }
        function showProsesPulang(norec,nosurat){
            if(nosurat =='-'){
                add_toast("Belum ada rencana pulang","info");
                return;
            }
            var namapasien = $("#namapasien").val()
            var departemen = $("#comboDepartemen").val()
            var ruangan = $("#comboRuangan").val()
            var lamarawats = $("#lamarawats").val()

            if(namapasien != ''){
                namapasien =namapasien.replace(' ','%20')
            }
            let newUrl = "{!! route("showProsesPulang") !!}?norec="+norec +"&namapasien=" +namapasien + '&objectdepartemenfk='+departemen + '&ruanganfk='+ruangan
                +'&lamarawats='+lamarawats
            $("#modalpulang2").modal("show");
            $("#modal-body-pulang2").load(newUrl);
        }

        $("#comboDepartemen").val({{request()->get("objectdepartemenfk")}})
        $("#comboRuangan").val({{request()->get("ruanganfk")}})
        $('#isLoading').hide()

        $("#comboDepartemen").change(function(e){

            $.ajax({
                type    : 'GET',
                url     : APP_URL+'/get-ruangan-by-dept',
                data    : {dep: $("#comboDepartemen").val()},
                cache   : false,
                success : function(respond){

                    $("#comboRuangan").html(respond);
                    // $("#comboRuangan").val()

                }
            })
        })
        function cari(){
            var namapasien = $("#namapasien").val()
            var departemen = $("#comboDepartemen").val()
            var ruangan = $("#comboRuangan").val()
            var lamarawats = $("#lamarawats").val()

            window.location.href = "{!! route("daftarPasien",[ 'paginate'=>  request()->get("paginate"), ]) !!}&namapasien="+namapasien + '&objectdepartemenfk='+departemen + '&ruanganfk='+ruangan+'&lamarawats='+lamarawats
        }


        function showPopUpPoli(norec){
            var namapasien = $("#namapasien").val()
            var departemen = $("#comboDepartemen").val()
            var ruangan = $("#comboRuangan").val()
            var lamarawats = $("#lamarawats").val()
            $("#modalkonsul").modal("show");
            if(namapasien != ''){
                namapasien =namapasien.replace(' ','%20')
            }
            let newUrl = "{!! route("showPopUpKonsul") !!}?norec="+norec +"&namapasien=" +namapasien + '&objectdepartemenfk='+departemen + '&ruanganfk='+ruangan
                +'&lamarawats='+lamarawats
            $("#modal-body-konsul").load(newUrl);
        }
    </script>

@endsection
