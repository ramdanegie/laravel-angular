<?php
/**
 * Created by PhpStorm.
 * User: Egie Ramdan
 * Date: 12/07/2019
 * Time: 10:40 AM
 */
namespace App\Http\Controllers\TataRekening;

use App\Http\Controllers\ApiController;
use App\Master\SettingDataFixed;
use App\Transaksi\AntrianPasienDiperiksa;
use App\Transaksi\LoggingUser;
use App\Transaksi\PasienDaftar;
use App\Transaksi\PelayananPasien;
use App\Transaksi\PelayananPasienDelete;
use App\Transaksi\PelayananPasienDetail;
use App\Transaksi\PelayananPasienPetugas;
use App\Transaksi\PelayananPasienTidakTerklaim;
use App\Transaksi\PelayananPasienTidakTerklaimDelete;
use App\Transaksi\PelayananPasienMutu;
use Illuminate\Http\Request;
use DB;
//use App\Pegawai\ModulAplikasi;
//use App\Pegawai\MapObjekModulToKelompokUser;
//use App\Pegawai\MapObjekModulAplikasiToModulAplikasi;
//use App\Pegawai\ObjekModulAplikasi;
//use App\Master\KelompokUser;

use App\Transaksi\StrukOrder;
use App\Transaksi\OrderPelayanan;
use App\Transaksi\OrderProduk;
use App\Master\Pegawai;
use App\Traits\Valet;
use phpDocumentor\Reflection\Types\Null_;
use Webpatser\Uuid\Uuid;

class TindakanController extends ApiController
{

    use Valet;

    public function __construct() {
        parent::__construct($skip_authentication=false);
    }

    public function getTindakan(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile =(int) $kdProfile;
        $dataLogin = $request->all();
        $data = \DB::table('harganettoprodukbykelas_m as hnp')
            ->join ('mapruangantoproduk_m as mpr ','mpr.objectprodukfk','=','hnp.objectprodukfk')
//            ->join ('harganettoprodukbykelasd_m as hnpd','hnpd.objectprodukfk','=','mpr.objectprodukfk')
            ->join ('produk_m as prd','prd.id','=','mpr.objectprodukfk')
            ->join ('kelas_m as kls','kls.id','=','hnp.objectkelasfk')
            ->join ('ruangan_m as ru', 'ru.id','=','mpr.objectruanganfk')
	        ->join ('suratkeputusan_m as sk', 'hnp.suratkeputusanfk','=','sk.id')
            ->select('mpr.objectprodukfk','prd.id','prd.namaproduk','hnp.hargasatuan','hnp.objectkelasfk',
                'kls.namakelas','mpr.objectruanganfk','ru.namaruangan',
            'prd.namaproduk1'
            )
            ->where('hnp.kdprofile', $idProfile)
            ->where('mpr.objectruanganfk',$request['idRuangan'])
            ->where('hnp.objectkelasfk',$request['idKelas'])
//            ->where('hnp.objectjenispelayananfk',$request['idJenisPelayanan'])
            ->where('mpr.statusenabled',true)
            ->where('hnp.statusenabled',true)
            ->where('sk.statusenabled',true)
            ->where('prd.statusenabled',true)
            // ->where('mpr.kodeexternal','2017')
            // ->where('hnp.kodeexternal', '2017')
        ;

        $data = $data->orderBy('prd.namaproduk', 'ASC');
//        $data=$data->groupBy('mpr.objectprodukfk','prd.id','prd.namaproduk','hnp.hargasatuan','hnp.objectkelasfk',
//            'kls.namakelas','mpr.objectruanganfk','ru.namaruangan');
        $data = $data->distinct();
        $data = $data->get();

        $result = array(
            'data' => $data,
            'message' => 'ramdanegie',
        );
        return $this->respond($result);
    }
    public function getCombo(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile =(int) $kdProfile;
        $detailLog = $request->all();
        $jenisPelaksana = \DB::table('jenispetugaspelaksana_m as jpp')
            ->where('jpp.statusenabled', true)
            ->orderBy('jpp.jenispetugaspe')
            ->get();
        $pegawai = \DB::table('pegawai_m as pg')
            ->where('pg.statusenabled', true)
            ->where('pg.kdprofile', $idProfile)
            ->orderBy('pg.namalengkap')
            ->get();

        $dataTarifAdminCito = \DB::table('settingdatafixed_m as rt')
            ->select('rt.namafield','rt.nilaifield')
            ->where('rt.statusenabled',true)
            ->where('rt.namafield','tarifadmincito')
            ->orderBy('rt.id')
            ->first();

        $result = array(
            'detaillogin' =>$detailLog,
            'jenispelaksana' => $jenisPelaksana,
            'pegawai' => $pegawai,
            'tarifcito' => $dataTarifAdminCito,
            'message' => 'ramdanegie',
        );

        return $this->respond($result);
    }

