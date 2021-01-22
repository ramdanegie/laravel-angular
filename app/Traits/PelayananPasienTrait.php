<?php
namespace App\Traits;

use App\Master\Pasien;
use App\Transaksi\PasienDaftar;
use App\Master\SettingDataFixed;
use App\Master\Rekanan;
use App\Master\Produk;
//use DB;
//use App\Transaksi\PelayananPasien;
//use App\Transaksi\PelayananPasienDetail;
//use App\Transaksi\PeriodeAccount;
//use App\Transaksi\PeriodeAccountSaldo;
Trait PelayananPasienTrait
{
    protected function getProdukIdDeposit(){
        $set = SettingDataFixed::where('namafield', 'idProdukDeposit')->first();
        $this->id= ($set) ? (int)$set->nilaifield: null;
        return $this->id;
    }

    protected function getPelayananPasienByNoRegistrasi($noRegistrasi){
        $pelayanan  = array();
        $pasienDaftar = PasienDaftar::where('noregistrasi', $noRegistrasi)->first();
        if(!$pasienDaftar){
            return $pelayanan;
        }else {
            $pelayanan = $pasienDaftar->pelayanan_pasien()->whereNull('strukfk')->get();
        }
        return $pelayanan;
    }

    protected function getPelayananPasienDetailByNoRegistrasi($noRegistrasi){
        $pelayanan  = array();
        $pasienDaftar = PasienDaftar::where('noregistrasi', $noRegistrasi)->first();
        if(!$pasienDaftar){
            return $pelayanan;
        }
        $pelayanan = $pasienDaftar->pelayanan_pasien_detail;
        return $pelayanan;
    }

    protected function getDaftarPasien($request){
        $pasienDaftar = PasienDaftar::has('pelayanan_pasien')->get();
        return $pasienDaftar;
    }

    protected function getDepositPasien($noregistrasi){
        $produkIdDeposit = $this->getProdukIdDeposit();
        $deposit = 0;
        $pasienDaftar  = PasienDaftar::has('pelayanan_pasien')->where('noregistrasi', $noregistrasi)->first();
        if($pasienDaftar){
            $depositList =$pasienDaftar->pelayanan_pasien()->where('nilainormal', '-1')->whereNull('strukfk')->get();
            foreach ($depositList as $item){
                if($item->produkfk==$produkIdDeposit){
                    $deposit = $deposit + $item->hargasatuan;
                }
            }
        }
        return $deposit;
    }

    protected function getBillingFromPelayananPasien($pelayanan){
        $totalBilling = 0;
        $totalKlaim = 0;
        $totalDeposit = 0;
        foreach ($pelayanan as $value){
            if($value->produkfk==$this->getProdukIdDeposit()){
                $totalDeposit = $totalDeposit + $value->hargajual;
            }else{
                $totalBilling = $totalBilling + (($value->hargajual-$value->hargadiscount) * $value->jumlah);
            }

        }

        $billing = new \stdClass();
        $billing->totalBilling = $totalBilling;
        $billing->totalKlaim= $totalKlaim;
        $billing->totalDeposit = $totalDeposit;

        return $billing;
    }

    protected function getBillingFromPelayananPasienDetail($pelayananDetail){
        $totalBilling = 0;
        $totalKlaim = 0;
        $totalDeposit = 0;
        foreach ($pelayananDetail as $value){
            if($value->produkfk==$this->getProdukIdDeposit()){
                $totalDeposit = $totalDeposit + $value->hargajual;
            }else{
                $totalBilling = $totalBilling + ($value->hargajual * $value->jumlah);
            }

        }
        $billing = new \stdClass();
        $billing->totalBilling = $totalBilling;
        $billing->totalKlaim= $totalKlaim;
        $billing->totalDeposit = $totalDeposit;

        return $billing;
    }

    //get siapa penjaminnya ini masih perlu disesuaikan nanti karna belum ngambil dari tabel pemakaianasuransi asuransi yang masih kecover itu umum
    //masih belum dimasi
    protected function getPenjamin($pasienDaftar){
        $rekananid=0;
        if($pasienDaftar->objectkelompokpasienlastfk==1 || $pasienDaftar->objectkelompokpasienlastfk==6){
            $rekananid=0;
        }elseif($pasienDaftar->objectkelompokpasienlastfk==2 || $pasienDaftar->objectkelompokpasienlastfk==4){
            $rekananid=2552;
        }

//        fungsi yang benarnya gini
//        $listPenjamin = $pasienDaftar->pemakaian_asuransi;
//        foreach ($listPenjamin as $pen){
//            $penjamin = $pen->asuransi->namarekanan;
//        }
        return Rekanan::find($rekananid);
    }

    protected function getProdukBiayaMaterai(){
        $set = SettingDataFixed::where('namafield', 'idProdukBiayaMaterai')->first();
        return Produk::find($set->nilaifield);
    }

    protected function getProdukBiayaAdministrasi(){
        $set = SettingDataFixed::where('namafield', 'idProdukAdministrasi')->first();
        return Produk::find($set->nilaifield);
    }

    protected function  getPercentageBiayaAdmin(){
        return 0.05;
    }

    protected function getUrlBrigdingBPJS(){
        $statusBridgingProduction = SettingDataFixed::where('namafield', 'isBridgingProduction')->first();
        if(!empty($statusBridgingProduction)){
            if($statusBridgingProduction->nilaifield == 'false') {
                $set = SettingDataFixed::where('namafield', 'linkBPJS')->first();
            } else{
                $set = SettingDataFixed::where('namafield', 'linkBPJSV1.1')->first();
            }
        }else{
            $set = SettingDataFixed::where('namafield', 'linkBPJSV1.1')->first();
        }
        return $set->nilaifield;
    }
    protected function dateRange( $first, $last, $step = '+1 day', $format = 'Y-m-d' ) {
        $dates = [];
        $current = strtotime( $first );
        $last = strtotime( $last );

        while( $current <= $last ) {

            $dates[] = date( $format, $current );
            $current = strtotime( $step, $current );
        }

        return $dates;
    }

//    protected function saveAkomodasiOtomatis($noregistrasi) {
//        DB::beginTransaction();
////        $dataLogin = $request->all();
//
//        try {
//            $data = DB::select(DB::raw("select pp.tglpelayanan,rpp.objectkelasfk,
//                    rpp.objectruanganfk,rpp.israwatgabung
//                    from pasiendaftar_t as pd
//                    INNER JOIN antrianpasiendiperiksa_t as apd on apd.noregistrasifk=pd.norec
//                    INNER JOIN registrasipelayananpasien_t as rpp on rpp.noregistrasifk=pd.norec
//                    INNER JOIN pelayananpasien_t as pp on pp.noregistrasifk=apd.norec
//                    INNER JOIN mapruangantoakomodasi_t as ma on ma.objectprodukfk=pp.produkfk
//                    INNER JOIN ruangan_m as ru_pd on ru_pd.id=pd.objectruanganlastfk
//                    where pd.tglpulang is null and rpp.tglkeluar is null and ru_pd.objectdepartemenfk=16
//                    and pp.tglpelayanan between :tglAwal and :tglAkhir
//                    and pd.noregistrasi=:noregistrasi;"),
//                array(
//                    'tglAwal' => date('Y-m-d 00:00:00'),
//                    'tglAkhir' => date('Y-m-d 23:59:59'),
//                    'noregistrasi' => $noregistrasi,
//                )
//            );
//            if (count($data) == 0){
//                $dataDong = DB::select(DB::raw("select rpp.objectkelasfk,
//                    rpp.objectruanganfk,rpp.israwatgabung ,apd.norec as norec_apd,pd.tglregistrasi
//                    from pasiendaftar_t as pd
//                    INNER JOIN antrianpasiendiperiksa_t as apd on apd.noregistrasifk=pd.norec
//                    INNER JOIN registrasipelayananpasien_t as rpp on rpp.noregistrasifk=pd.norec
//                    INNER JOIN ruangan_m as ru_pd on ru_pd.id=pd.objectruanganlastfk
//                    where pd.tglpulang is null and rpp.tglkeluar is null and ru_pd.objectdepartemenfk=16
//                    and pd.noregistrasi=:noregistrasi;"),
//                    array(
//                        'noregistrasi' => $noregistrasi,
//                    )
//                );
//                $sirahMacan = DB::select(DB::raw("select hett.* from mapruangantoakomodasi_t as map
//                    INNER JOIN harganettoprodukbykelas_m as hett on hett.objectprodukfk=map.objectprodukfk
//                    where map.objectruanganfk=:ruanganid and hett.objectkelasfk=:kelasid"),
//                    array(
//                        'ruanganid' => $dataDong[0]->objectruanganfk,
//                        'kelasid' => $dataDong[0]->objectkelasfk,
//                    )
//                );
//
//                $PelPasien = new PelayananPasien();
//                $PelPasien->norec = $PelPasien->generateNewId();
//                $PelPasien->kdprofile = 0;
//                $PelPasien->statusenabled = 't';
//                $PelPasien->noregistrasifk =  $dataDong[0]->norec_apd;
//                $PelPasien->tglregistrasi = $dataDong[0]->tglregistrasi;
//                $PelPasien->hargadiscount = 0;
//                $PelPasien->hargajual =  $sirahMacan[0]->hargasatuan;
//                $PelPasien->hargasatuan =  $sirahMacan[0]->hargasatuan;
//                $PelPasien->jumlah = 1;
//                $PelPasien->kelasfk =  $dataDong[0]->objectkelasfk;
//                $PelPasien->kdkelompoktransaksi =  1;
//                $PelPasien->piutangpenjamin =  0;
//                $PelPasien->piutangrumahsakit = 0;
//                $PelPasien->produkfk =  $sirahMacan[0]->objectprodukfk;
//                $PelPasien->stock =  1;
//                $PelPasien->tglpelayanan =  date('Y-m-d H:i:22');
//                $PelPasien->harganetto =  $sirahMacan[0]->harganetto1;
//
//                $PelPasien->save();
//                $PPnorec = $PelPasien->norec;
//
//                $buntutMacan = DB::select(DB::raw("select hett.* from mapruangantoakomodasi_t as map
//                    INNER JOIN harganettoprodukbykelasd_m as hett on hett.objectprodukfk=map.objectprodukfk
//                    where map.objectruanganfk=:ruanganid and hett.objectkelasfk=:kelasid;"),
//                    array(
//                        'ruanganid' => $dataDong[0]->objectruanganfk,
//                        'kelasid' => $dataDong[0]->objectkelasfk,
//                    )
//                );
//                foreach ($buntutMacan as $itemKomponen) {
//                    $PelPasienDetail = new PelayananPasienDetail();
//                    $PelPasienDetail->norec = $PelPasienDetail->generateNewId();
//                    $PelPasienDetail->kdprofile = 0;
//                    $PelPasienDetail->statusenabled = 't';
//                    $PelPasienDetail->noregistrasifk = $dataDong[0]->norec_apd;
//                    $PelPasienDetail->aturanpakai = '-';
//                    $PelPasienDetail->hargadiscount = 0;
//                    $PelPasienDetail->hargajual = $itemKomponen->hargasatuan;
//                    $PelPasienDetail->hargasatuan = $itemKomponen->hargasatuan;
//                    $PelPasienDetail->jumlah = 1;
//                    $PelPasienDetail->keteranganlain = '-';
//                    $PelPasienDetail->keteranganpakai2 = '-';
//                    $PelPasienDetail->komponenhargafk = $itemKomponen->objectkomponenhargafk;
//                    $PelPasienDetail->pelayananpasien = $PPnorec;
//                    $PelPasienDetail->piutangpenjamin = 0;
//                    $PelPasienDetail->piutangrumahsakit = 0;
//                    $PelPasienDetail->produkfk = $itemKomponen->objectprodukfk;
//                    $PelPasienDetail->stock = 1;
//                    $PelPasienDetail->tglpelayanan = date('Y-m-d H:i:22');
//                    $PelPasienDetail->harganetto = $itemKomponen->harganetto1;
//                    $PelPasienDetail->save();
////                    $PPDnorec = $PelPasienDetail->norec;
//                }
//            }
//
//            $transStatus = 'true';
//        } catch (\Exception $e) {
//            $transStatus = 'false';
//        }
//        $transMessage = "Akomodasi Otomatis";
//
////        if ($transStatus == 'true') {
////            $transMessage = $transMessage . "";
////            DB::commit();
////            $result = array(
////                "status" => 201,
//////                "message" => $transMessage,
//////                "data" => $dataLogin,//$noResep,,//$noResep,
//////                "count" => count($PP),
//////                "trans" => $postingJurnalTransaksi,
////                "as" => 'as@epic',
////            );
////        } else {
////            $transMessage = $transMessage ." Gagal!!";
////            DB::rollBack();
////            $result = array(
////                "status" => 400,
//////                "message"  => $transMessage,
////                "data" => $PelPasien,//$noResep,
////                "dataDetail" => $PelPasienDetail,
////                "as" => 'as@epic',
////            );
////        }
////        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
//        return 'OK';
//    }
    protected function getPortBrigdingBPJS(){
        $set = SettingDataFixed::where('namafield', 'portBrigdingBPJS')->first();
        return '';//$set->nilaifield;
    }
    protected function getUrlBrigdingBPJSnew(){
        $set = SettingDataFixed::where('namafield', 'linkBPJS')->first();//linkBPJSV1.1
        return $set->nilaifield;
    }
    protected function getUrlSisrute(){
        $set = SettingDataFixed::where('namafield', 'urlBridgingSisrute')->first();
        return $set->nilaifield;
    }
    protected function getUrlYankes(){
        $set = SettingDataFixed::where('namafield', 'urlBridgingYankes')->first();
        return $set->nilaifield;
    }
    protected function getIdConsumerBPJS(){
        $set = SettingDataFixed::where('namafield', 'idConsumerBPJS')->first(); // IdConsumerBPJSHarkit
        return $set->nilaifield;
    }
    protected function getPasswordConsumerBPJS(){
        $set = SettingDataFixed::where('namafield', 'passwordConsumerBPJS')->first(); //PasswordConsumerBPJSHarkit
        return $set->nilaifield;
    }

    protected function encryptSHA1($pass)
    {
        return sha1($pass);
    }
}