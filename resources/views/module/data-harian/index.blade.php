
@extends('template.template')
@section('css')
    <style>
        .padding-cell {
            /*padding-top: 1.25rem;*/
            /*padding-right: 7rem;*/
            /*padding-bottom: 1.25rem;*/
            /*padding-left: 7rem;*/

        }
        .form-control {
            display: block;
            width: 100%;
            padding: .5rem .75rem;
            font-size: 1rem;
            /* line-height: 1.25; */
            color: #495057;
            background-color: #fff;
            background-image: none;
            background-clip: padding-box;
            border: 1px solid rgba(0,0,0,.15);
            border-radius: .25rem;
            transition: border-color ease-in-out .15s,box-shadow ease-in-out .15s;
        }
        body table tr td {
            font-size: 10px;
        }
        #return-to-top {
            z-index: 999;
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: #404e67;
            width: 50px;
            height: 50px;
            display: block;
            text-decoration: none;
            -webkit-border-radius: 35px;
            -moz-border-radius: 35px;
            border-radius: 35px;
            display: none;
            -webkit-transition: all 0.3s linear;
            -moz-transition: all 0.3s ease;
            -ms-transition: all 0.3s ease;
            -o-transition: all 0.3s ease;
            transition: all 0.3s ease;
        }
        #return-to-top i {
            color: #fff;
            margin: 0;
            position: relative;
            left: 16px;
            top: 13px;
            font-size: 19px;
            -webkit-transition: all 0.3s ease;
            -moz-transition: all 0.3s ease;
            -ms-transition: all 0.3s ease;
            -o-transition: all 0.3s ease;
            transition: all 0.3s ease;
        }
        #return-to-top:hover {
            background: rgba(0, 0, 0, 0.62);
        }
        #return-to-top:hover i {
            color: #fff;
            top: 5px;
        }

        .b-b-default {
            border-bottom: none
        }
        .f-s-12{
            font-size: 1.2rem;
        }
        .brd {
            border: 1px solid rgba(69, 90, 100, 0.14);
            /*background: #01dbdf1f;*/
            background: -webkit-gradient(linear,left top,right top,from(#01dbdf40),to(#01dbdf1f));
            background: linear-gradient(to right,#01dbdf40,#01dbdf1f);
        }
        .left-c {
            /*border: 1px solid rgba(69, 90, 100, 0.14);*/
            /*background: #01dbdf1f;*/
            background: -webkit-gradient(linear,left top,right top,from(#01dbdf40),to(#01dbdf1f));
            background: linear-gradient(to right,#01dbdf40,#01dbdf1f);
        }
        .text-na {
            font-size: 1.2rem;
            border-bottom: none;
            border-bottom: 1px solid #e0e0e0;
            font-weight: 600;
            margin-bottom: 20px;
            padding-bottom: 5px!important;
        }
        .ml-20{
            margin-left: 20px;
        }
    </style>
@endsection
@section('content-body')
    <div id="isLoading">
        @include('template.loader2')
    </div>
    <a href="javascript:" id="return-to-top"><i class="fa fa-chevron-up"></i></a>
    <div class="row">

        <div class="col-md-8 col-xs-12">
                <div class="page-wrapper" >
                <form method="get" id="formAction"  action="{!! route("dataHarian") !!}" >
                    <div class="page-header">
                        <div class="row align-items-end">
                            <div class="col-lg-12 text-center">
                                <div class="page-header-title">
                                    <div class="d-inline ">
                                        <h4 style="font-size: 35px;">RS DARURAT PENANGANAN COVID 19 </h4>
                                        <h4 style="font-size: 35px;">WISMA ATLET KEMAYORAN </h4>
                                        <span style="font-size: 25px">{{ $result['now'] }}</span>
                                        <span style="font-size: 25px">{{ $result['hour'] }}</span>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <div class="row align-items-end">

                        </div>

                    </div>
                </form>
                <div class="page-body">
                    <div class="row">
                        <div class="col-md-12 col-xs-12">
                            <div class="card ">
                                <div class="card-header">
                                    <h5 style="font-size: 1.3rem;">DATA HARIAN</h5>
                                    <div class="card-header-right">
                                        <ul class="list-unstyled card-option">
                                            <li><i class="feather icon-maximize full-card"></i></li>
                                            <li><i class="feather icon-minus minimize-card"></i></li>
                                            <li><i class="feather icon-trash-2 close-card"></i></li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="card-block table-border-style">
                                    <div class="row">
                                      <div class="col-xl-12 col-md-12">
                                        <div class="card user-card-full brd">
                                            <div class="row m-l-0 m-r-0">
                                                <div class="col-sm-8">
                                                    <div class="card-block padding-cell">
                                                        <h6 class="m-b-20 p-b-5 b-b-default f-w-600 f-s-12" >Rawat Jalan</h6>
                                                        <h6 class="m-b-20 p-b-5 b-b-default f-w-600 f-s-12">Pasien Masuk Rawat Inap [Pasien Baru]</h6>
                                                        <h6 class="m-b-20 p-b-5 b-b-default f-w-600 f-s-12">Pasien Keluar Isolasi Mandiri </h6>
{{--                                                        Pasien Pulang--}}
                                                        <h6 class="m-b-20 p-b-5 b-b-default f-w-600 f-s-12" style="margin-left: 20px">
                                                            <i class="fa fa-check-square" style="margin-right: 5px"></i>
                                                            Pulang Kasus Konfirmasi Positif Covid 19 Sembuh </h6>
{{--                                                        Kasus Konfirmasi Covid 19 Sembuh--}}
                                                        <h6 class="m-b-20 p-b-5 b-b-default f-w-600 f-s-12" style="margin-left: 20px">
                                                            <i class="fa fa-check-square" style="margin-right: 5px"></i>
                                                            Pulang Kasus Konfirmasi Positif Melanjutkan Isolasi Mandiri
                                                        </h6>
{{--                                                            Kasus Konfirmasi yang Melanjutkan Isolasi Mandiri</h6>--}}
                                                        <h6 class="m-b-20 p-b-5 b-b-default f-w-600 f-s-12" style="margin-left: 20px">
                                                            <i class="fa fa-check-square" style="margin-right: 5px"></i>
                                                           Keluar Isolasi Mandiri ke Tower Lainnya karena Simtomatik
                                                        </h6>
                                                        <h6 class="m-b-20 p-b-5 b-b-default f-w-600 f-s-12">Rujuk</h6>
                                                    </div>
                                                </div>
                                                <div class="col-sm-4 bg-c-lite-green ">
                                                    <div class="card-block text-center text-white" style="">
                                                        <h6 class="m-b-20 p-b-5 b-b-default f-w-600 f-s-12">{{ $result['harianRajal'] }} </h6>
                                                        <h6 class="m-b-20 p-b-5 b-b-default f-w-600 f-s-12">{{ $result['harianRanap'] }} </h6>
                                                        <h6 class="m-b-20 p-b-5 b-b-default f-w-600 f-s-12">{{ $result['pulangSembuh']  + $result['pulangIsolasi'] + $result['pulangRujukTowerLain']}} </h6>
                                                        <h6 class="m-b-20 p-b-5 b-b-default f-w-600 f-s-12">{{ $result['pulangSembuh'] }} </h6>
                                                        <h6 class="m-b-20 p-b-5 b-b-default f-w-600 f-s-12">{{ $result['pulangIsolasi'] }} </h6>
                                                        <h6 class="m-b-20 p-b-5 b-b-default f-w-600 f-s-12">{{ $result['pulangRujukTowerLain'] }} </h6>
                                                        <h6 class="m-b-20 p-b-5 b-b-default f-w-600 f-s-12">{{ $result['rujuk'] }} </h6>
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
                    <div class="row">
                        <div class="col-md-12 col-xs-12">
                            <div class="card ">
                                <div class="card-header">
                                    <h5 style="font-size: 1.3rem;">DATA PASIEN DIRAWAT</h5>
                                    <div class="card-header-right">
                                        <ul class="list-unstyled card-option">
                                            <li><i class="feather icon-maximize full-card"></i></li>
                                            <li><i class="feather icon-minus minimize-card"></i></li>
                                            <li><i class="feather icon-trash-2 close-card"></i></li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="card-block table-border-style">
                                    <div class="row">
                                        <div class="col-xl-12 col-md-12">
                                            <div class="card user-card-full brd">
                                                <div class="row m-l-0 m-r-0">
                                                    <div class="col-sm-8">
                                                        <div class="card-block padding-cell">
                                                            <h6 class="m-b-20 p-b-5 b-b-default f-w-600 f-s-12 " >Dirawat</h6>
                                                            <h6 class="m-b-20 p-b-5 b-b-default f-w-600 f-s-12" style="margin-left: 20px">
                                                                <i class="fa fa-check-square" style="margin-right: 5px"></i>
                                                                Kasus Konfirmasi Covid 19</h6>
                                                            <h6 class="m-b-20 p-b-5 b-b-default f-w-600 f-s-12" style="margin-left: 20px">
                                                                <i class="fa fa-check-square" style="margin-right: 5px"></i>
                                                                Kasus Suspek</h6>
                                                            <h6 class="m-b-20 p-b-5 b-b-default f-w-600 f-s-12" style="margin-left: 20px">
                                                                <i class="fa fa-check-square" style="margin-right: 5px"></i>
                                                                Kasus Kontak Erat</h6>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-4 bg-c-lite-green ">
                                                        <div class="card-block text-center text-white" style="">
                                                            <h6 class="m-b-20 p-b-5 b-b-default f-w-600 f-s-12">{{ $result['totalDirawat'] }} </h6>
                                                            <h6 class="m-b-20 p-b-5 b-b-default f-w-600 f-s-12">{{ $result['dirawatKonfirmasi'] }} </h6>
                                                            <h6 class="m-b-20 p-b-5 b-b-default f-w-600 f-s-12">{{ $result['dirawatSuspek'] }} </h6>
                                                            <h6 class="m-b-20 p-b-5 b-b-default f-w-600 f-s-12">{{ $result['dirawatKontakErat'] }} </h6>
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
                    <div class="row">
                        <div class="col-md-12 col-xs-12">
                            <div class="card ">
                                <div class="card-header">
                                    <h5 style="font-size: 1.3rem;">DATA KUMULATIF</h5>
                                    <div class="card-header-right">
                                        <ul class="list-unstyled card-option">
                                            <li><i class="feather icon-maximize full-card"></i></li>
                                            <li><i class="feather icon-minus minimize-card"></i></li>
                                            <li><i class="feather icon-trash-2 close-card"></i></li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="card-block table-border-style">
                                    <div class="row">
                                        <div class="col-xl-12 col-md-12">
                                            <div class="card user-card-full brd">
                                                <div class="row m-l-0 m-r-0">
                                                    <div class="col-sm-8">
                                                        <div class="card-block padding-cell">
                                                            <h6 class="m-b-20 p-b-5 b-b-default f-w-600 f-s-12 " >Kumulatif Kunjungan Pasien</h6>
                                                            <h6 class="m-b-20 p-b-5 b-b-default f-w-600 f-s-12" style="margin-left: 20px">
                                                                <i class="fa fa-check-square" style="margin-right: 5px"></i>
                                                                Rawat Inap </h6>
                                                            <h6 class="m-b-20 p-b-5 b-b-default f-w-600 f-s-12" style="margin-left: 20px">
                                                                <i class="fa fa-check-square" style="margin-right: 5px"></i>
                                                                Rawat Jalan</h6>
                                                            <h6 class="m-b-20 p-b-5 b-b-default f-w-600 f-s-12 " >Kumulatif Rawat Inap</h6>
                                                            <h6 class="m-b-20 p-b-5 b-b-default f-w-600 f-s-12" style="margin-left: 20px">
                                                                <i class="fa fa-check-square" style="margin-right: 5px"></i>
                                                                Kasus Konfirmasi Covid 19 </h6>
                                                            <h6 class="m-b-20 p-b-5 b-b-default f-w-600 f-s-12" style="margin-left: 20px">
                                                                <i class="fa fa-check-square" style="margin-right: 5px"></i>
                                                                Kasus Suspek</h6>
                                                            <h6 class="m-b-20 p-b-5 b-b-default f-w-600 f-s-12" style="margin-left: 20px">
                                                                <i class="fa fa-check-square" style="margin-right: 5px"></i>
                                                                Kontak Erat</h6>

                                                            <h6 class="m-b-20 p-b-5 b-b-default f-w-600 f-s-12 " >Kumulatif Pasien Keluar Isolasi Mandiri </h6>
                                                            <h6 class="m-b-20 p-b-5 b-b-default f-w-600 f-s-12" style="margin-left: 20px">
                                                                <i class="fa fa-check-square" style="margin-right: 5px"></i>
                                                                Pulang Kasus Konfirmasi Positif Covid 19 Sembuh </h6>
                                                            <h6 class="m-b-20 p-b-5 b-b-default f-w-600 f-s-12" style="margin-left: 20px">
                                                                <i class="fa fa-check-square" style="margin-right: 5px"></i>
                                                                Pulang Kasus Konfirmasi Positif Melanjutkan Isolasi Mandiri</h6>
{{--                                                            <h6 class="m-b-20 p-b-5 b-b-default f-w-600 f-s-12" style="margin-left: 20px">--}}
{{--                                                                <i class="fa fa-check-square" style="margin-right: 5px"></i>--}}
{{--                                                                Kasus Suspek yang Discarded </h6>--}}
                                                            <h6 class="m-b-20 p-b-5 b-b-default f-w-600 f-s-12" style="margin-left: 20px">
                                                                <i class="fa fa-check-square" style="margin-right: 5px"></i>
                                                                Keluar Isolasi Mandiri ke Tower Lainnya karena Simtomatik </h6>
                                                            <h6 class="m-b-20 p-b-5 b-b-default f-w-600 f-s-12 " >Rujuk</h6>
                                                            <h6 class="m-b-20 p-b-5 b-b-default f-w-600 f-s-12 " >Meninggal (Probable)</h6>
                                                            <h6 class="m-b-20 p-b-5 b-b-default f-w-600 f-s-12 " >Meninggal (Covid 19)</h6>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-4 bg-c-lite-green ">
                                                        <div class="card-block text-center text-white" style="">
                                                            <h6 class="m-b-20 p-b-5 b-b-default f-w-600 f-s-12">{{ $result['kumulatif']['kunjunganPasien'] }} </h6>
                                                            <h6 class="m-b-20 p-b-5 b-b-default f-w-600 f-s-12">{{ $result['kumulatif']['kunjunganPasien'] }} </h6>
                                                            <h6 class="m-b-20 p-b-5 b-b-default f-w-600 f-s-12">0 </h6>
                                                            <h6 class="m-b-20 p-b-5 b-b-default f-w-600 f-s-12">{{ $result['kumulatif']['kunjunganPasien'] }} </h6>
                                                            <h6 class="m-b-20 p-b-5 b-b-default f-w-600 f-s-12">{{ $result['kumulatif']['konfirmasi'] }} </h6>
                                                            <h6 class="m-b-20 p-b-5 b-b-default f-w-600 f-s-12">{{ $result['kumulatif']['suspek'] }} </h6>
                                                            <h6 class="m-b-20 p-b-5 b-b-default f-w-600 f-s-12">{{ $result['kumulatif']['kontakerat'] }} </h6>
                                                            <h6 class="m-b-20 p-b-5 b-b-default f-w-600 f-s-12">{{ $result['kumulatif']['pulangisolasi'] +$result['kumulatif']['pulangSembuhKum']  +$result['kumulatif']['pulangRujukTowerLain']}} </h6>

                                                            @php
                                                            $persenIsolasi =0;
                                                            if(( $result['kumulatif']['pulangisolasi'] + $result['kumulatif']['pulangSembuhKum']+$result['kumulatif']['pulangRujukTowerLain']) != 0){
                                                                 $persenIsolasi =$result['kumulatif']['pulangisolasi']
                                                                            /( $result['kumulatif']['pulangisolasi'] + $result['kumulatif']['pulangSembuhKum']+$result['kumulatif']['pulangRujukTowerLain']) *100 ;
                                                            }
                                                             $persenTwrLain =0;
                                                             if(( $result['kumulatif']['pulangisolasi'] +$result['kumulatif']['pulangSembuhKum']+$result['kumulatif']['pulangRujukTowerLain']) !=0){
                                                            $persenTwrLain =$result['kumulatif']['pulangRujukTowerLain']
                                                                            /( $result['kumulatif']['pulangisolasi'] +$result['kumulatif']['pulangSembuhKum']+$result['kumulatif']['pulangRujukTowerLain']) *100 ;
                                                                        }

                                                             $prsnSemb       =0;      
                                                             if(( $result['kumulatif']['pulangisolasi'] +$result['kumulatif']['pulangSembuhKum']+$result['kumulatif']['pulangRujukTowerLain']) !=0){  
                                                            $prsnSemb=$result['kumulatif']['pulangSembuhKum']
                                                                            /( $result['kumulatif']['pulangisolasi'] +$result['kumulatif']['pulangSembuhKum']+$result['kumulatif']['pulangRujukTowerLain']) *100 ;
                                                                        }

                                                            @endphp
                                                            <h6 class="m-b-20 p-b-5 b-b-default f-w-600 f-s-12">{{ $result['kumulatif']['pulangSembuhKum'] }}  ({{number_format($prsnSemb,2,',','.')}}%)</h6>
                                                            <h6 class="m-b-20 p-b-5 b-b-default f-w-600 f-s-12"> {{  $result['kumulatif']['pulangisolasi'] }} ({{number_format($persenIsolasi,2,',','.')}}%) </h6>
{{--                                                            <h6 class="m-b-20 p-b-5 b-b-default f-w-600 f-s-12">0</h6>--}}
                                                            <h6 class="m-b-20 p-b-5 b-b-default f-w-600 f-s-12">{{ $result['kumulatif']['pulangRujukTowerLain'] }} ({{ number_format( $persenTwrLain,2,',','.')}}%)</h6>
                                                            <h6 class="m-b-20 p-b-5 b-b-default f-w-600 f-s-12">{{ $result['kumulatif']['rujuk'] }}</h6>
                                                            <h6 class="m-b-20 p-b-5 b-b-default f-w-600 f-s-12">{{ $result['kumulatif']['meninggal'] }}</h6>
                                                            <h6 class="m-b-20 p-b-5 b-b-default f-w-600 f-s-12">{{ $result['kumulatif']['probable'] }}</h6>
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
                    <div class="row">
                        <div class="col-md-12 col-xs-12">
                            <div class="card ">
                                <div class="card-header">
                                    <h5 style="font-size: 1.3rem;">DATA PASIEN DIRAWAT LAINNYA</h5>
                                    <div class="card-header-right">
                                        <ul class="list-unstyled card-option">
                                            <li><i class="feather icon-maximize full-card"></i></li>
                                            <li><i class="feather icon-minus minimize-card"></i></li>
                                            <li><i class="feather icon-trash-2 close-card"></i></li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="card-block table-border-style">
                                    <div class="row">
                                        <div class="col-xl-12 col-md-12">
                                            <div class="card user-card-full brd">
                                                <div class="row m-l-0 m-r-0">
                                                    <div class="col-sm-8">
                                                        <div class="card-block padding-cell">
                                                            <h6 class="m-b-20 p-b-5 b-b-default f-w-600 f-s-12 " >Okupansi Bed</h6>
                                                            <h6 class="m-b-20 p-b-5 b-b-default f-w-600 f-s-12 " >Jumlah Pasien Berdasarkan Jenis Kelamin</h6>
                                                            <h6 class="m-b-20 p-b-5 b-b-default f-w-600 f-s-12" style="margin-left: 20px">
                                                                <i class="fa fa-check-square" style="margin-right: 5px"></i>
                                                                Laki-laki</h6>
                                                            <h6 class="m-b-20 p-b-5 b-b-default f-w-600 f-s-12" style="margin-left: 20px">
                                                                <i class="fa fa-check-square" style="margin-right: 5px"></i>
                                                                Perempuan</h6>
                                                            <h6 class="m-b-20 p-b-5 b-b-default f-w-600 f-s-12 " >Jumlah Pasien Berdasarkan Usia</h6>
                                                            <h6 class="m-b-20 p-b-5 b-b-default f-w-600 f-s-12" style="margin-left: 20px">
                                                                <i class="fa fa-check-square" style="margin-right: 5px"></i>
                                                                Balita</h6>
                                                            <h6 class="m-b-20 p-b-5 b-b-default f-w-600 f-s-12" style="margin-left: 20px">
                                                                <i class="fa fa-check-square" style="margin-right: 5px"></i>
                                                                Anak</h6>
                                                            <h6 class="m-b-20 p-b-5 b-b-default f-w-600 f-s-12" style="margin-left: 20px">
                                                                <i class="fa fa-check-square" style="margin-right: 5px"></i>
                                                                Dewasa</h6>
                                                            <h6 class="m-b-20 p-b-5 b-b-default f-w-600 f-s-12" style="margin-left: 20px">
                                                                <i class="fa fa-check-square" style="margin-right: 5px"></i>
                                                                Geriatri</h6>

                                                            <h6 class="m-b-20 p-b-5 b-b-default f-w-600 f-s-12 " >Jumlah Pasien Berdasarkan Asal Rujukan</h6>
                                                            @foreach($result['usulTotal']['asalrujukan'] as $p)
                                                                <h6 class="m-b-20 p-b-5 b-b-default f-w-600 f-s-12" style="margin-left: 20px">
                                                                    <i class="fa fa-check-square" style="margin-right: 5px"></i>
                                                                    {{ $p->asalrujukan }}</h6>
                                                            @endforeach
{{--                                                            <h6 class="m-b-20 p-b-5 b-b-default f-w-600 f-s-12" style="margin-left: 20px">--}}
{{--                                                                <i class="fa fa-check-square" style="margin-right: 5px"></i>--}}
{{--                                                                Mandiri</h6>--}}
{{--                                                            <h6 class="m-b-20 p-b-5 b-b-default f-w-600 f-s-12" style="margin-left: 20px">--}}
{{--                                                                <i class="fa fa-check-square" style="margin-right: 5px"></i>--}}
{{--                                                                Dirujuk</h6>--}}


                                                            <h6 class="m-b-20 p-b-5 b-b-default f-w-600 f-s-12 " >Jumlah Pasien Berdasarkan Kebangsaan</h6>
                                                            <h6 class="m-b-20 p-b-5 b-b-default f-w-600 f-s-12" style="margin-left: 20px">
                                                                <i class="fa fa-check-square" style="margin-right: 5px"></i>
                                                                WNI</h6>
                                                            <h6 class="m-b-20 p-b-5 b-b-default f-w-600 f-s-12" style="margin-left: 20px">
                                                                <i class="fa fa-check-square" style="margin-right: 5px"></i>
                                                                WNA</h6>

                                                            <h6 class="m-b-20 p-b-5 b-b-default f-w-600 f-s-12 " >Jumlah Pasien Berdasarkan Pendidikan</h6>
                                                            @foreach($result['usulTotal']['pendidikan'] as $p)
                                                                <h6 class="m-b-20 p-b-5 b-b-default f-w-600 f-s-12" style="margin-left: 20px">
                                                                    <i class="fa fa-check-square" style="margin-right: 5px"></i>
                                                                   {{ $p->pendidikan }}</h6>
                                                            @endforeach


                                                            <h6 class="m-b-20 p-b-5 b-b-default f-w-600 f-s-12 " >Jumlah Pasien Berdasarkan Pekerjaan</h6>
                                                            @foreach($result['usulTotal']['pekerjaan'] as $p)
                                                                <h6 class="m-b-20 p-b-5 b-b-default f-w-600 f-s-12" style="margin-left: 20px">
                                                                    <i class="fa fa-check-square" style="margin-right: 5px"></i>
                                                                    {{ $p->pekerjaan }}</h6>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-4 bg-c-lite-green ">
                                                        <div class="card-block text-center text-white" style="">
                                                            <h6 class="m-b-20 p-b-5 b-b-default f-w-600 f-s-12">{{ $result['usulTotal']['jumlah'] }} </h6>
                                                            <h6 class="m-b-20 p-b-5 b-b-default f-w-600 f-s-12">{{ $result['usulTotal']['jumlah'] }} </h6>
                                                            <h6 class="m-b-20 p-b-5 b-b-default f-w-600 f-s-12">{{ $result['usulTotal']['jeniskelamin']['laki2'] }}  </h6>
                                                            <h6 class="m-b-20 p-b-5 b-b-default f-w-600 f-s-12">{{ $result['usulTotal']['jeniskelamin']['perempuan'] }} </h6>
                                                            <h6 class="m-b-20 p-b-5 b-b-default f-w-600 f-s-12">{{ $result['usulTotal']['jumlah'] }} </h6>
                                                            <h6 class="m-b-20 p-b-5 b-b-default f-w-600 f-s-12">{{ $result['usulTotal']['usia']['balita'] }}  </h6>
                                                            <h6 class="m-b-20 p-b-5 b-b-default f-w-600 f-s-12">{{ $result['usulTotal']['usia']['anak'] }} </h6>
                                                            <h6 class="m-b-20 p-b-5 b-b-default f-w-600 f-s-12">{{ $result['usulTotal']['usia']['dewasa'] }} </h6>
                                                            <h6 class="m-b-20 p-b-5 b-b-default f-w-600 f-s-12">{{ $result['usulTotal']['usia']['geriatri'] }} </h6>

                                                            <h6 class="m-b-20 p-b-5 b-b-default f-w-600 f-s-12">{{ $result['usulTotal']['jumlah'] }} </h6>
                                                            @foreach($result['usulTotal']['asalrujukan'] as $p)
                                                                <h6 class="m-b-20 p-b-5 b-b-default f-w-600 f-s-12">{{ $p->jml }}</h6>
                                                            @endforeach
{{--                                                            <h6 class="m-b-20 p-b-5 b-b-default f-w-600 f-s-12">{{ $result['usulTotal']['asalrujukan']['datangsendiri'] }} </h6>--}}
{{--                                                            <h6 class="m-b-20 p-b-5 b-b-default f-w-600 f-s-12">{{ $result['usulTotal']['asalrujukan']['rujuk'] }} </h6>--}}

                                                            <h6 class="m-b-20 p-b-5 b-b-default f-w-600 f-s-12">{{ $result['usulTotal']['jumlah'] }} </h6>
                                                            <h6 class="m-b-20 p-b-5 b-b-default f-w-600 f-s-12">{{ $result['usulTotal']['kebangsaan']['wni'] }} </h6>
                                                            <h6 class="m-b-20 p-b-5 b-b-default f-w-600 f-s-12">{{ $result['usulTotal']['kebangsaan']['wna'] }} </h6>

                                                            <h6 class="m-b-20 p-b-5 b-b-default f-w-600 f-s-12">{{ $result['usulTotal']['jumlah'] }} </h6>
                                                            @foreach($result['usulTotal']['pendidikan'] as $p)
                                                                 <h6 class="m-b-20 p-b-5 b-b-default f-w-600 f-s-12">{{ $p->jml }}</h6>
                                                            @endforeach

                                                            <h6 class="m-b-20 p-b-5 b-b-default f-w-600 f-s-12">{{ $result['usulTotal']['jumlah'] }} </h6>
                                                            @foreach($result['usulTotal']['pekerjaan'] as $p)
                                                                <h6 class="m-b-20 p-b-5 b-b-default f-w-600 f-s-12">{{ $p->jml }}</h6>
                                                            @endforeach


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

{{--                    <div class="row">--}}
{{--                        <div class="col-md-12 col-xs-12">--}}
{{--                            <div class="card ">--}}
{{--                                <div class="card-header">--}}
{{--                                    <h5 style="font-size: 1.3rem;">DATA HARIAN</h5>--}}
{{--                                    <div class="card-header-right">--}}
{{--                                        <ul class="list-unstyled card-option">--}}
{{--                                            <li><i class="feather icon-maximize full-card"></i></li>--}}
{{--                                            <li><i class="feather icon-minus minimize-card"></i></li>--}}
{{--                                            <li><i class="feather icon-trash-2 close-card"></i></li>--}}
{{--                                        </ul>--}}
{{--                                    </div>--}}
{{--                                </div>--}}

{{--                                <div class="card-block table-border-style">--}}
{{--                                    <div class="table-responsive">--}}
{{--                                        <table class="table">--}}
{{--                                            <tbody>--}}
{{--                                            <tr >--}}
{{--                                                <td class="left-c">  <h6 class="text-na">Rawat Jalan</h6></td>--}}
{{--                                                <td class="bg-c-lite-green"><h6 class="text-na text-white">{{ $result['harianRajal'] }}</h6></td>--}}
{{--                                            </tr>--}}
{{--                                            <tr >--}}
{{--                                                <td class="left-c">  <h6 class="text-na">Pasien Masuk Rawat Inap [Pasien Baru]</h6></td>--}}
{{--                                                <td class="bg-c-lite-green"><h6 class="text-na text-white">{{ $result['harianRanap'] }}</h6></td>--}}
{{--                                            </tr>--}}
{{--                                            <tr >--}}
{{--                                                <td class="left-c">  <h6 class="text-na">Pasien Pulang</h6></td>--}}
{{--                                                <td class="bg-c-lite-green"><h6 class="text-na text-white">{{ $result['pulangSembuh']  + $result['pulangIsolasi']}}</h6></td>--}}
{{--                                            </tr>--}}
{{--                                            <tr>--}}
{{--                                                <td class="left-c"> <h6 class="text-na ml-20" >--}}
{{--                                                        <i class="fa fa-check-square" style="margin-right: 5px"></i>--}}
{{--                                                        Kasus Konfirmasi Covid 19 Sembuh</h6></td>--}}
{{--                                                <td class="bg-c-lite-green"><h6 class="text-na text-white">{{ $result['pulangSembuh'] }}</h6></td>--}}
{{--                                            </tr>--}}
{{--                                            <tr>--}}
{{--                                                <td class="left-c"> <h6 class="text-na ml-20" >--}}
{{--                                                        <i class="fa fa-check-square" style="margin-right: 5px"></i>--}}
{{--                                                        Kasus Konfirmasi yang Melanjutkan Isolasi Mandiri</h6></td>--}}
{{--                                                <td class="bg-c-lite-green"><h6 class="text-na text-white">{{ $result['pulangIsolasi'] }}</h6></td>--}}
{{--                                            </tr>--}}
{{--                                            <tr >--}}
{{--                                                <td class="left-c">  <h6 class="text-na">Rujuk</h6></td>--}}
{{--                                                <td class="bg-c-lite-green"><h6 class="text-na text-white">{{ $result['rujuk'] }}</h6></td>--}}
{{--                                            </tr>--}}
{{--                                            </tbody>--}}
{{--                                        </table>--}}
{{--                                    </div>--}}
{{--                                </div>--}}

{{--                            </div>--}}
{{--                        </div>--}}
{{--                    </div>--}}
                    <!-- Page-body end -->
                </div>
            </div>
         </div>
        <div class="col-md-4">
            <div class="row">

                 <div class="col-md-6">
                        <div class="input-group" style="float:right" >
                            <span class="input-group-addon" id="basic-addon1"><i class="fa fa-arrow-up""></i></span>
                           
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
                <div class="col-md-6">
                    <button style="float:right" class="btn btn-inverse btn-outline-inverse" onclick="back()"><i class="icofont icofont-arrow-left"></i>Daftar Pasien Aktif</button>
                </div>
               <div class="col-md-12">
                  <div class="page-wrapper" >
                    <div class="page-header">

                    </div>
                    <div class="page-body">
                        <div class="row">
                                <div class="col-md-12 col-xs-12">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 style="font-size: 1.3rem">PERSONEL</h5>

                                        </div>
                                        @php
                                            $d =  App\Http\Controllers\Auth\AuthController::getPersonel();
                                        @endphp
                                        <div class="card-block">
                                            <div class="table-responsive">
                                                <table class="table table-xs" style="font-size: 10px;">
                                                    <thead style="font-size: 10px;">
                                                    <tr>
                                                        <th rowspan="2">NO</th>
                                                        <th rowspan="2">KUALIFIKASI</th>
                                                        <th colspan="3" style="text-align: center">JUMLAH </th>

                                                    </tr>
                                                    <tr>
                                                        <th>AKTIF</th>
                                                        <th>ISOLASI</th>
                                                        <th>TOTAL</th>
                                                    </tr>
                                                    </thead>
                                                    <tbody style="font-size: 10px;">
                                                    @php
                                                        $ttlAktif =0;
                                                        $ttlIsolasi =0;
                                                        $ttlTTL =0;
                                                    @endphp
                                                    @foreach($d as $k)
                                                        @php
                                                            $ttlAktif = $ttlAktif + (float)$k['aktif'];
                                                            $ttlIsolasi =$ttlIsolasi + (float)$k['isolasi'];
                                                            $ttlTTL =$ttlTTL + (float)$k['total'];
                                                            $class = '';
                                                            if($k['no'] =='A' || $k['no'] =='B' ||$k['no'] =='C'){
                                                              $class = 'table-success';
                                                            }
                                                        @endphp
                                                    <tr class="{{ $class }}">
                                                        <th scope="row">{{ $k['no'] }}</th>
                                                        <td>{{ $k['ket'] }}</td>
                                                        <td>{{ $k['aktif'] }}</td>
                                                        <td>{{ $k['isolasi'] }}</td>
                                                        <td>{{ $k['total'] }}</td>
                                                    </tr>
                                                    @endforeach
                                                    <tr style="font-weight: bold" class="table-active">
                                                        <th scope="row"></th>
                                                        <td>JUMLAH</td>
                                                        <td>{{ $ttlAktif}}</td>
                                                        <td>{{ $ttlIsolasi  }}</td>
                                                        <td>{{ $ttlTTL }}</td>
                                                    </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <div class="row">
                            <div class="col-md-12 col-xs-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 style="font-size: 1.3rem">KAPASITAS BED</h5>

                                    </div>
                                    @php
                                        $bed =  App\Http\Controllers\Auth\AuthController::getKapasitasBed();
                                    @endphp
                                    <div class="card-block">
                                        <div class="table-responsive">
                                            <table class="table table-xs" style="font-size: 10px;">
                                                <tbody style="font-size: 10px;">
                                                    <tr >
                                                        <th scope="row">1</th>
                                                        <td>Rawat Umum</td>
                                                        <td>{{ $bed }}</td>
                                                    </tr>
                                                    <tr >
                                                        <th scope="row">2</th>
                                                        <td>IHCU</td>
                                                        <td>0</td>
                                                    </tr>
                                                    <tr >
                                                        <th scope="row">1</th>
                                                        <td>ICU</td>
                                                        <td>0</td>
                                                    </tr>
                                                    <tr >
                                                        <th scope="row">1</th>
                                                        <td>IGD</td>
                                                        <td>0</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 col-xs-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 style="font-size: 1.3rem">MATKES</h5>

                                    </div>
                                    @php
                                        $matkes =  App\Http\Controllers\Auth\AuthController::getMatkes();
                                    @endphp
                                    <div class="card-block">
                                        <div class="table-responsive">
                                            <table class="table table-xs" style="font-size: 10px;">

                                                <tbody style="font-size: 10px;">
                                                @foreach($matkes as $k)
                                                    <tr >
                                                        <th scope="row">{{ $k['no'] }}</th>
                                                        <td>{{ $k['ket'] }}</td>
                                                        <td>{{ $k['total'] }}</td>
                                                    </tr>
                                                @endforeach
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

@endsection

@section('javascript')
    <script>

        
        $("#comboDepartemen").val({{request()->get("objectdepartemenfk")}})
      
   
        $("#comboDepartemen").change(function(e){
             load()
        })
        $('.js-example-basic-single').select2();
        $('#isLoading').hide()
        function back(){
            window.location.href = '{!! route("daftarPasienAktif") !!}'
        }
        function load(){
            var kddiagnosa = $("#comboDepartemen").val()
              window.location.href = "{!! route("dataHarian",[ 'dari'=>  request()->get("dari"),'sampai'=>  request()->get("sampai"), ]) !!}&objectdepartemenfk="+kddiagnosa
        }
        setTimeout(function(){
          load()
        }, 240000); // 6 menit in ms
        // ===== Scroll to Top ====
        $(window).scroll(function() {
            if ($(this).scrollTop() >= 50) {        // If page is scrolled more than 50px
                $('#return-to-top').fadeIn(200);    // Fade in the arrow
            } else {
                $('#return-to-top').fadeOut(200);   // Else fade out the arrow
            }
        });
        $('#return-to-top').click(function() {      // When arrow is clicked
            $('body,html').animate({
                scrollTop : 0                       // Scroll to top of body
            }, 500);
        });
    </script>
@endsection
