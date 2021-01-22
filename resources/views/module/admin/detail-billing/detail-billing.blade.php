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
    <div class="page-header m-t-50">
        <div class="row align-items-end">
            <div class="col-lg-10">
                <div class="page-header-title">
                    <div class="d-inline">
                        <h4>Detail Pemeriksaan</h4>
                        <span>Detail Pasien dan Rincian Tagihannya </span>
                    </div>
                </div>
            </div>
            <div class="col-lg-2">
                <button style="float:right" class="btn btn-inverse btn-outline-inverse" onclick="back()"><i class="icofont icofont-arrow-left"></i>Back</button>
            </div>
        </div>
    </div>
    <div class="page-body">
                 <div class="row">
                     <div class="col-xl-6 col-md-12">
                         <div class="card user-card-full">
                             <div class="row m-l-0 m-r-0">
                                 <div class="col-sm-4 bg-c-lite-green user-profile">
                                     <div class="card-block text-center text-white">
                                         <div class="m-b-25">
                                             <i class="feather icon-user class-icon"></i>
                                         </div>
                                         <h6 class="f-w-600">{!! $res['pasien']->namapasien  !!} </h6>
                                         <p>{!! $res['pasien']->nocm  !!} </p>
                                         <i class="feather icon-edit m-t-10 f-16"></i>
                                     </div>
                                 </div>
                                 <div class="col-sm-8">
                                     <div class="card-block">
                                         <h6 class="m-b-20 p-b-5 b-b-default f-w-600">Detail Pasien</h6>
                                         <div class="row">
                                             <div class="col-sm-6">
                                                 <p class="m-b-10 f-w-600">No Registrasi</p>
                                                 <h6 class="text-muted f-w-400">{!! $res['pasien']->noregistrasi  !!} </h6>
                                             </div>
                                             <div class="col-sm-6">
                                                 <p class="m-b-10 f-w-600">JK / Tgl Lahir</p>
                                                 <h6 class="text-muted f-w-400">{!! $res['pasien']->jeniskelamin.' / '.\App\Traits\Valet::getDateIndo($res['pasien']->tgllahir  )  !!} </h6>
                                             </div>
                                         </div>
{{--                                         <h6 class="m-b-20 m-t-40 p-b-5 b-b-default f-w-600">Projects</h6>--}}
                                         <div class="row">
                                             <div class="col-sm-6">
                                                 <p class="m-b-10 f-w-600">Tipe Penjamin</p>
                                                 <h6 class="text-muted f-w-400">{!! $res['pasien']->kelompokpasien  !!}</h6>
                                             </div>
                                             <div class="col-sm-6">
                                                 <p class="m-b-10 f-w-600">Ruangan</p>
                                                 <h6 class="text-muted f-w-400">{!! $res['pasien']->namaruangan  !!}</h6>
                                             </div>
                                         </div>
                                         <div class="row">
                                             <div class="col-sm-6">
                                                 <p class="m-b-10 f-w-600">Kelas</p>
                                                 <h6 class="text-muted f-w-400">{!! $res['pasien']->namakelas  !!}</h6>
                                             </div>
                                             <div class="col-sm-6">
                                                 <p class="m-b-10 f-w-600">No HP</p>
                                                 <h6 class="text-muted f-w-400">{!! $res['pasien']->nohp  !!}</h6>
                                             </div>
                                          </div>
                                         <div class="row">
                                             <div class="col-sm-6">
                                                 <p class="m-b-10 f-w-600">Tgl Masuk </p>
                                                 @php
                                                     $jamMasuk =   substr($res['pasien']->tglregistrasi, 11,5);
                                                     if($res['pasien']->tglpulang !=null){
                                                       $jamKeluar =   substr($res['pasien']->tglpulang, 11,5);
                                                       $tglKeluar =  \App\Traits\Valet::getDateIndo($res['pasien']->tglpulang);
                                                       $keluar=  $tglKeluar.' '. $jamKeluar ;
                                                     }else{
                                                         $keluar =  '-';
                                                     }
                                                      $tglmas =  \App\Traits\Valet::getDateIndo($res['pasien']->tglregistrasi);

                                                 @endphp
                                                 <h6 class="text-muted f-w-400">{!!  $tglmas.' '. $jamMasuk !!}</h6>
                                             </div>
                                             <div class="col-sm-6">
                                                 <p class="m-b-10 f-w-600"> Tgl Keluar</p>
                                                 <h6 class="text-muted f-w-400">{!! $keluar !!}</h6>
                                             </div>

                                         </div>
                                         <ul class="social-link list-unstyled m-t-40 m-b-10">
                                             <li><a href="#!" data-toggle="tooltip" data-placement="bottom" title="" data-original-title="facebook"><i class="feather icon-facebook facebook" aria-hidden="true"></i></a></li>
                                             <li><a href="#!" data-toggle="tooltip" data-placement="bottom" title="" data-original-title="twitter"><i class="feather icon-twitter twitter" aria-hidden="true"></i></a></li>
                                             <li><a href="#!" data-toggle="tooltip" data-placement="bottom" title="" data-original-title="instagram"><i class="feather icon-instagram instagram" aria-hidden="true"></i></a></li>
                                         </ul>
                                     </div>
                                 </div>
                             </div>
                         </div>
                     </div>
                     <div class="col-xl-6 col-md-12">
                         <div class="card table-card">
                             <div class="card-header">
                                 <h5>Pemeriksaan</h5>
                                 <div class="card-header-right">
                                     <ul class="list-unstyled card-option">
                                         <li><i class="feather icon-maximize full-card"></i></li>
                                         <li><i class="feather icon-minus minimize-card"></i></li>
                                         <li><i class="feather icon-trash-2 close-card"></i></li>
                                     </ul>
                                 </div>
                             </div>
                             <div class="card-block">
                                 <div class="table-responsive">
                                     <table class="table table-hover  table-borderless table table-striped">
                                         <thead>
                                         <tr>
                                             <th>No</th>
                                             <th>Keterangan</th>
                                             <th>Subtotal</th>
                                             <th>Jasa Pelayanan</th>
                                         </tr>
                                         </thead>
                                         <tbody>
                                         @php
                                            $totalTag = 0;
                                             $jasPel = 0;
                                         @endphp
                                         @forelse($res['details'] as $key => $item)
                                             @php
                                                 $totalTag = $totalTag + $item['total'];
                                                 $jasPel = $jasPel + $item['jasapelayanan'];
                                             @endphp
                                             <tr>
                                                 <td>{!! $key + 1 !!}</td>
                                                 <td>{!! $item['ruanganTindakan'] !!}</td>
                                                 <td class="text-c-blue" style="text-align: right">{!! number_format($item['total'],2,",",".") !!}</td>
                                                 <td class="text-c-pink" style="text-align: right">{!! number_format($item['jasapelayanan'],2,",",".") !!}</td>
                                             </tr>
                                             @empty

                                          @endforelse
                                         </tbody>
                                         @php
                                             $sisaTagihan = $totalTag - (float)$res['bayar']  -(float)$res['deposit']  - (float)$res['totalklaim'];
                                             $terbilang  = \App\Traits\Valet::terbilangs($sisaTagihan);
                                         @endphp
                                         <tfoot>
                                         <tr>
                                             <th colspan="2">Total Tagihan</th>
                                             <th style="text-align:right">{!! number_format($totalTag,2,",",".") !!}</th>
                                         </tr>
                                         <tr>
                                             <th colspan="2">Deposit</th>
                                             <th style="text-align:right">{!! number_format($res['deposit'] ,2,",",".") !!}</th>
                                         </tr>
                                         <tr>
                                             <th colspan="2">Dibayar</th>
                                             <th style="text-align:right">{!! number_format($res['bayar'],2,",",".") !!}</th>
                                         </tr>
                                         <tr>
                                             <th colspan="2">Tanggungan BPJS/INA-CBG's</th>
                                             <th style="text-align:right">{!! number_format($res['totalklaim'],2,",",".") !!}</th>
                                         </tr>
                                         <tr>
                                             <th colspan="2">Sisa Tagihan</th>
                                             <th style="text-align:right">{!! number_format($sisaTagihan,2,",",".") !!}</th>
                                         </tr>
                                         <tr>
                                             <td colspan="3" style="text-align:left;"><i class="text-c-blue">Terbilang : {!! ucwords($terbilang).' Rupiah' !!}</i></td>
                                         </tr>
                                         <tr>
                                             <th colspan="3" class="text-c-pink">Total Jasa Pelayanan</th>
                                             <th style="text-align:right" class="text-c-pink">{!! number_format($jasPel,2,",",".") !!}</th>
                                         </tr>
                                         </tfoot>
                                     </table>

                                 </div>
                             </div>
                         </div>
                     </div>
                </div>

</div>


@endsection

@section('javascript')
<script>

    var APP_URL = {!! json_encode(url('/')) !!}
    function back() {
        window.history.back()
    }
</script>

@endsection
