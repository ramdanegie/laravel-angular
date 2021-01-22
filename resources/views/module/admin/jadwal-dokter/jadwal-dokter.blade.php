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
                    <h5>Jadwal Dokter</h5>

                </div>
                <div class=" card-block">
                    <form action="{!! route("show_page", ["role" => $_SESSION['role'], "pages" => $r->pages ]) !!}" method="get">
                        <div class="row">
{{--                            <div class="col-md-3 col-xs-12" style="margin-top: 5px">--}}
{{--                                <div class="input-group">--}}
{{--                                    <span class="input-group-addon"><i class="fa fa-search"></i></span>--}}
{{--                                    <input type="text" name="namapasien" id="namapasien" class="form-control" placeholder="Search" value="{{request()->get("namapasien")}}">--}}

{{--                                </div>--}}
{{--                            </div>--}}
                            <div class="col-md-3 col-xs-12" style="margin-top: 5px">
                                <div class="input-group" >
                                    <span class="input-group-addon" id="basic-addon1"><i class="fa fa-search"></i></span>

                                    <select id="cboDokter" class="form-control js-example-basic-single"
                                            name="dokterId"  >
                                        <option value="">-- Dokter --</option>

                                        @foreach($dokter as $k)
                                            <option {!!   $k->id == request()->get('dokterId') ?  'selected' : '' !!}
                                                    value='{{$k->id}}'  > {{ $k->namalengkap }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3 col-xs-12" style="margin-top: 5px">
                                <div class="input-group" >
                                    <span class="input-group-addon" id="basic-addon1"><i class="fa fa-search"></i></span>
                                    <select id="cboRuangan" class="form-control js-example-basic-single"
                                            name="ruanganId"  >
                                        <option value="">-- Ruangan --</option>
                                        @php
                                            @endphp
                                        @foreach($ruangan as $k)
                                            <option {!! request()->get('ruanganId') == $k->id ? 'selected' : '' !!}
                                                    value='{{$k->id}}'  > {{ $k->namaruangan }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-lg-1" style="margin-top: 5px" >
{{--                                onclick="cari()"--}}
                                <button class="btn btn-success  btn-outline-success"  type="submit">
                                    <i class="icofont icofont-search"></i>Search</button>
                            </div>
                        </div>
                        <div class="row"  style='margin-top: 10px;'>
                            @forelse($data->groupBy('namalengkap') as $key => $p)
                                @php
                                    $jkName = '';
                                    $label = 'default';
                                    $jk ='fa-genderless';
                                    if($p[0]->jkid == 1){
                                        $jk = 'fa-mars';
                                        $jkName = 'L';
                                          $label = 'primary';
                                    }
                                    if($p[0]->jkid == 2){
                                    $jk = 'fa-venus';
                                    $jkName = 'P';
                                       $label = 'danger';
                                    }
                                @endphp
                                 <div class="col-md-4 col-xs-12">
                                    <ul class="list-view">
                                        <li>
                                            <div class="card list-view-media">
                                                <div class="card-block">
                                                    <div class="media">
                                                        <a class="media-left" href="#">
                                                            <i class="fa fa-user-md class-icon"></i>
                                                            <!-- <img class="media-object card-list-img" src="..\files\assets\images\avatar-1.jpg" alt="Generic placeholder image"> -->
                                                        </a>
                                                        <div class="media-body">
                                                            <div class="col-xs-12">
                                                                <h6 class="d-inline-block">
                                                                    {{ $p[0]->namalengkap }}</h6>
{{--                                                                <label class="label label-info">{{ $p->nocm }}</label>--}}
                                                                    <label class="label label-{{ $label }}"><i class="fa {{ $jk }} f-15"></i> </label>
                                                            </div>
                                                            <div class="f-12 text-muted m-b-15">
                                                              {{ $p[0]->nip }}</h6>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class=" latest-update-card">
                                                    <div class="card-block">
                                                        <div class="latest-update-box">
                                                            @php

                                                            $icon = [
                                                                'feather icon-check bg-simple-c-yellow  update-icon',
                                                                'feather icon-briefcase bg-simple-c-pink update-icon',
                                                                'feather icon-facebook bg-simple-c-green update-icon',
                                                            ];
                                                            $i = 0;
                                                            @endphp
                                                            @foreach($p as $k => $det)
                                                            <div class="row p-b-15">
                                                                <div class="col-auto text-right update-meta">
                                                                    <p class="text-muted m-b-0 d-inline">{!! $det->namaruangan !!} </p>
                                                                    @php
                                                                    if($k== count($icon) ){
                                                                     $i = 0;
                                                                    }
                                                                    @endphp
                                                                    <i class="{!! $icon[$i] !!}"></i>
                                                                </div>
                                                                <div class="col">
                                                                    <h6>{!! $det->hari !!}</h6>
                                                                    <p class="text-muted m-b-0">
                                                                        <i class="feather icon-clock m-r-10"></i>

                                                                        <span  class="label label-success" style="    font-size: 13px;" > {!! $det->jammulai.'-'.$det->jamakhir !!}</span>
                                                                    </p>
                                                                  </div>
                                                            </div>
                                                                @php
                                                                     $i++;

                                                                @endphp
                                                                @endforeach

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
{{--                                    @if ($data->hasMorePages())--}}
{{--                                        {!!--}}
{{--                                        $data->appends(["nocm"=>request()->get("nocm"),--}}
{{--                                        "namapasien"=>request()->get("namapasien"),--}}
{{--                                        "ruanganfk"=>request()->get("ruanganfk"),--}}
{{--                                        "paginate"=>request()->get("paginate")--}}
{{--                                        ])--}}
{{--                                        ->links()--}}
{{--                                        !!}--}}
{{--                                    @endif--}}
                                </div>
                                <div class="col-md-3 col-xs-12">
                                </div>
{{--                                <div class="col-md-2 col-xs-12" >--}}
{{--                                    <div class="input-group">--}}
{{--                                        <span class="input-group-addon"><i class="fa fa-search"></i></span>--}}
{{--                                        <input type="text" name="ketkliniss" id="ketkliniss" class="form-control" placeholder="Ket Klinis" value="{{request()->get("ketkliniss")}}">--}}

{{--                                    </div>--}}
{{--                                </div>--}}
{{--                                <div class="col-md-1 col-xs-12" style="float: right">--}}
{{--                                    <select class="form-control cbo-page" name="paginate" id="paginate">--}}

{{--                                        @foreach($listPage as $k)--}}
{{--                                            <option {{ request()->get("paginate") ==  $k ? 'selected' : ''  }} value='{{ $k}}'> {{ $k }}</option>--}}
{{--                                        @endforeach--}}
{{--                                    </select>--}}
{{--                                </div>--}}
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
        $(document).ready(function() {
            $('.js-example-basic-single').select2();

        });
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
