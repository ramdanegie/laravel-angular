
@extends('template.template')
@section('css')
<style>
    .green {
        background-color: #00ff00;
        /*color: #000000;*/
        /*font-weight: bold;*/
    }

    .orange {
        background-color: #ff6600;
        color: #FFFFFF;
        /*font-weight: bold;*/
    }

    .koneng {
        background-color: #FFFF00;
        /*color: #191970;*/
        /*font-weight: bold;*/
    }
</style>
@endsection
@section('content-body')
    <div class="page-wrapper" style="padding-top: 0">
        <!-- Page-header start -->
        <div class="page-header m-t-50">
            <div class="row align-items-end">
                <div class="col-lg-10">
                    <div class="page-header-title">
                        <div class="d-inline">
                            <h4>DAFTAR PASIEN AKTIF</h4>
                            <span>RS DARURAT PENANGANAN COVID 19 WISMA ATLET KEMAYORAN TOWER 5</span>
{{--                            <span>Form Detail Ketersediaan Tempat Tidur Perkelas</span>--}}
                        </div>
                    </div>
                </div>
                <div class="col-lg-2" style="float: right;">
                    <button style="float:right" class="btn btn-inverse btn-outline-inverse" onclick="back()"><i class="icofont icofont-arrow-left"></i>Back</button>
                </div>
                    {{--            <div class="col-lg-4">--}}
                {{--                <div class="page-header-breadcrumb">--}}
                {{--                    <ul class="breadcrumb-title">--}}
                {{--                        <li class="breadcrumb-item">--}}
                {{--                            <a href="{!! route("show_page",["role"=> "user","pages"=>'pegawai'])!!}">--}}
                {{--                                <i class="icofont icofont-home"></i>--}}
                {{--                            </a>--}}
                {{--                        </li>--}}
                {{--                        <li class="breadcrumb-item"><a href="#!">Detail Pegawai</a></li>--}}

                {{--                    </ul>--}}
                {{--                </div>--}}
                {{--            </div>--}}
            </div>
        </div>
        <!-- Page-header end -->
        <!-- Page body start -->
        <div class="page-body">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-xs-12 col-sm-12">
                    <!-- Default card start -->
                    <div class="card">
                        <div class="card-header">
                            <h5>Cari Berdasarkan </h5>
                        </div>
                        <div class="card-block">
                            <form action="{!! route("daftarPasienAktif") !!}"  method="get">

                                <div class="row">
                                    <div class="col-lg-2">
                                        <div class="input-group" >
                                            <span class="input-group-addon" id="basic-addon1"><i class="fa fa-user"></i></span>
                                            <input type="text" id="nocm" name="nocm" class="form-control" placeholder=" No RM" value="{{request()->get("nocm")}}" >

                                        </div>
                                    </div>
                                    <div class="col-lg-3" style="margin-top: 4px">
                                        <div class="input-group" >
                                            <span class="input-group-addon" id="basic-addon1"><i class="fa fa-user"></i></span>
                                            <input type="text" name="namapasien" name="namapasien" class="form-control" placeholder=" Nama Pasien" value="{{request()->get("namapasien")}}" >

                                        </div>
                                    </div>
                                     <div class="col-lg-2" style="margin-top: 4px">
                                         <div class="input-group" >
                                            <span class="input-group-addon" id="basic-addon1"><i class="fa fa-arrow-up""></i></span>
                                           
                                            <select id="comboDepartemen" class="form-control js-example-basic-single" name="objectdepartemenfk"
                                            >
                                                <option value="">-- Instalasi --</option>
                                                @php
                                                    @endphp
                                                @foreach($departemen as $k)
                                                    <option 
                                                     {{ !empty(request()->get("objectdepartemenfk"))  ==  $k->id ? 'selected' : ''  }}
                                                    value='{{$k->id}}' > {{ $k->namadepartemen }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-2" style="margin-top: 4px">
                                        <div class="input-group" >
                                            <span class="input-group-addon" id="basic-addon1"><i class="fa fa-bed"></i></span>
                                            <select  id="comboRuangan"  class="form-control js-example-basic-single" name="ruanganfk"  >
                                                <option value="">-- Ruangan --</option>
                                                @foreach($listruangan as $k)
                                                    <option
                                                        {{ !empty(request()->get("ruanganfk"))  ==  $k->id ? 'selected' : ''  }}
                                                        value='{{ $k->id }}' > {{ $k->namaruangan }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="col-lg-3" style="margin-top: 4px">
                                        <button type="submit" class="btn btn-success"><i class="fa fa-search"></i>  Search </button>

                                        <button type="button" class="btn btn-danger button-clear" ><i class="fa fa-refresh"></i>  Clear </button>
                                    </div>
                                </div>


                            </form>
                            <hr>
                            <p>Terdapat [ <b>{{ $data->total() }}</b> ] Data Pasien</p>
                            <div class="table-responsive">
                                <table class="table  table-striped table-sm table-styling">
                                    <thead>
                                    <tr class="table-inverse">
                                        <th>No</th>
                                        <th>No RM</th>
                                        <th>Nama Pasien</th>
                                        <th>Jenis Kelamin</th>
                                        <th>Umur</th>
                                        <th>Tgl Masuk</th>
                                        <th>Lama Rawat</th>
                                        <th>Rencana Pulang</th>
                                        <th>Kamar</th>
                                        <th>Asal Rujukan</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @forelse($data->groupBy('namaruangan') as $key => $dat)
{{--                                    @forelse ($data as $key => $p)--}}
                                        @php
                                            $ru ='';
                                        @endphp
                                        @foreach( $dat as $key => $p)
                                            @if($ru != $p->namaruangan)
                                            <tr>
                                                <td style="color: white;text-align: left; background-color: #4d525aab;font-weight: bold;" colspan="10">
                                                  {{ $dat[0]->namaruangan }}
                                                </td>
                                            </tr>
                                                @php
                                                    $ru = $p->namaruangan;
                                                @endphp
                                            @endif
                                        @php
                                            $class='';
                                            if ($p->lamarawat == '5 Hari') { $class ='koneng' ;}
                                            if ($p->lamarawat == '9 Hari')  { $class ='orange'; }
                                            if ($p->lamarawat == '10 Hari') { $class ='green'; }
                                        @endphp
                                        <tr>
                                            <td>{{  $key+ $data->firstItem() }}</td>
                                            <td>{{ $p->nocm }}</td>
                                            <td>
                                                <span  style="color:#01a9ac"> {{ $p->namapasien }}</span>
                                            </td>
                                            <td>{{ $p->jeniskelamin }}</td>
                                            <td>{{ $p->umur }}</td>
                                            <td>{{date_format(date_create( $p->tglregistrasi ),"d-m-Y")}}</td>
                                            <td class="{{$class}}">{{ $p->lamarawat }}</td>
                                            <td>{{date_format(date_create( $p->rencanapulang ),"d-m-Y")}}</td>
                                            <td>{{ $p->kamarpasien }}</td>
                                            <td>{{ $p->asalrujukan }}</td>

                                        </tr>
                                            @endforeach
                                    @empty

                                    @endforelse
                                    </tbody>
                                </table>
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
                                    <div class="col-md-5 col-xs-12" >
                                    </div>
                                    <div class="col-md-1 col-xs-12" style="float: right">
                                        <select  class="form-control cbo-page" name="paginate" id="paginate"  >

                                            @foreach($listPage as $k)
                                                <option
                                                    {{ request()->get("paginate") ==  $k ? 'selected' : ''  }}
                                                    value='{{ $k}}' > {{ $k }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>


                            </div>
                        </div>
                    </div>
                    <!-- Default card end -->
                </div>

            </div>
        </div>
    </div>


@endsection

@section('javascript')
    <script>
         var APP_URL = {!! json_encode(url('/')) !!}
        $("#comboDepartemen").change(function(e){

            $.ajax({
                type    : 'GET',
                url     : APP_URL+'/get-ruangan-by-dept',
                data    : {dep: $("#comboDepartemen").val()},
                cache   : false,
                success : function(respond){
                 
                     $("#comboRuangan").html(respond);
                }
            })
        })
        $("#paginate").change(function(){
            window.location.href= "{!! route("daftarPasienAktif") !!}?paginate="+$("#paginate").val()
        })
        function back(){
            window.location.href = '{!! route("dataHarian") !!}'
        }
        $('.js-example-basic-single').select2();
        $(".button-clear").on("click", function(event) {
            $('#ruanganfk').val("");
            $('#nocm').val("");
            $('#namapasien').val("");
            $('#objectdepartemenfk').val("");
            $('#paginate').val(10);

            window.location.href = '{!! route("daftarPasienAktif") !!}'
        })
    </script>

@endsection

