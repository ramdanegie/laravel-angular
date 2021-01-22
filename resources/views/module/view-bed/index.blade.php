
@extends('template.template3')
@section('css')
    <style>
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
            font-size: 15px;
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


  </style>

@endsection
@section('content-body')
    <div id="isLoading">
        @include('template.loader2')
    </div>
    <!-- Return to Top -->
    <a href="javascript:" id="return-to-top"><i class="fa fa-chevron-up"></i></a>
    <div class="page-wrapper" id="id_template" >
        <!-- Page-header start -->
        <form method="get" id="formAction"  action="{!! route("home") !!}" >
            <div class="page-header">
                <div class="row align-items-end">
                    <div class="col-lg-12">
                        <div class="page-header-title">
                            <div class="d-inline">
                                    <h4 style="font-size: 35px;">KETERSEDIAAN TEMPAT TIDUR  </h4>
                                    <!-- TOWER 5 -->
                                <span style="font-size: 25px">{{$namaruang}}</span>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="row align-items-end">
                    <div class="col-lg-1">
                    </div>
                    <div class="col-lg-2">
                        <div class="input-group" >
                            <span class="input-group-addon" id="basic-addon1"><i class="fa fa-user"></i></span>
                            <input type="text" id="namapasien" name="namapasien" class="form-control" placeholder="Filter Nama" value="{{request()->get("namapasien")}}" >

                        </div>
                    </div>
                    <div class="col-lg-2">
                        <div class="input-group" >
                            <span class="input-group-addon" id="basic-addon1"><i class="fa fa-home"></i></span>
                            <input type="text" id="namakamar" name="namakamar" class="form-control" placeholder="Filter Kamar" value="{{request()->get("namakamar")}}" >

                        </div>
                    </div>
                    <div class="col-lg-2">
                        <div class="input-group" >
                            <span class="input-group-addon" id="basic-addon1"><i class="fa fa-bed"></i></span>
                            {{--                            <input type="text" id="objectruanganfk" name="objectruanganfk" class="date-custom form-control" value="{{request()->get("objectruanganfk")}}" >--}}
                            <select id="comboRuangan2" class="form-control js-example-basic-single" name="statusbedfk"
                            >
                                <option value="">-- Filter Status --</option>
                                @php
                                    @endphp
                                @foreach($stts as $k)
                                    <option value='{{$k->id}}' > {{ $k->statusbed }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-lg-2">
                        <div class="input-group" >
                            <span class="input-group-addon" id="basic-addon1"><i class="fa fa-bed"></i></span>

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
                    <div class="col-lg-2">
                        <div class="input-group" >
                            <span class="input-group-addon" id="basic-addon1"><i class="fa fa-bed"></i></span>
                            {{--                            <input type="text" id="objectruanganfk" name="objectruanganfk" class="date-custom form-control" value="{{request()->get("objectruanganfk")}}" >--}}
                            <select id="comboRuangan" class="form-control js-example-basic-single" name="objectruanganfk" required=""
                            >
                                <option value="">-- Filter Ruangan --</option>
                                @php
                                    @endphp
                                @foreach($ruangan as $k)
                                    <option value='{{$k->id}}' > {{ $k->namaruangan }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>


                    <div class="col-lg-1" >
                        <button class="btn btn-success  btn-outline-success "  type="submit"><i class="icofont icofont-search"></i>Search</button>
                    </div>
                </div>

            </div>
        </form>
        <!-- Page-header end -->

        <!-- Page-body start -->
        <div class="page-body">
            <div class="row">
                <div class="col-md-12 col-xs-12">
                    <div class="card">
                        <div class="card-header" style="padding: .75rem 1.25rem;">
                            <h5>Keterangan : </h5>
                            <div class="card-header-right">
                                <ul class="list-unstyled card-option">
                                    <li><i class="feather icon-maximize full-card"></i></li>
                                    <li><i class="feather icon-minus minimize-card"></i></li>
                                    <li><i class="feather icon-trash-2 close-card"></i></li>
                                </ul>
                            </div>
                        </div>
                        <div class="card-block">
                            <div class="row">
                                @foreach($sbStatus as $key => $d)
                                <div class="col-md-3 col-xs-12">
                                    <div class="card widget-card-1"  style="border: 1px solid rgba(69, 90, 100, 0.14);">
                                        <div class="card-block-small">
                                            <i class="fa {{ $d['statusbed'] == 'TOTAL'?'fa-bars':'fa-bed'}} {{ $d['color'] != null? $d['color'] :'bg-c-3' }}  card1-icon"></i>
                                            <span class="text-c-blue f-w-600">{{$d['statusbed']}}</span>
                                            <h4>{{$d['jml']}}</h4>
                                            <div>
                                          </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach

                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
            @php
            $isi = 0;
            $kosong = 0;
            $kotor = 0;
            $rusak = 0;
            @endphp
                @foreach($data->sortBy('namakamar')->groupBy('namakamar') as $head => $item)
{{--                @foreach($dataNa as $key => $item)--}}

                <div class="col-md-3 col-xs-12">
                    <div class="card border-1" >
                        <div class="card-header" style="padding: .75rem 1.25rem;">
                            <h5 class="text-center"  style="font-size: 1.3rem;">{{ 'Kamar '. $item[0]->namakamar }}</h5>
                        </div>
                        <div class="card-block"  >
                            <div class="row">
                                @php

                                    //  foreach ($item as $ks => $row) {
                                         //  $count[$ks] = $row->namabed;
                                     //  }
                                       //array_multisort($count, SORT_ASC, $item);
                                @endphp
                                @foreach($item as $key => $d)
                                @php
                                $jk = 'fa-bed';
                                $jkName = '';
                                if($d->jkid == 1){
                                    $jk = 'fa-mars';
                                     $jkName = 'L';
                                }
                                if($d->jkid == 2){
                                    $jk = 'fa-venus';
                                     $jkName = 'P';
                                }
                                @endphp
                                <div class="col-md-6 col-xs-12"
                                     onclick="show_popup({{ $d->tt_id }})"
                                     style="cursor: pointer" >
                                    <div class="card {{ $d->color != null? $d->color :'bg-c-3' }} update-card">
                                        <div class="card-header text-center " style="padding: .55rem 1.25rem;" >
                                            <h5 class="text-white text-center " style="font-size: 1.1rem;">{{ 'Bed '. $d->namabed }}</h5>
                                        </div>
                                        <div class="card-block" >
                                            <div class="row align-items-end">
                                                <div class="col-12">
                                                    <i class="fa {{ $jk }} f-30"></i> <span class="f-20">&nbsp;&nbsp; {{ $jkName }}</span>
                                                </div>
                                                <div class="col-12 text-right" style="padding-top:5px">
                                                    <h6 class="{{ $d->namapasien != null? $d->txtcolor : str_replace('bg-c-','text-c-',$d->color) }} m-b-0 uhuy" >{{ $d->namapasien != null ?  $d->namapasien  :'-' }}</h6>
                                                    <h6 class="{{ $d->nocm != null? $d->txtcolor : str_replace('bg-c-','text-c-',$d->color) }} m-b-0">{{ $d->nocm != null ?  $d->nocm  :'-' }}</h6>
                                                    <p class="{{ $d->umur_string != null? $d->txtcolor : str_replace('bg-c-','text-c-',$d->color) }} m-b-0 uhuy">{{ $d->umur_string != null ?  $d->umur_string  : '-' }}</p>
                                                    <!-- <p class="{{ $d->nohp != null? $d->txtcolor : str_replace('bg-c-','text-c-',$d->color) }} m-b-0 uhuy">{{ $d->nohp != null ?  $d->nohp  : '-' }}</p> -->

                                                    <p class="{{ $d->lamarawat != null? $d->txtcolor : str_replace('bg-c-','text-c-',$d->color) }} m-b-0">{{ $d->lamarawat != null ?  'LD : '.$d->lamarawat .' hr'  : '-' }}</p>

                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                @endforeach

                            </div>
                        </div>
                    </div>
                </div>
                @endforeach

            </div>
            <div class="row">
                <div class="col-md-12 col-xs-12">
                    <div class="card table-card">

                            <div class="card-header">
                                <h5 style="font-size: 1.3rem;">KAPASITAS UNIT KAMAR UNTUK PASIEN </h5>
                                <!-- TOWER 5 -->
{{--                                <span class="text-muted">For more details about usage, please refer <a href="https://www.amcharts.com/online-store/" target="_blank">amCharts</a> licences.</span>--}}
                                <div class="card-header-right">
                                    <ul class="list-unstyled card-option">
                                        <li><i class="feather icon-maximize full-card"></i></li>
                                        <li><i class="feather icon-minus minimize-card"></i></li>
                                        <li><i class="feather icon-trash-2 close-card"></i></li>
                                    </ul>
                                </div>
                            </div>

                        @php
                           $kamarAll =  App\Http\Controllers\Auth\AuthController::getAllBed(request());
                           // dd($kamarAll);
                        @endphp
                        <div class="card-block table-border-style">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                    <tr>
                                        <th>UNIT KAMAR RAWAT INAP DI RSD WISMA ATLET KEMAYORAN </th>
                                        <th>KAMAR</th>
                                        <th>BED</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr class="table-active">
                                        <td scope="row">TOTAL UNIT KAMAR DENGAN HUNIAN</td>
                                        <td>{{number_format( $kamarAll['totalKamar'],0,',','.') }}</td>
                                        <td>{{number_format( $kamarAll['totalBed'],0,',','.')}}</td>
                                    </tr>
                                    <tr>
                                        <td scope="row">UNIT KAMAR YG RUSAK TDK BISA DIPAKAI </td>

                                        <td>{{number_format( $kamarAll['totalKamarRusak'],0,',','.') }}</td>
                                        <td>{{number_format( $kamarAll['totalBedRusak'],0,',','.')}}</td>
                                    </tr>
                                    <tr class="table-active">
                                        <td scope="row">UNIT KAMAR UNTUK JAGA PERAWAT DAN KAMAR PEMERIKSAAN</td>

                                        <td>{{number_format( $kamarAll['totalKamarPerawat'],0,',','.') }}</td>
                                        <td>{{number_format( $kamarAll['totalBedPerawat'],0,',','.')}}</td>
                                    </tr>
                                    <tr>
                                        <td scope="row">UNIT KAMAR YANG DISIAPKAN UNTUK PASIEN</td>

                                        <td>{{number_format( $kamarAll['totalKamarUtkPasien'],0,',','.') }}</td>
                                        <td>{{number_format( $kamarAll['totalBedUtkPasien'],0,',','.')}}</td>
                                    </tr>
                                    <tr class="table-active">
                                        <td scope="row">UNIT KAMAR YG SDH TERISI PASIEN</td>

                                        <td>{{number_format( $kamarAll['totalKamarIsi'],0,',','.') }}</td>
                                        <td>{{number_format( $kamarAll['totalBedIsi'],0,',','.')}}</td>
                                    </tr>
                                    <tr>
                                        <td scope="row">SISA KAPASITAS HUNIAN UTK PASIEN (UNIT - PASIEN)</td>

                                        <td>{{number_format( $kamarAll['totalKamarKapasitas'],0,',','.') }}</td>
                                        <td>{{number_format( $kamarAll['totalBedKapasitas'],0,',','.')}}</td>
                                    </tr>
                                    <tr class="table-success">
                                        <td  scope="row"> PRESENTASE TINGKAT HUNIAN</td>
                                        <td style="text-align: right"> {{ $kamarAll['totalPresentase'].'%' }}</td>
                                        <td></td>
                                    </tr>

                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            <!-- Page-body end -->
        </div>

        <div class="modal fade bs-example" id="modaledit" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-lg" style="    max-width: 800px">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" >Detail Bed</h4>
                    </div>
                    <div id="modal-body">
                    </div>
                </div>
            </div>
        </div>
@endsection

@section('javascript')
    <script>
        var APP_URL = {!! json_encode(url('/')) !!}
        $("#comboDepartemen").val({{request()->get("objectdepartemenfk")}})
        $("#comboRuangan").val({{request()->get("objectruanganfk")}})
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
        $('.js-example-basic-single').select2();



        function show_popup(id){
            $("#modaledit").modal("show");
            let url ="{!! route("showBedDetail", ":id") !!}";
            url = url.replace('?:id', '?id='+id);
            $("#modal-body").load(url);
        }
        setTimeout(function(){
           window.location.href= '{!! route("home",
                [
                    'objectruanganfk' => request()->get("objectruanganfk"),
                    'namakamar'=>  request()->get("namakamar"),
                      'namapasien'=>  request()->get("namapasien"),
                      'statusbedfk'=>  request()->get("statusbedfk"),
                        'objectdepartemenfk'=>  request()->get("objectdepartemenfk")
                ]) !!}'
        }, 360000); // 6 menit in ms

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
