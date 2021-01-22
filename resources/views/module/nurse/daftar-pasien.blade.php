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
</style>
@endsection
@section('content-body')
<div class="page-wrapper pad" >
    <div class="page-body">
        <div class="card">
            <div class="card-header">
                <h5>Daftar Pasien</h5>

            </div>
            <div class=" card-block">
                <form action="{!! route("show_page", ["role" => "admin",
                                "pages" => $r->pages,
                                "dokterId" => $r->dokterId,
                                "ruanganId" => $r->ruanganId]) !!}" method="get">
                    <div class="row">
                        <div class="col-md-3 col-xs-12" style="margin-top: 5px">
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-search"></i></span>
                                <input type="text" name="namapasien" id="namapasien" class="form-control" placeholder="Search" value="{{request()->get("namapasien")}}">

                            </div>
                        </div>
                         <div class="col-md-3 col-xs-12" style="margin-top: 5px">
                            <div class="input-group" >
                                <span class="input-group-addon" id="basic-addon1"><i class="fa fa-search"></i></span>

                                <select id="comboDepartemen" class="form-control js-example-basic-single" name="objectdepartemenfk"
                                >
                                    <option value="">-- Filter Instalasi --</option>
                                    @php
                                        @endphp
                                    @foreach($departemen as $k)
                                        <option value='{{$k->id}}' > {{ $k->namadepartemen }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3 col-xs-12" style="margin-top: 5px">
                            <div class="input-group" >
                                <span class="input-group-addon" id="basic-addon1"><i class="fa fa-search"></i></span>

                                <select id="comboRuangan" class="form-control js-example-basic-single" name="ruanganfk"
                                >
                                    <option value="">-- Filter Ruangan --</option>
                                    @php
                                        @endphp
                                    @foreach($listruangan as $k)
                                        <option value='{{$k->id}}' > {{ $k->namaruangan }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2 col-xs-12" style="margin-top: 5px">
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-search"></i></span>
                                <input type="text" name="lamarawats" id="lamarawats" class="form-control" placeholder="Lama Rawat" value="{{request()->get("lamarawats")}}">

                            </div>
                        </div>

                         <div class="col-lg-1" style="margin-top: 5px" >
                            <button class="btn btn-success  btn-outline-success" onclick="cari()" type="submit"><i class="icofont icofont-search"></i>Search</button>
                        </div>
                    </div>
                    <div class="row"  style='margin-top: 10px;'>
                        @forelse($data as $key => $p)
                        @php
                        $jkName = '';
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
                                                        {{ $p->kamarpasien }}
                                                    </div>
                                                    <p style="margin-top: -10px;">{{ $p->umur }}</p>
                                                    <p style="margin-top: -10px;">{{ 'Lama Rawat : '.$p->lamarawat }}</p>
                                                    @php
                                                        $ket = '-';
                                                    if($p->nosurat == ''){
                                                        if($p->norec_so != null ) {
                                                             $ket = 'Rencana Pulang '.$p->tglrencana ;
                                                       }
                                                    }else{
                                                        $ket = 'No Surat : '. $p->nosurat ;
                                                    }

                                                    @endphp
                                                    <p style="margin-top: -10px;" class="label label-{{  $p->norec_so == null ?'success':'warning' }}">{{ $ket }}</p>
                                                    <p style="margin-top: 10px;
                                                        border-radius: 4px;
                                                        font-size: 75%;
                                                        padding: 4px 7px;
                                                        margin-right: 5px;
                                                        font-weight: 400;"

                                                    class=" label-{{  $p->ketklinis }}">{{ 'Kondisi Klinis : '. $p->ketklinis }}</p>
                                                    <div class="m-t-15">
                                                        <button type="button" onclick="showPopPindah('{{ $p->norec_apd }}')" data-toggle="tooltip" title="Pindah Ruangan" class="btn btn-facebook btn-mini waves-effect waves-light">
                                                            <span class="fa fa-arrow-right"></span>
                                                        </button>
                                                        <button type="button" data-toggle="tooltip" title="Order Laboratorium" class="btn btn-twitter btn-mini waves-effect waves-light">
                                                            <span class="fa fa-flask"></span>
                                                        </button>
                                                        <button type="button" data-toggle="tooltip" title="Order Radiologi" class="btn btn-linkedin btn-mini waves-effect waves-light">
                                                            <span class="fa fa-code-fork"></span>
                                                        </button>
                                                        <button type="button" onclick="showPopUpPoli('{{ $p->norec_apd }}')" data-toggle="tooltip" title="Rujuk Poli" class="btn btn-dribbble btn-mini waves-effect waves-light">
                                                            <span class="fa fa-history"></span>
                                                        </button>
                                                        <button type="button" onclick="showPopPulang('{{ $p->norec_apd }}')" data-toggle="tooltip" title="Pulang" class="btn btn-danger btn-mini waves-effect waves-light">
                                                            <span class="fa fa-power-off"></span>
                                                        </button>
                                                        <button type="button" onclick="showProsesPulang('{{ $p->norec_apd }}','{{ $p->nosurat != ''?'ada':'-' }}')" data-toggle="tooltip" title="Proses Pulang" class="btn btn-danger btn-mini waves-effect waves-light">
                                                            <span class="fa fa-sign-out"></span>
                                                        </button>

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
                                $data->appends(["nocm"=>request()->get("nocm"),
                                "namapasien"=>request()->get("namapasien"),
                                "ruanganfk"=>request()->get("ruanganfk"),
                                "paginate"=>request()->get("paginate")
                                ])
                                ->links()
                                !!}
                                @endif
                            </div>
                            <div class="col-md-3 col-xs-12">
                            </div>
                            <div class="col-md-2 col-xs-12" >
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-search"></i></span>
                                    <input type="text" name="ketkliniss" id="ketkliniss" class="form-control" placeholder="Ket Klinis" value="{{request()->get("ketkliniss")}}">

                                </div>
                            </div>
                            <div class="col-md-1 col-xs-12" style="float: right">
                                <select class="form-control cbo-page" name="paginate" id="paginate">

                                    @foreach($listPage as $k)
                                    <option {{ request()->get("paginate") ==  $k ? 'selected' : ''  }} value='{{ $k}}'> {{ $k }}</option>
                                    @endforeach
                                </select>
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
