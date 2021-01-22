
@extends('template.template')
@section('css')
<style>
    #mapDiagnosa { height: 650px; }
    .show-chart {
        display: none;
    }
    .table td, .table th {
        padding: 0.30rem .75rem ;
        vertical-align: top;
        border-top: 1px solid #e9ecef;
    }
    #modal-demografi-penyakit > .modal-lg{
        max-width:1140px;
    }

    .demografi-content{

    }
    .center-euy{
        margin: 0;
    /* background: yellow; */
    position: absolute;
    top: 50%;
    left: 50%;
    margin-right: -50%;
    transform: translate(-50%, -50%)
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
@endsection
@section('content-body')
<div id="isLoading">
    @include('template.loader2')
</div>
<div class="page-wrapper" id="id_template" style="padding-top: 0">
    <!-- Page-header start -->
    <form method="get" id="formAction"  action="{!! route("show_page",["role"=>"admin","pages"=>"dashboard-v2"]) !!}" >
      <div class="page-header">
        <div class="row align-items-end">
            <div class="col-lg-7">
                <div class="page-header-title">
                    <div class="d-inline">
                        <div style="display: inline-block; vertical-align: top;">
                            <img class="img-fluid" src="{!! asset('images/puskes.jpeg') !!}" alt="" style="width: 90px">
                          </div>
                          <div style=" display: inline-block;vertical-align: middle;margin-top: 30px;">
                             <h4 style="font-size: 35px;">Indonesian Armed Forces Medical Command Center </h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-2">
                <div class="input-group" >
                    <span class="input-group-addon" id="basic-addon1"><i class="ti-calendar"></i></span>
                    <input type="text" id="tglawal" name="tglawal" class="date-custom form-control" value="{{request()->get("tglawal")}}" >
                  </div>
                <!-- <input type="text" value="{{ $tglawal }}" name="tglawal" id="tglawal" class="form-control"> -->
                <!--                &nbsp; sd-->
            </div>
            <div class="col-lg-2">
                 <div class="input-group">
                    <span class="input-group-addon" id="basic-addon1"><i class="ti-calendar"></i></span>
                    <input type="text" id="tglakhir" name="tglakhir"  class="date-custom form-control" value="{{request()->get("tglakhir")}}" >
                  </div>
                <!-- <input type="text" value="{{ $tglakhir }}" name="tglakhir" id="tglakhir"  class="form-control"> -->

            </div>
            <div class="col-lg-1" >
                <button class="btn btn-success  btn-outline-success "  type="submit"><i class="icofont icofont-search"></i>Cari</button>

            </div>
         </div>
       </div>
    </form>
    <!-- Page-header end -->

    <!-- Page-body start -->
    <div class="page-body">
        <div class="row">
            <!--            <div class="col-12">-->
            <div class="col-md-6 col-xs-12">
                <div class="card">
                    <div class="card-header" style="padding: .75rem 1.25rem;">
                        <h5>10 Besar Diagnosa</h5>
                    </div>
                    <div class="card-block"  style="overflow: auto;height: 470px;">
                        <div id="isLoadingTable">
                            @include('template.loader')
                        </div>

                        <div class="dt-responsive table-responsive d-none">
                            <table id="tableDemog" class="table table-striped table-bordered nowrap">
                                <thead>
                                <tr>
                                    <th style="width: 40px">No</th>
                                    <th>Diagnosa</th>
                                    <th>Jumlah Kasus</th>
                                    <th style="width: 80px;text-align: center;"># </th>
                                </tr>
                                </thead>
                                <tbody id="tBodyDemog">
                                </tbody>

                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xs-12">
                <div class="card">
                    <div class="card-header" style="padding: .75rem 1.25rem;">
                        <h5>Chart 10 Besar Diagnosa</h5>

                    </div>
                    <div class="card-block" style="overflow: auto;height: 470px;">
                        <div id="isLoadingChart">
                            @include('template.loader')
                        </div>
                        <div class="class-chart d-none">
                            <div class="chart-primary ">
                                <div id="container"></div>
                            </div>
                            <div class="chart-secondary show-chart">
                                <div id="chartSecondary"></div>
                            </div>
                        </div>
<!--                        <canvas id="chartDonut" ></canvas>-->
                    </div>
                </div>
            </div>
            <div class="col-md-12 col-xs-12">
                <div class="card">
                    <div class="card-header" style="padding: .75rem 1.25rem;" >
                        <h5>Sebaran Diagnosa Covid-19 [ B34.2 - Coronavirus infection, unspecified ]</h5>
                    </div>
                    <div class="card-block">
                        <div class="row">
            <div class="col-xl-3 col-md-12">
                <div class="card" onclick="clickCovid('Suspek')" style="cursor: pointer;border: 1px solid rgba(69, 90, 100, 0.14);">
                    <div class="card-block">
                        <div id="isLoadingFas">
                            @include('template.loader')
                        </div>
                        <div class="row align-items-center">
                            <div class="col-8">
                                <h4 class="text-c-yellow f-w-600 modal-title"><span id="titleSuspek"></span></h4>
                                <h6 class="text-muted m-b-0"></h6>
                            </div>
                            <div class="col-4 text-right">
                                <i class="fa fa-hospital-o f-46"></i>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-c-yellow">
                        <div class="row align-items-center">
                            <div class="col-9">
                                <h6 class="text-muted m-b-0"><p class="text-white m-b-0">KASUS SUSPEK</p></h6>
                            </div>
                            <div class="col-3 text-right">
                                {{--                                <i class="feather icon-trending-up text-white f-16">pasien dalam pengawasan (PDP)</i>--}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-12">
                <div class="card click-suspek"  onclick="clickCovid('Probable')" style="cursor: pointer;border: 1px solid rgba(69, 90, 100, 0.14);">
                    <div class="card-block">
                        <div id="isLoadingFas2">
                            @include('template.loader')
                        </div>
                        <div class="row align-items-center">
                            <div class="col-8">
                                <h4 class="text-c-green f-w-600 modal-title1"><span id="titleProbable"></span></h4>
                                <h6 class="text-muted m-b-0"></h6>
                            </div>
                            <div class="col-4 text-right">
                                <i class="fa fa-plus-square f-46"></i>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-c-green">
                        <div class="row align-items-center">
                            <div class="col-9">
                                <h6 class="text-muted m-b-0"><p class="text-white m-b-0">KASUS PROBABLE</p></h6>
                            </div>
                            <div class="col-3 text-right">
                                {{--                                <i class="feather icon-trending-up text-white f-16"></i>--}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-12 " id="isHideKonfir">

                  <div class="card click-suspek animated zoomInUp" onclick="clickKonfirmasi()"  style="cursor: pointer;border: 1px solid rgba(69, 90, 100, 0.14);">

                        <div class="card-block">
                            <div id="isLoadingFas3">
                                @include('template.loader')
                            </div>
                            <div class="row align-items-center">
                                <div class="col-8">
                                    <h4 class="text-c-blue f-w-600"><span id="countTerkonfirmasi"></span></h4>
                                    <h6 class="text-muted m-b-0"></h6>
                                </div>
                                <div class="col-4 text-right">
                                    <i class="fa fa-heartbeat f-46"></i>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-c-2">
                            <div class="row align-items-center">
                                <div class="col-9">
                                <!-- <a class="mytooltip tooltip-effect-4">
                                     <span class="tooltip-content3">Show Details</span> -->
                                    <h6 class="text-muted m-b-0"><p class="text-white m-b-0">KASUS KONFIRMASI</p></h6>
                                    <!-- </a> -->
                                </div>
                                <div class="col-3 text-right">
                                </div>
                            </div>
                        </div>
                    </div>

            </div>
            <div class="col-xl-3 col-md-12">
                <div class="card click-suspek" style="cursor: pointer;border: 1px solid rgba(69, 90, 100, 0.14);">
                    <div class="card-block">
                        <div id="isLoadingFas5">
                            @include('template.loader')
                        </div>
                        <div class="row align-items-center">
                            <div class="col-8">
                                <h4 class="text-c-pink f-w-600"><span id="titleKontakErat"></span></h4>
                                <h6 class="text-muted m-b-0"></h6>
                            </div>
                            <div class="col-4 text-right">
                                <i class="fa fa-heart f-46"></i>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-c-blue">
                        <div class="row align-items-center">
                            <div class="col-9">
                                <h6 class="text-muted m-b-0"><p class="text-white m-b-0">KONTAK ERAT</p></h6>
                            </div>
                            <div class="col-3 text-right">
                                {{--                                <i class="feather icon-trending-up text-white f-16"></i>--}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-12">
                <div class="card click-suspek" style="cursor: pointer;border: 1px solid rgba(69, 90, 100, 0.14);">
                    <div class="card-block">
                        <div id="isLoadingFas6">
                            @include('template.loader')
                        </div>
                        <div class="row align-items-center">
                            <div class="col-8">
                                <h4 class="text-c-green f-w-600"><span id="titlePelakuPerjalanan"></span></h4>
                                <h6 class="text-muted m-b-0"></h6>
                            </div>
                            <div class="col-4 text-right">
                                <i class="fa fa-suitcase f-46"></i>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-c-pink">
                        <div class="row align-items-center">
                            <div class="col-9">
                                <h6 class="text-muted m-b-0"><p class="text-white m-b-0">PELAKU PERJALANAN</p></h6>
                            </div>
                            <div class="col-3 text-right">
                                {{--                                <i class="feather icon-trending-up text-white f-16"></i>--}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-12">
                <div class="card click-suspek" onclick="clickCovid('Discarded')"  style="cursor: pointer;border: 1px solid rgba(69, 90, 100, 0.14);">
                    <div class="card-block">
                        <div id="isLoadingFas7">
                            @include('template.loader')
                        </div>
                        <div class="row align-items-center">
                            <div class="col-8">
                                <h4 class="text-c-yellow f-w-600"><span id="titleDiscarded"></span></h4>
                                <h6 class="text-muted m-b-0"></h6>
                            </div>
                            <div class="col-4 text-right">
                                <i class="fa fa-xing f-46"></i>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-c-1">
                        <div class="row align-items-center">
                            <div class="col-9">
                                <h6 class="text-muted m-b-0"><p class="text-white m-b-0">DISCARDED</p></h6>
                            </div>
                            <div class="col-3 text-right">
                                {{--                                <i class="feather icon-trending-up text-white f-16"></i>--}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-12">
                <div class="card click-suspek" onclick="clickCovid('Selesai Isolasi')"  style="cursor: pointer;border: 1px solid rgba(69, 90, 100, 0.14);">
                    <div class="card-block">
                        <div id="isLoadingFas8">
                            @include('template.loader')
                        </div>
                        <div class="row align-items-center">
                            <div class="col-8">
                                <h4 class="text-c-blue f-w-600"><span id="titleSelesaiIsolasi"></span></h4>
                                <h6 class="text-muted m-b-0"></h6>
                            </div>
                            <div class="col-4 text-right">
                                <i class="fa fa-child f-46"></i>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-c-3">
                        <div class="row align-items-center">
                            <div class="col-9">
                                <h6 class="text-muted m-b-0"><p class="text-white m-b-0">SELESAI ISOLASI</p></h6>
                            </div>
                            <div class="col-3 text-right">
                                {{--                                <i class="feather icon-trending-up text-white f-16"></i>--}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-12">
                <div class="card click-suspek" onclick="clickCovid('Kematian')"  style="cursor: pointer;border: 1px solid rgba(69, 90, 100, 0.14);">
                    <div class="card-block">
                        <div id="isLoadingFas9">
                            @include('template.loader')
                        </div>
                        <div class="row align-items-center">
                            <div class="col-8">
                                <h4 class="text-c-pink f-w-600"><span id="titleKematian"></span></h4>
                                <h6 class="text-muted m-b-0"></h6>
                            </div>
                            <div class="col-4 text-right">
                                <i class="fa fa-eercast f-46"></i>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-c-4">
                        <div class="row align-items-center">
                            <div class="col-9">
                                <h6 class="text-muted m-b-0"><p class="text-white m-b-0">KEMATIAN</p></h6>
                            </div>
                            <div class="col-3 text-right">
                                {{--                                <i class="feather icon-trending-up text-white f-16"></i>--}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-12 col-md-12 " id="isShowKonfir" >
                    <div class="card o-hidden animated zoomInDown" style="cursor: pointer;border: 1px solid rgba(69, 90, 100, 0.14);">
                        <div class="card-block bg-c-blue text-white">
                            <h6>KASUS TERKONFIRMASI &nbsp;&nbsp;
                                     <i class="feather icon-activity m-r-15"></i>
                                    <span style="font-weight:bold" id="countTerkonfirmasi2"></span>
                                 <span class="f-right">
                                    <!-- <i class="feather icon-activity m-r-15"></i> -->
                                    <!-- <span id="countTerkonfirmasi"></span> -->
                                    <i class="feather icon-minus" onclick="clickNonKonfirmasi()"></i>
                                </span>

                            </h6>

                        </div>
                        <div class="card-footer text-center">
                            <div class="row">
                            <div class="col-md-3 b-r-default">
                            <h6 class="m-b-20 p-b-5 b-b-default f-w-600">ASIMTOMATIC :</h6>
                                <div class="row ">
                                    <div class="col-xl-12 col-md-12">
                                        <div class="card bg-c-orenge  text-white widget-visitor-card">
                                            <div class="card-block-small text-center">
                                                <h2><span id="countAsimtomatic"></span></h2>
                                                <h6>-</h6>
                                                <i class="feather icon-filter"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-9">
                            <h6 class="m-b-20 p-b-5 b-b-default f-w-600">SIMTOMATIC :</h6>
                                <div class="row">
                                            <div class="col-xl-3 col-md-12" >
                                                <div class="card bg-c-blue text-white widget-visitor-card"  >
                                                    <div class="card-block-small text-center">
                                                        <h2><span id="countSakitRingan"></span></h2>
                                                        <h6>Sakit Ringan</h6>
                                                        <i class="feather icon-alert-circle"></i>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xl-3 col-md-12">
                                                <div class="card bg-c-yellow text-white widget-visitor-card" onclick="clickCovid('Sakit Sedang')">
                                                    <div class="card-block-small text-center">
                                                        <h2><span id="countSakitSedang"></span></h2>
                                                        <h6>Sakit Sedang</h6>
                                                        <i class="feather icon-alert-octagon"></i>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xl-3 col-md-12">
                                                <div class="card bg-simple-c-green text-white widget-visitor-card">
                                                    <div class="card-block-small text-center">
                                                        <h2><span id="countSakitBerat"></span></h2>
                                                        <h6>Sakit Berat </h6>
                                                        <i class="feather icon-alert-triangle"></i>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xl-3 col-md-12">
                                                <div class="card bg-c-pink text-white widget-visitor-card">
                                                    <div class="card-block-small text-center">
                                                        <h2><span id="countSakitKritis"></span></h2>
                                                        <h6>Sakit Kritis </h6>
                                                        <i class="feather icon-radio"></i>
                                                    </div>
                                                </div>
                                            </div>
                                </div>
                            </div>
                            </div>
                        </div>
                    </div>
            </div>

            <!-- <div class="col-xl-12 col-md-12">
                 <div class="card user-card-full" style="cursor: pointer;border: 1px solid rgba(69, 90, 100, 0.14);">
                    <div class="row m-l-0 m-r-0">
                        <div class="col-sm-4 bg-c-lite-green user-profile">
                            <div class="card-block text-center text-white center-euy" >
                                <div class="m-b-25">
                                    <i class="feather icon-users" style="font-size: 80px;"></i>
                                </div>
                                <h4 class="f-w-600" style="font-size:40px"><span id="countTerkonfirmasi"></span></h4>
                                <h6 class="f-w-600" style="font-size:20px" >KASUS KONFIRMASI</h6>
                            </div>
                        </div>
                        <div class="col-sm-8">
                            <div class="card-block">

                                <h6 class="m-b-20 p-b-5 b-b-default f-w-600">ASIMTOMATIC :</h6>
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="card bg-c-orenge  text-white widget-visitor-card">
                                            <div class="card-block-small text-center">
                                                <h2><span id="countAsimtomatic"></span></h2>
                                                <h6>Asimtomatic</h6>
                                                <i class="feather icon-filter"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <h6 class="m-b-20 m-t-40 p-b-5 b-b-default f-w-600">SIMTOMATIC :</h6>
                                <div class="row">
                                            <div class="col-sm-3">
                                                <div class="card bg-c-blue text-white widget-visitor-card">
                                                    <div class="card-block-small text-center">
                                                        <h2><span id="countSakitRingan"></span></h2>
                                                        <h6>Sakit Ringan</h6>
                                                        <i class="feather icon-alert-circle"></i>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-3">
                                                <div class="card bg-c-yellow text-white widget-visitor-card">
                                                    <div class="card-block-small text-center">
                                                        <h2><span id="countSakitSedang"></span></h2>
                                                        <h6>Sakit Sedang</h6>
                                                        <i class="feather icon-alert-octagon"></i>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-3">
                                                <div class="card bg-simple-c-green text-white widget-visitor-card">
                                                    <div class="card-block-small text-center">
                                                        <h2><span id="countSakitBerat"></span></h2>
                                                        <h6>Sakit Berat </h6>
                                                        <i class="feather icon-alert-triangle"></i>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-3">
                                                <div class="card bg-c-pink text-white widget-visitor-card">
                                                    <div class="card-block-small text-center">
                                                        <h2><span id="countSakitKritis"></span></h2>
                                                        <h6>Sakit Kritis </h6>
                                                        <i class="feather icon-radio"></i>
                                                    </div>
                                                </div>
                                            </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

                        </div> -->
                    </div>
                </div>
            </div>
            </div>



            <div class="col-md-12 col-xs-12">
                <div class="card">
                    <div class="card-header" style="padding: .75rem 1.25rem;">
                        <h5>Demografi 10 Besar Diagnosa </h5>
                        <div class="card-header-right">
                            <ul class="list-unstyled card-option">
                                <li><i class="feather icon-maximize full-card"></i></li>
                                <li><i class="feather icon-minus minimize-card"></i></li>
                                <li><i class="feather icon-trash-2 close-card"></i></li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-sm-4 col-md-4 col-xs-12" >
                        <select id="comboDiagnosa" class="form-control js-example-basic-single" name="diagnosa">
                            <option value="">-- Filter Diagnosa --</option>
                        </select>
                    </div>

                    <div class="card-block">
                      <div id="map-indo" style="width: 100%; height: 650px"></div>
                    </div>
                </div>
            </div>
{{--     KUNJUNGAN       --}}
            <div class="col-md-12 col-xs-12">
                <div class="card">
                    <div class="card-header" style="padding: .75rem 1.25rem;" >
                        <h5>Rekapitulasi Jumah Kunjungan Pasien</h5>
                        <div class="card-header-right">
                            <ul class="list-unstyled card-option">
                                <li><i class="feather icon-maximize full-card"></i></li>
                                <li><i class="feather icon-minus minimize-card"></i></li>
                                <li><i class="feather icon-trash-2 close-card"></i></li>
                            </ul>
                        </div>
                    </div>
                    <div class="card-block">
                        <div class="row" style=" margin-top: 15px;">
{{--                            <div class="col-md-12 col-xs-12">--}}
                                <div class="col-xl-4 col-md-12">
                                    <div class="card bg-c-yellow text-white" style="cursor: pointer" onclick="clickDetailKun('RJ')">
                                        <div class="card-block">
                                            <div class="row align-items-center">
                                                <div class="col">
                                                    <p class="m-b-5">Rawat Jalan</p>
                                                    <h4 class="m-b-0">{{ $rekapKunjungan['rajal'] }}</h4>
                                                </div>
                                                <div class="col col-auto text-right">
                                                    <i class="feather icon-user f-50 text-c-yellow"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-4 col-md-12">
                                    <div class="card bg-c-green text-white" style="cursor: pointer" onclick="clickDetailKun('RI')">
                                        <div class="card-block">
                                            <div class="row align-items-center">
                                                <div class="col">
                                                    <p class="m-b-5">Rawat Inap</p>
                                                    <h4 class="m-b-0">{{ $rekapKunjungan['ranap'] }}</h4>
                                                </div>
                                                <div class="col col-auto text-right">
                                                    <i class="feather icon-credit-card f-50 text-c-green"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-4 col-md-12">
                                    <div class="card bg-c-pink text-white" style="cursor: pointer" onclick="clickDetailKun('LAB')">
                                        <div class="card-block">
                                            <div class="row align-items-center">
                                                <div class="col">
                                                    <p class="m-b-5">Laboratorium</p>
                                                    <h4 class="m-b-0">{{ $rekapKunjungan['lab'] }}</h4>
                                                </div>
                                                <div class="col col-auto text-right">
                                                    <i class="feather icon-book f-50 text-c-pink"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-4 col-md-12">
                                    <div class="card bg-c-blue text-white" style="cursor: pointer" onclick="clickDetailKun('RAD')">
                                        <div class="card-block">
                                            <div class="row align-items-center">
                                                <div class="col">
                                                    <p class="m-b-5">Radiologi</p>
                                                    <h4 class="m-b-0">{{ $rekapKunjungan['rad'] }}</h4>
                                                </div>
                                                <div class="col col-auto text-right">
                                                    <i class="feather icon-trending-up  f-50 text-c-blue"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-4 col-md-12">
                                    <div class="card bg-c-1 text-white">
                                        <div class="card-block">
                                            <div class="row align-items-center">
                                                <div class="col">
                                                    <p class="m-b-5">Farmasi</p>
                                                    <h4 class="m-b-0">{{ $rekapKunjungan['farmasi'] }}</h4>
                                                </div>
                                                <div class="col col-auto text-right">
                                                    <i class="feather icon-trending-up  f-50 text-c-1"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-4 col-md-12">
                                    <div class="card bg-c-2 text-white" style="cursor: pointer" onclick="clickDetailKun('IGD')">
                                        <div class="card-block">
                                            <div class="row align-items-center">
                                                <div class="col">
                                                    <p class="m-b-5">IGD</p>
                                                    <h4 class="m-b-0">{{ $rekapKunjungan['igd'] }}</h4>
                                                </div>
                                                <div class="col col-auto text-right">
                                                    <i class="feather icon-trending-up  f-50 text-c-2"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
{{--                            </div>--}}
                            <div class="col-md-12 col-xs-12" >
                                <div class="card" style="border:1px solid rgba(69, 90, 100, 0.14);">
                                    <div class="card-header" style="padding: .75rem 1.25rem;" >
                                        <h5>Trend Kunjungan Rawat Jalan</h5>
                                    </div>
                                    <div class="card-block">
                                        <div class="row">
                                            <div class="col-md-12 col-xs-12" style="width: 100%">
                                                <div id="chartTrendRajal"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                       </div>
                    </div>
                </div>
            </div>
            <div class="col-md-12 col-xs-12">
            <div class="card">
                <div class="card-header" style="padding: .75rem 1.25rem;" >
                    <h5>Ketersediaan Tempat Tidur</h5>
                    <div class="card-header-right">
                        <ul class="list-unstyled card-option">
                            <li><i class="feather icon-maximize full-card"></i></li>
                            <li><i class="feather icon-minus minimize-card"></i></li>
                            <li><i class="feather icon-trash-2 close-card"></i></li>
                        </ul>
                    </div>
                </div>
                <div class="card-block">
                    <div class="row" style=" margin-top: 15px;">
                        <div class="col-md-12 col-xl-5" >
                            <div class="row">
                                <div class="col-xl-4 col-md-12">
                                    <div class="card" style="box-shadow: 10px 1px 20px 0 rgb(69 90 100 / 46%);cursor: pointer"
                                     onclick="clickBed('VIP')">
                                        <div class="card-block" style="background-color: rgb(183, 216, 229);">
                                            <div class="row align-items-center">
                                                <div class="col-12 txt-center">
                                                    <span class="f-s-15">VIP</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-footer ">
                                            <div class="row align-items-center">
                                                <div class="col-12 txt-center"><span class=" f-s-20">{{ $rekapKelas['vip'] }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-4 col-md-12">
                                    <div class="card" style="box-shadow: 10px 1px 20px 0 rgb(69 90 100 / 46%);cursor:pointer"
                                         onclick="clickBed('Kelas I')">
                                        <div class="card-block" style="background-color: rgb(183, 216, 229);">
                                            <div class="row align-items-center">
                                                <div class="col-12 txt-center">
                                                    <span class=" f-s-15">Kelas I</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-footer ">
                                            <div class="row align-items-center">
                                                <div class="col-12 txt-center"><span class=" f-s-20">{{ $rekapKelas['kls1'] }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-4 col-md-12">
                                    <div class="card" style="box-shadow: 10px 1px 20px 0 rgb(69 90 100 / 46%);cursor:pointer"
                                         onclick="clickBed('Kelas II')">
                                        <div class="card-block" style="background-color: rgb(183, 216, 229);">
                                            <div class="row align-items-center">
                                                <div class="col-12 txt-center">
                                                    <span class=" f-s-15">Kelas II</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-footer ">
                                            <div class="row align-items-center">
                                                <div class="col-12 txt-center"><span class=" f-s-20">{{ $rekapKelas['kls2'] }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-4 col-md-12">
                                    <div class="card" style="box-shadow: 10px 1px 20px 0 rgb(69 90 100 / 46%);cursor:pointer"
                                         onclick="clickBed('Kelas III')">
                                        <div class="card-block" style="background-color: rgb(183, 216, 229);">
                                            <div class="row align-items-center">
                                                <div class="col-12 txt-center">
                                                    <span class=" f-s-15">Kelas III</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-footer ">
                                            <div class="row align-items-center">
                                                <div class="col-12 txt-center"><span class=" f-s-20">{{ $rekapKelas['kls3'] }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-4 col-md-12">
                                    <div class="card" style="box-shadow: 10px 1px 20px 0 rgb(69 90 100 / 46%); cursor:pointer"
                                         onclick="clickBed('ICU')">
                                        <div class="card-block" style="background-color: rgb(183, 216, 229);" >
                                            <div class="row align-items-center">
                                                <div class="col-12 txt-center">
                                                    <span class="f-w-600">ICU</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-footer ">
                                            <div class="row align-items-center">
                                                <div class="col-12 txt-center"><span class=" f-s-20">{{ $rekapKelas['icu'] }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-4 col-md-12">
                                    <div class="card" style="box-shadow: 10px 1px 20px 0 rgb(69 90 100 / 46%);cursor:pointer"  onclick="clickBed('NICU')">
                                        <div class="card-block" style="background-color: rgb(183, 216, 229);">
                                            <div class="row align-items-center">
                                                <div class="col-12 txt-center">
                                                    <span class="f-w-600">NICU</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-footer ">
                                            <div class="row align-items-center">
                                                <div class="col-12 txt-center"><span class=" f-s-20">{{ $rekapKelas['nicu'] }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-4 col-md-12">
                                    <div class="card" style="box-shadow: 10px 1px 20px 0 rgb(69 90 100 / 46%);cursor:pointer"
                                         onclick="clickBed('PICU')">
                                        <div class="card-block" style="background-color: rgb(183, 216, 229);">
                                            <div class="row align-items-center">
                                                <div class="col-12 txt-center">
                                                    <span class=" f-s-15">PICU</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-footer ">
                                            <div class="row align-items-center">
                                                <div class="col-12 txt-center"><span class=" f-s-20">{{ $rekapKelas['picu'] }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-4 col-md-12">
                                    <div class="card" style="box-shadow: 10px 1px 20px 0 rgb(69 90 100 / 46%);cursor:pointer"
                                         onclick="clickBed('HCU')">
                                        <div class="card-block" style="background-color: rgb(183, 216, 229);">
                                            <div class="row align-items-center">
                                                <div class="col-12 txt-center">
                                                    <span class=" f-s-15">HCU</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-footer ">
                                            <div class="row align-items-center">
                                                <div class="col-12 txt-center"><span class=" f-s-20">{{ $rekapKelas['hcu'] }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-4 col-md-12">
                                    <div class="card" style="box-shadow: 10px 1px 20px 0 rgb(69 90 100 / 46%);cursor:pointer"
                                         onclick="clickBed('ICCU')">

                                        <div class="card-block" style="background-color: rgb(183, 216, 229);">
                                            <div class="row align-items-center">
                                                <div class="col-12 txt-center">
                                                    <span class=" f-s-15">ICCU</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-footer ">
                                            <div class="row align-items-center">
                                                <div class="col-12 txt-center"><span class=" f-s-20">{{ $rekapKelas['iccu'] }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-4 col-md-12">
{{--                                    <div class="card" style="box-shadow: 10px 1px 20px 0 rgb(69 90 100 / 46%);">--}}
{{--                                        <div class="card-block" style="background-color: rgb(183, 216, 229);">--}}
{{--                                            <div class="row align-items-center">--}}
{{--                                                <div class="col-12 txt-center">--}}
{{--                                                    <span class=" f-s-15"></span>--}}
{{--                                                </div>--}}
{{--                                            </div>--}}
{{--                                        </div>--}}
{{--                                        <div class="card-footer ">--}}
{{--                                            <div class="row align-items-center">--}}
{{--                                                <div class="col-12 txt-center"><span class=" f-s-20"></span>--}}
{{--                                                </div>--}}
{{--                                            </div>--}}
{{--                                        </div>--}}
{{--                                    </div>--}}
                                </div>
                                <div class="col-xl-4 col-md-12">
                                    <div class="card" style="box-shadow: 10px 1px 20px 0 rgb(69 90 100 / 46%);cursor:pointer"
                                         onclick="clickBed('Ruang Isolasi')">
                                        <div class="card-block" style="background-color: rgb(183, 216, 229);">
                                            <div class="row align-items-center">
                                                <div class="col-12 txt-center">
                                                    <span class=" f-s-15">Isolasi</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-footer ">
                                            <div class="row align-items-center">
                                                <div class="col-12 txt-center"><span class=" f-s-20">{{ $rekapKelas['isolasi'] }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-4 col-md-12">
{{--                                    <div class="card" style="box-shadow: 10px 1px 20px 0 rgb(69 90 100 / 46%);">--}}
{{--                                        <div class="card-block" style="background-color: rgb(183, 216, 229);">--}}
{{--                                            <div class="row align-items-center">--}}
{{--                                                <div class="col-12 txt-center">--}}
{{--                                                    <span class=" f-s-15"> </span>--}}
{{--                                                </div>--}}
{{--                                            </div>--}}
{{--                                        </div>--}}
{{--                                        <div class="card-footer ">--}}
{{--                                            <div class="row align-items-center">--}}
{{--                                                <div class="col-12 txt-center"><span class=" f-s-20"></span>--}}
{{--                                                </div>--}}
{{--                                            </div>--}}
{{--                                        </div>--}}
{{--                                    </div>--}}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12 col-xl-7" >
                            <div class="card" style="border:1px solid rgba(69, 90, 100, 0.14);">
                                <div class="card-header" style="padding: .75rem 1.25rem;" >
                                    <h5>Detail Ketersediaan Tempat Tidur</h5>
                                </div>
                                <div class="card-block">
                                    <div class="row">
                                        <div class="col-md-12 col-xs-12" style="width: 100%">
                                                <div id="loaddetailketersediaankamar">
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

{{--     SDM       --}}
            <div class="col-md-12 col-xs-12">
                <div class="card">
                    <div class="card-header" style="padding: .75rem 1.25rem;" >
                        <h5>Rekapitulasi Ketersediaan Sumber Daya Manusia </h5>
                        <div class="card-header-right">
                            <ul class="list-unstyled card-option">
                                <li><i class="feather icon-maximize full-card"></i></li>
                                <li><i class="feather icon-minus minimize-card"></i></li>
                                <li><i class="feather icon-trash-2 close-card"></i></li>
                            </ul>
                        </div>
                    </div>
                    <div class="card-block">
                        <div class="row" style=" margin-top: 15px;">
                            <div class="col-md-12 col-xl-6" >
                                <div class="card" style="border:1px solid rgba(69, 90, 100, 0.14);">
                                    <div class="card-header" style="padding: .75rem 1.25rem;" >
                                        <h5>Jenis Pegawai</h5>
                                    </div>
                                    <div class="card-block">
                                        <div class="row">
                                            <div class="col-md-12 col-xs-12" style="width: 100%">
                                                <div id="chartJenisPegawai"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12 col-xl-6" >
                                <div class="card" style="border:1px solid rgba(69, 90, 100, 0.14);">
                                    <div class="card-header" style="padding: .75rem 1.25rem;" >
                                        <h5>Pendidikan</h5>
                                    </div>
                                    <div class="card-block">
                                        <div class="row">
                                            <div class="col-md-12 col-xs-12" style="width: 100%">
                                                <div id="chartPendidikan"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12 col-xl-6" >
                                <div class="card" style="border:1px solid rgba(69, 90, 100, 0.14);">
                                    <div class="card-header" style="padding: .75rem 1.25rem;" >
                                        <h5>Jabatan</h5>
                                    </div>
                                    <div class="card-block">
                                        <div class="row">
                                            <div class="col-md-12 col-xs-12" style="width: 100%">
                                                <div id="chartJabatan"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12 col-xl-6" >
                                <div class="card" style="border:1px solid rgba(69, 90, 100, 0.14);">
                                    <div class="card-header" style="padding: .75rem 1.25rem;" >
                                        <h5>Jenis Kelamin</h5>
                                    </div>
                                    <div class="card-block">
                                        <div class="row">
                                            <div class="col-md-12 col-xs-12" style="width: 100%">
                                                <div id="chartJenisKelamin"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

{{--     STOK       --}}
            <div class="col-md-12 col-xs-12">
                <div class="card">
                    <div class="card-header" style="padding: .75rem 1.25rem;" >
                        <h5>Rekapitulasi Persediaan</h5>
                        <div class="card-header-right">
                            <ul class="list-unstyled card-option">
                                <li><i class="feather icon-maximize full-card"></i></li>
                                <li><i class="feather icon-minus minimize-card"></i></li>
                                <li><i class="feather icon-trash-2 close-card"></i></li>
                            </ul>
                        </div>
                    </div>
                    <div class="card-block">
                        <div class="row" style=" margin-top: 15px;">
                            <div class="col-md-12 col-xl-6" >
                                <div class="card" style="border:1px solid rgba(69, 90, 100, 0.14);">
                                    <div class="card-header" style="padding: .75rem 1.25rem;" >
                                        <h5>Stok</h5>
                                    </div>
                                    <div class="card-block"  style="    overflow: auto;
    height: 550px;">
                                        <div class="row">
                                            <div class="col-md-12 col-xs-12" style="width: 100%">
                                                <div id="loaddetailtablestok">
                                                </div>
{{--                                                <div class="dt-responsive table-responsive d-none">--}}
{{--                                                    <table id="tableStok" class="table table-striped table-bordered nowrap">--}}
{{--                                                        <thead>--}}
{{--                                                        <tr>--}}
{{--                                                            <th style="width: 40px">No</th>--}}
{{--                                                            <th>Nama Produk</th>--}}
{{--                                                            <th>Satuan</th>--}}
{{--                                                            <th>Stok</th>--}}
{{--                                                            <th>RS</th>--}}
{{--                                                            <th style="width: 80px;text-align: center;"># </th>--}}
{{--                                                        </tr>--}}
{{--                                                        </thead>--}}
{{--                                                        <tbody id="tBodyStok">--}}
{{--                                                        </tbody>--}}

{{--                                                    </table>--}}
{{--                                                </div>--}}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12 col-xl-6" >
                                <div class="card" style="border:1px solid rgba(69, 90, 100, 0.14);">
                                    <div class="card-header" style="padding: .75rem 1.25rem;" >
                                        <h5>Penggunaan 10 Besar Obat
                                        </h5>
                                    </div>
                                    <div class="card-block" style="    overflow: auto;
    height: 550px;">
                                        <div class="row">
                                            <div class="col-md-12 col-xs-12" style="width: 100%">
                                                <div id="chartTrendObat"></div>
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
    <!-- Page-body end -->
</div>
<div class="modal fade" id="modalDetailFaskes" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg"  role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Fasilitas Kesehatan</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="loaddetailtablefakses">
            </div>

            <div class="modal-footer">
            </div>
        </div>
    </div>
</div>
    <div class="modal fade" id="modalSuspek" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg"  role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title"><span id="titleModalCovid"></span></h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="load_suspek">
                </div>

                <div class="modal-footer">
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
    <div class="modal fade" id="modalBed" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg"  role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title"><span id="titleModalBed"></span></h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="load_bed">
                </div>

                <div class="modal-footer">
                </div>
            </div>
        </div>
    </div>
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
<script src="{{ asset('js/jvectormap/indonesia-adm1.js') }}"></script>
<script>
     $('#isLoading').show()
    var mymapDashboard;
    var APP_URL = {!! json_encode(url('/')) !!}
    function clickFaskes(){
        var tglawal = $("#tglawal").val();
        var tglakhir = $("#tglakhir").val();
        $.ajax({
            type    : 'GET',
            url     : APP_URL+'/get-data-faskes',
            data    : {tglawal:tglawal,tglakhir:tglakhir},
            cache   : false,
            success : function(respond){
                 $('#modalDetailFaskes').modal("show");
                 $("#loaddetailtablefakses").html(respond);
            }
        })
    }
    function clickBed(title) {
        $('#isLoading').show()
        var tglawal = $("#tglawal").val();
        var tglakhir = $("#tglakhir").val();
        $.ajax({
            type    : 'GET',
            url     : APP_URL+'/get-detail-bed',
            data    : {tglawal:tglawal,tglakhir:tglakhir,param:title},
            cache   : false,
            success : function(respond){
                document.getElementById("titleModalBed").innerHTML =  ' ' + title;
                $('#isLoading').hide()
                $('#modalBed').modal("show");
                $("#load_bed").html(respond);
            }
        })
    }
    function clickCovid(title){
        $('#isLoading').show()
        var tglawal = $("#tglawal").val();
        var tglakhir = $("#tglakhir").val();
        $.ajax({
            type    : 'GET',
            url     : APP_URL+'/get-detail-covid-pasien',
            data    : {tglawal:tglawal,tglakhir:tglakhir,param:title},
            cache   : false,
            success : function(respond){
                document.getElementById("titleModalCovid").innerHTML =  title;
                $('#isLoading').hide()
                $('#modalSuspek').modal("show");
                $("#load_suspek").html(respond);
            }
        })
    }
    function clickDetailKun(title){
        $('#isLoading').show()
        var tglawal = $("#tglawal").val();
        var tglakhir = $("#tglakhir").val();
        $.ajax({
            type    : 'GET',
            url     : APP_URL+'/get-detail-kunjungan-pasien',
            data    : {tglawal:tglawal,tglakhir:tglakhir,param:title},
            cache   : false,
            success : function(respond){
                document.getElementById("titleModalKun").innerHTML =  'Kunjungan ' + title;
                 $('#isLoading').hide()
                $('#modalKunjungan').modal("show");
                $("#load_kunjungan").html(respond);
            }
        })
    }

    $('#isShowKonfir').hide()
    function clickNonKonfirmasi(){
         $('#isShowKonfir').hide()
        //  $('#isHideKonfir').show()
    }
    function clickKonfirmasi(){
         $('#isShowKonfir').show()
        //  $('#isHideKonfir').hide()
    }
    function showPopUp(obj, kecamatan,kddiagnosa){
        var tglawal = $('#tglawal').val()
        var tglakhir = $('#tglakhir').val()
        document.getElementById("titleModal").innerHTML = 'Kecamatan ' +kecamatan + '['+ kddiagnosa +']' ;

        $('#modal-demografi-penyakit').modal("show");
    }
    function setKabupaten(obj, kabupaten,kddiagnosa){
        var tglawal = $('#tglawal').val()
        var tglakhir = $('#tglakhir').val()
        $.ajax({
            type   : 'GET',
            url    : APP_URL+'/get-pasien-by-kotakab',
            cache  : false,
            data    : {tglawal:tglawal,tglakhir:tglakhir,kddiagnosa:kddiagnosa,kabupaten:kabupaten},
            success :function(data){

                if (data.jumlah == 0 ){
                    toastr.error("Kecamatan belum di set koordinatnya","Perhatian!");
                    return false;
                }
                var countCamat  = 0
                $.each(data.pasienbykecamatan, function(index, value){
                    var greenIcon = new L.Icon({
                        iconUrl: "{!! asset('js/leaflet/images/marker-icon-2x-green.png') !!}",
                        shadowUrl: "{!! asset('js/leaflet/images/marker-shadow.png') !!}",
                        iconSize: [25, 41],
                        iconAnchor: [12, 41],
                        popupAnchor: [1, -34],
                        shadowSize: [41, 41]
                    });
                    if(value.lat == null && value.long == null){
                        countCamat = countCamat + 1

                    }else{
                        L.marker([value.lat, value.long],{icon: greenIcon}).bindTooltip("<b>Kecamatan "+ value.kecamatan +"</b><br>"+ value.jumlah +' Pasien').addTo(mymapDashboard).on('click', function(e){
                            showPopUp(this, value.kecamatan,kddiagnosa);
                        });
                    }

                    // L.marker([value.lat, value.long],{icon: greenIcon}).addTo(mymapDashboard)
                    //     .bindPopup("<b>Kecamatan "+ value.kecamatan +"</b><br>"+ value.jumlah +' Pasien').openPopup();
                });
                if(countCamat != 0){
                    toastr.info(countCamat+' Kecamatan Belum di set Koordinatnya','Perhatian')
                }

                return true;
            },
            error   : function (jqXHR, textStatus, errorThrown) { console.log(errorThrown);}
        });

    }
    "use strict";
    $(document).ready(function(){


        $('#tableDemog').DataTable({
            dom: 'tp'
        })
        $('#tableStok').DataTable({
            dom: 'tp'
        })

        var APP_URL = {!! json_encode(url('/')) !!}
        $('.js-example-basic-single').select2();

        //showMapData(null,kddiagnosaaa);
        var diagnosaPrimary =''
        var namaDiagnosaPrimary =''
        var dataMapAwal = []
        loadData()

        // LoadFaskes()
        function setMapLeaflet(kddiagnosa) {
            var latIndo = -2.548926
            var longIndo = 118.0148634
            // mymapDashboard = L.map('mapDiagnosa').setView([-7.2540452, 108.8497264], 8);
            mymapDashboard = L.map('mapDiagnosa').setView([latIndo,longIndo], 5);
            L.tileLayer('https://api.mapbox.com/styles/v1/{id}/tiles/{z}/{x}/{y}?access_token=pk.eyJ1IjoicmFtZGFuZWdpZSIsImEiOiJja2N1OG1uYjUyNWtjMnFsYjB3cGxiZ2RvIn0.bIO5MwJKX98q8D2-1lJ8zQ', {
                maxZoom: 15,
                attribution: 'Map data &copy; <a href="http://transmedic.co.id">transmedic@epic_team</a> contributors, ' +
                    '<a href="https://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, ' +
                    'Imagery � <a href="https://www.mapbox.com/">Mapbox</a>',
                id: 'mapbox/streets-v11',
                tileSize: 512,
                zoomOffset: -1
            }).addTo(mymapDashboard);
            var tglawal = $('#tglawal').val()
            var tglakhir = $('#tglakhir').val()
            $.ajax({
                type   : 'GET',
                url    : APP_URL+'/get-pasien-bymap/',
                cache  : false,
                data    : {tglawal:tglawal,tglakhir:tglakhir,kddiagnosa:kddiagnosa},
                success: function(respond)
                {
                    let data2 = respond.pasienbykota
                    if(data2.length > 0){
                        for (let i = 0; i < data2.length; i++) {
                            const element = data2[i]
                            // L.marker([element.lat, element.long]).bindTooltip(element.kotakabupaten + ' : '+element.jumlah + ' Pasien').addTo(mymapDashboard)

                            L.marker([element.lat, element.long]).bindTooltip("<b> "  + element.kotakabupaten +"</b><br>"+ element.jumlah +' Pasien').addTo(mymapDashboard).on('click', function(e){
                                setKabupaten(this, element.kotakabupaten,kddiagnosa);
                            });

                        }
                    }

                    // let nama = ''
                    // if(arrNama.length> 0){
                    //     nama = arrNama[1]
                    // }
                    // setMapData(respond,kddiagnosa,nama);
                }
            });
        }
        function onMapClick(e) {
            L.popup()
                .setLatLng(e.latlng)
                .setContent("You clicked the map at " + e.latlng.toString())
                .openOn(mymapDashboard);
        }


        function loadCombo() {
            $.ajax({
                type    : 'GET',
                url     : APP_URL+'/get-combo-diagnosa',
                cache   : false,
                success : function(respond){
                    $("#comboDiagnosa").html(respond);
                }
            });

        }

        $("#comboDiagnosa").on('select2:select', function(){
            var tglawal = $("#tglawal").val();
            var tglakhir = $("#tglakhir").val();
            var selectedDiagnosa = $(this).select2('data')
            var kddiagnosa = selectedDiagnosa[0].id
            var namaDiagnosa = selectedDiagnosa[0].text
            var arrNama = namaDiagnosa.split('-')
            let chartDefault = $('.chart-primary')
            let chartSecondary = $('.chart-secondary')

           if(kddiagnosa ==''){
               chartDefault.removeClass('show-chart')
               chartSecondary.addClass('show-chart')
               setMapData(dataMapAwal,diagnosaPrimary,namaDiagnosaPrimary);
           }else{
               chartDefault.addClass('show-chart')
               chartSecondary.removeClass('show-chart')
               setChartSecondary(kddiagnosa,namaDiagnosa)
               $.ajax({
                   type   : 'GET',
                   url    : APP_URL+'/get-diagnosa-bykode-byrsaddress/'+kddiagnosa,
                   cache  : false,
                   data    : {tglawal:tglawal,tglakhir:tglakhir},
                   success: function(respond)
                   {
                       let nama = ''
                       if(arrNama.length> 0){
                           nama = arrNama[1]
                       }
                       setMapData(respond,kddiagnosa,nama);
                   }
               });

           }
        })

        function  setChartSecondary(kddiagnosa,namadiagnosa) {
            var tglawal = $("#tglawal").val();
            var tglakhir = $("#tglakhir").val();
            $.ajax({
                type   : 'GET',
                url    : APP_URL+'/get-data-chart-rs',
                cache  : false,
                data    : {tglawal:tglawal,tglakhir:tglakhir,kddiagnosa:kddiagnosa},
                success: function(respond)
                {

                    let samateuuu = false
                    let resultSumProv = [];
                    for (let i in respond) {
                        samateuuu = false
                        for (let x in resultSumProv) {
                            if (resultSumProv[x].provinsi == respond[i].provinsi) {
                                resultSumProv[x].jumlah = parseFloat(resultSumProv[x].jumlah) + parseFloat(respond[i].jumlah)
                                samateuuu = true;
                            }
                        }
                        if (samateuuu == false) {
                            let result = {
                                provinsi: respond[i].provinsi,
                                jumlah: respond[i].jumlah,
                                namaprofile: respond[i].namaprofile,
                            }
                            resultSumProv.push(result)
                        }
                    }

                    let sama = false
                    let resultSumDep = [];
                    for (let i in respond) {
                        sama = false
                        for (let x in resultSumDep) {
                            if (resultSumDep[x].namaprofile == respond[i].namaprofile) {
                                sama = true;
                                resultSumDep[x].jumlah = parseFloat(resultSumDep[x].jumlah) + parseFloat(respond[i].jumlah)

                            }
                        }
                        if (sama == false) {
                            var dataDetail0 = [];
                            for (var f = 0; f < resultSumProv.length; f++) {
                                if (respond[i].namaprofile == resultSumProv[f].namaprofile) {
                                    if(resultSumProv[f].provinsi == null){
                                        resultSumProv[f].provinsi = '-'
                                    }
                                    dataDetail0.push([resultSumProv[f].provinsi, resultSumProv[f].jumlah]);
                                };
                            }
                            let result = {
                                id: respond[i].namaprofile,
                                name: respond[i].namaprofile,
                                namaprofile: respond[i].namaprofile,
                                jumlah: respond[i].jumlah,
                                data: dataDetail0
                            }
                            resultSumDep.push(result)
                        }
                    }
                    var dataz =[]
                    var dataDrilldown = []
                    for (let i = 0; i < resultSumDep.length ; i++) {
                        dataz.push({
                            name: resultSumDep[i].namaprofile,
                            y: parseFloat(resultSumDep[i].jumlah),
                            drilldown: resultSumDep[i].namaprofile
                        });
                        // dataDrilldown.push({name:resultSumDep[i].namaprofile,id:resultSumDep[i].namaprofile,data:[]})
                    }

                    Highcharts.chart('chartSecondary', {
                        chart: {
                            height: 500,
                            plotBackgroundColor: null,
                            plotBorderWidth: null,
                            plotShadow: false,
                            type: 'pie'
                        },
                        title: {
                            text: '',

                        },

                        // tooltip: {
                        //     pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
                        // },
                        plotOptions: {
                            pie: {
                                allowPointSelect: true,
                                cursor: 'pointer',
                                dataLabels: {
                                    enabled: true,
                                    color: '#000000',
                                    connectorColor: '#000000',
                                    formatter: function () {
                                        return this.percentage.toFixed(2) + ' %';
                                    }
                                },
                                showInLegend: true
                            },

                        },

                        credits: {
                            // text: '' ,
                            enabled: false
                        },
                        legend: {
                            enabled: true,
                            borderRadius: 5,
                            borderWidth: 1
                        },
                        series: [{
                            type: 'pie',
                            name:  namadiagnosa,
                            // colorByPoint: true,
                            data: dataz

                        }],
                        drilldown: {
                            series: resultSumDep,
                        }

                    })
                }
            });
        }
        function showTable(bool) {
            let tableWrap = $('.table-responsive')
            let classCha = $('.class-chart')

            if(bool){
                tableWrap.removeClass('d-none')
                classCha.removeClass('d-none')
            }else{
                tableWrap.addClass('d-none')
                classCha.addClass('d-none')
            }
        }
        function  showFlag(respond) {
            if(respond == null){
                document.getElementById("titleSuspek").innerHTML = 0
                document.getElementById("countTerkonfirmasi").innerHTML = 0
                document.getElementById("countTerkonfirmasi2").innerHTML = 0
                document.getElementById("titleProbable").innerHTML = 0
                document.getElementById("titleKontakErat").innerHTML = 0
                document.getElementById("titlePelakuPerjalanan").innerHTML = 0
                document.getElementById("titleDiscarded").innerHTML = 0
                document.getElementById("titleSelesaiIsolasi").innerHTML = 0
                document.getElementById("titleKematian").innerHTML = 0
                document.getElementById("countSakitKritis").innerHTML = 0
                document.getElementById("countSakitBerat").innerHTML = 0
                document.getElementById("countSakitSedang").innerHTML = 0
                document.getElementById("countSakitRingan").innerHTML = 0
                document.getElementById("countAsimtomatic").innerHTML = 0
            }else{
                let jmlSuspek = respond.suspect
                let jmlTerkonfirmasi =  respond.terkonfirmasi
                let jmlProbable =  respond.probable
                let jmlKontakErat =  respond.kontakerat
                let jmlPelakuPerjalanan  =  respond.pelakuperjalanan
                let jmlDiscarded = respond.discarded
                let jmlSelesaiIsolasi = respond.selesaiisolasi
                let jmlKematian = respond.kematian

                let jmlAsimtomic =  respond.asimtomatic
                let jmlSakitRingan  =  respond.sakitringan
                let jmlSakitSedang = respond.sakitsedang
                let jmlSakitBerat = respond.sakitberat
                let jmlSakitKritis = respond.sakitkritis

                document.getElementById("titleSuspek").innerHTML = jmlSuspek
                document.getElementById("countTerkonfirmasi").innerHTML = jmlTerkonfirmasi
                document.getElementById("countTerkonfirmasi2").innerHTML = jmlTerkonfirmasi
                document.getElementById("titleProbable").innerHTML = jmlProbable
                document.getElementById("titleKontakErat").innerHTML = jmlKontakErat
                document.getElementById("titlePelakuPerjalanan").innerHTML = jmlPelakuPerjalanan
                document.getElementById("titleDiscarded").innerHTML = jmlDiscarded
                document.getElementById("titleSelesaiIsolasi").innerHTML = jmlSelesaiIsolasi
                document.getElementById("titleKematian").innerHTML = jmlKematian
                document.getElementById("countSakitKritis").innerHTML = jmlSakitKritis
                document.getElementById("countSakitBerat").innerHTML = jmlSakitBerat
                document.getElementById("countSakitSedang").innerHTML = jmlSakitSedang
                document.getElementById("countSakitRingan").innerHTML = jmlSakitRingan
                document.getElementById("countAsimtomatic").innerHTML = jmlAsimtomic
            }
        }
        function loadData(){

            // $("#comboDiagnosa").select2("val", "");
            $("#comboDiagnosa").val(null)
            $("#comboDiagnosa").html("")
            $("#comboDiagnosa").append('<option value=""  >-- Filter Diagnosa --</option>')
            document.getElementById("titleSuspek").innerHTML = 0
            document.getElementById("countTerkonfirmasi").innerHTML = 0
            document.getElementById("countTerkonfirmasi2").innerHTML = 0

            document.getElementById("titleProbable").innerHTML = 0
            document.getElementById("titleKontakErat").innerHTML = 0
            document.getElementById("titlePelakuPerjalanan").innerHTML = 0
            document.getElementById("titleDiscarded").innerHTML = 0
            document.getElementById("titleSelesaiIsolasi").innerHTML = 0
            document.getElementById("titleKematian").innerHTML = 0
            document.getElementById("countSakitKritis").innerHTML = 0
            document.getElementById("countSakitBerat").innerHTML = 0
            document.getElementById("countSakitSedang").innerHTML = 0
            document.getElementById("countSakitRingan").innerHTML = 0
            // document.getElementById("countAsimtomatic").innerHTML = 0
            document.getElementById("countAsimtomatic").innerHTML = 0
            loadCombo()
            $('#isLoadingChart').show()
            // $('#isLoadingMap').show()
            $('#isLoadingTable').show()
            $('#isLoadingFas').show()
            $('#isLoadingFas2').show()
            $('#isLoadingFas3').show()
            $('#isLoadingFas4').show()
            $('#isLoadingFas5').show()
            $('#isLoadingFas6').show()
            $('#isLoadingFas7').show()
            $('#isLoadingFas8').show()
            $('#isLoadingFas9').show()
            showTable(false)
            var tglawal = $("#tglawal").val();
            var tglakhir = $("#tglakhir").val();
            // var fasKes = $("#fasKes").val();


                    $('#isLoadingChart').hide()
                    // $('#isLoadingMap').hide()
                    $('#isLoadingTable').hide()
                    $('#isLoadingFas').hide()
                    $('#isLoadingFas2').hide()
                    $('#isLoadingFas3').hide()
                    $('#isLoadingFas4').hide()
                    $('#isLoadingFas5').hide()
                    $('#isLoadingFas6').hide()
                    $('#isLoadingFas7').hide()
                    $('#isLoadingFas8').hide()
                    $('#isLoadingFas9').hide()
                    showTable(true)

                    let listdiagnosa = @json($listdiagnosa);

                    if(listdiagnosa == undefined){
                        window.location.href= "{!! route("logout") !!}"
                    }
                    // {{--let listdiagnosacovid = respond.listdiagnosacovid--}}
                    // {{--if(listdiagnosacovid == undefined){--}}
                    // {{--    window.location.href= "{!! route("logout") !!}"--}}
                    // {{--}--}}
                    let drilldown = @json($drilldown)//respond.drilldown
                    let kddiagnosa = "{!! $kddiagnosa !!}"//respond.kddiagnosa
                    let namadiagnosa =  "{!! $namadiagnosa !!}" //respond.namadiagnosa
                    let dataChartTrend =  @json($trend)
                    {{--let jmlSuspek = "{!! $titleSuspek !!}" //respond.titleSuspek--}}
                    {{--let jmlTerkonfirmasi =  "{!! $titleTerkonfirmasi !!}" //respond.titleTerkonfirmasi--}}
                    {{--let jmlProbable = "{!! $titleProbable !!}" //respond.titleProbable--}}
                    {{--let jmlKontakErat =  "{!! $titleKontakErat !!}"//respond.titleKontakErat--}}
                    {{--let jmlPelakuPerjalanan =  "{!! $titlePelakuPerjalanan !!}"//respond.titlePelakuPerjalanan--}}
                    {{--let jmlDiscarded = "{!! $titleDiscarded !!}"//respond.titleDiscarded--}}
                    {{--let jmlSelesaiIsolasi = "{!! $titleSelesaiIsolasi !!}"//respond.titleSelesaiIsolasi--}}
                    {{--let jmlKematian = "{!! $titleKematian !!}"// respond.titleKematian--}}

                    // let jmlSuspek = respond.suspect
                    // let jmlTerkonfirmasi =  respond.terkonfirmasi
                    // let jmlProbable =  respond.probable
                    // let jmlKontakErat =  respond.kontakerat
                    // let jmlPelakuPerjalanan  =  respond.pelakuperjalanan
                    // let jmlDiscarded = respond.discarded
                    // let jmlSelesaiIsolasi = respond.selesaiisolasi
                    // let jmlKematian = respond.kematian
                    //
                    // let jmlAsimtomic =  respond.asimtomatic
                    // let jmlSakitRingan  =  respond.sakitringan
                    // let jmlSakitSedang = respond.sakitsedang
                    // let jmlSakitBerat = respond.sakitberat
                    // let jmlSakitKritis = respond.sakitkritis
                    {{--let jmlTerkonfirmasi =  "{!! $titleTerkonfirmasi !!}" //respond.titleTerkonfirmasi--}}
                    {{--let jmlProbable = "{!! $titleProbable !!}" //respond.titleProbable--}}
                    {{--let jmlKontakErat =  "{!! $titleKontakErat !!}"//respond.titleKontakErat--}}
                    {{--let jmlPelakuPerjalanan =  "{!! $titlePelakuPerjalanan !!}"//respond.titlePelakuPerjalanan--}}
                    {{--let jmlDiscarded = "{!! $titleDiscarded !!}"//respond.titleDiscarded--}}
                    {{--let jmlSelesaiIsolasi = "{!! $titleSelesaiIsolasi !!}"//respond.titleSelesaiIsolasi--}}
                    {{--let jmlKematian = "{!! $titleKematian !!}"// respond.titleKematian   --}}

                    // if (jmlSuspek == ''){
                    //     jmlSuspek = 0
                    // }
                    // if (jmlTerkonfirmasi == ''){
                    //     jmlTerkonfirmasi = 0
                    // }
                    // if (jmlProbable == ''){
                    //     jmlProbable = 0
                    // }
                    // if (jmlKontakErat == ''){
                    //     jmlKontakErat = 0
                    // }
                    // if (jmlPelakuPerjalanan == ''){
                    //     jmlPelakuPerjalanan = 0
                    // }
                    // if (jmlDiscarded == ''){
                    //     jmlDiscarded = 0
                    // }
                    // if (jmlSelesaiIsolasi == ''){
                    //     jmlSelesaiIsolasi = 0
                    // }
                    // if (jmlKematian == ''){
                    //     jmlKematian = 0
                    // }

                    // document.getElementById("titleSuspek").innerHTML = jmlSuspek
                    // document.getElementById("countTerkonfirmasi").innerHTML = jmlTerkonfirmasi
                    // document.getElementById("countTerkonfirmasi2").innerHTML = jmlTerkonfirmasi
                    // document.getElementById("titleProbable").innerHTML = jmlProbable
                    // document.getElementById("titleKontakErat").innerHTML = jmlKontakErat
                    // document.getElementById("titlePelakuPerjalanan").innerHTML = jmlPelakuPerjalanan
                    // document.getElementById("titleDiscarded").innerHTML = jmlDiscarded
                    // document.getElementById("titleSelesaiIsolasi").innerHTML = jmlSelesaiIsolasi
                    // document.getElementById("titleKematian").innerHTML = jmlKematian
                    // document.getElementById("countSakitKritis").innerHTML = jmlSakitKritis
                    // document.getElementById("countSakitBerat").innerHTML = jmlSakitSedang
                    // document.getElementById("countSakitSedang").innerHTML = jmlSakitSedang
                    // document.getElementById("countSakitRingan").innerHTML = jmlSakitRingan
                    // document.getElementById("countAsimtomatic").innerHTML = jmlAsimtomic
                    {{--document.getElementById("countSakitKritis").innerHTML = "{!! $countSakitKritis !!}"--}}
                    {{--document.getElementById("countSakitBerat").innerHTML = "{!! $countSakitBerat !!}"--}}
                    {{--document.getElementById("countSakitSedang").innerHTML = "{!! $countSakitSedang !!}"--}}
                    {{--document.getElementById("countSakitRingan").innerHTML = "{!! $countSakitRingan !!}"--}}
                    {{--document.getElementById("countAsimtomatic").innerHTML = "{!! $countAsimtomatic !!}"--}}

                    // document.getElementById("titleModal").innerHTML = fasKes
                    // document.getElementById("titleTerkonfirmasi").innerHTML = Terkonfirmasi
                    // document.getElementById("titleSembuh").innerHTML = PasienSembuh
                    // let map = respond.map
                    diagnosaPrimary = kddiagnosa
                    namaDiagnosaPrimary = namadiagnosa
                    // dataMapAwal = map
                    setTableDiagnosa(listdiagnosa)
                    // setMapData(map,kddiagnosa,namadiagnosa);
                    getDataMap(kddiagnosa,namadiagnosa)
                    // setChartDiagnosDonut(listdiagnosa)
                    setHighChartDiagnosa(listdiagnosa,drilldown)
                    setChartTrend(dataChartTrend)
                    loadDataCovid()



        }
        function loadDetailKamr() {
            $.ajax({
                type    : 'GET',
                url     : APP_URL+'/get-dashboard-kamar',
                cache   : false,
                success : function(respond){
                    $("#loaddetailketersediaankamar").html(respond);
                    loadSDM()

                }
            });
        }
        function loadPersediaan() {
            var tglawal = $("#tglawal").val();
            var tglakhir = $("#tglakhir").val();
            $.ajax({
                type    : 'GET',
                url     : APP_URL+'/get-dashboard-persediaan-stok',
                cache   : false,
                success : function(respond){
                    $("#loaddetailtablestok").html(respond);
                    $.ajax({
                        type    : 'GET',
                        url     : APP_URL+'/get-dashboard-persediaan',
                        data    : {tglawal:tglawal,tglakhir:tglakhir},
                        cache   : false,
                        success : function(respond){
                            // setPersediaanStok()

                            $('#isLoading').hide()
                            setPersediaanTrend(respond.trendobat)
                        }
                    });
                }
            });





        }
        function setPersediaanTrend(dataChartTrend) {
            let categories = []
            // let data1 =[]
            let series =[]
            let color = getColorChart()
            for (let i in dataChartTrend) {
                categories.push(dataChartTrend[i].deskripsi)
                let data1 =[]
                data1.push({
                    y: parseFloat(dataChartTrend[i].jml),
                    color: color[i]
                });
                series.push({
                    name: dataChartTrend[i].deskripsi,
                    data: data1
                });
            }

            Highcharts.chart('chartTrendObat', {
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
                        text: 'Jumlah'
                    }
                },
                plotOptions: {
                    column: {
                        // url:"#",
                        cursor: 'pointer',

                        dataLabels: {
                            enabled: true,
                            color:'black', // getColorChart()[1],

                            formatter: function () {
                                return Highcharts.numberFormat(this.y, 0, '.', ',') + ' Obat';
                            }
                        },
                        showInLegend: true
                    }
                },
                tooltip: {
                    formatter: function () {
                        let point = this.point,
                            s = this.x + ':' + Highcharts.numberFormat(this.y, 0, '.', ',') + ' Obat <br/>';
                        return s;

                    }
                    // headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
                    // pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
                    //     '<td style="padding:0"><b>{point.y:.1f} </b></td></tr>',
                    // footerFormat: '</table>',
                    // shared: true,
                    // useHTML: true
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
        function setPersediaanStok() {
            $('#tBodyStok').empty();
            var trHTML = "";

            $.each(dataSource, function (i, item) {
                trHTML += "<tr><td style='width:40px;background-color: " +item.color+ "' >" + (i+1)
                    + "</td><td style='background-color: " +item.color+ "' >" + item.kddiagnosa +" - "+item.namadiagnosa
                    + "</td><td style='background-color: " +item.color+ "' >" + item.jumlah
                    + "</td><td style='background-color: " +item.color+ "' >" +
                    " <a href='#'  data-toggle='tooltip' data-placement='right' title='' ta-original-title='Klik untuk melihat Sebaran Diagnosa pada Demografi' class='btn btn-primary btn-outline-primary btn-mini diagnosmap ' "
                    + " data-kodediagnosa=" + item.kddiagnosa + " data-namadiagnosa=" + item.namadiagnosa + "  ><i class='icofont icofont-search'></i></a>"
                    + "</td></tr> ";
            });
            $('#tBodyStok').append(trHTML);
        }
        function  loadDataCovid() {
            var tglawal = $("#tglawal").val();
            var tglakhir = $("#tglakhir").val();

            $.ajax({
                type    : 'GET',
                url     : APP_URL+'/get-data-flag',
                data    : {tglawal:tglawal,tglakhir:tglakhir},
                cache   : false,
                success : function(respond){
                    showFlag(respond);
                    loadDetailKamr()

                }
            });
        }
        function  loadSDM() {
            $.ajax({
                type    : 'GET',
                url     : APP_URL+'/get-dashboard-pegawai',
                // data    : {tglawal:tglawal,tglakhir:tglakhir},
                cache   : false,
                success : function(respond){
                    setChartSDMJenisPegawai(respond.jenispegawai)
                    setChartSDMPendidikan(respond.pendidikan)
                    setChartSDMJK(respond.jeniskelamin)
                    setChartSDMJabatan(respond.jabatan)
                    loadPersediaan()
                }
            });
        }
        function  setChartSDMJenisPegawai(data) {
            let samateuuu = false
            let grouProf = [];
            let array = data
            let series = []
            for (let i in array) {
                samateuuu = false
                for (let x in grouProf) {
                    if (grouProf[x].namaprofile == array[i].namaprofile && grouProf[x].jenis == array[i].jenis) {
                        grouProf[x].total = parseFloat(grouProf[x].total) + parseFloat(array[i].total)
                        samateuuu = true;
                    }
                }
                if (samateuuu == false) {
                    let result = {
                        jenis: array[i].jenis,
                        namaprofile: array[i].namaprofile,
                        total: array[i].total,
                    }
                    grouProf.push(result)
                }
            }
            // console.log(grouProf)

            let sama = false
            let groupJenis = [];
            for (let i in array) {
                sama = false
                for (let x in groupJenis) {
                    if (groupJenis[x].jenis == array[i].jenis) {
                        sama = true;
                        groupJenis[x].total = parseFloat(groupJenis[x].total) + parseFloat(array[i].total)
                    }
                }
                // let resultGroupingRuangan = []
                if (sama == false) {
                    let result = {
                        jenis: array[i].jenis,
                        total: array[i].total,
                    }
                    groupJenis.push(result)
                }
            }

            // console.log(groupJenis)

            var dataz =[]
            var dataDrilldown = []
            for (let i = 0; i < groupJenis.length ; i++) {
                dataz.push({
                    name: groupJenis[i].jenis,
                    y: parseFloat(groupJenis[i].total),
                    drilldown: groupJenis[i].jenis
                });
                dataDrilldown.push({name:groupJenis[i].jenis,id:groupJenis[i].jenis,data:[]})
            }

            for (let i = 0; i < grouProf.length; i++) {
                const element = grouProf[i]
                for (let x = 0; x < dataDrilldown.length; x++) {
                    const element2 = dataDrilldown[x]
                    if(element.jenis == element2.name ){
                        element2.data.push([element.namaprofile,element.total ])
                    }
                }
            }

            Highcharts.chart('chartJenisPegawai', {
                chart: {
                    height: 450,
                    plotBackgroundColor: null,
                    plotBorderWidth: null,
                    plotShadow: false,
                    type: 'pie'
                },
                title: {
                    text: '',

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
                                return this.percentage.toFixed(2) + ' %';
                            }
                        },
                        showInLegend: true
                    },

                },

                credits: {
                    // text: '' ,
                    enabled: false
                },
                legend: {
                    enabled: true,
                    borderRadius: 5,
                    borderWidth: 1
                },
                series: [{
                    type: 'pie',
                    name: 'Jenis Pegawai',
                    // colorByPoint: true,
                    data: dataz

                }],
                drilldown: {
                    series: dataDrilldown
                }
            });
        }
        function  setChartSDMPendidikan(data) {
            let samateuuu = false
            let grouProf = [];
            let array = data
            let series = []
            for (let i in array) {
                samateuuu = false
                for (let x in grouProf) {
                    if (grouProf[x].namaprofile == array[i].namaprofile && grouProf[x].jenis == array[i].jenis) {
                        grouProf[x].total = parseFloat(grouProf[x].total) + parseFloat(array[i].total)
                        samateuuu = true;
                    }
                }
                if (samateuuu == false) {
                    let result = {
                        jenis: array[i].jenis,
                        namaprofile: array[i].namaprofile,
                        total: array[i].total,
                    }
                    grouProf.push(result)
                }
            }
            // console.log(grouProf)

            let sama = false
            let groupJenis = [];
            for (let i in array) {
                sama = false
                for (let x in groupJenis) {
                    if (groupJenis[x].jenis == array[i].jenis) {
                        sama = true;
                        groupJenis[x].total = parseFloat(groupJenis[x].total) + parseFloat(array[i].total)
                    }
                }
                // let resultGroupingRuangan = []
                if (sama == false) {
                    let result = {
                        jenis: array[i].jenis,
                        total: array[i].total,
                    }
                    groupJenis.push(result)
                }
            }

            // console.log(groupJenis)

            var dataz =[]
            var dataDrilldown = []
            for (let i = 0; i < groupJenis.length ; i++) {
                dataz.push({
                    name: groupJenis[i].jenis,
                    y: parseFloat(groupJenis[i].total),
                    drilldown: groupJenis[i].jenis
                });
                dataDrilldown.push({name:groupJenis[i].jenis,id:groupJenis[i].jenis,data:[]})
            }

            for (let i = 0; i < grouProf.length; i++) {
                const element = grouProf[i]
                for (let x = 0; x < dataDrilldown.length; x++) {
                    const element2 = dataDrilldown[x]
                    if(element.jenis == element2.name ){
                        element2.data.push([element.namaprofile,element.total ])
                    }
                }
            }

            Highcharts.chart('chartPendidikan', {
                chart: {
                    height: 450,
                    plotBackgroundColor: null,
                    plotBorderWidth: null,
                    plotShadow: false,
                    type: 'pie'
                },
                title: {
                    text: '',

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
                                return this.percentage.toFixed(2) + ' %';
                            }
                        },
                        showInLegend: true
                    },

                },

                credits: {
                    // text: '' ,
                    enabled: false
                },
                legend: {
                    enabled: true,
                    borderRadius: 5,
                    borderWidth: 1
                },
                series: [{
                    type: 'pie',
                    name: 'Pendidikan',
                    // colorByPoint: true,
                    data: dataz

                }],
                drilldown: {
                    series: dataDrilldown
                }
            });
        }
        function  setChartSDMJK(data) {
            let samateuuu = false
            let grouProf = [];
            let array = data
            let series = []
            for (let i in array) {
                samateuuu = false
                for (let x in grouProf) {
                    if (grouProf[x].namaprofile == array[i].namaprofile && grouProf[x].jenis == array[i].jenis) {
                        grouProf[x].total = parseFloat(grouProf[x].total) + parseFloat(array[i].total)
                        samateuuu = true;
                    }
                }
                if (samateuuu == false) {
                    let result = {
                        jenis: array[i].jenis,
                        namaprofile: array[i].namaprofile,
                        total: array[i].total,
                    }
                    grouProf.push(result)
                }
            }
            // console.log(grouProf)

            let sama = false
            let groupJenis = [];
            for (let i in array) {
                sama = false
                for (let x in groupJenis) {
                    if (groupJenis[x].jenis == array[i].jenis) {
                        sama = true;
                        groupJenis[x].total = parseFloat(groupJenis[x].total) + parseFloat(array[i].total)
                    }
                }
                // let resultGroupingRuangan = []
                if (sama == false) {
                    let result = {
                        jenis: array[i].jenis,
                        total: array[i].total,
                    }
                    groupJenis.push(result)
                }
            }

            // console.log(groupJenis)

            var dataz =[]
            var dataDrilldown = []
            for (let i = 0; i < groupJenis.length ; i++) {
                dataz.push({
                    name: groupJenis[i].jenis,
                    y: parseFloat(groupJenis[i].total),
                    drilldown: groupJenis[i].jenis
                });
                dataDrilldown.push({name:groupJenis[i].jenis,id:groupJenis[i].jenis,data:[]})
            }

            for (let i = 0; i < grouProf.length; i++) {
                const element = grouProf[i]
                for (let x = 0; x < dataDrilldown.length; x++) {
                    const element2 = dataDrilldown[x]
                    if(element.jenis == element2.name ){
                        element2.data.push([element.namaprofile,element.total ])
                    }
                }
            }

            Highcharts.chart('chartJenisKelamin', {
                chart: {
                    height: 450,
                    plotBackgroundColor: null,
                    plotBorderWidth: null,
                    plotShadow: false,
                    type: 'pie'
                },
                title: {
                    text: '',

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
                                return this.percentage.toFixed(2) + ' %';
                            }
                        },
                        showInLegend: true
                    },

                },

                credits: {
                    // text: '' ,
                    enabled: false
                },
                legend: {
                    enabled: true,
                    borderRadius: 5,
                    borderWidth: 1
                },
                series: [{
                    type: 'pie',
                    name: 'Jenis Kelamin',
                    // colorByPoint: true,
                    data: dataz

                }],
                drilldown: {
                    series: dataDrilldown
                }
            });
        }
        function  setChartSDMJabatan(data) {
            let samateuuu = false
            let grouProf = [];
            let array = data
            let series = []
            for (let i in array) {
                samateuuu = false
                for (let x in grouProf) {
                    if (grouProf[x].namaprofile == array[i].namaprofile && grouProf[x].jenis == array[i].jenis) {
                        grouProf[x].total = parseFloat(grouProf[x].total) + parseFloat(array[i].total)
                        samateuuu = true;
                    }
                }
                if (samateuuu == false) {
                    let result = {
                        jenis: array[i].jenis,
                        namaprofile: array[i].namaprofile,
                        total: array[i].total,
                    }
                    grouProf.push(result)
                }
            }
            // console.log(grouProf)

            let sama = false
            let groupJenis = [];
            for (let i in array) {
                sama = false
                for (let x in groupJenis) {
                    if (groupJenis[x].jenis == array[i].jenis) {
                        sama = true;
                        groupJenis[x].total = parseFloat(groupJenis[x].total) + parseFloat(array[i].total)
                    }
                }
                // let resultGroupingRuangan = []
                if (sama == false) {
                    let result = {
                        jenis: array[i].jenis,
                        total: array[i].total,
                    }
                    groupJenis.push(result)
                }
            }

            // console.log(groupJenis)

            var dataz =[]
            var dataDrilldown = []
            for (let i = 0; i < groupJenis.length ; i++) {
                dataz.push({
                    name: groupJenis[i].jenis,
                    y: parseFloat(groupJenis[i].total),
                    drilldown: groupJenis[i].jenis
                });
                dataDrilldown.push({name:groupJenis[i].jenis,id:groupJenis[i].jenis,data:[]})
            }

            for (let i = 0; i < grouProf.length; i++) {
                const element = grouProf[i]
                for (let x = 0; x < dataDrilldown.length; x++) {
                    const element2 = dataDrilldown[x]
                    if(element.jenis == element2.name ){
                        element2.data.push([element.namaprofile,element.total ])
                    }
                }
            }

            Highcharts.chart('chartJabatan', {
                chart: {
                    height: 450,
                    plotBackgroundColor: null,
                    plotBorderWidth: null,
                    plotShadow: false,
                    type: 'pie'
                },
                title: {
                    text: '',

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
                                return this.percentage.toFixed(2) + ' %';
                            }
                        },
                        showInLegend: true
                    },

                },

                credits: {
                    // text: '' ,
                    enabled: false
                },
                legend: {
                    enabled: true,
                    borderRadius: 5,
                    borderWidth: 1
                },
                series: [{
                    type: 'pie',
                    name: 'Jabatan',
                    // colorByPoint: true,
                    data: dataz

                }],
                drilldown: {
                    series: dataDrilldown
                }
            });
        }
        function setChartTrend(dataChartTrend) {
            let categories = []
            let data1 =[]
            let color = getColorChart()
            for (let i in dataChartTrend) {
                categories.push(dataChartTrend[i].tgl)
                data1.push({
                    y: parseFloat(dataChartTrend[i].jumlah),
                    color: color[i]
                });
            }
            Highcharts.chart('chartTrendRajal', {
                chart: {
                    zoomType: 'x',
                    spacingRight: 20
                },
                title: {
                    text: ''
                },

                xAxis: {
                    categories: categories,
                    crosshair: true,
                    // type: 'datetime',
                    //  maxZoom: 24 * 3600 * 1000, // fourteen days
                    title: {
                        text: null
                    }
                },
                yAxis: {
                    title: {
                        text: 'Jumlah Pasien'
                    }
                },
                tooltip: {
                    shared: true
                },
                legend: {
                    enabled: true,
                    borderRadius: 5,
                    borderWidth: 1,
                    // backgroundColor:undefined
                },
                plotOptions: {
                    area: {
                        fillColor: {
                            linearGradient: { x1: 0, y1: 0, x2: 0, y2: 1 },
                            stops: [
                                [0, Highcharts.getOptions().colors[0]],
                                // [1, Highcharts.Color(Highcharts.getOptions().colors[0]).setOpacity(0).get('rgba')]
                                [1, Highcharts.Color(Highcharts.getOptions().colors[0])]
                            ]
                        },
                        lineWidth: 1,
                        marker: {
                            enabled: true
                        },
                        shadow: false,
                        states: {
                            hover: {
                                lineWidth: 1
                            }
                        },
                        threshold: null
                    },
                    column: {
                        cursor: 'pointer',

                        dataLabels: {
                            enabled: true,
                            color: 'black', //getColorChart()[1],

                            formatter: function () {
                                return Highcharts.numberFormat(this.y, 0, '.', ',');
                            }
                        },
                        showInLegend: true
                    },

                },
                credits: {
                    enabled: false
                },

                series: [{
                    type: 'column',
                    name: 'Rawat Jalan',
                    data: data1
                }, ],

                responsive: {
                    rules: [{
                        condition: {
                            maxWidth: 500
                        },
                        chartOptions: {
                            legend: {
                                layout: 'horizontal',
                                align: 'center',
                                verticalAlign: 'bottom'
                            }
                        }
                    }]
                }

            });
            // Highcharts.chart('chartTrendRajal', {
            //     chart: {
            //         height: 450,
            //         plotBackgroundColor: null,
            //         plotBorderWidth: null,
            //         plotShadow: false,
            //         type: 'pie'
            //     },
            //     title: {
            //         text: '',
            //
            //     },
            //
            //     // tooltip: {
            //     //     pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
            //     // },
            //     plotOptions: {
            //         pie: {
            //             allowPointSelect: true,
            //             cursor: 'pointer',
            //             dataLabels: {
            //                 enabled: true,
            //                 color: '#000000',
            //                 connectorColor: '#000000',
            //                 formatter: function () {
            //                     return this.percentage.toFixed(2) + ' %';
            //                 }
            //             },
            //             showInLegend: true
            //         },
            //
            //     },
            //     // plotOptions: {
            //     //     series: {
            //     //         dataLabels: {
            //     //             enabled: true,
            //     //             format: '{point.name}: {point.y:.1f}%'
            //     //         }
            //     //     }
            //     // },
            //     credits: {
            //         // text: '' ,
            //         enabled: false
            //     },
            //     legend: {
            //         enabled: true,
            //         borderRadius: 5,
            //         borderWidth: 1
            //     },
            //     series: [{
            //         type: 'pie',
            //         name: 'Persentase 10 Besar Diagnosa',
            //         // colorByPoint: true,
            //         data: dataz
            //
            //     }],
            //     drilldown: {
            //         series: dataDrilldown
            //     }
            //
            // });
        }

        function getDataMap (kddiagnosa,namadiagnosa){
            var tglawal = $("#tglawal").val();
            var tglakhir = $("#tglakhir").val();

            $.ajax({
                type   : 'GET',
                url    : APP_URL+'/get-diagnosa-bykode-byrsaddress/'+kddiagnosa,
                cache  : false,
                data    : {tglawal:tglawal,tglakhir:tglakhir},
                success: function(respond)
                {
                    dataMapAwal = respond
                    setMapData(respond,kddiagnosa,namadiagnosa);
                }
            });

            // setMapLeaflet(kddiagnosa)
        }

        function setHighChartDiagnosa(listdiagnosa,drilldown) {
            var dataz =[]
            var dataDrilldown = []
            for (let i = 0; i < listdiagnosa.length ; i++) {
                dataz.push({
                    name: listdiagnosa[i].kddiagnosa+ ' - '+ listdiagnosa[i].namadiagnosa,
                    y: parseFloat(listdiagnosa[i].jumlah),
                    drilldown: listdiagnosa[i].kddiagnosa
                });
                dataDrilldown.push({name:listdiagnosa[i].kddiagnosa,id:listdiagnosa[i].kddiagnosa,data:[]})
            }
            // console.log(listdiagnosa)
            // console.log(drilldown)
            for (let i = 0; i < drilldown.length; i++) {
                const element = drilldown[i]
                for (let x = 0; x < dataDrilldown.length; x++) {
                    const element2 = dataDrilldown[x]
                    if(element.kddiagnosa == element2.name ){
                        element2.data.push([element.namaprofile,element.jumlah ])
                    }
                }
            }
            Highcharts.setOptions({
                colors:getColorChart()
            });
            Highcharts.chart('container', {
                chart: {
                    height: 450,
                    plotBackgroundColor: null,
                    plotBorderWidth: null,
                    plotShadow: false,
                    type: 'pie'
                },
                title: {
                    text: '',

                },

                // tooltip: {
                //     pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
                // },
                plotOptions: {
                    pie: {
                        allowPointSelect: true,
                        cursor: 'pointer',
                        dataLabels: {
                            enabled: true,
                            color: '#000000',
                            connectorColor: '#000000',
                            formatter: function () {
                                return this.percentage.toFixed(2) + ' %';
                            }
                        },
                        showInLegend: true
                    },

                },
                // plotOptions: {
                //     series: {
                //         dataLabels: {
                //             enabled: true,
                //             format: '{point.name}: {point.y:.1f}%'
                //         }
                //     }
                // },
                credits: {
                    // text: '' ,
                    enabled: false
                },
                legend: {
                    enabled: true,
                    borderRadius: 5,
                    borderWidth: 1
                },
                series: [{
                    type: 'pie',
                    name: 'Persentase 10 Besar Diagnosa',
                    // colorByPoint: true,
                    data: dataz

                }],
                drilldown: {
                    series: dataDrilldown
                }
                // chart: {
                //     plotBackgroundColor: null,
                //     plotBorderWidth: null,
                //     plotShadow: false,
                //     type: 'pie'
                // },
                // title: {
                //     text: ''
                // },
                // tooltip: {
                //     pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
                // },
                // accessibility: {
                //     point: {
                //         valueSuffix: '%'
                //     }
                // },
                // plotOptions: {
                //     pie: {
                //         allowPointSelect: true,
                //         cursor: 'pointer',
                //         dataLabels: {
                //             enabled: true,
                //             format: '<b>{point.name}</b>: {point.percentage:.1f} %'
                //         }
                //     }
                // },
                // credits: {
                //     // text: '' ,
                //     enabled: false
                // },
                // legend: {
                //     enabled: true,
                //     borderRadius: 5,
                //     borderWidth: 1
                // },
                // series: [{
                //     name: 'Brands',
                //     colorByPoint: true,
                //     data: [{
                //         name: 'Chrome',
                //         y: 61.41,
                //         sliced: true,
                //         selected: true
                //     }, {
                //         name: 'Internet Explorer',
                //         y: 11.84
                //     }, {
                //         name: 'Firefox',
                //         y: 10.85
                //     }, {
                //         name: 'Edge',
                //         y: 4.67
                //     }, {
                //         name: 'Safari',
                //         y: 4.18
                //     }, {
                //         name: 'Sogou Explorer',
                //         y: 1.64
                //     }, {
                //         name: 'Opera',
                //         y: 1.6
                //     }, {
                //         name: 'QQ',
                //         y: 1.2
                //     }, {
                //         name: 'Other',
                //         y: 2.61
                //     }]
                // }]
            });

        }

        function setTableDiagnosa(dataSource){

            $('#tBodyDemog').empty();
            var trHTML = "";

            $.each(dataSource, function (i, item) {
                trHTML += "<tr><td style='width:40px;background-color: " +item.color+ "' >" + (i+1)
                    + "</td><td style='background-color: " +item.color+ "' >" + item.kddiagnosa +" - "+item.namadiagnosa
                    + "</td><td style='background-color: " +item.color+ "' >" + item.jumlah
                    + "</td><td style='background-color: " +item.color+ "' >" +
                    " <a href='#'  data-toggle='tooltip' data-placement='right' title='' ta-original-title='Klik untuk melihat Sebaran Diagnosa pada Demografi' class='btn btn-primary btn-outline-primary btn-mini diagnosmap ' "
                    + " data-kodediagnosa=" + item.kddiagnosa + " data-namadiagnosa=" + item.namadiagnosa + "  ><i class='icofont icofont-search'></i></a>"
                    + "</td></tr> ";
            });
            $('#tableDemog').append(trHTML);
            // tableDemografiPenyakit.clear()

            // dataSource.forEach(function(item, index){
            //     tableDemografiPenyakit.row.add([
            //         index+1,item.kddiagnosa +' - '+ item.namadiagnosa, item.jumlah
            //     ])
            // })

            // tableDemografiPenyakit.draw()
            $(".diagnosmap").click(function(e){
                e.preventDefault();
                var kddiagnosa = $(this).attr("data-kodediagnosa");

                var namadiagnosa = $(this).attr("data-namadiagnosa");
                var tglawal = $("#tglawal").val();
                var tglakhir = $("#tglakhir").val();


                $.ajax({
                    type   : 'GET',
                    url    : APP_URL+'/get-diagnosa-bykode-byrsaddress/'+kddiagnosa,
                    cache  : false,
                    data    : {tglawal:tglawal,tglakhir:tglakhir},
                    success: function(respond)
                    {
                        setMapData(respond,kddiagnosa,namadiagnosa);
                    }
                });
            });

        }
        $(".click-suspek").click(function(e){
            e.preventDefault();
            var tglawal = $("#tglawal").val();
            var tglakhir = $("#tglakhir").val();

            var kddiagnosa ='B34.2'
            var namadiagnosa ='Coronavirus infection, unspecified'
            $.ajax({
                type   : 'GET',
                url    : APP_URL+'/get-diagnosa-bykode-byrsaddress/'+ kddiagnosa,
                cache  : false,
                data    : {tglawal:tglawal,tglakhir:tglakhir},
                success: function(respond)
                {
                    setMapData(respond,kddiagnosa,namadiagnosa);
                }
            });
        })


        // $("#tglawal").change(function(e){
        //     e.preventDefault();
        //
        //     loadData();
        //     // loadFaskes();
        //     let chartDefault = $('.chart-primary')
        //     let chartSecondary = $('.chart-secondary')
        //
        //     chartDefault.removeClass('show-chart')
        //     chartSecondary.addClass('show-chart')
        // });
        //
        // $("#tglakhir").change(function(e){
        //     e.preventDefault();
        //     loadData();
        //     // loadFaskes();
        //     let chartDefault = $('.chart-primary')
        //     let chartSecondary = $('.chart-secondary')
        //
        //     chartDefault.removeClass('show-chart')
        //     chartSecondary.addClass('show-chart')
        // });

        function setMapData(dataSource,kddiagnosa,namadiagnosa) {
            $('#comboDiagnosa').val(kddiagnosa).trigger('change');

            $('#map-indo').empty();
            var gdpData2 = []
            if(dataSource.length != undefined && dataSource.length > 0){
                dataSource.forEach(function(item, index){
                    // gdpData2.push({ 'item.kdmap': item.jumlah})
                    gdpData2[item.kdmap] = item.jumlah;
                })
            }else{
                gdpData2 = dataSource
            }

            $('#map-indo').vectorMap({
                map: 'indonesia-adm1_merc',
                backgroundColor:'#cccccc',
                onRegionClick: function(e, code){
                    if(gdpData2[code] != undefined){

                        $.ajax({
                            type   : 'GET',
                            url    : APP_URL+'/get-name-prov/'+code,
                            cache  : false,
                            success: function(responsd)
                            {
                                if(responsd.length> 0){
                                    window.location.href= "{!! route("show_page",["role"=>"admin","pages"=>'dashboard-detail']) !!}?kddiagnosa="
                                    + kddiagnosa + "&code="+code
                                    +"&namawilayah="+ responsd[0].provinsi
                                    +"&tglawal="+ $("#tglawal").val()
                                    +"&tglakhir="+ $("#tglakhir").val();
                                }else{
                                }
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
                    el.html(el.html()+' ('+kddiagnosa + '-'+namadiagnosa+' : '+jml+')');
                }
            });
        }
        function setChartDiagnosDonut(dataSource){
            let dataS = []
            let labelS = []
            dataSource.forEach(function(item, index){
                labelS.push(item.kddiagnosa)
                dataS.push(parseFloat(item.jumlah))
            })
            var config = {
                type: 'doughnut',
                data: {
                    datasets: [{
                        data: dataS ,
                        backgroundColor: getColorChart(),
                        label: 'Dataset 1'
                    }],
                    labels: {
                        render: 'percentage',
                        fontColor: ['green', 'white', 'red'],
                        precision: 2,
                        data:labelS
                    }
                },
                options: {
                    maintainAspectRatio:false,
                    pieceLabel: {
                        // mode 'label', 'value' or 'percentage', default is 'percentage'


                        // precision for percentage, default is 0
                        precision: 0,

                        // font size, default is defaultFontSize
                        fontSize: 18,

                        // font color, default is '#fff'
                        fontColor: '#fff',

                        // font style, default is defaultFontStyle
                        fontStyle: 'bold',

                        // font family, default is defaultFontFamily
                        fontFamily: "'Helvetica Neue', 'Helvetica', 'Arial', sans-serif"
                    },
                    responsive: true,
                    // legend:false,
                    legend:{
                        position:'top'
                    },
                    tooltips: {
                        enabled: true,
                        callbacks: {
                            label: function(tooltipItem, data) {
                                var da = dataSource;
                                ;
                                var label = da[tooltipItem.index].kddiagnosa+' - ' + da[tooltipItem.index].namadiagnosa;
                                var val = data.datasets[tooltipItem.datasetIndex].data[tooltipItem.index];
                                var lenn= dataS;
                                var tot = 0
                                for (var i = lenn.length - 1; i >= 0; i--) {
                                    tot= tot+  lenn[i]
                                }

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

            // var ctx = document.getElementById('chartDonut').getContext('2d');
            // var myDoughnut = new Chart(ctx, config);
        }
        function setChartumur(){
            var config ={
                type: 'pie',
                data: {
                    datasets: [{
                        data: [],
                        backgroundColor: colors,
                        label: 'Dataset 1'
                    }],
                    labels:[]
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

        let tableDemografiPenyakit = $('#table-rs').DataTable({
            dom: 'tp'
        });
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

    });
    //    https://github.com/nsetyo/jvectormap-indonesia/tree/master/maps/origin



</script>
@endsection