    public function getPegawaiByJenisPetugasPe(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile =(int) $kdProfile;
            $data = \DB::table('mapjenispetugasptojenispegawai_m as mpp')
                ->join('jenispegawai_m as jp', 'jp.id', '=', 'mpp.objectjenispegawaifk')
                ->join('pegawai_m as pg', 'pg.objectjenispegawaifk', '=', 'jp.id')
                ->join('jenispetugaspelaksana_m as jpp', 'jpp.id', '=', 'mpp.objectjenispetugaspefk')
                ->leftJoin('loginuser_s as lg', 'lg.objectpegawaifk', '=', 'pg.id')
                ->select('mpp.objectjenispegawaifk', 'jp.jenispegawai', 'mpp.objectjenispetugaspefk', 'jpp.jenispetugaspe',
                    'pg.namalengkap', 'pg.id'
//                    ,'lg.id as idloginuser','lg.namauser'
                )
                ->groupBy('mpp.objectjenispegawaifk', 'jp.jenispegawai', 'mpp.objectjenispetugaspefk', 'jpp.jenispetugaspe',
                    'pg.namalengkap','pg.id'
//                    ,'lg.id','lg.namauser'
                )
                ->where('mpp.kdprofile', $idProfile)
                ->where('mpp.objectjenispetugaspefk', $request['idJenisPetugas'])
                ->where('mpp.statusenabled', true)
                ->where('pg.statusenabled', true)
                ->where('jpp.statusenabled', true);
        $data = $data->orderBy('pg.namalengkap', 'ASC');
        $data = $data->get();

        $result = array(
            'jenispelaksana' => $data,
            'message' => 'ramdanegie',
        );

        return $this->respond($result);
    }
    public function saveTindakan(Request $request) {

        //TODO : SAVE TINDAKAN
        DB::beginTransaction();
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile =(int) $kdProfile;
        try{

        $antrian = AntrianPasienDiperiksa::where('norec',$request['pelayananpasien'][0]['noregistrasifk'])
            ->update([
                'ispelayananpasien' => true
            ]);
        $totalJasa=0;
        $totJasa=0;
        $penjumlahanJasa=0;
        $penjumlahanJasaTuslah=0;
        foreach ($request['pelayananpasien'] as $item){
            $totJasa=0;
            $totalJasa=0;
            $penjumlahanJasa=0;
            $penjumlahanJasaTuslah=0;
//            return $this->respond($item);
            $PelPasien = new PelayananPasien();
            $PelPasien->norec = $PelPasien->generateNewId();
            $PelPasien->kdprofile = $idProfile;
            $PelPasien->statusenabled = true;
    //        $noRegistrasi = $this->generateCode(new PasienDaftar(), 'noregistrasi', 10, $this->getDateTime()->format('ym'));
            $PelPasien->noregistrasifk =  $item['noregistrasifk'];
            $PelPasien->tglregistrasi = $item['tglregistrasi'];
    //        $PelPasien->aturanpakai = $New_PP['aturanpakai'];
    //        $PelPasien->generik =  $New_PP['generik'];
            $PelPasien->hargadiscount =  $item['diskon']; //0;
            $PelPasien->hargajual =  $item['hargajual'];
            $PelPasien->hargasatuan =  $item['hargasatuan'];
    //        $PelPasien->isdokumentasi =  $New_PP['isdokumentasi'];
    //        $PelPasien->isdosis =  $New_PP['isdosis'];
    //        $PelPasien->isinformasi =  $New_PP['isinformasi'];
    //        $PelPasien->isobat =  $New_PP['isobat'];
    //        $PelPasien->ispasien =  $New_PP['ispasien'];
    //        $PelPasien->isroute =  $New_PP['isroute'];
    //        $PelPasien->iswaktu =  $New_PP['iswaktu'];
    //        $PelPasien->jenisobatfk =  $New_PP['jenisobatfk'];
            $PelPasien->jumlah =  $item['jumlah'];
            $PelPasien->kelasfk =  $item['kelasfk'];
            $PelPasien->kdkelompoktransaksi =  1;
            if(isset( $item['keterangan'])){
                $PelPasien->keteranganlain =  $item['keterangan'];
            }
            //        $PelPasien->keteranganpakai2 =  $New_PP['keteranganpakai2'];
    //        $PelPasien->keteranganpakaifk =  $New_PP['keteranganpakaifk'];
    //        $PelPasien->nilainormal =  $New_PP['nilainormal'];
    //        $PelPasien->nobatch =  $New_PP['nobatch'];
            $PelPasien->piutangpenjamin =  0;
            $PelPasien->piutangrumahsakit = 0;
            $PelPasien->produkfk =  $item['produkfk'];
    //        $PelPasien->routefk =  $New_PP['routefk'];
    //        $PelPasien->status =  $New_PP['status'];
    //        $PelPasien->statusorder =  $New_PP['statusorder'];
            $PelPasien->stock =  1;
    //        $PelPasien->strukorderfk =  $New_PP['strukorderfk'];
            $PelPasien->tglpelayanan =  $item['tglpelayanan'];
            $PelPasien->harganetto =  $item['harganetto'];
    //        $PelPasien->strukfk =  $New_PP['strukfk'];
    //        $PelPasien->isbenar =  $New_PP['isbenar'];
    //        $PelPasien->norectriger =  $New_PP['norectriger'];
    //        $PelPasien->jeniskemasanfk =  $New_PP['jeniskemasanfk'];
    //        $PelPasien->rke =  $New_PP['rke'];
    //        $PelPasien->strukresepfk =  $New_PP['strukresepfk'];
    //        $PelPasien->satuanviewfk =  $New_PP['satuanviewfk'];
    //        $PelPasien->nilaikonversi =  $New_PP['nilaikonversi'];
    //        $PelPasien->strukterimafk =  $New_PP['strukterimafk'];
    //        $PelPasien->dosis =  $New_PP['dosis'];

//            $PelPasien->jasa =  $item['jasacito'];
            $PelPasien->iscito =  $item['iscito'];

            if(isset( $item['isparamedis'])){
                $PelPasien->isparamedis =  $item['isparamedis'];
            }
            if(isset( $item['jenispelayananfk'])){
                $PelPasien->jenispelayananfk =  $item['jenispelayananfk'];
            }

            
            $PelPasien->save();
            $PPnorec = $PelPasien->norec;

            $new_PPP=$item['pelayananpetugas'];
            foreach ($new_PPP as $itemPPP) {
                $detailItemPPP=$itemPPP['listpegawai'];
                foreach ($detailItemPPP as $detailItemPPPz){
                    $PelPasienPetugas = new PelayananPasienPetugas();
                    $PelPasienPetugas->norec = $PelPasienPetugas->generateNewId();
                    $PelPasienPetugas->kdprofile = $idProfile;
                    $PelPasienPetugas->statusenabled = true;
                    $PelPasienPetugas->nomasukfk = $item['noregistrasifk'];
                    $PelPasienPetugas->objectjenispetugaspefk = $itemPPP['objectjenispetugaspefk'];
        //            $PelPasienPetugas->objectasalprodukfk = $itemPPP['objectasalprodukfk'];
                     if ($detailItemPPPz=='undefined'){
                         $PelPasienPetugas->objectpegawaifk = null;
                     }else{

                         $PelPasienPetugas->objectpegawaifk = $detailItemPPPz['id'];
                     }
        //            $PelPasienPetugas->objectprodukfk = $itemPPP['objectprodukfk'];
        //            $PelPasienPetugas->objectruanganfk = $itemPPP['objectruanganfk'];
        //            $PelPasienPetugas->deskripsitugasfungsi = $itemPPP['deskripsitugasfungsi'];
        //            $PelPasienPetugas->ispetugaspepjawab = $itemPPP['ispetugaspepjawab'];
                    $PelPasienPetugas->pelayananpasien = $PPnorec;
        //            $PelPasienPetugas->tglpelayanan = $itemPPP['tglpelayanan'];
                    $PelPasienPetugas->save();
                    $PPPnorec = $PelPasienPetugas->norec;
               }
            }
            //TODO : TARIFF UP TUSLAH
            $dataTuslah = SettingDataFixed::where('id',1222)->get();
            //END TARIFF UP TUSLAH
            foreach ($item['komponenharga'] as $itemKomponen) {
                     $PelPasienDetail = new PelayananPasienDetail();
                     $PelPasienDetail->norec = $PelPasienDetail->generateNewId();
                     $PelPasienDetail->kdprofile = $idProfile;
                     $PelPasienDetail->statusenabled =true;
                     $PelPasienDetail->noregistrasifk = $item['noregistrasifk'];
                     //            $PelPasienDetail->tglregistrasi = $New_PPP['tglregistrasi'];
                     $PelPasienDetail->aturanpakai = '-';
                     //            $PelPasienDetail->generik =  $New_PPP['generik'];
                     $PelPasienDetail->hargadiscount = $item['diskon']; //0;
                     $PelPasienDetail->hargajual = $itemKomponen['hargasatuan'];
                     $PelPasienDetail->hargasatuan = $itemKomponen['hargasatuan'];
                     //            $PelPasienDetail->jenisobatfk =  $New_PPP['jenisobatfk'];
                     $PelPasienDetail->jumlah = 1;
                     $PelPasienDetail->keteranganlain = '-';
                     $PelPasienDetail->keteranganpakai2 = '-';
                     //            $PelPasienDetail->keteranganpakaifk =  $New_PPP['keteranganpakaifk'];
                     $PelPasienDetail->komponenhargafk = $itemKomponen['objectkomponenhargafk'];
                     //            $PelPasienDetail->nilainormal =  $New_PPP['nilainormal'];
                     $PelPasienDetail->pelayananpasien = $PPnorec;
                     $PelPasienDetail->piutangpenjamin = 0;
                     $PelPasienDetail->piutangrumahsakit = 0;
                     $PelPasienDetail->produkfk = $item['produkfk'];
                     //            $PelPasienDetail->routefk =  $New_PPP['routefk'];
                     //            $PelPasienDetail->statusorder =  $New_PPP['statusorder'];
                     $PelPasienDetail->stock = 1;
                     //            $PelPasienDetail->strukorderfk =  $New_PPP['strukorderfk'];
                     $PelPasienDetail->tglpelayanan = $item['tglpelayanan'];
                     $PelPasienDetail->harganetto = $itemKomponen['hargasatuan'];
                     //            $PelPasienDetail->strukfk =  $New_PPP['strukfk'];
                     //            $PelPasienDetail->norectriger =  $New_PPP['norectriger'];
                    If($itemKomponen['iscito'] == "1"){
//                        return $this->respond($item['nilaicito']);
                        if ($dataTuslah[0]->nilaifield > 0){
                            $penjumlahanJasa = ($itemKomponen['hargasatuan'] - $item['diskon']) * $item['nilaicito'] ;//$New_PPP['jasa'];
                            $penjumlahanJasaTuslah =  (((float)$itemKomponen['hargasatuan'] * (int)$dataTuslah[0]->nilaifield) /100) ;
                            $totalJasa = $totalJasa +  $penjumlahanJasa + $penjumlahanJasaTuslah;

                            $PelPasienDetail->jasa = $penjumlahanJasa + $penjumlahanJasaTuslah;
                        }else{
                            $penjumlahanJasa = ($itemKomponen['hargasatuan'] - $item['diskon']) * $item['nilaicito'] ;//$New_PPP['jasa'];
                            $PelPasienDetail->jasa = $penjumlahanJasa;
                            $totalJasa = $totalJasa +  $penjumlahanJasa;
                        }
                    }else{
                        if ($dataTuslah[0]->nilaifield > 0){
                            $penjumlahanJasaTuslah = ((float)$itemKomponen['hargasatuan'] * (int)$dataTuslah[0]->nilaifield) /100 ;
                            $PelPasienDetail->jasa = $penjumlahanJasaTuslah;
                            $totalJasa = $totalJasa +  $penjumlahanJasaTuslah;
                        }else{
                            $penjumlahanJasa=0;
                            $PelPasienDetail->jasa = $penjumlahanJasa;
                            $totalJasa = $totalJasa +  $penjumlahanJasa;
                        }
                    }

                     $PelPasienDetail->save();
                     $PPDnorec = $PelPasienDetail->norec;
                     $transStatus = 'true';
                 }


                 if ($item['iscito'] == 1){
                     $dataaa= PelayananPasienDetail::where('pelayananpasien', $PPnorec)->get();
                     foreach ($dataaa as $itemss){
                         $totJasa=$totJasa+$itemss->jasa;
                     }
                     $dataJasa= PelayananPasien::where('norec',$PPnorec)
                         ->update([
                             'jasa' => $totalJasa
                     ]);
                 }


                 if ($dataTuslah[0]->nilaifield > 0){
                     $dataJasa= PelayananPasien::where('norec',$PPnorec)
                         ->update([
                             'istuslah' => 1,
                             'jasa' => $totalJasa
                         ]);
                 }

                 if(isset($item['diskon'])){
	                if ($item['diskon'] != 0){
		                $data= PelayananPasienDetail::where('pelayananpasien', $PPnorec)
			                ->where('komponenhargafk',35)
			                ->update([
					                'hargadiscount' => $item['diskon']]
			                );

		                $data2= PelayananPasien::where('norec', $PPnorec)
			                ->update([
					                'hargadiscount' => $item['diskon']]
			                );
	                }
                }
            }


                $transStatus = 'true';
            } catch (\Exception $e) {
                $transStatus = 'false';
                $transMessage = "simpan PelPasien";
            }
//        try{
//            foreach ($request['pelayananpasien'] as $itemzz){
//                 foreach ($itemzz['komponenharga'] as $itemKomponen) {
//                     $PelPasienDetail = new PelayananPasienDetail();
//                     $PelPasienDetail->norec = $PelPasienDetail->generateNewId();
//                     $PelPasienDetail->kdprofile = 0;
//                     $PelPasienDetail->statusenabled = true;
//                     $PelPasienDetail->noregistrasifk = $itemzz['noregistrasifk'];
//                     //            $PelPasienDetail->tglregistrasi = $New_PPP['tglregistrasi'];
//                     $PelPasienDetail->aturanpakai = '-';
//                     //            $PelPasienDetail->generik =  $New_PPP['generik'];
//                     $PelPasienDetail->hargadiscount = 0;
//                     $PelPasienDetail->hargajual = $itemKomponen['hargasatuan'];
//                     $PelPasienDetail->hargasatuan = $itemKomponen['hargasatuan'];
//                     //            $PelPasienDetail->jenisobatfk =  $New_PPP['jenisobatfk'];
//                     $PelPasienDetail->jumlah = 1;
//                     $PelPasienDetail->keteranganlain = '-';
//                     $PelPasienDetail->keteranganpakai2 = '-';
//                     //            $PelPasienDetail->keteranganpakaifk =  $New_PPP['keteranganpakaifk'];
//                     $PelPasienDetail->komponenhargafk = $itemKomponen['objectkomponenhargafk'];
//                     //            $PelPasienDetail->nilainormal =  $New_PPP['nilainormal'];
//                     $PelPasienDetail->pelayananpasien = $PPnorec;
//                     $PelPasienDetail->piutangpenjamin = 0;
//                     $PelPasienDetail->piutangrumahsakit = 0;
//                     $PelPasienDetail->produkfk = $itemzz['produkfk'];
//                     //            $PelPasienDetail->routefk =  $New_PPP['routefk'];
//                     //            $PelPasienDetail->statusorder =  $New_PPP['statusorder'];
//                     $PelPasienDetail->stock = 1;
//                     //            $PelPasienDetail->strukorderfk =  $New_PPP['strukorderfk'];
//                     $PelPasienDetail->tglpelayanan = $itemzz['tglpelayanan'];
//                     $PelPasienDetail->harganetto = $itemKomponen['hargasatuan'];
//                     //            $PelPasienDetail->strukfk =  $New_PPP['strukfk'];
//                     //            $PelPasienDetail->norectriger =  $New_PPP['norectriger'];
//                     //            $PelPasienDetail->jasa =  $New_PPP['jasa'];
//                     $PelPasienDetail->save();
//                     $PPDnorec = $PelPasienDetail->norec;
//                     $transStatus = 'true';
//                 }

//            }
//                $transStatus = 'true';
//        } catch (\Exception $e) {
//                $transStatus = 'false';
//                $transMessage = "simpan Pel Petugas";
//        }

        if ($transStatus == 'true') {
            $transMessage = "Simpan PelayananPasien Sukses";
            DB::commit();
            $result = array(
                'status' => 201,
                'message' => $transMessage,
                'dataPP' => $PelPasien,
//                'dataPPP' => $PelPasienPetugas,
                'dataPPD' => $PelPasienDetail,
                'dataTuslah' => $dataTuslah,
                'as' => 'ramdanegie',
                'edited' => 'ea@epic',
                'as' => 'as@epic',
            );
        } else {
            $transMessage = "Simpan PelayananPasien Gagal";
            DB::rollBack();
            $result = array(
                'status' => 400,
                'message'  => $transMessage,
                'dataPP' => $PelPasien,
//                'dataPPP' => $PelPasienPetugas,
//                'dataPPD' => $PelPasienDetail,
                'as' => 'ramdanegie',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
//        return $this->respond($requestAll);
    }

    public function getKomponenHarga(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile =(int) $kdProfile;
        //TODO :GET HARGA & HARGA KOMPONEN
//        $dataLogin = $request->all();
        $data = \DB::table('harganettoprodukbykelasd_m as hnp')
            ->join ('mapruangantoproduk_m as mpr ','mpr.objectprodukfk','=','hnp.objectprodukfk')
//            ->join ('harganettoprodukbykelasd_m as hnpd','hnpd.objectprodukfk','=','mpr.objectprodukfk')
            ->join ('produk_m as prd','prd.id','=','mpr.objectprodukfk')
            ->join ('komponenharga_m as kh','kh.id','=','hnp.objectkomponenhargafk')
            ->join ('kelas_m as kls','kls.id','=','hnp.objectkelasfk')
            ->join ('ruangan_m as ru', 'ru.id','=','mpr.objectruanganfk')
            ->select('hnp.objectkomponenhargafk','kh.komponenharga','hnp.hargasatuan','mpr.objectprodukfk','kh.iscito')

            ->where('mpr.objectruanganfk',$request['idRuangan'])
            ->where('hnp.objectkelasfk',$request['idKelas'])
            ->where('mpr.objectprodukfk',$request['idProduk'])
           ->where('hnp.objectjenispelayananfk',$request['idJenisPelayanan'])
            ->where('mpr.statusenabled',true)
            ->where('hnp.statusenabled',true)
            // ->where('sk.statusenabled',true)
            ->where('prd.statusenabled',true)
            ->where('hnp.kdprofile',$idProfile)
//            ->where('mpr.statusenabled',true)
//            ->where('hnp.statusenabled',true)
//            ->where('prd.statusenabled',true)
            // ->where('mpr.kodeexternal','2017')
//            ->where('hnp.kodeexternal', '2017')
//            ->where('prd.kodeexternal','2017')
            ;

//        $data = $data->groupBy('prd.namaproduk','hnp.objectkomponenhargafk','kh.komponenharga','hnp.hargasatuan','mpr.objectprodukfk');
        $data = $data->distinct();
        $data = $data->get();


        $data2 = \DB::table('harganettoprodukbykelas_m as hnp')
            ->join ('mapruangantoproduk_m as mpr ','mpr.objectprodukfk','=','hnp.objectprodukfk')
//            ->join ('harganettoprodukbykelasd_m as hnpd','hnpd.objectprodukfk','=','mpr.objectprodukfk')
            ->join ('produk_m as prd','prd.id','=','mpr.objectprodukfk')
            ->join ('kelas_m as kls','kls.id','=','hnp.objectkelasfk')
            ->join ('ruangan_m as ru', 'ru.id','=','mpr.objectruanganfk')
            ->join ('suratkeputusan_m as sk', 'hnp.suratkeputusanfk','=','sk.id')
            ->select('mpr.objectprodukfk','prd.id','prd.namaproduk','hnp.hargasatuan','hnp.objectkelasfk',
                'kls.namakelas','mpr.objectruanganfk','ru.namaruangan',
                'prd.namaproduk'
            )



            ->where('mpr.objectruanganfk',$request['idRuangan'])
            ->where('hnp.objectkelasfk',$request['idKelas'])
            ->where('mpr.objectprodukfk',$request['idProduk'])
           ->where('hnp.objectjenispelayananfk',$request['idJenisPelayanan'])
            ->where('hnp.statusenabled',true)
            ->where('sk.statusenabled',true)
            ->where('mpr.statusenabled',true)
            ->where('hnp.kdprofile',$idProfile)
            ->where('prd.statusenabled',true);
        $data2 = $data2->distinct();
        $data2 = $data2->get();


        $result = array(
            'data' => $data,
            'data2' => $data2,
            'message' => 'ramdanegie',
            'edited' => 'as@epic',
        );

        return $this->respond($result);
    }

    public function updateDokterAll(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile =(int) $kdProfile;
        DB::beginTransaction();
        $noRegis = $request['noregistrasi'];
        $pelayananPasien = DB::select(DB::raw("select pp.norec,pp.tglpelayanan, prd.namaproduk,
                ppp.objectpegawaifk,apd.norec as norec_apd,pp.produkfk,  apd.objectruanganfk,ru.namaruangan
                from pasiendaftar_t as pd
                INNER JOIN antrianpasiendiperiksa_t as apd on apd.noregistrasifk =pd.norec
                INNER JOIN pelayananpasien_t as pp on apd.norec =pp.noregistrasifk
                inner join produk_m as prd on prd.id=pp.produkfk
                inner join detailjenisproduk_m as djp on djp.id=prd.objectdetailjenisprodukfk
                inner join jenisproduk_m as jp on jp.id=djp.objectjenisprodukfk
                inner join ruangan_m as ru on ru.id=apd.objectruanganfk
                LEFT JOIN pelayananpasienpetugas_t as ppp on ppp.pelayananpasien =pp.norec
                where ppp.objectpegawaifk is null and pd.kdprofile = $idProfile
                and pp.produkfk<>395 --karcis
                and ru.objectdepartemenfk in (18,28,24) --rajal
                and jp.id <> 97 --obat
                and pd.noregistrasi='$noRegis'"));

        try{
            foreach ($pelayananPasien as $item) {
                $PelPasienPetugas = new PelayananPasienPetugas();
                $PelPasienPetugas->norec = $PelPasienPetugas->generateNewId();
                $PelPasienPetugas->kdprofile = $idProfile;
                $PelPasienPetugas->statusenabled = true;
                $PelPasienPetugas->nomasukfk = $item->norec_apd;
                $PelPasienPetugas->objectjenispetugaspefk = 4; //dokter pemeriksa
                $PelPasienPetugas->objectpegawaifk = $request['objectpegawaifk'];
                $PelPasienPetugas->objectprodukfk = $item->produkfk;
                $PelPasienPetugas->objectruanganfk = $item->objectruanganfk;
                //            $PelPasienPetugas->deskripsitugasfungsi = $itemPPP['deskripsitugasfungsi'];
                //            $PelPasienPetugas->ispetugaspepjawab = $itemPPP['ispetugaspepjawab'];
                $PelPasienPetugas->pelayananpasien = $item->norec;
                $PelPasienPetugas->tglpelayanan = $item->tglpelayanan;
                $PelPasienPetugas->save();
            }

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
            $transMessage = "simpan PelPasien";
        }

        if ($transStatus == 'true') {
            $transMessage = "Simpan Pelayanan Petugas Sukses";
            DB::commit();
            $result = array(
                'status' => 201,
                'message' => $transMessage,
//                'result' => $PelPasienPetugas,
                'as' => 'ramdanegie',
            );
        } else {
            $transMessage = "Simpan Pelayanan Petugas Gagal";
            DB::rollBack();
            $result = array(
                'status' => 400,
                'message'  => $transMessage,
//                'result' => $PelPasienPetugas,
                'as' => 'ramdanegie',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function updateDokterppp(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile =(int) $kdProfile;
        DB::beginTransaction();
        $noRegis = $request['noregistrasi'];
        $pelayanan = DB::select(DB::raw("select pp.norec,pp.tglpelayanan, prd.namaproduk,
                ppp.objectpegawaifk,apd.norec as norec_apd,pp.produkfk,  apd.objectruanganfk,ru.namaruangan
                from pasiendaftar_t as pd
                INNER JOIN antrianpasiendiperiksa_t as apd on apd.noregistrasifk =pd.norec
                INNER JOIN pelayananpasien_t as pp on apd.norec =pp.noregistrasifk
                inner join produk_m as prd on prd.id=pp.produkfk
                inner join detailjenisproduk_m as djp on djp.id=prd.objectdetailjenisprodukfk
                inner join jenisproduk_m as jp on jp.id=djp.objectjenisprodukfk
                inner join ruangan_m as ru on ru.id=apd.objectruanganfk
                LEFT JOIN pelayananpasienpetugas_t as ppp on ppp.pelayananpasien =pp.norec
                where ppp.objectpegawaifk = 320272  and pd.kdprofile = $idProfile
                and pp.produkfk<>395 --karcis
                and ru.objectdepartemenfk in (18,28,24) --rajal
                and jp.id <> 97 --obat
                and pd.noregistrasi='$noRegis'"));

        try{
            foreach ($pelayanan as $item1) {
                $data = PelayananPasienPetugas::where('pelayananpasien', $item1->norec)->where('kdprofile', $idProfile)
                    ->update([
                        'objectpegawaifk' => $request['objectpegawaifk'],
                    ]);
            }

//
            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
            $transMessage = "update PelPasien";
        }

        if ($transStatus == 'true') {
            $transMessage = "Update Pelayanan Petugas Sukses";
            DB::commit();
            $result = array(
                'status' => 201,
                'message' => $transMessage,
                'as' => 'ramdanegie',
            );
        } else {
            $transMessage = "Update Pelayanan Petugas Gagal";
            DB::rollBack();
            $result = array(
                'status' => 400,
                'message'  => $transMessage,
                'as' => 'ramdanegie',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function getPegawaiByJnsPetugasByAPD(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile =(int) $kdProfile;
        if ($request['idJenisPetugas']==4){
            $data = \DB::table('mapjenispetugasptojenispegawai_m as mpp')
                ->join ('jenispegawai_m as jp','jp.id','=','mpp.objectjenispegawaifk')
                ->join ('pegawai_m as pg','pg.objectjenispegawaifk','=','jp.id')
                ->join ('antrianpasiendiperiksa_t as apd','apd.objectpegawaifk','=','pg.id')//pake pegawai antrian
                ->join ('pasiendaftar_t as pd','pd.norec','=','apd.noregistrasifk')
                ->join ('jenispetugaspelaksana_m as jpp','jpp.id','=','mpp.objectjenispetugaspefk')
                ->leftJoin('loginuser_s as lg','lg.objectpegawaifk','=','pg.id')
                ->select( 'mpp.objectjenispegawaifk','jp.jenispegawai','mpp.objectjenispetugaspefk' ,'jpp.jenispetugaspe',
                    'pg.namalengkap','pg.id','lg.id as idloginuser','lg.namauser'
                )
                ->where('mpp.objectjenispetugaspefk',$request['idJenisPetugas'])
                ->where('pd.noregistrasi',$request['noregistrasi'])
                ->where('mpp.kdprofile', $idProfile)
                ->where('mpp.statusenabled',true)
                ->where('pg.statusenabled',true)
                ->where('jpp.statusenabled',true);
        }else {
            $data = \DB::table('mapjenispetugasptojenispegawai_m as mpp')
                ->join('jenispegawai_m as jp', 'jp.id', '=', 'mpp.objectjenispegawaifk')
                ->join('pegawai_m as pg', 'pg.objectjenispegawaifk', '=', 'jp.id')
                ->join('jenispetugaspelaksana_m as jpp', 'jpp.id', '=', 'mpp.objectjenispetugaspefk')
                ->leftJoin('loginuser_s as lg', 'lg.objectpegawaifk', '=', 'pg.id')
                ->select('mpp.objectjenispegawaifk', 'jp.jenispegawai', 'mpp.objectjenispetugaspefk', 'jpp.jenispetugaspe',
                    'pg.namalengkap', 'pg.id', 'lg.id as idloginuser', 'lg.namauser'
                )
                ->where('mpp.objectjenispetugaspefk', $request['idJenisPetugas'])
                ->where('mpp.kdprofile', $idProfile)
                ->where('mpp.statusenabled', true)
                ->where('pg.statusenabled', true)
                ->where('jpp.statusenabled', true);
        }
        $data = $data->orderBy('pg.namalengkap', 'ASC');
        $data = $data->get();

        $result = array(
            'jenispelaksana' => $data,
            'message' => 'ramdanegie',
        );

        return $this->respond($result);
    }

    public function getKomponenHargaJasaMedis(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile =(int) $kdProfile;
        $data = \DB::table('harganettoprodukbykelasd_m as hnp')
            ->join ('mapruangantoproduk_m as mpr ','mpr.objectprodukfk','=','hnp.objectprodukfk')
            ->join ('produk_m as prd','prd.id','=','mpr.objectprodukfk')
            ->join ('komponenharga_m as kh','kh.id','=','hnp.objectkomponenhargafk')
            ->join ('kelas_m as kls','kls.id','=','hnp.objectkelasfk')
            ->join ('ruangan_m as ru', 'ru.id','=','mpr.objectruanganfk')
            ->select('hnp.objectkomponenhargafk','kh.komponenharga','hnp.hargasatuan','mpr.objectprodukfk')
            ->where('hnp.kdprofile', $idProfile)
            ->where('mpr.objectruanganfk',$request['idRuangan'])
            ->where('hnp.objectkelasfk',$request['idKelas'])
            ->where('mpr.objectprodukfk',$request['idProduk'])
            ->where('hnp.objectjenispelayananfk',$request['idJenisPelayanan'])
            ->where('hnp.objectkomponenhargafk',35)
            ->where('mpr.kodeexternal','2017')
            ->where('hnp.statusenabled', true);
        $data = $data->distinct();
        $data = $data->get();

        $result = array(
            'data' => $data,
            'message' => 'ea@epic',
        );

        return $this->respond($result);
    }

    public function getDataLogin(Request $request){
        $dataLogin = $request->all();
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile =(int) $kdProfile;
        $dataPegawaiUser = DB::select(DB::raw("select pg.id,pg.namalengkap,lu.objectkelompokuserfk from loginuser_s as lu
                INNER JOIN pegawai_m as pg on lu.objectpegawaifk=pg.id
                where lu.id=:idLoginUser and pg.kdprofile = $idProfile"),
            array(
                'idLoginUser' => $request['userData']['id'],
            )
        );

        $Dokter = SettingDataFixed::where('id',1171)
                  ->where('statusenabled',true)
                  ->select('nilaifield')
                  ->first();

        $Suster = SettingDataFixed::where('id',1170)
                ->where('statusenabled',true)
                ->select('nilaifield')
                ->first();

        $result = array(
            'data' => $dataPegawaiUser,
            'idlogindokter' => $Dokter['nilaifield'],
            'idloginsuster' => $Suster['nilaifield'],
            'message' => 'ea@epic',
        );

        return $this->respond($result);
    }
	public function getPelayananPasienNonDetail(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile =(int) $kdProfile;
		$data = \DB::table('pelayananpasien_t as pp')
			->join ('antrianpasiendiperiksa_t as apd ','pp.noregistrasifk','=','apd.norec')
			->join ('pasiendaftar_t as pd','apd.noregistrasifk','=','pd.norec')
			->join ('produk_m as pr','pp.produkfk','=','pr.id')
			->leftjoin ('detailjenisproduk_m as djp','djp.id','=','pr.objectdetailjenisprodukfk')
			->join ('ruangan_m as ru', 'ru.id','=','apd.objectruanganfk')
			->select('pp.norec as noRec','pp.strukfk as noRecStruk','pp.tglpelayanan as tglPelayanan',
				'pr.id as produkId','pp.hargasatuan as hargaSatuan','pp.harganetto as hargaNetto','pr.namaproduk as namaProduk',
				'djp.detailjenisproduk as detailJenisProduk','pp.jumlah','ru.namaruangan as namaRuangan')
            ->where('pd.kdprofile', $idProfile)
			->where('pd.norec',$request['norec_pd']);
		$data = $data->get();

		$result = array(
			'data' => $data,
			'message' => 'inhuman',
		);

		return $this->respond($result);
	}
	public function hapusPelayananPasien(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile =(int) $kdProfile;
		DB::beginTransaction();
		try{
			foreach ($request['dataDel'] as $item) {
				PelayananPasienDetail::where('pelayananpasien', $item['norec_pp'])->where('kdprofile', $idProfile)->delete();
				PelayananPasienPetugas::where('pelayananpasien', $item['norec_pp'])->where('kdprofile', $idProfile)->delete();
				PelayananPasien::where('norec', $item['norec_pp'])->where('kdprofile', $idProfile)->delete();
			}
			$transStatus = 'true';
		} catch (\Exception $e) {
			$transStatus = 'false';
		}
		if ($transStatus == 'true') {
			$transMessage = "Data Terhapus";
			DB::commit();
			$result = array(
				"status" => 201,
				"as" => 'inhuman',
			);
		} else {
			$transMessage = "Delete Pelayanan Pasien Gagal";
			DB::rollBack();
			$result = array(
				"status" => 400,
				"as" => 'inhuman',
			);
		}
		return $this->setStatusCode($result['status'])->respond($result, $transMessage);
	}

    public function hapusPelayananPasienTidakTerklaim(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile =(int) $kdProfile;
        DB::beginTransaction();
        $dataLogin = $request->all();
        $tglAyeuna = date('Y-m-d H:i:s');
        $dataPegawai = \DB::table('loginuser_s as lu')
            ->select('lu.objectpegawaifk')
            ->where('lu.kdprofile',$idProfile)
            ->where('lu.id',$dataLogin['userData']['id'])
            ->first();
        $PPnorec='';
        try{
            foreach ($request['pelayananpasiendelete'] as $item) {
                $dataAwal = PelayananPasienTidakTerklaim::where('norec', $item['norec_pptk']) ->where('lu.kdprofile',$idProfile)->first();
//                return $this->respond($dataAwal->hargadiscount);
                $PelPasien = new PelayananPasienTidakTerklaimDelete();
                $PelPasien->norec = $PelPasien->generateNewId();
                $PelPasien->kdprofile = $idProfile;
                $PelPasien->statusenabled = true;
                $PelPasien->objectloginuserfk =  $dataLogin['userData']['id'];
                $PelPasien->noregistrasifk = $item['norec_apd'];
                $PelPasien->tglregistrasi = $request['tglregistrasi'];
                $PelPasien->hargadiscount = (float)$dataAwal->hargadiscount;
                $PelPasien->hargajual = (float)$dataAwal->hargajual;
                $PelPasien->hargasatuan =(float)$dataAwal->hargasatuan;
                if (isset($dataAwal->jeniskemasanfk)) {
                    $PelPasien->jeniskemasanfk = $dataAwal->jeniskemasanfk;
                }
                if (isset($dataAwal->jasa)) {
                    $PelPasien->jasa = $dataAwal->jasa;
                }
                if (isset($PelPasien->rke)) {
                    $PelPasien->rke = $PelPasien->rke;
                }
                if (isset($PelPasien->strukresepfk)) {
                    $PelPasien->strukresepfk = $PelPasien->strukresepfk;
                }
                if (isset($PelPasien->satuanviewfk)) {
                    $PelPasien->satuanviewfk = $PelPasien->satuanviewfk;
                }
                if (isset($PelPasien->nilaikonversi)) {
                    $PelPasien->nilaikonversi = $PelPasien->nilaikonversi;
                }
                if (isset($PelPasien->strukterimafk)) {
                    $PelPasien->strukterimafk =  $PelPasien->strukterimafk;
                }
                if (isset($PelPasien->dosis)) {
                    $PelPasien->dosis = $PelPasien->dosis;
                }
                if (isset($PelPasien->nilaikonversi)) {
                    $PelPasien->nilaikonversi = $PelPasien->nilaikonversi;
                }
                if (isset($PelPasien->strukterimafk)) {
                    $PelPasien->strukterimafk =  $PelPasien->strukterimafk;
                }
                $PelPasien->jumlah = $item['jumlah'];
                $PelPasien->kelasfk = $dataAwal->kelasfk;
                $PelPasien->kdkelompoktransaksi = 124;
                if (isset($dataAwal->keteranganlain)) {
                    $PelPasien->keteranganlain = $dataAwal->keteranganlain;
                }
                $PelPasien->piutangpenjamin = 0;
                $PelPasien->piutangrumahsakit = 0;
                $PelPasien->produkfk = $dataAwal->produkfk;
                $PelPasien->stock = 1;
                $PelPasien->tglpelayanan = $item['tglPelayanan'];
                $PelPasien->harganetto = (float)$dataAwal->harganetto;
                $PelPasien->pelayananpasientidakterklaim = $item['norec_pptk'];
                $PelPasien->nomasukfk = $item['norec_pd'];
                $PelPasien->tgldelete = $tglAyeuna;
                $PelPasien->save();
                $PPnorec = $PelPasien->norec;

                PelayananPasienTidakTerklaim::where('norec', $item['norec_pptk'])->delete();
            }

                $dataLogin = $request->all();
                $newId = LoggingUser::max('id');
                $newId = $newId +1;
                $logUser = new LoggingUser();
                $logUser->id = $newId;
                $logUser->norec = $logUser->generateNewId();
                $logUser->kdprofile= $idProfile;
                $logUser->statusenabled=true;
                $logUser->jenislog ='Hapus Layanan Tidak Terklaim';
                $logUser->noreff = $PPnorec;
                $logUser->referensi='norec PelayananPasienTidakTerklaimDelete';
//                $logUser->keterangan=$request['keterangan'];
                $logUser->objectloginuserfk =  $dataLogin['userData']['id'];
                $logUser->tanggal = $tglAyeuna;
                $logUser->save();


            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
        }
        if ($transStatus == 'true') {
            $transMessage = "Data Terhapus";
            DB::commit();
            $result = array(
                "status" => 201,
                "as" => 'ea@epic',
            );
        } else {
            $transMessage = "Delete Pelayanan Pasien Tidak Terklaim Gagal";
            DB::rollBack();
            $result = array(
                "status" => 400,
                "as" => 'ea@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function saveTindakanTidakTerklaim(Request $request) {
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile =(int) $kdProfile;
        DB::beginTransaction();
        $dataLogin = $request->all();
        $tglAyeuna = date('Y-m-d H:i:s');
        $dataPegawai = \DB::table('loginuser_s as lu')
            ->select('lu.objectpegawaifk')
            ->where('lu.id',$dataLogin['userData']['id'])
            ->first();
        try{
            $PPnorec ="";
            $TeuKasep=0;
            foreach ($request['pelayananpasien'] as $item) {
                $dataAwal = PelayananPasien::where('norec', $item['norec'])->first();
//
                $dataPelayananTakTerklaim = PelayananPasienTidakTerklaim::where('pelayananpasien', $item['norec'])->where('kdprofile', $idProfile)->first();

//                return $this->respond($dataPelayananTakTerklaim == '');

                if ($dataPelayananTakTerklaim == '' || $dataPelayananTakTerklaim == null){
                    $PelPasien = new PelayananPasienTidakTerklaim();
                    $PelPasien->norec = $PelPasien->generateNewId();
                    $PelPasien->kdprofile = $idProfile;
                    $PelPasien->statusenabled = true;
                    $PelPasien->noregistrasifk = $item['norec_apd'];
                    $PelPasien->tglregistrasi = $request['tglregistrasi'];
                    $PelPasien->hargadiscount = $dataAwal->hargadiscount;
                    $PelPasien->hargajual = $dataAwal->hargajual;
                    $PelPasien->hargasatuan =$dataAwal->hargasatuan;
                    if (isset($dataAwal->jeniskemasanfk)) {
                        $PelPasien->jeniskemasanfk = $dataAwal->jeniskemasanfk;
                    }
                    if (isset($dataAwal->jasa)) {
                        $PelPasien->jasa = $dataAwal->jasa;
                    }
                    if (isset($PelPasien->rke)) {
                        $PelPasien->rke = $PelPasien->rke;
                    }
                    if (isset($PelPasien->strukresepfk)) {
                        $PelPasien->strukresepfk = $PelPasien->strukresepfk;
                    }
                    if (isset($PelPasien->satuanviewfk)) {
                        $PelPasien->satuanviewfk = $PelPasien->satuanviewfk;
                    }
                    if (isset($PelPasien->nilaikonversi)) {
                        $PelPasien->nilaikonversi = $PelPasien->nilaikonversi;
                    }
                    if (isset($PelPasien->strukterimafk)) {
                        $PelPasien->strukterimafk =  $PelPasien->strukterimafk;
                    }
                    if (isset($PelPasien->dosis)) {
                        $PelPasien->dosis = $PelPasien->dosis;
                    }
                    if (isset($PelPasien->nilaikonversi)) {
                        $PelPasien->nilaikonversi = $PelPasien->nilaikonversi;
                    }
                    if (isset($PelPasien->strukterimafk)) {
                        $PelPasien->strukterimafk =  $PelPasien->strukterimafk;
                    }
                    $PelPasien->jumlah = $item['jumlah'];
                    $PelPasien->kelasfk = $dataAwal->kelasfk;
                    $PelPasien->kdkelompoktransaksi = 124;
                    if (isset($dataAwal->keteranganlain)) {
                        $PelPasien->keteranganlain = $dataAwal->keteranganlain;
                    }
                    $PelPasien->piutangpenjamin = 0;
                    $PelPasien->piutangrumahsakit = 0;
                    $PelPasien->produkfk = $dataAwal->produkfk;
                    $PelPasien->stock = 1;
                    $PelPasien->tglpelayanan = $item['tglPelayanan'];
                    $PelPasien->harganetto = $dataAwal->harganetto;
                    $PelPasien->pelayananpasien = $item['norec'];
                    $PelPasien->nomasukfk = $item['norec_pd'];
                    $PelPasien->save();
                    $PPnorec = $PelPasien->norec;

                    //## Logging User
                    $newId = LoggingUser::max('id');
                    $newId = $newId +1;
                    $logUser = new LoggingUser();
                    $logUser->id = $newId;
                    $logUser->norec = $logUser->generateNewId();
                    $logUser->kdprofile= $idProfile;
                    $logUser->statusenabled=true;
                    $logUser->jenislog = 'Klaim Tindakan Tidak Terklaim';
                    $logUser->noreff =$PPnorec;
                    $logUser->referensi='norec pelayananpasientidakterklaim';
                    $logUser->objectloginuserfk =  $dataLogin['userData']['id'];
                    $logUser->tanggal = $tglAyeuna;
                    $logUser->save();

                }else{
                    $TeuKasep=$TeuKasep+1;
                }

            }
            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
            $transMessage = "Simpan PelPasien";
        }

        if ($transStatus == 'true') {
            $transMessage = "Simpan Sukses";
            DB::commit();
            $result = array(
                'status' => 201,
                'message' => $transMessage,
                'dataPP' => $PPnorec,
                'datataktersimpan' => $TeuKasep,
                'as' => 'ea@epic',
            );
        } else {
            $transMessage = "Simpan Gagal";
            DB::rollBack();
            $result = array(
                'status' => 400,
                'message'  => $transMessage,
                'dataPP' => $PPnorec,
                'datataktersimpan' => $TeuKasep,
                'as' => 'ea@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function getRiwayarRuanganPerAntrian(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $data = \DB::table('pasiendaftar_t as pd')
            ->join ('antrianpasiendiperiksa_t as apd','apd.noregistrasifk','=','pd.norec')
            ->join ('pelayananpasien_t as pp','pp.noregistrasifk','=','apd.norec')
            ->join ('produk_m as pr','pp.produkfk','=','pr.id')
            ->leftJoin('detailjenisproduk_m as djp','djp.id','=','pr.objectdetailjenisprodukfk')
            ->join ('ruangan_m as ru', 'ru.id','=','apd.objectruanganfk')
            ->select(DB::raw("pd.noregistrasi,pd.tglregistrasi,
                     pp.tglpelayanan,pp.produkfk,pr.namaproduk,pp.jumlah,pp.hargajual,
                     case when pp.hargadiscount is null then 0 else pp.hargadiscount end as diskon,
                     ru.namaruangan,(pp.hargajual - (case when pp.hargadiscount is null then 0 else pp.hargadiscount end) * pp.jumlah) as subtotal"))
            ->whereNotIn('djp.objectjenisprodukfk',[97])
            ->where('pd.kdprofile', $idProfile)
            ->where('ru.namaruangan','ilike','%'.  $request['NamaRuangan'].'%')
            ->where('pd.noregistrasi',$request['NoRegistrasi'])
            ->get();

        $result = array(
            'data' => $data,
            'message' => 'ea@epic',
        );

        return $this->respond($result);
    }

    public function GetPegawaiPenginputTindakan(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $dataLogin = $request->all();
        $dataPegawai = \DB::table('loginuser_s as lu')
            ->select('lu.objectpegawaifk')
            ->where('lu.id',$dataLogin['userData']['id'])
            ->first();

        $data = \DB::table('pasiendaftar_t as pd')
            ->join ('antrianpasiendiperiksa_t as apd','apd.noregistrasifk','=','pd.norec')
            ->join ('pelayananpasien_t as pp','pp.noregistrasifk','=','apd.norec')
            ->leftJoin('logginguser_t as lg','lg.noreff','=','pp.norec')
            ->join ('loginuser_s as lu','lu.id','=','lg.objectloginuserfk')
            ->join ('pegawai_m as pg','pg.id','=','lu.objectpegawaifk')
            ->select(DB::raw("lu.objectpegawaifk,pg.namalengkap"))
            ->where('pd.kdprofile', $idProfile)
            ->where('pp.norec',$request['norec_pp'])
            ->get();

        $result = array(
            'data' => $data,
            'datalogin'=>$dataPegawai,
            'message' => 'ea@epic',
        );

        return $this->respond($result);
    }
    public function getTindakanPart(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        //TODO : GET LIST TINDAKAN
        $req = $request->all();
         $data = \DB::table('mapruangantoproduk_m as mpr')
//            ->join ('harganettoprodukbykelasd_m as hnpd','hnpd.objectprodukfk','=','mpr.objectprodukfk')
            ->join ('produk_m as prd','prd.id','=','mpr.objectprodukfk')
//            ->join ('kelas_m as kls','kls.id','=','hnp.objectkelasfk')
//            ->join ('ruangan_m as ru', 'ru.id','=','mpr.objectruanganfk')
//            ->join ('suratkeputusan_m as sk', 'hnp.suratkeputusanfk','=','sk.id')
//            ->select('mpr.objectprodukfk','prd.id','prd.namaproduk','hnp.hargasatuan','hnp.objectkelasfk',
//                'kls.namakelas','mpr.objectruanganfk','ru.namaruangan',
             ->select('mpr.objectprodukfk as id','prd.namaproduk',
                 'mpr.objectruanganfk',
            'prd.namaproduk'
            )
            ->where('mpr.kdprofile', $idProfile)
            ->where('mpr.objectruanganfk',$request['idRuangan'])
//            ->where('hnp.objectkelasfk',$request['idKelas'])
//           ->where('hnp.objectjenispelayananfk',$request['idJenisPelayanan'])
//            ->where('mpr.statusenabled',true)
//            ->where('hnp.statusenabled',true)
//            ->where('sk.statusenabled',true)
            ->where('mpr.statusenabled',true)
       
            // ->where('sk.statusenabled',true)
            ->where('prd.statusenabled',true)
            // ->where('mpr.kodeexternal','2017')
            // ->where('hnp.kodeexternal', '2017')
        ;


      
        if(isset($req['filter']['filters'][0]['value']) &&
            $req['filter']['filters'][0]['value']!="" &&
            $req['filter']['filters'][0]['value']!="undefined"){
            $data = $data
                ->where('prd.namaproduk','ilike','%'.$req['filter']['filters'][0]['value'].'%' );
        }
        $data = $data->orderBy('prd.namaproduk', 'ASC');
        $data = $data->take(15);
        $data = $data->get();
        // $result = array(
        //     'data' => $data,
        //     'message' => 'ramdanegie',
        // );

     
        return $this->respond($data);
    }
    public function getJenisPelayananByNorecPd(Request $request){
        $norec_pd = ['norec_pd'];
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
         $data = \DB::table('pasiendaftar_t as pd')
            ->select('pd.*')
            ->where('pd.norec',$norec_pd)
             ->where('pd.kdprofile', $idProfile)
            ->first();
        return $this->respond($data);
    }
    public function getPostingTgl(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $data = DB::select(DB::raw("
              select max(pjt.tglbuktitransaksi)   + interval '-30' day as max from postingjurnal_t as pj
                INNER JOIN postingjurnaltransaksi_t as pjt on pjt.nojurnal_intern=pj.norecrelated
                 where pj.norecrelated like '%PN%' and  RIGHT(pj.norecrelated,5) in ('00001','00002')
          ")
        );
//        $data = DB::select(DB::raw("
//              -- select now()  + interval '-26' day as max
//                   SELECT  DATEADD(month, -1, GETDATE()) AS max
//          ")
//        );
        $tgl = $data[0]->max;
        $tgl = $data[0]->max;
        $datadate = DB::select(DB::raw("

            --select distinct DAY( pjt.tglbuktitransaksi) as tgl  from postingjurnal_t as pj
            --INNER JOIN postingjurnaltransaksi_t as pjt on pjt.nojurnal_intern=pj.norecrelated
            --where pj.norecrelated ilike '%PN%' and RIGHT(pj.norecrelated,5) in ('00001','00002') 
            --and pjt.tglbuktitransaksi >  '$tgl' and pj.kdprofile = $idProfile;
             
                select distinct to_char(pjt.tglbuktitransaksi,'dd') as tgl   from postingjurnal_t as pj
             INNER JOIN postingjurnaltransaksi_t as pjt on pjt.nojurnal_intern=pj.norecrelated
                where pj.norecrelated like '%PN%' and  RIGHT(pj.norecrelated,5) in ('00001','00002') 
                and pjt.tglbuktitransaksi > '$tgl';
          ")
        );

        $arrtgl = [] ;
        foreach ($datadate as $item){
            $arrtgl[] = (int)$item->tgl;
        }

        $status=array(
            'mindate' => $data,
            'datedate' => $arrtgl,
        );

        return $this->respond($status);
    }
    public function getHeaderInputTindakan(Request $request){
        $norec_pd=$request['norec_pd'];
        $norec_apd=$request['norec_apd'];
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $data = \DB::table('pasiendaftar_t as pd')
            ->join('antrianpasiendiperiksa_t as apd','apd.noregistrasifk','=','pd.norec')
            ->join('pasien_m as ps','ps.id','=','pd.nocmfk')
            ->leftjoin('jeniskelamin_m as jk','jk.id','=','ps.objectjeniskelaminfk')
            ->leftjoin('alamat_m as alm','alm.nocmfk','=','ps.id')
            ->leftjoin('pendidikan_m as pdd','pdd.id','=','ps.objectpendidikanfk')
            ->leftjoin('pekerjaan_m as pk','pk.id','=','ps.objectpekerjaanfk')
            ->leftjoin('kelompokpasien_m as kps','kps.id','=','pd.objectkelompokpasienlastfk')
            ->leftjoin('rekanan_m as rk','rk.id','=','pd.objectrekananfk')
            ->join('ruangan_m as ru','ru.id','=','apd.objectruanganfk')
            ->join('departemen_m as dpm','dpm.id','=','ru.objectdepartemenfk')
            ->leftjoin('pegawai_m as peg','peg.id','=','pd.objectpegawaifk')
            ->join('kelas_m as kls','kls.id','=','apd.objectkelasfk')
            ->LEFTjoin('jenispelayanan_m as jpl','jpl.kodeinternal','=','pd.jenispelayanan')
            ->select('ps.nocm','ps.id as nocmfk','ps.noidentitas','ps.namapasien','pd.noregistrasi', 'pd.tglregistrasi','jk.jeniskelamin',
                'ps.tgllahir','alm.alamatlengkap','pdd.pendidikan','pk.pekerjaan','ps.nohp as notelepon','ps.objectjeniskelaminfk',
                'apd.objectruanganfk', 'ru.namaruangan', 'apd.norec as norec_apd','pd.norec as norec_pd',
                'kps.kelompokpasien','kls.namakelas','apd.objectkelasfk','pd.objectkelompokpasienlastfk','pd.objectrekananfk',
                'rk.namarekanan','pd.objectruanganlastfk','jpl.jenispelayanan','apd.objectasalrujukanfk',
                'ru.kdinternal','jpl.kodeinternal as objectjenispelayananfk','pd.objectpegawaifk','pd.statuspasien',
                'ps.nobpjs','pd.statuspasien',
                DB::raw('case when ru.objectdepartemenfk in (16,35,17) then \'true\' else \'false\' end as israwatinap')
            )
            ->where('pd.kdprofile', $idProfile)
            ->where('pd.norec','=',$norec_pd)
            ->where('apd.norec','=',$norec_apd)
            ->get();
//           try {
//                   $umur = $this->hitungUmur($data->tgllahir);
//               } catch (\Exception $e) {
//                   $umur = '-';
//               }
        return $this->respond($data);


    }

    public function getMutu(Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        //TODO : GET LIST TINDAKAN
        $req = $request->all();
         $data = \DB::table('mapruangantopelayananmutu_m as mpr')
//            ->join ('harganettoprodukbykelasd_m as hnpd','hnpd.objectprodukfk','=','mpr.objectprodukfk')
            ->join ('pelayananmutu_m as prd','prd.id','=','mpr.objectpelayananmutufk')
//            ->join ('kelas_m as kls','kls.id','=','hnp.objectkelasfk')
//            ->join ('ruangan_m as ru', 'ru.id','=','mpr.objectruanganfk')
//            ->join ('suratkeputusan_m as sk', 'hnp.suratkeputusanfk','=','sk.id')
//            ->select('mpr.objectprodukfk','prd.id','prd.namaproduk','hnp.hargasatuan','hnp.objectkelasfk',
//                'kls.namakelas','mpr.objectruanganfk','ru.namaruangan',
             ->select('mpr.objectpelayananmutufk as id','prd.pelayananmutu',
                 'mpr.objectruanganfk',
            'prd.pelayananmutu'
            )
            ->where('mpr.kdprofile', $idProfile)
            ->where('mpr.objectruanganfk',$request['idRuangan'])
//            ->where('hnp.objectkelasfk',$request['idKelas'])
//           ->where('hnp.objectjenispelayananfk',$request['idJenisPelayanan'])
//            ->where('mpr.statusenabled',true)
//            ->where('hnp.statusenabled',true)
//            ->where('sk.statusenabled',true)
            ->where('mpr.statusenabled',true)
       
            // ->where('sk.statusenabled',true)
            ->where('prd.statusenabled',true)
            // ->where('mpr.kodeexternal','2017')
            // ->where('hnp.kodeexternal', '2017')
        ;


      
        if(isset($req['filter']['filters'][0]['value']) &&
            $req['filter']['filters'][0]['value']!="" &&
            $req['filter']['filters'][0]['value']!="undefined"){
            $data = $data
                ->where('prd.pelayananmutu','ilike','%'.$req['filter']['filters'][0]['value'].'%' );
        }
        $data = $data->orderBy('prd.pelayananmutu', 'ASC');
        $data = $data->take(15);
        $data = $data->get();
        // $result = array(
        //     'data' => $data,
        //     'message' => 'ramdanegie',
        // );

     
        return $this->respond($data);
    }

    public function saveMutu (Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile =(int) $kdProfile;
        DB::beginTransaction();
        $dataLogin = $request->all();
        try{
            foreach ($request['pelayananpasien'] as $item){
                $mutu = new PelayananPasienMutu();
                $mutu->norec = $mutu->generateNewId();
                $mutu->statusenabled = true;
                $mutu->kdprofile = $idProfile;
                $mutu->norec_apd = $item['norec_apd'];
                $mutu->norec_pd = $item['norec_pd'];
                $mutu->tglregistrasi = $item['tglregistrasi'];
                $mutu->tglpelayanan = $item['tglpelayanan'];
                $mutu->jumlah = $item['jumlah'];
                $mutu->pelayananmutufk = $item['pelayananmutu'];
                $mutu->objectpegawaifk = $item['pegawai'];
                $mutu->save();
            }

            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
            $transMessage = "simpan PelPasien";
        }

        if ($transStatus == 'true') {
            $transMessage = "Simpan Sukses";
            DB::commit();
            $result = array(
                'status' => 201,
                'message' => $transMessage,
                'as' => 'ea@epic',
            );
        } else {
            $transMessage = "Simpan Gagal";
            DB::rollBack();
            $result = array(
                'status' => 400,
                'message'  => $transMessage,
                'as' => 'ea@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

    public function getRiwayatMutu  (Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        $data = \DB::table('pelayananpasienmutu_t as ppm')
                ->join('pelayananmutu_m as pm','pm.id','=','ppm.pelayananmutufk')
                ->join('pegawai_m as p','p.id','=','ppm.objectpegawaifk')
                ->select('ppm.norec','ppm.tglpelayanan','p.namalengkap','pm.pelayananmutu','ppm.jumlah')
                ->where('ppm.norec_pd',$request['norec_pd'])
                ->where('ppm.statusenabled',true)
                ->where('ppm.kdprofile',$idProfile)
                ->orderBy('ppm.tglpelayanan','asc')
                ->get();

        $result = array(
            'data' => $data,
            'message' => 'ea@epic',
        );
        return $this->respond($data);
    }

    public function delMutu (Request $request){
        $kdProfile = $this->getDataKdProfile($request);
        $idProfile = (int) $kdProfile;
        DB::beginTransaction();
        $dataLogin = $request->all();
        try{
        $data = PelayananPasienMutu::where('norec',$request['objSave']['norec'])
                ->update([
                'statusenabled' => 0
            ]);
            $transStatus = 'true';
        } catch (\Exception $e) {
            $transStatus = 'false';
            $transMessage = "simpan PelPasien";
        }

        if ($transStatus == 'true') {
            $transMessage = "Simpan Sukses";
            DB::commit();
            $result = array(
                'status' => 201,
                'message' => $transMessage,
                'as' => 'ea@epic',
            );
        } else {
            $transMessage = "Simpan Gagal";
            DB::rollBack();
            $result = array(
                'status' => 400,
                'message'  => $transMessage,
                'as' => 'ea@epic',
            );
        }
        return $this->setStatusCode($result['status'])->respond($result, $transMessage);
    }

}